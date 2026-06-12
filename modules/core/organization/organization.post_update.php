<?php

/**
 * @file
 * Post update functions for organization module.
 */

declare(strict_types=1);

/**
 * Add revision_data_table to organization entity type definition.
 */
function organization_post_update_revision_data_table(&$sandbox) {
  $manager = \Drupal::service('entity.definition_update_manager');
  $entity_type = $manager->getEntityType('organization');
  $entity_type->set('revision_data_table', 'organization_field_revision');
  $manager->updateEntityType($entity_type);
}
