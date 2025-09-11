<?php

/**
 * @file
 * Provides Views runtime hooks for farm_input module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_input\Hook\FarmInputViewsExecutionHooks;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_view().
 */
#[LegacyHook]
function farm_input_views_pre_view(ViewExecutable $view, $display_id, array &$args) {

  return \Drupal::service(FarmInputViewsExecutionHooks::class)->viewsPreView($view, $display_id, $args);
}
