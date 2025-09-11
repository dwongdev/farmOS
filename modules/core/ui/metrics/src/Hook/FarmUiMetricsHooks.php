<?php

namespace Drupal\farm_ui_metrics\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_ui_metrics.
 */
class FarmUiMetricsHooks
{
    /**
     * Implements hook_farm_dashboard_panes().
     */
    #[Hook('farm_dashboard_panes')]
    public function farmDashboardPanes()
    {
        return [
            'metrics' => [
                'block' => 'farm_metrics_block',
                'title' => t('Metrics'),
                'region' => 'second',
            ],
        ];
    }
}
