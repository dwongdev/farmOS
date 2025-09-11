<?php

namespace Drupal\farm_owner\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_owner.
 */
class FarmOwnerHooks
{
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        $fields = [
        ];
        // Add owner field to logs and assets.
        if (in_array($entity_type->id(), [
            'asset',
            'log',
        ])) {
            $field_info = [
                'type' => 'entity_reference',
                'label' => t('Owners'),
                'description' => t('Assign ownership to one or more users.'),
                'target_type' => 'user',
                'multiple' => TRUE,
                'weight' => [
                    'form' => -70,
                    'view' => -70,
                ],
            ];
            $fields['owner'] = \Drupal::service('farm_field.factory')->baseFieldDefinition($field_info);
        }
        return $fields;
    }
}
