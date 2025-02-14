<?php

declare(strict_types=1);

namespace Drupal\farm_kml;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Adds kml as known format.
 */
class FarmKmlServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Ignore PHPStan error for conditional logic.
    // We check that the container service exists for extra safety.
    // @phpstan-ignore booleanAnd.leftAlwaysTrue
    if ($container->has('http_middleware.negotiation') && is_a($container->getDefinition('http_middleware.negotiation')->getClass(), '\Drupal\Core\StackMiddleware\NegotiationMiddleware', TRUE)) {
      $container->getDefinition('http_middleware.negotiation')->addMethodCall('registerFormat', ['kml', ['application/vnd.google-earth.kml+xml']]);
    }
  }

}
