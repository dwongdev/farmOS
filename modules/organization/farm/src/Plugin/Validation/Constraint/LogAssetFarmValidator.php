<?php

declare(strict_types=1);

namespace Drupal\farm_farm\Plugin\Validation\Constraint;

use Drupal\log\Entity\LogInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the LogAssetFarm constraint.
 */
class LogAssetFarmValidator extends ConstraintValidator {

  /**
   * Log asset reference fields.
   *
   * @var string[]
   */
  protected array $fieldNames = [
    'asset',
    'equipment',
    'group',
    'location',
  ];

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    /** @var \Drupal\log\Entity\LogInterface $value */
    /** @var \Drupal\farm_farm\Plugin\Validation\Constraint\LogAssetFarm $constraint */

    // If multiple unique farm IDs are referenced, add a violation.
    if (count($this->logReferencedAssetFarmIds($value)) > 1) {
      $this->context->addViolation($constraint->message);
    }
  }

  /**
   * Get a list of all the farm IDs that referenced assets are assigned to.
   *
   * @param \Drupal\log\Entity\LogInterface $log
   *   The log entity.
   *
   * @return int[]
   *   Returns an array of unique farm IDs. If an asset is not in a farm, an ID
   *   of 0 will be included in the list.
   */
  protected function logReferencedAssetFarmIds(LogInterface $log) {
    $farm_ids = [];

    // Check all assets that are referenced directly from the log.
    foreach ($this->fieldNames as $name) {
      if (!$log->hasField($name)) {
        continue;
      }
      if ($log->get($name)->isEmpty()) {
        continue;
      }
      foreach ($log->get($name)->referencedEntities() as $asset) {
        if ($asset->get('farm')->isEmpty()) {
          $farm_ids[] = 0;
        }
        else {
          foreach ($asset->get('farm')->referencedEntities() as $farm) {
            $farm_ids[] = $farm->id();
          }
        }
      }
    }

    // Check assets that are referenced in quantity inventory adjustments.
    if ($log->hasField('quantity') && !$log->get('quantity')->isEmpty()) {
      foreach ($log->get('quantity')->referencedEntities() as $quantity) {
        /** @var \Drupal\quantity\Entity\QuantityInterface $quantity */
        if ($quantity->hasField('inventory_asset') && !$quantity->get('inventory_asset')->isEmpty()) {
          foreach ($quantity->get('inventory_asset')->referencedEntities() as $asset) {
            /** @var \Drupal\asset\Entity\AssetInterface $asset */
            if ($asset->get('farm')->isEmpty()) {
              $farm_ids[] = 0;
            }
            else {
              foreach ($asset->get('farm')->referencedEntities() as $farm) {
                $farm_ids[] = $farm->id();
              }
            }
          }
        }
      }
    }

    return array_unique($farm_ids);
  }

}
