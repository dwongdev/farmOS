<?php

declare(strict_types=1);

namespace Drupal\farm_update_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_update_test.
 */
class FarmUpdateTestHooks {

  /**
   * Implements hook_farm_update_exclude_config().
   */
  #[Hook('farm_update_exclude_config')]
  public function farmUpdateExcludeConfig() {
    return [
      'farm_flag.flag.priority',
    ];
  }

}
