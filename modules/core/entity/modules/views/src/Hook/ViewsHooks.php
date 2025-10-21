<?php

declare(strict_types=1);

namespace Drupal\farm_entity_views\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\OrderAfter;
use Drupal\views\ViewsData;

/**
 * Views hook implementations for farm_entity_views.
 */
class ViewsHooks {

  use AutowireTrait;

  public function __construct(
    protected ViewsData $viewsData,
  ) {}

  /**
   * Implements hook_modules_installed().
   *
   * Make sure this module's implementation runs after that of the entity
   * module, so that we rebuild views data after bundle fields are installed.
   */
  #[Hook('modules_installed', order: new OrderAfter(['entity']))]
  public function modulesInstalled($modules, $is_syncing) {

    // Reset the views data after installing modules.
    // See https://www.drupal.org/project/entity/issues/3206703#comment-14073184
    $this->viewsData->clear();
  }

  /**
   * Implements hook_views_data_alter().
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data) {

    // Because Drupal core does not provide full Views integration for base
    // fields we must manually add support for certain fields.
    // Workaround for core issue #2489476.

    // Add support for state_machine filters.
    $status_filter = [
      'id' => 'state_machine_state',
      'field_name' => 'status',
    ];
    $tables = [
      'log_field_data',
      'log_field_revision',
      'organization_field_data',
      'organization_field_revision',
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
