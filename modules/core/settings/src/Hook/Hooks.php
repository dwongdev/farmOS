<?php

declare(strict_types=1);

namespace Drupal\farm_settings\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for farm_settings.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';
    // Modules form.
    if ($route_name == 'farm_settings.modules_form') {
      $output .= '<p>' . $this->t('Select the core and community farmOS modules that you would like to be installed.') . '</p>';
    }
    return $output;
  }

}
