<?php

/**
 * @file
 * Post update functions for asset module.
 */

declare(strict_types=1);

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\system\Entity\Action;

/**
 * Remove the asset status field.
 */
function asset_post_update_remove_status(&$sandbox) {

  // Get the Drupal entity definition update manager.
  $update_manager = \Drupal::entityDefinitionUpdateManager();

  // Install the new last_archived field.
  $field_definition = BaseFieldDefinition::create('timestamp')
    ->setLabel(t('Last Archived'))
    ->setDescription(t('The time the asset was last archived.'))
    ->setRevisionable(TRUE);
  $update_manager->installFieldStorageDefinition('last_archived', 'asset', 'asset', $field_definition);

  // Populate last_archived field values from the old archived field.
  \Drupal::database()->query("UPDATE asset_field_data SET last_archived = archived");
  \Drupal::database()->query("UPDATE asset_field_revision SET last_archived = archived");

  // Delete the old archived field.
  $storage_definition = $update_manager->getFieldStorageDefinition('archived', 'asset');
  $update_manager->uninstallFieldStorageDefinition($storage_definition);

  // Install the new boolean archived field.
  $field_definition = BaseFieldDefinition::create('boolean')
    ->setLabel(t('Archived'))
    ->setDescription(t('Whether the asset is archived.'))
    ->setRevisionable(TRUE)
    ->setSetting('on_label', 'Yes')
    ->setSetting('off_label', 'No')
    ->setDisplayOptions('view', [
      'label' => 'inline',
      'type' => 'boolean',
      'settings' => [
        'format' => 'default',
        'format_custom_false' => '',
        'format_custom_true' => '',
      ],
      'weight' => 100,
    ])
    ->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'settings' => [
        'display_label' => TRUE,
      ],
      'weight' => 100,
    ]);
  $update_manager->installFieldStorageDefinition('archived', 'asset', 'asset', $field_definition);

  // Archive assets with a status of archived.
  \Drupal::database()->query("UPDATE asset_field_data SET archived = 0 WHERE status != 'archived'");
  \Drupal::database()->query("UPDATE asset_field_data SET archived = 1 WHERE status = 'archived'");
  \Drupal::database()->query("UPDATE asset_field_revision SET archived = 0 WHERE status != 'archived'");
  \Drupal::database()->query("UPDATE asset_field_revision SET archived = 1 WHERE status = 'archived'");

  // Rename asset_activate_action action configuration entity to
  // asset_unarchive_action.
  $action = Action::load('asset_activate_action');
  if (!empty($action)) {
    $action->delete();
    $action->setPlugin('asset_unarchive_action');
    $action->set('id', 'asset_unarchive_action');
    $action->save();
  }

  // Delete the status field.
  $storage_definition = $update_manager->getFieldStorageDefinition('status', 'asset');
  $update_manager->uninstallFieldStorageDefinition($storage_definition);
}
