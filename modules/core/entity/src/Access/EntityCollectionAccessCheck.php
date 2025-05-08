<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Checks access for the specified entity type collection.
 */
class EntityCollectionAccessCheck implements AccessInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type ID.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * FarmAssetLogViewsAccessCheck constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, string $entity_type_id) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeId = $entity_type_id;
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function access(RouteMatchInterface $route_match) {

    // Bail if the entity type does not exist.
    if (!$this->entityTypeManager->hasDefinition($this->entityTypeId)) {
      // TODO: Cache tag for when entity type is later installed?
      return AccessResult::forbidden();
    }

    // Check if the route is expecting a specific bundle.
    $entity_type = $this->entityTypeManager->getDefinition($this->entityTypeId);
    $bundle = $route_match->getParameter($entity_type->getBundleEntityType()) ?: $route_match->getParameter('arg_0');

    // Return entity collection access result.
    return self::checkEntityCollectionAccess($this->currentUser, $this->entityTypeId, $bundle);
  }

  /**
   * Helper function to check entity collection access.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user to check access for.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string|null $bundle
   *   An optional bundle to limit collection access checking to.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public static function checkEntityCollectionAccess(AccountInterface $user, string $entity_type_id, ?string $bundle): AccessResultInterface {

    // Bail if the entity type does not exist.
    $entity_type_manager = \Drupal::entityTypeManager();
    if (!$entity_type_manager->hasDefinition($entity_type_id)) {
      // TODO: Cache tag for when entity type is later installed?
      return AccessResult::forbidden();
    }

    // Load entity type.
    $entity_type = $entity_type_manager->getDefinition($entity_type_id);

    // Check that user has the access {entity_type} collection permission.
    $collection_permission = AccessResult::allowedIfHasPermission($user, $entity_type->getCollectionPermission());

    // View any/own {entity_type} permissions grant access to all bundles.
    $view_permissions = [
      "view any $entity_type_id",
      "view own $entity_type_id",
    ];

    // Load available bundles for the entity type.
    /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info */
    $entity_type_bundle_info = \Drupal::service('entity_type.bundle.info');
    $entity_bundles = $entity_type_bundle_info->getBundleInfo($entity_type_id);
    $all_bundles = array_keys($entity_bundles);

    // Check if the route is expecting a specific bundle.
    if ($bundle && in_array($bundle, $all_bundles)) {
      $view_permissions[] = "view any $bundle $entity_type_id";
      $view_permissions[] = "view own $bundle $entity_type_id";
    }
    // Else add view permissions for all bundles.
    else {
      foreach ($all_bundles as $bundle) {
        $view_permissions[] = "view any $bundle $entity_type_id";
        $view_permissions[] = "view own $bundle $entity_type_id";
      }
    }

    // Grant permission if the user has at least one of the view permissions.
    $view_permission = AccessResult::allowedIfHasPermissions($user, $view_permissions, 'OR');
    return $collection_permission->andIf($view_permission);
  }

}
