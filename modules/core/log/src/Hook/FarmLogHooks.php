<?php

declare(strict_types=1);

namespace Drupal\farm_log\Hook;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\log\Entity\LogInterface;

/**
 * Hook implementations for farm_log.
 */
class FarmLogHooks {

  /**
   * Implements hook_entity_prepare_form().
   */
  #[Hook('entity_prepare_form')]
  public function entityPrepareForm(EntityInterface $entity, $operation, FormStateInterface $form_state) {

    // If not adding a new entity, bail.
    if ($operation !== 'add' || !$entity->isNew()) {
      return;
    }

    // If the entity is not a log, bail.
    if ($entity->getEntityTypeId() !== 'log' || !$entity instanceof LogInterface) {
      return;
    }

    // Save the current user.
    $user = \Drupal::currentUser();

    // Save the request query params.
    $query = \Drupal::request()->query;

    // Prepopulate the log asset field.
    if ($query->has('asset')) {

      // Get asset IDs. We can't use $query->get('asset') or
      // $query->all('asset') because those throw a client error if the
      // parameter is not the expected cardinality (single value vs array of
      // values).
      $asset_ids = (array) $query->all()['asset'];
      $asset_field = $entity->get('asset');

      // Add each asset the user has view access to.
      $assets = \Drupal::entityTypeManager()->getStorage('asset')->loadMultiple($asset_ids);
      foreach ($assets as $asset) {
        if ($asset->access('view', $user)) {
          $asset_field->appendItem($asset);
        }
      }
      $entity->set('asset', $asset_ids);
    }
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_log_form_alter')]
  public function formLogFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Alter the Quantity inline entity form to set the default quantity type.
    if (!empty($form['quantity']['widget']['actions']['bundle']['#options'])) {
      $bundle_select = &$form['quantity']['widget']['actions']['bundle'];

      // Load the log type storage.
      assert($form_state->getFormObject() instanceof EntityFormInterface);
      /** @var \Drupal\log\Entity\Log $entity */
      $entity = $form_state->getFormObject()->getEntity();

      // Determine the default quantity type.
      $default_type = farm_log_quantity_default_type($entity->bundle());

      // Set the default value.
      if (array_key_exists($default_type, $bundle_select['#options'])) {
        $bundle_select['#default_value'] = $default_type;
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_quantity_delete_multiple_confirm_form_alter')]
  public function formQuantityDeleteMultipleConfirmFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Add a warning to bulk quantity delete confirmation form, to emphasize
    // that the quantity will be deleted from all log revisions.
    $message = t('Warning: Deleting quantities will remove them from all revisions of records that reference them.');
    $form['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'strong',
      '#value' => $message,
      '#weight' => -10,
    ];
  }

}
