<?php

declare(strict_types=1);

namespace Drupal\farm_ui_theme\Hook;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_ui_theme\Form\AssetForm;
use Drupal\farm_ui_theme\Form\LogForm;
use Drupal\farm_ui_theme\Form\OrganizationForm;
use Drupal\farm_ui_theme\Form\PlanForm;
use Drupal\farm_ui_theme\Form\TaxonomyTermForm;

/**
 * Hook implementations for farm_ui_theme.
 */
class FarmUiThemeHooks {

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_asset_form_alter')]
  public function formAssetFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\Core\Entity\EntityForm $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\asset\Entity\AssetInterface $entity */
    $entity = $form_object->getEntity();
    farm_ui_theme_set_archived_message($entity);
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_plan_form_alter')]
  public function formPlanFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\Core\Entity\EntityForm $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\asset\Entity\AssetInterface $entity */
    $entity = $form_object->getEntity();
    farm_ui_theme_set_archived_message($entity);
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_quick_form_alter')]
  public function formQuickFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form['#attached']['library'][] = 'farm_ui_theme/quick';
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'html__asset__map_popup' => [
        'base hook' => 'html',
      ],
      'menu_local_tasks__farm' => [
        'base hook' => 'menu_local_tasks',
      ],
      'menu_local_task__secondary' => [
        'base hook' => 'menu_local_task',
      ],
      'page__asset__map_popup' => [
        'base hook' => 'page',
      ],

      // Implement the node edit form theme hook.
      // See https://www.drupal.org/project/gin/issues/3342164
      'node_edit_form' => [
        'render element' => 'form',
      ],
    ];
  }

  /**
   * Implements hook_theme_registry_alter().
   */
  #[Hook('theme_registry_alter')]
  public function themeRegistryAlter(&$theme_registry) {
    $theme_registry['comment']['path'] = \Drupal::service('extension.list.module')->getPath('farm_ui_theme') . '/templates';
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_menu_local_task')]
  public function themeSuggestionsMenuLocalTask(array $variables) {

    // Add suggestions for primary and secondary task levels.
    $suggestions = [];
    if (isset($variables['element']['#level'])) {
      $suggestions[] = 'menu_local_task__' . $variables['element']['#level'];
    }
    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_menu_local_tasks')]
  public function themeSuggestionsMenuLocalTasks(array $variables) {
    return [
      'menu_local_tasks__farm',
    ];
  }

  /**
   * Implements hook_entity_form_display_alter().
   */
  #[Hook('entity_form_display_alter')]
  public function entityFormDisplayAlter(EntityFormDisplayInterface $form_display, array $context) {

    // Only alter farm entity types.
    $entity_types = [
      'asset',
      'log',
      'organization',
      'plan',
      'taxonomy_term',
    ];
    if (!in_array($context['entity_type'], $entity_types)) {
      return;
    }

    // Ask modules for a list of field group items.
    $field_map = \Drupal::moduleHandler()->invokeAll('farm_ui_theme_field_group_items', [
      $context['entity_type'],
      $context['bundle'],
    ]);

    // Apply the field group mapping if not already specified on the form
    // display.
    foreach ($field_map as $field_id => $field_group) {
      if (($renderer = $form_display->getRenderer($field_id)) && !$renderer->getThirdPartySetting('farm_ui_theme', 'field_group', FALSE)) {
        $renderer->setThirdPartySetting('farm_ui_theme', 'field_group', $field_group);
      }
    }
  }

  /**
   * Implements hook_farm_ui_theme_field_group_items().
   */
  #[Hook('farm_ui_theme_field_group_items')]
  public function farmUiThemeFieldGroupItems(string $entity_type, string $bundle) {

    // Define base fields for asset, log, and plans on behalf of core modules.
    $fields = [
      'name' => 'default',
      'status' => 'meta',
      'flag' => 'meta',
      'file' => 'file',
      'image' => 'file',
      'revision' => 'revision',
      'revision_log_message' => 'revision',
    ];
    switch ($entity_type) {
      case 'asset':
        $fields['owner'] = 'meta';
        $fields['parent'] = 'parent';
        $fields['intrinsic_geometry'] = 'location';
        $fields['is_location'] = 'location';
        $fields['is_fixed'] = 'location';
        $fields['id_tag'] = 'id_tag';
        $fields['archived'] = 'meta';
        break;

      case 'log':
        $fields['timestamp'] = 'default';
        $fields['category'] = 'meta';
        $fields['owner'] = 'meta';
        $fields['asset'] = 'asset';
        $fields['geometry'] = 'location';
        $fields['location'] = 'location';
        $fields['is_movement'] = 'location';
        $fields['quantity'] = 'quantity';
        break;

      case 'organization':
        $fields['archived'] = 'meta';
        break;

      case 'plan':
        $fields['archived'] = 'meta';
        break;

      case 'taxonomy_term':
        $fields['external_uri'] = 'reference';
        break;

      default:
        $fields = [];
    }
    return $fields;
  }

  /**
   * Implements hook_gin_content_form_routes().
   */
  #[Hook('gin_content_form_routes')]
  public function ginContentFormRoutes() {
    $routes = [];
    $entity_types = [
      'asset',
      'log',
      'organization',
      'plan',
      'taxonomy_term',
    ];
    foreach ($entity_types as $entity_type) {
      $routes[] = "entity.{$entity_type}.add_form";
      $routes[] = "entity.{$entity_type}.edit_form";
    }
    return $routes;
  }

  /**
   * Implements hook_entity_type_build().
   */
  #[Hook('entity_type_build')]
  public function entityTypeBuild(array &$entity_types) {

    // Override the default add and edit form class.
    $target_entity_types = [
      'asset' => AssetForm::class,
      'log' => LogForm::class,
      'organization' => OrganizationForm::class,
      'plan' => PlanForm::class,
      'taxonomy_term' => TaxonomyTermForm::class,
    ];
    foreach ($target_entity_types as $entity_type => $form_class) {
      if (isset($entity_types[$entity_type])) {
        $entity_types[$entity_type]->setFormClass('default', $form_class);
        $entity_types[$entity_type]->setFormClass('add', $form_class);
        $entity_types[$entity_type]->setFormClass('edit', $form_class);
      }
    }
  }

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$info) {
    if (isset($info['farm_map'])) {
      $info['farm_map']['#attached']['library'][] = 'farm_ui_theme/map';
    }
  }

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type) {

    // Define common asset, log, and plan region items on behalf of core
    // modules.
    switch ($entity_type) {
      case 'asset':
        return [
          'top' => [
            'geometry',
          ],
          'first' => [],
          'second' => [
            'inventory',
            'is_location',
            'is_fixed',
            'location',
            'owner',
            'type',
            'archived',
          ],
          'bottom' => [
            'api',
            'image',
            'file',
          ],
        ];

      case 'log':
        return [
          'top' => [
            'geometry',
          ],
          'first' => [],
          'second' => [
            'is_movement',
            'owner',
            'status',
            'type',
          ],
          'bottom' => [
            'image',
            'file',
          ],
        ];

      case 'plan':
        return [
          'top' => [],
          'first' => [],
          'second' => [
            'status',
            'type',
            'archived',
          ],
          'bottom' => [
            'image',
            'file',
          ],
        ];

      default:
        return [];
    }
  }

  /**
   * Implements hook_farm_update_exclude_config().
   */
  #[Hook('farm_update_exclude_config')]
  public function farmUpdateExcludeConfig() {

    // Exclude config that we have overridden in hook_install() or the
    // farm_ui_theme.overrider service.
    return [
      'block.block.gin_local_actions',
      'block.block.gin_content',
    ];
  }

}
