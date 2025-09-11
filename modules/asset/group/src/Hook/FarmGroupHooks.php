<?php

declare(strict_types=1);

namespace Drupal\farm_group\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_group\Field\AssetGroupItemList;

/**
 * Hook implementations for farm_group.
 */
class FarmGroupHooks {

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {

    // Add group base fields to entity types.
    if ($entity_type->id() == 'asset') {
      return $this->assetFields();
    }
    elseif ($entity_type->id() == 'log') {
      return $this->logFields();
    }
    return [];
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
    if (in_array($entity_type, ['asset', 'log'])) {
      $base_fields[] = 'group';
    }

    // Add is_group_assignment base field to log Views.
    if ($entity_type == 'log') {
      $base_fields[] = 'is_group_assignment';
    }

    return $base_fields;
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_log_form_alter')]
  public function formLogFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Check if the form has the required group fields.
    if (isset($form['group']) && isset($form['is_group_assignment'])) {

      // Set the visible state of the log.group field.
      // Only display if is_group_assignment is checked.
      $form['group']['#states']['visible'] = [':input[name="is_group_assignment[value]"]' => ['checked' => TRUE]];
    }
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

  /**
   * Define asset location base fields.
   *
   * @return array
   *   Returns an array of field information for use with farm_field.factory.
   */
  private function assetFields(): array {
    $fields = [];

    // Group membership field.
    // This is computed based on an asset's group assignment logs.
    $options = [
      'type' => 'entity_reference',
      'label' => t('Group membership'),
      'target_type' => 'asset',
      'target_bundle' => 'group',
      'multiple' => TRUE,
      'computed' => AssetGroupItemList::class,
      'hidden' => 'form',
      'weight' => [
        'view' => 94,
      ],
    ];
    $fields['group'] = \Drupal::service('farm_field.factory')->baseFieldDefinition($options);

    return $fields;
  }

  /**
   * Define log location base fields.
   *
   * @return array
   *   Returns an array of field information for use with farm_field.factory.
   */
  private function logFields(): array {
    $fields = [];

    // "Is group assignment" boolean field.
    $options = [
      'type' => 'boolean',
      'label' => t('Is group assignment'),
      'description' => t('If this log is a group assignment, any referenced assets will become members of the groups referenced below.'),
      'weight' => [
        'form' => 30,
      ],
      'view_display_options' => [
        'label' => 'inline',
        'type' => 'hideable_boolean',
        'settings' => [
          'format' => 'default',
          'format_custom_false' => '',
          'format_custom_true' => '',
          'hide_if_false' => TRUE,
        ],
        'weight' => 30,
      ],
    ];
    $fields['is_group_assignment'] = \Drupal::service('farm_field.factory')->baseFieldDefinition($options);

    // Group reference field.
    $options = [
      'type' => 'entity_reference',
      'label' => t('Groups'),
      'description' => t('If this is a group assignment log, which groups should the referenced assets be assigned to?'),
      'target_type' => 'asset',
      'target_bundle' => 'group',
      'multiple' => TRUE,
      'weight' => [
        'form' => 30,
        'view' => 30,
      ],
    ];
    $fields['group'] = \Drupal::service('farm_field.factory')->baseFieldDefinition($options);

    return $fields;
  }

}
