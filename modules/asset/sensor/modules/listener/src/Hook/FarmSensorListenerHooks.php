<?php

namespace Drupal\farm_sensor_listener\Hook;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\asset\Entity\AssetInterface;
use Drupal\data_stream\Entity\DataStream;
use Drupal\data_stream\Entity\DataStreamInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_sensor_listener.
 */
class FarmSensorListenerHooks
{
    /**
     * Implements hook_farm_entity_bundle_field_info().
     */
    #[Hook('farm_entity_bundle_field_info')]
    public function farmEntityBundleFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type, string $bundle)
    {
        $fields = [
        ];
        // Add a public_key reference field to sensor assets.
        if ($entity_type->id() === 'asset' && $bundle === 'sensor') {
            $options = [
                'type' => 'string',
                'label' => t('Public key (legacy)'),
                'description' => t('Public key (legacy) for the sensor.'),
                'default_value_callback' => \Drupal\data_stream\Entity\DataStream::class . '::createUniqueKey',
                'weight' => [
                    'form' => 3,
                ],
            ];
            $fields['public_key'] = \Drupal::service('farm_field.factory')->bundleFieldDefinition($options);
        }
        return $fields;
    }
}
