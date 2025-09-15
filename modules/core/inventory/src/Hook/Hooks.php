<?php

declare(strict_types=1);

namespace Drupal\farm_inventory\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\farm_field\FarmFieldFactoryInterface;
use Drupal\farm_inventory\Field\AssetInventoryItemList;

/**
 * Hook implementations for farm_inventory.
 */
class Hooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    protected FarmFieldFactoryInterface $farmFieldFactory,
  ) {}

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {

    // Add inventory base fields to entity types.
    if ($entity_type->id() == 'asset') {
      return $this->assetFields();
    }
    elseif ($entity_type->id() == 'quantity') {
      return $this->quantityFields();
    }
    return [];
  }

  /**
   * Implements hook_inline_entity_form_entity_form_alter().
   */
  #[Hook('inline_entity_form_entity_form_alter')]
  public function inlineEntityFormEntityFormAlter(array &$entity_form, FormStateInterface &$form_state) {

    // Bail if not a quantity inline entity form.
    if ($entity_form['#entity_type'] !== 'quantity') {
      return;
    }

    // Specify special validation for the inventory values.
    // Validation is needed because we cannot solely rely on FAPI #states,
    // partially because it is hard to target the entity browser form widget.
    $entity_form['#element_validate'][] = [self::class, 'quantityEntityInlineFormValidate'];

    // Set the inventory_adjustment default value to N/A unless already
    // provided.
    if (empty($entity_form['inventory_adjustment']['widget']['#default_value'])) {
      $entity_form['inventory_adjustment']['widget']['#default_value'] = '_none';
    }

    // Build a selector for the inventory adjustment input.
    // This is complicated because the input name depends on the delta value,
    // and whether or not it is an existing entity in the inline entity form.
    $parents = $entity_form['#parents'];
    $adjustment_identifier = $parents[0] . '[' . implode('][', array_slice($parents, 1)) . '][inventory_adjustment]';
    $inventory_adjustment_selector = ":input[name=\"{$adjustment_identifier}\"]";

    // Hide the inventory asset selector until an adjustment is selected.
    $entity_form['inventory_asset']['#states']['invisible'] = [
      $inventory_adjustment_selector => [
        'value' => '_none',
      ],
    ];
  }

  /**
   * Custom validation callback for the quantity inline form.
   *
   * @param array $form
   *   The entity form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The entity form state.
   */
  public static function quantityEntityInlineFormValidate(array &$form, FormStateInterface $form_state): void {

    // Get the inline entity form values out of the entire entity form state.
    $quantity_form_values = $form_state->getValue($form['#parents']);

    // If a quantity was provided, validate correct inventory values are
    // provided.
    if (!empty($quantity_form_values)) {
      $adjustment = $quantity_form_values['inventory_adjustment'];
      $asset = $quantity_form_values['inventory_asset']['target_id'];

      // Set error if an adjustment is provided without an asset.
      if (!empty($adjustment) && empty($asset)) {
        // Error is set on the inventory_adjustment field because form errors
        // are not highlighted when set on the entity browser widget.
        $form_state->setError($form['inventory_adjustment']['widget'], t('Inventory asset is required if an inventory adjustment is selected.'));
      }

      // Set error if an asset is provided without an adjustment.
      if (empty($adjustment) && !empty($asset)) {
        $form_state->setError($form['inventory_adjustment']['widget'], t('Inventory adjustment is required if an inventory asset is selected.'));
      }
    }
  }

  /**
   * Define quantity inventory base fields.
   *
   * @return array
   *   Returns an array of field information for use with farm_field.factory.
   */
  private function assetFields(): array {
    $field_info = [
      'inventory' => [
        'type' => 'inventory',
        'label' => $this->t('Current inventory'),
        'multiple' => TRUE,
        'computed' => AssetInventoryItemList::class,
        'hidden' => 'form',
        'weight' => [
          'view' => 94,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

  /**
   * Define quantity inventory base fields.
   *
   * @return array
   *   Returns an array of field information for use with farm_field.factory.
   */
  private function quantityFields(): array {
    $field_info = [
      'inventory_adjustment' => [
        'type' => 'list_string',
        'label' => $this->t('Inventory adjustment'),
        'description' => $this->t('What type of inventory adjustment is this?'),
        'allowed_values' => [
          'increment' => $this->t('Increment'),
          'decrement' => $this->t('Decrement'),
          'reset' => $this->t('Reset'),
        ],
        'multiple' => FALSE,
        'weight' => [
          'form' => 50,
          'view' => 50,
        ],
      ],
      'inventory_asset' => [
        'type' => 'entity_reference',
        'label' => $this->t('Inventory asset'),
        'description' => $this->t('Which asset will this adjust the inventory of?'),
        'target_type' => 'asset',
        'multiple' => FALSE,
        'weight' => [
          'form' => 51,
          'view' => 51,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }
    return $fields;
  }

  /**
   * Implements hook_preprocess_HOOK().
   */
  #[Hook('preprocess_quantity')]
  public function preprocessQuantity(array &$variables) {

    /** @var \Drupal\quantity\Entity\QuantityInterface $quantity */
    $quantity = $variables['elements']['#quantity'];
    if (!empty($variables['content']['inventory_adjustment']) && !empty($variables['content']['inventory_asset'])) {

      // Do not render the inventory fields themselves.
      unset($variables['content']['inventory_adjustment']);
      unset($variables['content']['inventory_asset']);

      // Get the adjustment label.
      $adjustment_field = $quantity->get('inventory_adjustment');
      $adjustment_values = $adjustment_field->getFieldDefinition()->getSetting('allowed_values');
      $adjustment = $adjustment_values[$adjustment_field->value];

      // Get the inventory asset.
      $assets = $quantity->get('inventory_asset')->referencedEntities();
      $asset = reset($assets);

      // Render array of inventory info to display after the quantity.
      $inventory = [
        '#prefix' => '<span class="inventory">',
        '#suffix' => '</span>',
        '#markup' => '(' . $this->t('@adjustment <a href=":url">@asset</a> inventory', ['@adjustment' => $adjustment, ':url' => $asset->toUrl()->toString(), '@asset' => $asset->label()]) . ')',
        '#weight' => 5,
      ];
      $variables['content']['inventory'] = $inventory;
    }
  }

}
