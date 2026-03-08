<?php

declare(strict_types=1);

namespace Drupal\farm_entity_views\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Views hook implementations for farm_entity_views.
 */
class ViewsHooks {

  use AutowireTrait;

  public function __construct(
    protected EntityFieldManagerInterface $entityFieldManager,
  ) {}

  /**
   * Implements hook_views_data_alter().
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data) {

    // Use core entity_reference filter plugin for all entity reference fields.
    // @todo Refactor/remove this when the following core issues are resolved.
    // @see https://www.drupal.org/project/drupal/issues/3458099
    // @see https://www.drupal.org/project/drupal/issues/3438054
    $entity_reference_field_map = $this->entityFieldManager->getFieldMapByFieldType('entity_reference');
    foreach ($entity_reference_field_map as $entity_type_id => $fields) {
      foreach ($fields as $field_name => $map) {
        if (!empty($data[$entity_type_id . '__' . $field_name][$field_name . '_target_id']['filter'])) {
          $data[$entity_type_id . '__' . $field_name][$field_name . '_target_id']['filter']['id'] = 'entity_reference';
        }
      }
    }

    // Add support for state_machine filters.
    // Because Drupal core does not provide full Views integration for base
    // fields we must manually add support for certain fields.
    // Workaround for core issue #2489476.
    $status_filter = [
      'id' => 'state_machine_state',
      'field_name' => 'status',
    ];
    $tables = [
      'log_field_data',
      'log_field_revision',
      'plan_field_data',
      'plan_field_revision',
    ];
    foreach ($tables as $table) {
      if (!empty($data[$table]['status'])) {
        $data[$table]['status']['filter'] = $status_filter;
      }
    }
  }

}
