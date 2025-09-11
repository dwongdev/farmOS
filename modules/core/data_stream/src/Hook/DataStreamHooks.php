<?php

namespace Drupal\data_stream\Hook;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Url;
use Drupal\data_stream\Entity\DataStreamInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for data_stream.
 */
class DataStreamHooks
{
    /**
     * Implements hook_entity_type_build().
     */
    #[Hook('entity_type_build')]
    public function entityTypeBuild(array &$entity_types)
    {
        if (!empty($entity_types['data_stream'])) {
            $entity_types['data_stream']->set('bundle_plugin_type', 'data_stream_type');
        }
    }
}
