<?php

declare(strict_types=1);

namespace Drupal\farm_flag\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_field\FarmFieldFactoryInterface;
use Drupal\farm_flag\Form\EntityFlagActionForm;
use Drupal\farm_flag\Routing\EntityFlagActionRouteProvider;

/**
 * Hook implementations for farm_flag.
 */
class FarmFlagHooks {

  use AutowireTrait;

  public function __construct(
    protected FarmFieldFactoryInterface $farmFieldFactory,
  ) {}

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    $fields = [];

    // Add flag field to farmOS entities.
    if (in_array($entity_type->id(), ['asset', 'log', 'plan'])) {
      $field_info = [
        'type' => 'list_string',
        'label' => t('Flags'),
        'description' => t('Add flags to enable better sorting and filtering of records.'),
        'allowed_values_function' => 'farm_flag_field_allowed_values',
        'multiple' => TRUE,
        'weight' => [
          'form' => -75,
          'view' => -75,
        ],
      ];
      $fields['flag'] = $this->farmFieldFactory->baseFieldDefinition($field_info);
    }

    return $fields;
  }

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type) {

    // Define common asset, log, and plan region items on behalf of core modules.
    switch ($entity_type) {
      case 'asset':
      case 'log':
      case 'plan':
        return [
          'second' => [
            'flag',
          ],
        ];

      default:
        return [];
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'field__flag' => [
        'base hook' => 'field',
        'initial preprocess' => static::class . '::preprocessFieldFlag',
      ],
    ];
  }

  /**
   * Prepares variables for field--flag templates.
   *
   * Adds classes to each flag wrapper.
   *
   * Default template: field--flag.html.twig.
   *
   * @param array $variables
   *   An associative array containing:
   *   - element: An associative array containing render arrays for the list of
   *     flags.
   */
  public function preprocessFieldFlag(array &$variables) {

    // Preprocess list_string flag fields.
    if ($variables['element']['#field_type'] == 'list_string') {

      /** @var \Drupal\Core\Field\FieldItemListInterface $items */
      $items = $variables['element']['#items'];

      // Add classes to each flag.
      foreach ($items as $key => $list_item) {
        $classes = ['flag', 'flag--' . $list_item->getString()];
        $variables['items'][$key]['attributes']->addClass($classes);
      }
    }
  }

  /**
   * Implements hook_entity_type_build().
   */
  #[Hook('entity_type_build')]
  public function entityTypeBuild(array &$entity_types) {

    // Enable the entity flag action on entity types with a flag field.
    foreach (['asset', 'log', 'plan'] as $entity_type) {
      if (!empty($entity_types[$entity_type])) {
        $route_providers = $entity_types[$entity_type]->getRouteProviderClasses();
        $route_providers['flag'] = EntityFlagActionRouteProvider::class;
        $entity_types[$entity_type]->setHandlerClass('route_provider', $route_providers);
        $entity_types[$entity_type]->setLinkTemplate('flag-action-form', '/' . $entity_type . '/flag');
        $entity_types[$entity_type]->setFormClass('flag-action-form', EntityFlagActionForm::class);
      }
    }
  }

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    return [
      'flag',
    ];
  }

}
