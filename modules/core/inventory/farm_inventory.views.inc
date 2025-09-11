<?php

/**
 * @file
 * Provides Views data for farm_inventory.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_inventory\Hook\FarmInventoryViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_inventory_views_data_alter(array &$data) {

  \Drupal::service(FarmInventoryViewsHooks::class)->viewsDataAlter($data);
}
