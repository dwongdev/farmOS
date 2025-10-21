<?php

declare(strict_types=1);

namespace Drupal\farm_log\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\log\Entity\LogInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form hook implementations for farm_log.
 */
class FormHooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected AccountInterface $currentUser,
    protected RequestStack $requestStack,
  ) {}

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

    // Save the request query params.
    $query = $this->requestStack->getCurrentRequest()->query;

    // Prepopulate the log asset field.
    if ($query->has('asset')) {

      // Get asset IDs. We can't use $query->get('asset') or
      // $query->all('asset') because those throw a client error if the
      // parameter is not the expected cardinality (single value vs array of
      // values).
      $asset_ids = (array) $query->all()['asset'];
      $asset_field = $entity->get('asset');

      // Add each asset the user has view access to.
      $assets = $this->entityTypeManager->getStorage('asset')->loadMultiple($asset_ids);
      foreach ($assets as $asset) {
        if ($asset->access('view', $this->currentUser)) {
          $asset_field->appendItem($asset);
        }
      }
      $entity->set('asset', $asset_ids);
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_quantity_delete_multiple_confirm_form_alter')]
  public function formQuantityDeleteMultipleConfirmFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Add a warning to bulk quantity delete confirmation form, to emphasize
    // that the quantity will be deleted from all log revisions.
    $message = $this->t('Warning: Deleting quantities will remove them from all revisions of records that reference them.');
    $form['warning'] = [
      '#type' => 'html_tag',
      '#tag' => 'strong',
      '#value' => $message,
      '#weight' => -10,
    ];
  }

}
