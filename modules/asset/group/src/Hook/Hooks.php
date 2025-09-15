<?php

declare(strict_types=1);

namespace Drupal\farm_group\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for farm_group.
 */
class Hooks {

  use StringTranslationTrait;

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
          'title' => $this->t('Group'),
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
