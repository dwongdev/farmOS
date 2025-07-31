<?php

declare(strict_types=1);

namespace Drupal\asset;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the controller class for assets.
 *
 * This extends the base storage class, adding required special handling for
 * asset entities.
 */
class AssetStorage extends SqlContentEntityStorage {

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs an AssetStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache backend to be used.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(EntityTypeInterface $entity_type, Connection $database, EntityFieldManagerInterface $entity_field_manager, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, MemoryCacheInterface $memory_cache, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time) {
    parent::__construct($entity_type, $database, $entity_field_manager, $cache, $language_manager, $memory_cache, $entity_type_bundle_info, $entity_type_manager);
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doPreSave(EntityInterface $entity) {
    /** @var \Drupal\asset\Entity\AssetInterface $entity */
    $id = parent::doPreSave($entity);

    // If there is no original entity, bail.
    if (empty($entity->original)) {
      return $id;
    }

    // Load new and original archived state to see if it is changed.
    $archived = $entity->get('archived')->value;
    $original_archived = $entity->original->get('archived')->value;
    $archived_changed = $archived != $original_archived;

    // If the original asset is not archived, and the archived state is not
    // changing, but the archived timestamp is set, then archive the asset.
    if (!$original_archived && !$archived_changed && $entity->getArchivedTime() != NULL) {
      $entity->set('archived', TRUE);
    }

    // If the original asset is archived, and the archived state is not
    // changing, but the archived timestamp is NULL, then unarchive the asset.
    if ($original_archived && !$archived_changed && $entity->getArchivedTime() == NULL) {
      $entity->set('archived', FALSE);
    }

    // If the archived state has not changed, bail.
    if (!$archived_changed) {
      return $id;
    }

    // If the archived state has changed to TRUE and no archived timestamp was
    // specified, set it to the current time.
    if ($archived && $entity->getArchivedTime() == NULL) {
      $entity->setArchivedTime($this->time->getRequestTime());
    }

    // Or, if the archived state has changed from archived, set a null value.
    elseif ($original_archived) {
      $entity->setArchivedTime(NULL);
    }

    return $id;
  }

}
