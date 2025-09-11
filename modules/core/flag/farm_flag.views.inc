<?php

/**
 * @file
 * Provides Views data for farm_flag.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_flag\Hook\FarmFlagViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_flag_views_data_alter(array &$data) {
  \Drupal::service(FarmFlagViewsHooks::class)->viewsDataAlter($data);
}
