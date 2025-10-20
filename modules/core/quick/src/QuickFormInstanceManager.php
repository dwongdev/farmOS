<?php

declare(strict_types=1);

namespace Drupal\farm_quick;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\farm_quick\Entity\QuickFormInstance;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Quick form instance manager.
 */
class QuickFormInstanceManager implements QuickFormInstanceManagerInterface {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected QuickFormPluginManager $quickFormPluginManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.quick_form'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getInstances(): array {
    $instances = [];

    // Iterate through quick form plugin definitions.
    foreach ($this->quickFormPluginManager->getDefinitions() as $plugin) {

      // Load quick form instance configuration entities for this plugin.
      // Exclude disabled quick forms.
      /** @var \Drupal\farm_quick\Entity\QuickFormInstanceInterface[] $entities */
      $entities = $this->entityTypeManager->getStorage('quick_form')->loadByProperties(['plugin' => $plugin['id']]);
      foreach ($entities as $entity) {
        $entity->getPlugin()->setQuickId($entity->id());
        $instances[$entity->id()] = $entity;
      }

      // Or, if this plugin does not require a quick form instance configuration
      // entity, then add a new (unsaved) config entity with default values from
      // the plugin.
      if (!isset($instances[$plugin['id']]) && empty($plugin['requiresEntity'])) {
        $instances[$plugin['id']] = QuickFormInstance::create(['id' => $plugin['id'], 'plugin' => $plugin['id']]);
      }
    }

    return $instances;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance($id) {

    // First attempt to load a quick form instance config entity.
    $entity = $this->entityTypeManager->getStorage('quick_form')->load($id);
    if (!empty($entity)) {
      $entity->getPlugin()->setQuickId($id);
      return $entity;
    }

    // Or, if this plugin does not require a quick form instance configuration
    // entity, then add a new (unsaved) config entity with default values from
    // the plugin.
    elseif (($plugin = $this->quickFormPluginManager->getDefinition($id, FALSE)) && empty($plugin['requiresEntity'])) {
      return QuickFormInstance::create(['id' => $id, 'plugin' => $id]);
    }

    // No quick form could be instantiated.
    return NULL;
  }

}
