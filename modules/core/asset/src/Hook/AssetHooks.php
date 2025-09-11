<?php

declare(strict_types=1);

namespace Drupal\asset\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Hook implementations for asset.
 */
class AssetHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Main module help for the asset module.
    if ($route_name == 'help.page.asset') {
      $output = '';
      $output .= '<h3>' . t('About') . '</h3>';
      $output .= '<p>' . t('Provides asset entity') . '</p>';
    }

    return $output;
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'asset' => [
        'render element' => 'elements',
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_asset')]
  public function themeSuggestionsAsset(array $variables) {
    $suggestions = [];
    $asset = $variables['elements']['#asset'];
    $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');
    $suggestions[] = 'asset__' . $sanitized_view_mode;
    $suggestions[] = 'asset__' . $asset->bundle();
    $suggestions[] = 'asset__' . $asset->bundle() . '__' . $sanitized_view_mode;
    $suggestions[] = 'asset__' . $asset->id();
    $suggestions[] = 'asset__' . $asset->id() . '__' . $sanitized_view_mode;
    return $suggestions;
  }

}
