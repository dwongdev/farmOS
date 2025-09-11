<?php

declare(strict_types=1);

namespace Drupal\farm_ui_dashboard_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_ui_dashboard_test.
 */
class FarmUiDashboardTestHooks {

  /**
   * Implements hook_farm_dashboard_panes().
   */
  #[Hook('farm_dashboard_panes')]
  public function farmDashboardPanes() {
    return [
      'dashboard_block' => [
        'block' => 'dashboard_test_block',
      ],
      'dashboard_view' => [
        'view' => 'dashboard_test_view',
        'view_display_id' => 'user_list',
      ],
    ];
  }

}
