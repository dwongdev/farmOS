<?php

/**
 * @file
 * Post update hooks for the plan module.
 */

declare(strict_types=1);

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\system\Entity\Action;
use Drupal\views\Entity\View;

/**
 * Move archived plan status to boolean field.
 */
function plan_post_update_move_archived_status(&$sandbox) {

  // Get the Drupal entity definition update manager.
  $update_manager = \Drupal::entityDefinitionUpdateManager();

  // Delete the old archived field.
  $storage_definition = $update_manager->getFieldStorageDefinition('archived', 'plan');
  $update_manager->uninstallFieldStorageDefinition($storage_definition);

  // Install the new boolean archived field.
  $field_definition = BaseFieldDefinition::create('boolean')
    ->setLabel(t('Archived'))
    ->setDescription(t('Whether the plan is archived.'))
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
  $update_manager->installFieldStorageDefinition('archived', 'plan', 'plan', $field_definition);

  // Archive plans with a status of archived.
  \Drupal::database()->query("UPDATE {plan_field_data} SET archived = 0 WHERE status != 'archived'");
  \Drupal::database()->query("UPDATE {plan_field_data} SET archived = 1 WHERE status = 'archived'");
  \Drupal::database()->query("UPDATE {plan_field_revision} SET archived = 0 WHERE status != 'archived'");
  \Drupal::database()->query("UPDATE {plan_field_revision} SET archived = 1 WHERE status = 'archived'");

  // Change the status of archived plans to active.
  \Drupal::database()->query("UPDATE {plan_field_data} SET status = 'active' WHERE status = 'archived'");
  \Drupal::database()->query("UPDATE {plan_field_revision} SET status = 'active' WHERE status = 'archived'");

  // Rename plan_activate_action action configuration entity to
  // plan_unarchive_action.
  $action = Action::load('plan_activate_action');
  if (!empty($action)) {
    $action->delete();
    $action->setPlugin('plan_unarchive_action');
    $action->set('id', 'plan_unarchive_action');
    $action->save();
  }
}

/**
 * Remove the plan_admin View.
 */
function plan_post_update_remove_admin_view(&$sandbox) {
  $view = View::load('plan_admin');
  $view->delete();
}

/**
 * Implements hook_removed_post_updates().
 */
function plan_removed_post_updates() {
  return [
    'plan_post_update_install_plan_record' => '4.x',
    'plan_post_update_remove_plan_record_data_table' => '4.x',
  ];
}
