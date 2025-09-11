<?php

namespace Drupal\farm_comment_log\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_comment_log.
 */
class FarmCommentLogHooks
{
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        $fields = [
        ];
        // Add comment base field to logs.
        if ($entity_type->id() == 'log') {
            $fields['comment'] = farm_comment_base_field_definition('log');
        }
        return $fields;
    }
}
