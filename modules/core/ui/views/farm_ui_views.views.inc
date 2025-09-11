<?php

/**
 * @file
 * Provides Views data for farm_ui_views.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_ui_views\Hook\FarmUiViewsViewsHooks;

/**
 * Implements hook_views_data_alter().
 */
#[LegacyHook]
function farm_ui_views_views_data_alter(array &$data)
{
    \Drupal::service(FarmUiViewsViewsHooks::class)->viewsDataAlter($data);
}
