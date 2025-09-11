<?php

/**
 * @file
 * Provides Views data for farm_location.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_location\Hook\FarmLocationViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_location_views_data_alter(array &$data) {

  \Drupal::service(FarmLocationViewsHooks::class)->viewsDataAlter($data);
}
