<?php

namespace Drupal\farm_inventory\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_inventory.
 */
class FarmInventoryViewsHooks
{
    /**
     * Implements hook_views_data_alter().
     */
    #[Hook('views_data_alter')]
    public function viewsDataAlter(array &$data)
    {
        // Add computed inventory field to assets.
        if (isset($data['asset'])) {
            $data['asset']['inventory'] = [
                'title' => t('Current inventory'),
                'field' => [
                    'id' => 'asset_inventory',
                    'field_name' => 'inventory',
                ],
            ];
        }
    }
}
