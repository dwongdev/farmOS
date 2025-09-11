<?php

declare(strict_types=1);

namespace Drupal\farm_l10n\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Hook implementations for farm_l10n.
 */
class FarmL10nHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Help text for the farm/settings/language form.
    if ($route_name == 'farm_l10n.settings') {
      $output .= '<p>' . t('Select the default language for the user interface. Individual users can override this by editing their profile.') . '</p>';
    }

    return $output;
  }

  /**
   * Implements hook_entity_bundle_info_alter().
   */
  #[Hook('entity_bundle_info_alter')]
  public function entityBundleInfoAlter(&$bundles) {

    // If the content translation module is not enabled, alter all entity type
    // bundles to mark them as not translatable. This fixes an issue with
    // JSON:API PATCH requests when authenticated as a user with a non-default
    // language.
    // @see https://www.drupal.org/project/farm/issues/3335267
    if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
      return;
    }
    foreach ($bundles as $entity_type => $entity_type_bundles) {
      foreach ($entity_type_bundles as $bundle => $bundle_info) {
        if (!empty($bundle_info['translatable']) && $bundle_info['translatable'] === TRUE) {
          $bundles[$entity_type][$bundle]['translatable'] = FALSE;
        }
      }
    }
  }

}
