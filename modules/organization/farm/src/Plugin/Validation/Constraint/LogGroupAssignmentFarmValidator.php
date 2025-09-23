<?php

declare(strict_types=1);

namespace Drupal\farm_farm\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LogGroupAssignmentFarmValidator constraint.
 */
class LogGroupAssignmentFarmValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem $value */
    /** @var \Drupal\farm_farm\Plugin\Validation\Constraint\LogGroupAssignmentFarm $constraint */

    // Only continue if this log is designated as a group assignment.
    /** @var \Drupal\log\Entity\LogInterface|null $log */
    $log = $value->getParent()->getValue();
    if (is_null($log)) {
      return;
    }
    if (!$log->get('is_group_assignment')->value) {
      return;
    }

    // Only continue if both asset and group fields are populated.
    if ($log->get('asset')->isEmpty() || $log->get('group')->isEmpty()) {
      return;
    }

    // Load the farm IDs of all referenced group assets, if any.
    $group_farm_ids = [];
    foreach ($log->get('group')->referencedEntities() as $group) {
      foreach ($group->get('farm')->referencedEntities() as $farm) {
        if (!in_array($farm->id(), $group_farm_ids)) {
          $group_farm_ids[] = $farm->id();
        }
      }
    }
    sort($group_farm_ids);

    // Iterate through each referenced asset.
    foreach ($log->get('asset')->referencedEntities() as $asset) {

      // Load the farm IDs of the asset, if any.
      $asset_farm_ids = [];
      foreach ($asset->get('farm')->referencedEntities() as $farm) {
        if (!in_array($farm->id(), $asset_farm_ids)) {
          $asset_farm_ids[] = $farm->id();
        }
      }
      sort($asset_farm_ids);

      // If the asset farm IDs match the group farm IDs, continue.
      if (
        count($asset_farm_ids) == count($group_farm_ids)
        &&
        empty(array_diff($asset_farm_ids, $group_farm_ids))
        &&
        empty(array_diff($group_farm_ids, $asset_farm_ids))
      ) {
        continue;
      }

      // Otherwise, add a violation.
      $this->context->addViolation($constraint->message);
    }
  }

}
