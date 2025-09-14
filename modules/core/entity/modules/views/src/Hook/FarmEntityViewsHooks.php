<?php

declare(strict_types=1);

namespace Drupal\farm_entity_views\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\OrderAfter;
use Drupal\farm_entity_views\FarmEntityViewsData;
use Drupal\farm_entity_views\FarmLogViewsData;
use Drupal\farm_entity_views\FarmQuantityViewsData;
use Drupal\views\ViewsData;

/**
 * Hook implementations for farm_entity_views.
 */
class FarmEntityViewsHooks {

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
   * Implements hook_entity_type_build().
   */
  #[Hook('entity_type_build')]
  public function entityTypeBuild(array &$entity_types) {

    // Set the views data handler class to FarmEntityViewsData.
    foreach (['asset', 'log', 'organization', 'plan', 'plan_record', 'quantity'] as $entity_type) {
      if (!empty($entity_types[$entity_type])) {

        // Use the correct class for each entity type.
        // Logs and quantities provide their own that we must extend from.
        $views_data_class = FarmEntityViewsData::class;
        switch ($entity_type) {
          case 'log':
            $views_data_class = FarmLogViewsData::class;
            break;

          case 'quantity':
            $views_data_class = FarmQuantityViewsData::class;
            break;
        }
        $entity_types[$entity_type]->setHandlerClass('views_data', $views_data_class);
      }
    }
  }

}
