<?php

declare(strict_types=1);

namespace Drupal\farm_setup\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Help hook implementations for farm_setup.
 */
class HelpHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Modules form.
    if ($route_name == 'farm_setup.modules') {
      $output .= '<p>' . $this->t('Select the core and community farmOS modules that you would like to be installed.') . '</p>';
    }

    return $output;
  }

}
