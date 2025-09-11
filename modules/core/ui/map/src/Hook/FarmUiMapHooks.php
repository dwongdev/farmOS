<?php

declare(strict_types=1);

namespace Drupal\farm_ui_map\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_ui_map.
 */
class FarmUiMapHooks {

  /**
   * Implements hook_farm_dashboard_panes().
   */
  #[Hook('farm_dashboard_panes')]
  public function farmDashboardPanes() {
    return [
      'dashboard_map' => [
        'block' => 'map_block',
        'args' => [
          'map_type' => 'dashboard',
        ],
        'region' => 'top',
      ],
    ];
  }

  /**
   * Implements hook_menu_local_actions_alter().
   */
  #[Hook('menu_local_actions_alter')]
  public function menuLocalActionsAlter(&$local_actions) {

    // Include asset.add.log.* location actions on the asset.map_popup route.
    foreach ($local_actions as $id => $local_action) {
      if (strpos($id, 'farm.actions:farm.asset.add.log.') === 0) {
        $local_actions[$id]['appears_on'][] = 'farm_ui_map.asset.map_popup';
      }
    }
  }

}
