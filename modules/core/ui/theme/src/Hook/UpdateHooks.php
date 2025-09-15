<?php

declare(strict_types=1);

namespace Drupal\farm_ui_theme\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Update hook implementations for farm_ui_theme.
 */
class UpdateHooks {

  /**
   * Implements hook_farm_update_exclude_config().
   */
  #[Hook('farm_update_exclude_config')]
  public function farmUpdateExcludeConfig() {

    // Exclude config that we have overridden in hook_install() or the
    // farm_ui_theme.overrider service.
    return [
      'block.block.gin_local_actions',
      'block.block.gin_content',
    ];
  }

}
