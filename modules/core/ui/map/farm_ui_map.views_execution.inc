<?php

/**
 * @file
 * Provides Views runtime hooks for farm_ui_map.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_ui_map\Hook\FarmUiMapViewsExecutionHooks;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_view().
 */
#[LegacyHook]
function farm_ui_map_views_pre_view(ViewExecutable $view, $display_id, array &$args) {

  return \Drupal::service(FarmUiMapViewsExecutionHooks::class)->viewsPreView($view, $display_id, $args);
}

/**
 * Implements hook_views_pre_render().
 *
 * @see farm_ui_map_module_implements_alter()
 */
#[LegacyHook]
function farm_ui_map_views_pre_render(ViewExecutable $view) {

  \Drupal::service(FarmUiMapViewsExecutionHooks::class)->viewsPreRender($view);
}
