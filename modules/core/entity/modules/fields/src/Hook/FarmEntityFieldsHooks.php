<?php

namespace Drupal\farm_entity_fields\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_entity_fields.
 */
class FarmEntityFieldsHooks
{
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        // Include helper functions.
        \Drupal::moduleHandler()->loadInclude('farm_entity_fields', 'inc', 'farm_entity_fields.base_fields');
        // Add common base fields to all asset types.
        if ($entity_type->id() == 'asset') {
            return farm_entity_fields_asset_base_fields();
        } elseif ($entity_type->id() == 'log') {
            return farm_entity_fields_log_base_fields();
        } elseif ($entity_type->id() == 'organization') {
            return farm_entity_fields_organization_base_fields();
        } elseif ($entity_type->id() == 'plan') {
            return farm_entity_fields_plan_base_fields();
        } elseif ($entity_type->id() == 'taxonomy_term') {
            return farm_entity_fields_taxonomy_term_base_fields();
        }
        return [
        ];
    }
    /**
     * Implements hook_entity_base_field_info_alter().
     */
    #[Hook('entity_base_field_info_alter')]
    public function entityBaseFieldInfoAlter(&$fields, \Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        // Only alter asset, log, organization, and plan fields.
        if (!in_array($entity_type->id(), [
            'asset',
            'log',
            'organization',
            'plan',
        ])) {
            return;
        }
        $alter_fields = [
            'name' => [
                'label' => 'hidden',
                'weight' => -100,
            ],
            'status' => [
                'weight' => -95,
            ],
            'timestamp' => [
                'weight' => -90,
            ],
            'type' => [
                'weight' => -85,
                'hidden' => 'form',
            ],
            'created' => [
                'hidden' => TRUE,
            ],
            'uid' => [
                'hidden' => TRUE,
            ],
        ];
        foreach ($alter_fields as $name => $options) {
            // If the field does not exist on this entity type, skip it.
            if (empty($fields[$name])) {
                continue;
            }
            // Load the form and view display options.
            $form_display_options = $fields[$name]->getDisplayOptions('form');
            $view_display_options = $fields[$name]->getDisplayOptions('view');
            // Set the field weight.
            if (!empty($options['weight'])) {
                $form_display_options['weight'] = $view_display_options['weight'] = $options['weight'];
            }
            // Hide the field, if desired.
            if (!empty($options['hidden'])) {
                /** @var bool|string $hidden */
                $hidden = $options['hidden'];
                if ($hidden === TRUE || $hidden === 'form') {
                    $form_display_options['region'] = 'hidden';
                }
                if ($hidden === TRUE || $hidden === 'view') {
                    $view_display_options['region'] = 'hidden';
                }
            }
            // Set the label to inline by default, but allow overrides.
            $view_display_options['label'] = 'inline';
            if (!empty($options['label'])) {
                $view_display_options['label'] = $options['label'];
            }
            switch ($name) {
                // Change state field from transition form to default.
                case 'status':
                    $view_display_options['type'] = 'list_default';
                    break;
                // Don't display a link to the entity type reference.
                case 'type':
                    $view_display_options['settings']['link'] = FALSE;
                    break;
            }
            // Save the options.
            $fields[$name]->setDisplayOptions('form', $form_display_options);
            $fields[$name]->setDisplayOptions('view', $view_display_options);
        }
        // Allow the "type" base field view display to be configured.
        if (!empty($fields['type'])) {
            $fields['type']->setDisplayConfigurable('view', TRUE);
        }
    }
}
