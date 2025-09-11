<?php

namespace Drupal\farm_map\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_map.
 */
class FarmMapHooks
{
    /**
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme($existing, $type, $theme, $path)
    {
        return [
            'farm_map' => [
                'variables' => [
                    'attributes' => [
                    ],
                ],
            ],
        ];
    }
}
