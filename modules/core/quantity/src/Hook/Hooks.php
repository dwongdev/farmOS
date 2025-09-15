<?php

declare(strict_types=1);

namespace Drupal\quantity\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for quantity.
 */
class Hooks {

  use AutowireTrait;

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Implements hook_farm_api_meta_alter().
   */
  #[Hook('farm_api_meta_alter')]
  public function farmApiMetaAlter(&$data) {

    // Add the quantity system of measurement.
    $data['system_of_measurement'] = $this->configFactory->get('quantity.settings')->get('system_of_measurement');
  }

}
