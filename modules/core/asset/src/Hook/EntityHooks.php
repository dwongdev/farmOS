<?php

declare(strict_types=1);

namespace Drupal\asset\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\asset\Entity\AssetInterface;
use Drupal\asset\Event\AssetEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Entity hook implementations for asset.
 */
class EntityHooks {

  public function __construct(
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher,
  ) {}

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

}
