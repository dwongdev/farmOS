<?php

/**
 * @file
 * Provides Views runtime hooks for farm_land.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_land\Hook\FarmLandViewsExecutionHooks;
use Drupal\farm_land\Entity\FarmLandType;
use Drupal\views\ViewExecutable;

/**
 * Implements hook_views_pre_render().
 */
#[LegacyHook]
function farm_land_views_pre_render(ViewExecutable $view) {

  return \Drupal::service(FarmLandViewsExecutionHooks::class)->viewsPreRender($view);
}
