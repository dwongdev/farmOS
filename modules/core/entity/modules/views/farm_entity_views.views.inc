<?php

/**
 * @file
 * Provides Views data for farm_entity_views.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_entity_views\Hook\FarmEntityViewsViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_entity_views_views_data_alter(array &$data) {
  \Drupal::service(FarmEntityViewsViewsHooks::class)->viewsDataAlter($data);
}
