<?php

declare(strict_types=1);

namespace Drupal\farm_group\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_group.
 */
class FarmGroupHooks {

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    \Drupal::moduleHandler()->loadInclude('farm_group', 'inc', 'farm_group.base_fields');
    switch ($entity_type->id()) {
      // Build asset base fields.
      case 'asset':
        return farm_group_asset_base_fields();

      // Build log base fields.
      case 'log':
        return farm_group_log_base_fields();

      default:
        return [];
    }
  }

  /**
   * Implements hook_entity_base_field_info_alter().
   */
  #[Hook('entity_base_field_info_alter')]
  public function entityBaseFieldInfoAlter(&$fields, EntityTypeInterface $entity_type) {
    /** @var \Drupal\field\Entity\FieldConfig[] $fields */
    // Prevent creating circular group memberships.
    if ($entity_type->id() == 'log' && !empty($fields['asset'])) {
      $fields['asset']->addConstraint('CircularGroupMembership');
    }
  }

  /**
   * Implements hook_farm_import_csv_base_fields().
   */
  #[Hook('farm_import_csv_base_fields')]
  public function farmImportCsvBaseFields(string $entity_type) {
    $base_fields = [];
    // Add group and is_group_assignment base fields to log CSV importers.
    if ($entity_type == 'log') {
      $base_fields[] = 'group';
      $base_fields[] = 'is_group_assignment';
    }
    return $base_fields;
  }

  /**
   * Implements hook_farm_ui_views_base_fields().
   */
  #[Hook('farm_ui_views_base_fields')]
  public function farmUiViewsBaseFields(string $entity_type) {
    $base_fields = [];
    // Add group base field to farmOS asset and log Views.
    if (in_array($entity_type, [
      'asset',
      'log',
    ])) {
      $base_fields[] = 'group';
    }
    // Add is_group_assignment base field to log Views.
    if ($entity_type == 'log') {
      $base_fields[] = 'is_group_assignment';
    }
    return $base_fields;
  }

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type) {
    $region_items = [];
    if ($entity_type == 'asset') {
      $region_items = [
        'top' => [],
        'first' => [],
        'second' => [
          'group',
        ],
        'bottom' => [],
      ];
    }
    elseif ($entity_type == 'log') {
      $region_items = [
        'top' => [],
        'first' => [],
        'second' => [
          'is_group_assignment',
        ],
        'bottom' => [],
      ];
    }
    return $region_items;
  }

  /**
   * Implements hook_farm_ui_theme_field_groups().
   */
  #[Hook('farm_ui_theme_field_groups')]
  public function farmUiThemeFieldGroups(string $entity_type, string $bundle) {
    // Add a field group for group membership fields on logs.
    if ($entity_type == 'log') {
      return [
        'group' => [
          'location' => 'main',
          'title' => t('Group'),
          'weight' => 60,
        ],
      ];
    }
    return [];
  }

  /**
   * Implements hook_farm_ui_theme_field_group_items().
   */
  #[Hook('farm_ui_theme_field_group_items')]
  public function farmUiThemeFieldGroupItems(string $entity_type, string $bundle) {
    if ($entity_type == 'log') {
      return [
        'group' => 'group',
        'is_group_assignment' => 'group',
      ];
    }
    return [];
  }

}
