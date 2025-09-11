<?php

declare(strict_types=1);

namespace Drupal\farm_structure\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_structure.
 */
class FarmStructureHooks {

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type) {

    // Define common asset, log, and plan region items on behalf of core
    // modules.
    switch ($entity_type) {
      case 'asset':
        return [
          'second' => [
            'structure_type',
          ],
        ];

      default:
        return [];
    }
  }

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    return [
      'structure_type',
    ];
  }

}
