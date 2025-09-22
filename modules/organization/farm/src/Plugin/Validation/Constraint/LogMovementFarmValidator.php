<?php

declare(strict_types=1);

namespace Drupal\farm_farm\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LogMovementFarmValidator constraint.
 */
class LogMovementFarmValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem $value */
    /** @var \Drupal\farm_farm\Plugin\Validation\Constraint\LogMovementFarm $constraint */

    // Only continue if this log is designated as a movement.
    /** @var \Drupal\log\Entity\LogInterface|null $log */
    $log = $value->getParent()->getValue();
    if (is_null($log)) {
      return;
    }
    if (!$log->get('is_movement')->value) {
      return;
    }

    // Only continue if both asset and location fields are populated.
    if ($log->get('asset')->isEmpty() || $log->get('location')->isEmpty()) {
      return;
    }

    // Load the farm IDs of all referenced locations assets, if any.
    $location_farm_ids = [];
    foreach ($log->get('location')->referencedEntities() as $location) {
      foreach ($location->get('farm')->referencedEntities() as $farm) {
        $location_farm_ids[] = $farm->id();
      }
    }
    sort($location_farm_ids);

    // Iterate through each referenced asset.
    foreach ($log->get('asset')->referencedEntities() as $asset) {

      // Load the farm IDs of the asset, if any.
      $asset_farm_ids = [];
      foreach ($asset->get('farm')->referencedEntities() as $farm) {
        $asset_farm_ids[] = $farm->id();
      }
      sort($asset_farm_ids);

      // If the asset farm IDs match the location farm IDs, continue.
      if (
        count($asset_farm_ids) == count($location_farm_ids)
        &&
        empty(array_diff($asset_farm_ids, $location_farm_ids))
        &&
        empty(array_diff($location_farm_ids, $asset_farm_ids))
      ) {
        continue;
      }

      // Otherwise, add a violation.
      $this->context->addViolation($constraint->message);
    }
  }

}
