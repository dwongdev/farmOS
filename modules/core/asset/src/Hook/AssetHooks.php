<?php

declare(strict_types=1);

namespace Drupal\asset\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\asset\Entity\AssetInterface;
use Drupal\asset\Event\AssetEvent;

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
   * Implements hook_ENTITY_TYPE_presave().
   */
  #[Hook('asset_presave')]
  public function assetPresave(AssetInterface $asset) {

    // Dispatch an event on asset presave.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, AssetEvent::PRESAVE);
  }

  /**
   * Implements hook_ENTITY_TYPE_insert().
   */
  #[Hook('asset_insert')]
  public function assetInsert(AssetInterface $asset) {

    // Dispatch an event on asset insert.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, AssetEvent::INSERT);
  }

  /**
   * Implements hook_ENTITY_TYPE_update().
   */
  #[Hook('asset_update')]
  public function assetUpdate(AssetInterface $asset) {

    // Dispatch an event on asset update.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, AssetEvent::UPDATE);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('asset_delete')]
  public function assetDelete(AssetInterface $asset) {

    // Dispatch an event on asset delete.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, AssetEvent::DELETE);
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
