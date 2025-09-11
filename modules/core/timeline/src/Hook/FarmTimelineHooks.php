<?php

declare(strict_types=1);

namespace Drupal\farm_timeline\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_timeline.
 */
class FarmTimelineHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {
    return [
      'farm_timeline' => [
        'variables' => [
          'attributes' => [],
        ],
      ],
    ];
  }

}
