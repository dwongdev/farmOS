<?php

namespace Drupal\farm_image\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_image.
 */
class FarmImageHooks
{
    /**
     * Implements hook_farm_update_exclude_config().
     */
    #[Hook('farm_update_exclude_config')]
    public function farmUpdateExcludeConfig()
    {
        // Exclude config that we have overridden in hook_install().
        return [
            'image.style.large',
            'image.style.medium',
            'image.style.thumbnail',
            'image.style.wide',
        ];
    }
}
