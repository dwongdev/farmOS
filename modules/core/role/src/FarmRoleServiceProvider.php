<?php

declare(strict_types=1);

namespace Drupal\farm_role;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Override the permission_checker service with our own class.
 */
class FarmRoleServiceProvider extends ServiceProviderBase implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('permission_checker');
    $definition->setClass('Drupal\farm_role\ManagedRolePermissionChecker');
    $definition->setAutowired(TRUE);
    $definition->setArguments([]);
  }

}
