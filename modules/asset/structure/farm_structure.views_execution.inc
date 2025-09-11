<?php

/**
 * @file
 * Provides Views runtime hooks for farm_structure.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_structure\Hook\FarmStructureViewsExecutionHooks;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_render().
 */
#[LegacyHook]
function farm_structure_views_pre_render(ViewExecutable $view) {

  return \Drupal::service(FarmStructureViewsExecutionHooks::class)->viewsPreRender($view);
}
