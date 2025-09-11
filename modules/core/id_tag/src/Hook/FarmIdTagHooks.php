<?php

declare(strict_types=1);

namespace Drupal\farm_id_tag\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_id_tag.
 */
class FarmIdTagHooks {

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    $fields = [];

    // Add ID tag field to assets.
    if ($entity_type->id() == 'asset') {
      $field_info = [
        'type' => 'id_tag',
        'label' => t('ID tags'),
        'description' => t('List any identification tags that this asset has. Use the fields below to describe the type, location, and ID of each.'),
        'multiple' => TRUE,
        'weight' => [
          'form' => 20,
          'view' => 20,
        ],
      ];
      $fields['id_tag'] = \Drupal::service('farm_field.factory')->baseFieldDefinition($field_info);

      // Add an ID tag type constraint to ID tag fields to ensure valid type.
      $fields['id_tag']->addConstraint('IdTagType');
    }

    return $fields;
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'field__id_tag' => [
        'template' => 'field--id-tag',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    return [
      'tag_type',
    ];
  }

}
