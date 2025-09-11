<?php

namespace Drupal\farm_ui_dashboard\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_ui_dashboard.
 */
class FarmUiDashboardHooks
{
    /**
     * Implements hook_toolbar_alter().
     */
    #[Hook('toolbar_alter')]
    public function toolbarAlter(&$items)
    {
        // Rename home item to "Dashboard".
        if (!empty($items['home'])) {
            $items['home']['tab']['#title'] = t('Dashboard');
        }
    }
}
