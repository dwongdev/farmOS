<?php

/**
 * @file
 * Provides Views data for farm_input module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_input\Hook\FarmInputViewsHooks;

/**
 * Implements hook_views_data().
 */
#[LegacyHook]
function farm_input_views_data() {
  return \Drupal::service(FarmInputViewsHooks::class)->viewsData();
}
