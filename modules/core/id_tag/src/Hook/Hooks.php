<?php

declare(strict_types=1);

namespace Drupal\farm_id_tag\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_id_tag.
 */
class Hooks {

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    return [
      'tag_type',
    ];
  }

}
