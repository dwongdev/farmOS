<?php

declare(strict_types=1);

namespace Drupal\farm_update\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_update\FarmUpdateInterface;

/**
 * Update hook implementations for farm_update.
 */
class UpdateHooks {

  use AutowireTrait;

  public function __construct(
    protected FarmUpdateInterface $farmUpdate,
  ) {}

  /**
   * Implements hook_rebuild().
   */
  #[Hook('rebuild')]
  public function rebuild() {
    $this->farmUpdate->rebuild();
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
