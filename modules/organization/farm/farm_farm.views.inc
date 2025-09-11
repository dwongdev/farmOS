<?php

/**
 * @file
 * Provides Views data for farm_farm.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_farm\Hook\FarmFarmViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_farm_views_data_alter(array &$data) {

  \Drupal::service(FarmFarmViewsHooks::class)->viewsDataAlter($data);
}
