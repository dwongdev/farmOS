<?php

declare(strict_types=1);

namespace Drupal\farm_location\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_location.
 */
class FarmLocationHooks {

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    \Drupal::moduleHandler()->loadInclude('farm_location', 'inc', 'farm_location.base_fields');
    switch ($entity_type->id()) {

      // Build asset base fields.
      case 'asset':
        return farm_location_asset_base_fields();

      // Build log base fields.
      case 'log':
        return farm_location_log_base_fields();

      default:
        return [];
    }
  }

  /**
   * Implements hook_entity_base_field_info_alter().
   */
  #[Hook('entity_base_field_info_alter')]
  public function entityBaseFieldInfoAlter(&$fields, EntityTypeInterface $entity_type) {
    /** @var \Drupal\field\Entity\FieldConfig[] $fields */

    // Prevent creating circular asset location.
    if ($entity_type->id() == 'log' && !empty($fields['asset'])) {
      $fields['asset']->addConstraint('CircularAssetLocation');
    }
  }

}
