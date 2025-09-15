<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Hook;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\OrderBefore;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\EntityPermissionProvider;
use Drupal\farm_entity\BundlePlugin\FarmEntityBundlePluginHandler;
use Drupal\farm_entity\Routing\BundleEntityTypeRouteProvider;
use Drupal\farm_entity\Routing\EntityRouteProvider;

/**
 * Hook implementations for farm_entity.
 */
class Hooks {

  use AutowireTrait;

  public function __construct(
    protected EntityFieldManagerInterface $entityFieldManager,
    protected EntityTypeBundleInfoInterface $entityTypeBundleInfo,
    protected ModuleHandlerInterface $moduleHandler,
    protected AccountInterface $currentUser,
    protected TimeInterface $time,
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
   * Implements hook_entity_type_build().
   *
   * Make sure this module's implementation runs before that of the entity
   * module, so that we can override the bundle plugin handler, and so that we
   * can set the Log entity type's bundle_plugin_type.
   */
  #[Hook('entity_type_build', order: new OrderBefore(['entity']))]
  public function entityTypeBuild(array &$entity_types) {

    // Allow the "view label" operation on the bundle entity type.
    foreach (['asset', 'log', 'organization', 'plan', 'quantity', 'data_stream'] as $entity_type) {
      if (!empty($entity_types[$entity_type])) {
        $bundle_entity_type = $entity_types[$entity_type]->getBundleEntityType();
        $entity_types[$bundle_entity_type]->setHandlerClass('access', EntityAccessControlHandler::class);
        $entity_types[$bundle_entity_type]->setHandlerClass('permission_provider', EntityPermissionProvider::class);
      }
    }

    // Enable the use of bundle plugins on specific entity types.
    foreach (['asset', 'log', 'organization', 'plan', 'plan_record', 'quantity'] as $entity_type) {
      if (!empty($entity_types[$entity_type])) {
        $entity_types[$entity_type]->set('bundle_plugin_type', $entity_type . '_type');
        $entity_types[$entity_type]->setHandlerClass('bundle_plugin', FarmEntityBundlePluginHandler::class);

        // Override default entity route provider class.
        $route_providers = $entity_types[$entity_type]->getRouteProviderClasses();
        $route_providers['default'] = EntityRouteProvider::class;
        $entity_types[$entity_type]->setHandlerClass('route_provider', $route_providers);

        // Deny access to the entity type add form. New entity types of entities
        // with bundle plugins cannot be created in the UI.
        // See https://www.drupal.org/project/farm/issues/3196423
        $bundle_entity_type = $entity_types[$entity_type]->getBundleEntityType();
        $route_providers = $entity_types[$bundle_entity_type]->getRouteProviderClasses();
        $route_providers['default'] = BundleEntityTypeRouteProvider::class;
        $entity_types[$bundle_entity_type]->setHandlerClass('route_provider', $route_providers);
      }
    }
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
   * Implements hook_entity_presave().
   *
   * Forces revisions on all farm entities if the entity type supports them and
   * the bundle has them enabled. This removes the option for users to disable a
   * revision per-entity but as JSON:API doesn't support revisions yet, this is
   * a trade-off that allows us to create revisions consistently on both the UI
   * and the API.
   */
  #[Hook('entity_presave')]
  public function entityPresave(EntityInterface $entity) {

    // Only apply to farm controlled entities.
    $entity_types = [
      'asset',
      'log',
      'organization',
      'plan',
      'quantity',
    ];
    if (!in_array($entity->getEntityTypeId(), $entity_types) || !$entity instanceof RevisionLogInterface || !$entity instanceof FieldableEntityInterface) {
      return;
    }

    // Always create new revisions when an entity is saved.
    // This ensures a proper audit trail is available for important records.
    $entity_type = $entity->get($entity->getEntityType()->getKey('bundle'))->entity;
    if ($entity_type instanceof RevisionableEntityBundleInterface && $entity_type->shouldCreateNewRevision() && $entity->getEntityType()->isRevisionable()) {

      // Always create a new revision.
      $entity->setNewRevision(TRUE);

      // If the new revision log message matches the original, then set a blank
      // revision log message. We don't want the same message repeated across
      // every revision created by the API.
      if (!empty($entity->getOriginal())) {
        if ($entity->getOriginal()->get('revision_log_message')->value == $entity->get('revision_log_message')->value) {
          $entity->setRevisionLogMessage('');
        }
      }

      // Set the user ID and creation time.
      $entity->setRevisionUserId($this->currentUser->id());
      $entity->setRevisionCreationTime($this->time->getRequestTime());
    }
  }

  /**
   * Implements hook_form_alter().
   *
   * Hides the revision control from the user, @see ::entityPresave()
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
