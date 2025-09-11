<?php

declare(strict_types=1);

namespace Drupal\farm_land\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\farm_land\Entity\FarmLandType;
use Drupal\farm_map\Event\MapRenderEvent;
use Drupal\farm_map\LayerStyleLoaderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * An event subscriber for the MapRenderEvent.
 */
class MapRenderEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The layer style loader service.
   *
   * @var \Drupal\farm_map\LayerStyleLoaderInterface
   */
  protected $layerStyleLoader;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, LayerStyleLoaderInterface $layer_style_loader) {
    $this->entityTypeManager = $entity_type_manager;
    $this->layerStyleLoader = $layer_style_loader;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      MapRenderEvent::EVENT_NAME => ['onMapRender', -100],
    ];
  }

  /**
   * React to the MapRenderEvent.
   *
   * @param \Drupal\farm_map\Event\MapRenderEvent $event
   *   The MapRenderEvent.
   */
  public function onMapRender(MapRenderEvent $event) {

    // If the "locations" behavior is added to the map, add layers for each
    // land type.
    if (in_array('locations', $event->getMapBehaviors())) {
      $layers = [];
      $filters = [
        'is_location' => 1,
      ] + ($event->element['#location_filters'] ?? []);

      // Define the parent group.
      $group = $this->t('Locations');

      // Load the land asset type.
      $land_asset_type = $this->entityTypeManager->getStorage('asset_type')->load('land');

      // Create a layer group for the asset type.
      $layers['land'] = [
        'group' => $group,
        'label' => $land_asset_type->label(),
        'is_group' => TRUE,
      ];

      // Load land types.
      $land_types = FarmLandType::loadMultiple();

      // Create a layer for each sub-type.
      foreach ($land_types as $land_type) {
        /** @var \Drupal\farm_map\Entity\LayerStyleInterface $layer_style */
        $conditions = [
          'asset_type' => 'land',
          'land_type' => $land_type->id(),
        ];
        $layer_style = $this->layerStyleLoader->load($conditions);
        if (!empty($layer_style)) {
          $color = $layer_style->get('color');
        }
        $layers['land_' . $land_type->id()] = [
          'group' => $land_asset_type->label(),
          'label' => $land_type->label(),
          'asset_type' => 'land',
          'filters' => $filters + ['land_type_value[]' => $land_type->id()],
          'color' => $color ?? 'orange',
          'zoom' => TRUE,
        ];
      }

      // Add layers to the map settings.
      $settings[$event->getMapTargetId()]['asset_type_layers'] = $layers;
      $event->addSettings($settings);
    }
  }

}
