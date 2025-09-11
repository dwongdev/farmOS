<?php

declare(strict_types=1);

namespace Drupal\farm_location\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_location.
 */
class FarmLocationViewsHooks {

  /**
   * Implements hook_views_data_alter().
   */
  #[Hook('views_data_alter')]
  public function viewsDataAlter(array &$data) {

    // Add computed fields to assets.
    if (isset($data['asset'])) {

      // Computed geometry.
      $data['asset']['geometry'] = [
        'title' => t('Geometry'),
        'field' => [
          'id' => 'asset_geometry',
          'field_name' => 'geometry',
        ],
      ];

      // Computed location.
      $data['asset']['location'] = [
        'title' => t('Current location'),
        'field' => [
          'id' => 'asset_location',
          'field_name' => 'location',
        ],
        'argument' => [
          'id' => 'asset_location',
        ],
      ];
    }
  }

}
