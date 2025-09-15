<?php

declare(strict_types=1);

namespace Drupal\farm_l10n\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Hook implementations for farm_l10n.
 */
class Hooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected ConfigFactoryInterface $configFactory,
    protected MessengerInterface $messenger,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Help text for the farm/settings/language form.
    if ($route_name == 'farm_l10n.settings') {
      $output .= '<p>' . $this->t('Select the default language for the user interface. Individual users can override this by editing their profile.') . '</p>';
    }

    return $output;
  }

  /**
   * Implements hook_entity_bundle_info_alter().
   */
  #[Hook('entity_bundle_info_alter')]
  public function entityBundleInfoAlter(&$bundles) {

    // If the content translation module is not enabled, alter all entity type
    // bundles to mark them as not translatable. This fixes an issue with
    // JSON:API PATCH requests when authenticated as a user with a non-default
    // language.
    // @see https://www.drupal.org/project/farm/issues/3335267
    if ($this->moduleHandler->moduleExists('content_translation')) {
      return;
    }
    foreach ($bundles as $entity_type => $entity_type_bundles) {
      foreach ($entity_type_bundles as $bundle => $bundle_info) {
        if (!empty($bundle_info['translatable']) && $bundle_info['translatable'] === TRUE) {
          $bundles[$entity_type][$bundle]['translatable'] = FALSE;
        }
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_language_admin_overview_form_alter')]
  public function formLanguageAdminOverviewFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Disable the ability to change the site's default language and direct
    // users to /farm/settings/language instead.
    // @see https://www.drupal.org/project/farm/issues/3257430
    $message = $this->t('To change the default language of farmOS, please go to <a href=":url">farmOS language settings</a>.', [':url' => Url::fromRoute('farm_l10n.settings')->toString()]);
    $this->messenger->addWarning($message);
    foreach (Element::children($form['languages']) as $langcode) {
      $form['languages'][$langcode]['default']['#access'] = FALSE;
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_user_register_form_alter')]
  public function formUserRegisterFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Use the "Selected language" as the default for new users (unless it is
    // still set to "site_default").
    $selected_language = $this->configFactory->get('language.negotiation')->get('selected_langcode');
    if ($selected_language == 'site_default') {
      return;
    }
    if (!empty($form['language']['preferred_langcode'])) {
      $form['language']['preferred_langcode']['#default_value'] = $selected_language;
    }
    if (!empty($form['language']['preferred_admin_langcode'])) {
      $form['language']['preferred_admin_langcode']['#default_value'] = $selected_language;
    }
  }

}
