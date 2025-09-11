<?php

namespace Drupal\farm_log_quantity\Hook;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_log_quantity.
 */
class FarmLogQuantityHooks
{
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        // We only care about log entities.
        if ($entity_type->id() != 'log') {
            return [
            ];
        }
        // Add a quantity reference field to logs.
        $field_info = [
            'quantity' => [
                'type' => 'entity_reference_revisions',
                'label' => t('Quantity'),
                'description' => t('Add quantity measurements to this log.'),
                'target_type' => 'quantity',
                'multiple' => TRUE,
                'weight' => [
                    'form' => 0,
                    'view' => 50,
                ],
            ],
        ];
        $fields = [
        ];
        foreach ($field_info as $name => $info) {
            $fields[$name] = \Drupal::service('farm_field.factory')->baseFieldDefinition($info);
        }
        return $fields;
    }
}
