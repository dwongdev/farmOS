<?php

namespace Drupal\farm_settings\Hook;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_settings.
 */
class FarmSettingsHooks
{
    /**
     * Implements hook_help().
     */
    #[Hook('help')]
    public function help($route_name, \Drupal\Core\Routing\RouteMatchInterface $route_match)
    {
        $output = '';
        // Modules form.
        if ($route_name == 'farm_settings.modules_form') {
            $output .= '<p>' . t('Select the core and community farmOS modules that you would like to be installed.') . '</p>';
        }
        return $output;
    }
}
