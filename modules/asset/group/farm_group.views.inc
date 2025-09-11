<?php

/**
 * @file
 * Provides Views data for farm_group.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_group\Hook\FarmGroupViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_group_views_data_alter(array &$data) {

  \Drupal::service(FarmGroupViewsHooks::class)->viewsDataAlter($data);
}
