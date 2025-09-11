<?php

declare(strict_types=1);

namespace Drupal\farm_import_csv\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_import_csv.
 */
class FarmImportCsvHooks {

  /**
   * Implements hook_file_download().
   */
  #[Hook('file_download')]
  public function fileDownload($uri) {
    // Look up the file entity.
    /** @var \Drupal\file\FileInterface[] $files */
    $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties([
      'uri' => $uri,
    ]);
    /** @var \Drupal\file\FileInterface|null $file */
    $file = reset($files) ?: NULL;
    // If a file was not found, return NULL.
    if (is_null($file)) {
      return NULL;
    }
    // Get file usage.
    /** @var \Drupal\file\FileUsage\FileUsageInterface $usage_service */
    $usage_service = \Drupal::service('file.usage');
    $usages = $usage_service->listUsage($file);
    // If the file was not uploaded by farm_import_csv, return NULL.
    if (!array_key_exists('farm_import_csv', $usages)) {
      return NULL;
    }
    // Get the migration ID.
    $migration_id = array_key_first($usages['farm_import_csv']['migration']);
    // If the current user uploaded the file, or if the current user has access
    // to the migration that imported it, allow access.
    $current_user = \Drupal::currentUser();
    /** @var \Drupal\Core\Access\AccessResult $access */
    $access = \Drupal::service('farm_import_csv.access')->access($current_user, $migration_id);
    if ($file->getOwnerId() === $current_user->id() || $access->isAllowed()) {
      return [
        'Content-Type' => 'application/csv',
        'Content-disposition' => 'attachment; filename="' . $file->getFilename() . '"',
      ];
    }
    // Otherwise, deny access.
    return -1;
  }

  /**
   * Implements hook_entity_delete().
   */
  #[Hook('entity_delete')]
  public function entityDelete(EntityInterface $entity) {
    // If an asset, log, or taxonomy term is deleted, delete associated record
    // from the farm_import_csv_entity table.
    if (in_array($entity->getEntityType()->id(), [
      'asset',
      'log',
      'taxonomy_term',
    ])) {
      \Drupal::database()->delete('farm_import_csv_entity')->condition('entity_type', $entity->getEntityType()->id())->condition('entity_id', $entity->id())->execute();
    }
  }

}
