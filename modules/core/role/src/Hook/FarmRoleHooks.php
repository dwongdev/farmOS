<?php

declare(strict_types=1);

namespace Drupal\farm_role\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_role.
 */
class FarmRoleHooks {

  /**
   * Implements hook_entity_type_alter().
   */
  #[Hook('entity_type_alter')]
  public function entityTypeAlter(array &$entity_types) {
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_types */

    // Replace the storage handler class for Roles.
    $entity_types['user_role']->setHandlerClass('storage', 'Drupal\farm_role\FarmRoleStorage');
  }

}
