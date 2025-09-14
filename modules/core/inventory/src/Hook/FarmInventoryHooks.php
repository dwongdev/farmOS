<?php

declare(strict_types=1);

namespace Drupal\farm_inventory\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_inventory\Field\AssetInventoryItemList;

/**
 * Hook implementations for farm_inventory.
 */
class FarmInventoryHooks {

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
    $entity_form['#element_validate'][] = 'farm_inventory_quantity_entity_inline_form_validate';

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
   * Define quantity inventory base fields.
   *
   * @return array
   *   Returns an array of field information for use with farm_field.factory.
   */
  private function assetFields(): array {
    $field_info = [
      'inventory' => [
        'type' => 'inventory',
        'label' => t('Current inventory'),
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
      $fields[$name] = \Drupal::service('farm_field.factory')->baseFieldDefinition($info);
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
        'label' => t('Inventory adjustment'),
        'description' => t('What type of inventory adjustment is this?'),
        'allowed_values' => [
          'increment' => t('Increment'),
          'decrement' => t('Decrement'),
          'reset' => t('Reset'),
        ],
        'multiple' => FALSE,
        'weight' => [
          'form' => 50,
          'view' => 50,
        ],
      ],
      'inventory_asset' => [
        'type' => 'entity_reference',
        'label' => t('Inventory asset'),
        'description' => t('Which asset will this adjust the inventory of?'),
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
      $fields[$name] = \Drupal::service('farm_field.factory')->baseFieldDefinition($info);
    }
    return $fields;
  }

}
