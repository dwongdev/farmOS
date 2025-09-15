<?php

declare(strict_types=1);

namespace Drupal\farm_api\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_api.
 */
class FarmApiHooks {

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    // Allow farmOS core entity types on behalf of the modules that provide
    // them.
    return [
      'asset',
      'asset_type',
      'data_stream',
      'data_stream_type',
      'file',
      'log',
      'log_type',
      'plan',
      'plan_type',
      'plan_record',
      'plan_record_type',
      'quantity',
      'quantity_type',
      'taxonomy_term',
      'taxonomy_vocabulary',
      'user',
      'user_role',
    ];
  }

}
