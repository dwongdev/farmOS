<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_entity.
 */
class Hooks {

  use AutowireTrait;

  public function __construct(
    protected EntityFieldManagerInterface $entityFieldManager,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected ModuleHandlerInterface $moduleHandler,
  ) {}

  /**
   * Implements hook_modules_installed().
   */
  #[Hook('modules_installed')]
  public function modulesInstalled($modules, $is_syncing) {

    // Rebuild bundle field map when modules are installed.
    $this->entityFieldManager->rebuildBundleFieldMap();
  }

  /**
   * Implements hook_modules_uninstalled().
   */
  #[Hook('modules_uninstalled')]
  public function modulesUninstalled($modules, $is_syncing) {

    // Rebuild bundle field map when modules are uninstalled.
    $this->entityFieldManager->rebuildBundleFieldMap();
  }

  /**
   * Implements hook_entity_field_storage_info_alter().
   *
   * @todo https://www.drupal.org/project/farm/issues/3194206
   */
  #[Hook('entity_field_storage_info_alter')]
  public function entityFieldStorageInfoAlter(&$fields, EntityTypeInterface $entity_type) {

    // Bail if not a farm entity type that allows bundle plugins.
    if (!in_array($entity_type->id(), ['asset', 'log', 'organization', 'plan', 'plan_record', 'quantity'])) {
      return;
    }

    // Get all bundles of the entity type.
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type->id());

    // Invoke hook_farm_entity_bundle_field_info() with each bundle.
    $hook = 'farm_entity_bundle_field_info';
    foreach (array_keys($bundles) as $bundle) {
      $this->moduleHandler->invokeAllWith($hook, function (callable $hook, string $module) use ($fields, $entity_type, $bundle) {

        // Get bundle field definitions provided by the module.
        $definitions = $hook($entity_type, $bundle);

        // Set the provider for each field the module provided.
        // This is required so that field storage definitions are created in the
        // database when the module is installed.
        foreach (array_keys($definitions) as $field) {
          if (isset($fields[$field])) {
            $fields[$field]->setProvider($module);
          }
        }
      });
    }
  }

  /**
   * Implements hook_form_alter().
   *
   * Hides the revision control from the user.
   *
   * @see EntityHooks::entityPresave()
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Only alter content entity forms.
    $form_object = $form_state->getFormObject();
    if (!$form_object instanceof ContentEntityFormInterface) {
      return;
    }

    // Only apply to farm controlled entities.
    $entity = $form_object->getEntity();
    $entity_types = [
      'asset',
      'log',
      'organization',
      'plan',
      'quantity',
    ];
    if (!in_array($entity->getEntityTypeId(), $entity_types)) {
      return;
    }

    // Disable access to the revision checkbox.
    $form['revision']['#access'] = FALSE;
  }

}
