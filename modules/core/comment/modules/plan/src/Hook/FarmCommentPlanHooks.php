<?php

namespace Drupal\farm_comment_plan\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_comment_plan.
 */
class FarmCommentPlanHooks
{
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        $fields = [
        ];
        // Add comment base field to plans.
        if ($entity_type->id() == 'plan') {
            $fields['comment'] = farm_comment_base_field_definition('plan');
        }
        return $fields;
    }
}
