<?php

/**
 * @file
 * Post update functions for farm_ui_theme module.
 */

declare(strict_types=1);

/**
 * Install the farm_form module.
 */
function farm_ui_theme_post_update_install_farm_form(&$sandbox = NULL) {
  if (!\Drupal::service('module_handler')->moduleExists('farm_form')) {
    \Drupal::service('module_installer')->install(['farm_form']);
  }
}
