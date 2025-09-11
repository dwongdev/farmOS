<?php

declare(strict_types=1);

namespace Drupal\farm_update\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_update.
 */
class FarmUpdateHooks {

  /**
   * Implements hook_rebuild().
   */
  #[Hook('rebuild')]
  public function rebuild() {
    \Drupal::service('farm.update')->rebuild();
  }

  /**
   * Implements hook_farm_update_exclude_config().
   */
  #[Hook('farm_update_exclude_config')]
  public function farmUpdateExcludeConfig() {
    // Exclude Drupal core configurations from automatic updates.
    return [
      'user.role.anonymous',
      'user.role.authenticated',
    ];
  }

}
