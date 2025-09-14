<?php

declare(strict_types=1);

namespace Drupal\farm_log_category\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_field\FarmFieldFactoryInterface;

/**
 * Hook implementations for farm_log_category.
 */
class FarmLogCategoryHooks {

  use AutowireTrait;

  public function __construct(
    protected FarmFieldFactoryInterface $farmFieldFactory,
  ) {}

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    // Add category base field to all log types.
    $fields = [];
    if ($entity_type->id() == 'log') {
      $category_info = [
        'type' => 'entity_reference',
        'label' => t('Log category'),
        'description' => t('Use this to organize your logs into categories for easier searching and filtering later.'),
        'target_type' => 'taxonomy_term',
        'target_bundle' => 'log_category',
        'multiple' => TRUE,
        'weight' => [
          'view' => 80,
        ],
        'form_display_options' => [
          'type' => 'options_select',
          'weight' => 10,
        ],
      ];
      $fields['category'] = $this->farmFieldFactory->baseFieldDefinition($category_info);
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
      case 'log':
        return [
          'second' => [
            'category',
          ],
        ];

      default:
        return [];
    }
  }

}
