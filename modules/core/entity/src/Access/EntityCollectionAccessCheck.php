<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeBundleInfo;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
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
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

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
  public function __construct(AccountProxyInterface $current_user, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfo $entity_type_bundle_info, string $entity_type_id) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
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

    // Load entity type.
    $entity_type = $this->entityTypeManager->getDefinition($this->entityTypeId);

    // Check that user has the access {entity_type} collection permission.
    $collection_permission = AccessResult::allowedIfHasPermission($this->currentUser, $entity_type->getCollectionPermission());

    // View any/own {entity_type} permissions grant access to all bundles.
    $view_permissions = [
      "view any $this->entityTypeId",
      "view own $this->entityTypeId",
    ];

    // Load available bundles for the entity type.
    $entity_bundles = $this->entityTypeBundleInfo->getBundleInfo($this->entityTypeId);
    $all_bundles = array_keys($entity_bundles);

    // Check if the route is expecting a specific bundle.
    $bundle = $route_match->getParameter($entity_type->getBundleEntityType()) ?: $route_match->getParameter('arg_0');
    if ($bundle && in_array($bundle, $all_bundles)) {
      $view_permissions[] = "view any $bundle $this->entityTypeId";
      $view_permissions[] = "view own $bundle $this->entityTypeId";
    }
    // Else add view permissions for all bundles.
    else {
      foreach ($all_bundles as $bundle) {
        $view_permissions[] = "view any $bundle $this->entityTypeId";
        $view_permissions[] = "view own $bundle $this->entityTypeId";
      }
    }

    // Grant permission if the user has at least one of the view permissions.
    $view_permission = AccessResult::allowedIfHasPermissions($this->currentUser, $view_permissions, 'OR');
    return $collection_permission->andIf($view_permission);
  }

}
