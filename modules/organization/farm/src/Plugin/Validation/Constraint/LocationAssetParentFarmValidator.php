<?php

declare(strict_types=1);

namespace Drupal\farm_farm\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LocationAssetParentFarm constraint.
 */
class LocationAssetParentFarmValidator extends ConstraintValidator implements ContainerInjectionInterface {

  use AutowireTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $value */
    /** @var \Drupal\farm_farm\Plugin\Validation\Constraint\LocationAssetParentFarm $constraint */

    // Only continue if this asset is designated as a location.
    /** @var \Drupal\asset\Entity\AssetInterface|null $asset */
    $asset = $value->getParent()->getValue();
    if (is_null($asset)) {
      return;
    }
    if (!$asset->get('is_location')->value) {
      return;
    }

    // Only continue if the farm field is populated.
    if ($asset->get('farm')->isEmpty()) {
      return;
    }
    $farm_ids = array_map(function ($farm) {
      return $farm->id();
    }, $asset->get('farm')->referencedEntities());

    // Load all related parent and child assets.
    $relations = array_filter($asset->get('parent')->referencedEntities(), function ($parent) {
      return $parent->get('is_location')->value;
    });
    $asset_storage = $this->entityTypeManager->getStorage('asset');
    $children_ids = $asset_storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('is_location', TRUE)
      ->condition('parent', $asset->id())
      ->execute();
    $relations += $asset_storage->loadMultiple($children_ids);

    // Ensure that all assets are in the same farm.
    $violation = FALSE;
    /** @var \Drupal\asset\Entity\AssetInterface[] $relations */
    foreach ($relations as $relation) {

      // If the relation is in one or more farms, load the farm IDs and compare
      // them to the asset. If there is no overlap, add a violation.
      if (!$relation->get('farm')->isEmpty()) {
        $relation_farm_ids = array_map(function ($farm) {
          return $farm->id();
        }, $relation->get('farm')->referencedEntities());
        if (empty(array_intersect($farm_ids, $relation_farm_ids))) {
          $violation = TRUE;
          break;
        }
      }

      // Otherwise, add a violation because the relation isn't in a farm.
      else {
        $violation = TRUE;
        break;
      }
    }
    if ($violation) {
      $this->context->addViolation($constraint->message);
    }
  }

}
