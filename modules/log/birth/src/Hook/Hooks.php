<?php

declare(strict_types=1);

namespace Drupal\farm_birth\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Url;
use Drupal\asset\Entity\AssetInterface;

/**
 * Hook implementations for farm_birth.
 */
class Hooks {

  use AutowireTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_entity_bundle_field_info().
   */
  #[Hook('entity_bundle_field_info')]
  public function entityBundleFieldInfo(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    $fields = [];
    // Add the UniqueBirthLog validation constraint to the asset field of birth
    // logs. We need to do this via hook_entity_bundle_field_info() instead of
    // hook_entity_field_info_alter() because this module also provides a
    // BaseFieldOverride for the asset field, and there is a Drupal core issue
    // that prevents these from working together normally.
    // @see https://www.drupal.org/project/drupal/issues/3193351
    if ($entity_type->id() == 'log' && $bundle == 'birth') {
      $fields['asset'] = BaseFieldOverride::loadByName($entity_type->id(), $bundle, 'asset') ?: clone $base_field_definitions['asset'];
      $fields['asset']->addConstraint('UniqueBirthLog');
    }
    return $fields;
  }

  /**
   * Implements hook_ENTITY_TYPE_view_alter().
   */
  #[Hook('asset_view_alter')]
  public function assetViewAlter(array &$build, AssetInterface $asset, EntityViewDisplayInterface $display) {

    // Only alter animal assets.
    if ($asset->bundle() != 'animal') {
      return;
    }

    // Add a link to the asset's birth log.
    if (!empty($build['birthdate'][0])) {
      $log_storage = $this->entityTypeManager->getStorage('log');
      $log_ids = $log_storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('type', 'birth')
        ->condition('asset', $asset->id())
        ->execute();

      // Render a link to the log with a title of the timestamp field value.
      if (!empty($log_ids)) {
        $title = $build['birthdate'][0];
        $build['birthdate'][0] = [
          '#type' => 'link',
          '#title' => $title,
          '#url' => Url::fromRoute('entity.log.canonical', ['log' => reset($log_ids)]),
        ];
      }
    }
  }

}
