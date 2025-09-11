<?php

declare(strict_types=1);

namespace Drupal\farm_entity_contrib_test\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_entity_contrib_test.
 */
class FarmEntityContribTestHooks {

  /**
   * Implements hook_farm_entity_bundle_field_info().
   */
  #[Hook('farm_entity_bundle_field_info')]
  public function farmEntityBundleFieldInfo(EntityTypeInterface $entity_type, string $bundle) {
    $fields = [];
    // Add a new bundle field to test logs.
    if ($entity_type->id() == 'log' && in_array($bundle, [
      'test',
    ])) {
      $options = [
        'type' => 'string',
        'label' => t('Test hook bundle field'),
      ];
      $fields['test_contrib_hook_bundle_field'] = \Drupal::service('farm_field.factory')->bundleFieldDefinition($options);
    }
    return $fields;
  }

}
