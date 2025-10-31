<?php

/**
 * @file
 * Post update hooks for the farm_entity module.
 */

declare(strict_types=1);

/**
 * Implements hook_removed_post_updates().
 */
function farm_entity_removed_post_updates() {
  return [
    'farm_entity_post_update_enforce_plan_eri' => '4.x',
    'farm_entity_post_update_rebuild_bundle_field_maps' => '4.x',
    'farm_entity_post_update_uninstall_exif_orientation' => '4.x',
  ];
}

/**
 * Enforce entity reference integrity on organization reference fields.
 */
function farm_entity_post_update_enforce_organization_eri(&$sandbox) {
  $config = \Drupal::configFactory()->getEditable('entity_reference_integrity_enforce.settings');
  $entity_types = $config->get('enabled_entity_type_ids');
  $entity_types['organization'] = 'organization';
  $config->set('enabled_entity_type_ids', $entity_types);
  $config->save();
}

/**
 * Install the farm_entity_access module.
 */
function farm_entity_post_update_install_farm_entity_access(&$sandbox) {
  if (!\Drupal::service('module_handler')->moduleExists('farm_entity_access')) {
    \Drupal::service('module_installer')->install(['farm_entity_access']);
  }
}
