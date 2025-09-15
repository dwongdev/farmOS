<?php

declare(strict_types=1);

namespace Drupal\farm_ui_theme\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_ui_theme\FarmUiThemeHelper;

/**
 * Hook implementations for farm_ui_theme.
 */
class Hooks {

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_asset_form_alter')]
  public function formAssetFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\Core\Entity\EntityForm $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\asset\Entity\AssetInterface $entity */
    $entity = $form_object->getEntity();
    FarmUiThemeHelper::setArchivedMessage($entity);
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_plan_form_alter')]
  public function formPlanFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\Core\Entity\EntityForm $form_object */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\asset\Entity\AssetInterface $entity */
    $entity = $form_object->getEntity();
    FarmUiThemeHelper::setArchivedMessage($entity);
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_quick_form_alter')]
  public function formQuickFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    $form['#attached']['library'][] = 'farm_ui_theme/quick';
  }

  /**
   * Implements hook_farm_update_exclude_config().
   */
  #[Hook('farm_update_exclude_config')]
  public function farmUpdateExcludeConfig() {

    // Exclude config that we have overridden in hook_install() or the
    // farm_ui_theme.overrider service.
    return [
      'block.block.gin_local_actions',
      'block.block.gin_content',
    ];
  }

}
