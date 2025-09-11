<?php

declare(strict_types=1);

namespace Drupal\farm_login\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element\Email;

/**
 * Hook implementations for farm_login.
 */
class FarmLoginHooks {

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_user_login_form_alter')]
  public function formUserLoginFormAlter(&$form, FormStateInterface $form_state) {
    $config = \Drupal::config('system.site');

    // Update title and description to include email as an option.
    $form['name']['#title'] = t('Email or username');
    $form['name']['#description'] = t('Enter your @s email address or username.', ['@s' => $config->get('name')]);

    // Update the maxlength to account for emails.
    $form['name']['#maxlength'] = Email::EMAIL_MAX_LENGTH;

    // Add a validation handler for the name field.
    $form['name']['#element_validate'][] = [FarmLoginHooks::class, 'userLoginValidate'];

    // Update password description to be more generic.
    $form['pass']['#description'] = t('Enter the password that accompanies your account.');
  }

  /**
   * Form element validation handler for the username in the user login form.
   *
   * Allows users to authenticate by email.
   */
  public static function userLoginValidate($form, FormStateInterface $form_state) {

    // Check if a username was provided.
    $mail = $form_state->getValue('name');
    if (!empty($mail)) {

      // If the email address is associated with a user, use their account name
      // for later validation.
      if ($user = user_load_by_mail($mail)) {
        $form_state->setValue('name', $user->getAccountName());
      }
    }
  }

}
