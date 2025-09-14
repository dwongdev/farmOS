<?php

declare(strict_types=1);

namespace Drupal\asset\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\asset\Entity\AssetInterface;
use Drupal\asset\Event\AssetEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Hook implementations for asset.
 */
class AssetHooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Main module help for the asset module.
    if ($route_name == 'help.page.asset') {
      $output = '';
      $output .= '<h3>' . $this->t('About') . '</h3>';
      $output .= '<p>' . $this->t('Provides asset entity') . '</p>';
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
    $this->eventDispatcher->dispatch($event, AssetEvent::PRESAVE);
  }

  /**
   * Implements hook_ENTITY_TYPE_insert().
   */
  #[Hook('asset_insert')]
  public function assetInsert(AssetInterface $asset) {

    // Dispatch an event on asset insert.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $this->eventDispatcher->dispatch($event, AssetEvent::INSERT);
  }

  /**
   * Implements hook_ENTITY_TYPE_update().
   */
  #[Hook('asset_update')]
  public function assetUpdate(AssetInterface $asset) {

    // Dispatch an event on asset update.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $this->eventDispatcher->dispatch($event, AssetEvent::UPDATE);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('asset_delete')]
  public function assetDelete(AssetInterface $asset) {

    // Dispatch an event on asset delete.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new AssetEvent($asset);
    $this->eventDispatcher->dispatch($event, AssetEvent::DELETE);
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'asset' => [
        'render element' => 'elements',
        'initial preprocess' => static::class . '::preprocessAsset',
      ],
    ];
  }

  /**
   * Prepares variables for asset templates.
   *
   * Default template: asset.html.twig.
   *
   * @param array $variables
   *   An associative array containing:
   *   - elements: An associative array containing the asset information and any
   *     fields attached to the asset. Properties used:
   *     - #asset: A \Drupal\asset\Entity\Asset object. The asset entity.
   *   - attributes: HTML attributes for the containing element.
   */
  public function preprocessAsset(array &$variables) {
    $variables['asset'] = $variables['elements']['#asset'];
    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
      $variables['content'][$key] = $variables['elements'][$key];
    }
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
