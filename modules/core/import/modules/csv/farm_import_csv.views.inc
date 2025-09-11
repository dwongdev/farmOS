<?php

/**
 * @file
 * Provides Views data for the farmOS Import: CSV module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\farm_import_csv\Hook\FarmImportCsvViewsHooks;

/**
 * Implements hook_views_data().
 */
#[LegacyHook]
function farm_import_csv_views_data() {
  return \Drupal::service(FarmImportCsvViewsHooks::class)->viewsData();
}
