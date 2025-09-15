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
    if ($entity_type == 'asset') {
      return [
        'second' => [
          'structure_type',
        ],
      ];
    }
    return [];
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
