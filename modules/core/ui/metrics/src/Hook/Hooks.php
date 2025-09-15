<?php

declare(strict_types=1);

namespace Drupal\farm_ui_metrics\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for farm_ui_metrics.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_farm_dashboard_panes().
   */
  #[Hook('farm_dashboard_panes')]
  public function farmDashboardPanes() {
    return [
      'metrics' => [
        'block' => 'farm_metrics_block',
        'title' => $this->t('Metrics'),
        'region' => 'second',
      ],
    ];
  }

}
