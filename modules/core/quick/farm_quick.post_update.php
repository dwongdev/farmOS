<?php

/**
 * @file
 * Post update hooks for the farmOS Quick Form module.
 */

declare(strict_types=1);

/**
 * Install the farm_form module.
 */
function farm_quick_post_update_install_farm_form(&$sandbox = NULL) {
  if (!\Drupal::service('module_handler')->moduleExists('farm_form')) {
    \Drupal::service('module_installer')->install(['farm_form']);
  }
}

/**
 * Implements hook_removed_post_updates().
 */
function farm_quick_removed_post_updates() {
  return [
    'farm_quick_post_update_install_quick_form_entity_type' => '4.x',
  ];
}
