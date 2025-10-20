<?php

declare(strict_types=1);

namespace Drupal\farm_role;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccessPolicyProcessorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\PermissionChecker;

/**
 * Checks permissions for an account.
 */
class ManagedRolePermissionChecker extends PermissionChecker {

  public function __construct(
    protected AccessPolicyProcessorInterface $processor,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ManagedRolePermissionsManagerInterface $managedRolePermissionsManager,
  ) {
    parent::__construct($processor);
  }

  /**
   * {@inheritdoc}
   */
  public function hasPermission(string $permission, AccountInterface $account): bool {
    $has_permission = parent::hasPermission($permission, $account);

    // Check if the permission is included via farm_role rules.
    if (!$has_permission) {
      $managed_roles = $this->managedRolePermissionsManager->getMangedRoles();
      foreach ($account->getRoles() as $role_id) {
        if (in_array($role_id, array_keys($managed_roles))) {
          /** @var \Drupal\user\RoleInterface $role */
          $role = $this->entityTypeManager->getStorage('user_role')->load($role_id);
          $has_permission = $this->managedRolePermissionsManager->isPermissionInRole($permission, $role);
          if ($has_permission) {
            break;
          }
        }
      }
    }

    return $has_permission;
  }

}
