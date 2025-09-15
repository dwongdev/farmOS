<?php

declare(strict_types=1);

namespace Drupal\farm_quick\Hook;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\farm_field\FarmFieldFactoryInterface;
use Drupal\farm_quick\QuickFormInstanceManagerInterface;

/**
 * Hook implementations for farm_quick.
 */
class Hooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected QuickFormInstanceManagerInterface $quickFormInstanceManager,
    protected FarmFieldFactoryInterface $farmFieldFactory,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';
    // Quick forms index help text.
    if ($route_name == 'farm.quick') {
      $output .= '<p>' . $this->t('Quick forms make it easy to record common activities.') . '</p>';
    }
    // Load help text for individual quick forms.
    if (strpos($route_name, 'farm.quick.') === 0) {
      $quick_form_id = $route_match->getParameter('id');
      if ($route_name == 'farm.quick.' . $quick_form_id) {
        $quick_form = $this->quickFormInstanceManager->getInstance($quick_form_id);
        $output = [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => Html::escape($quick_form->getHelpText()),
          '#cache' => [
            'tags' => $quick_form->getCacheTags(),
          ],
        ];
      }
    }
    return $output;
  }

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    $fields = [];
    // We only act on asset and log entities.
    if (!in_array($entity_type->id(), [
      'asset',
      'log',
    ])) {
      return $fields;
    }
    // Add a hidden quick form field.
    $options = [
      'type' => 'string',
      'label' => $this->t('Quick form'),
      'description' => $this->t('References the quick form that was used to create this record.'),
      'multiple' => TRUE,
      'hidden' => TRUE,
    ];
    $fields['quick'] = $this->farmFieldFactory->baseFieldDefinition($options);
    return $fields;
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Only alter views_form_ forms.
    if (!str_starts_with($form_id, 'views_form_')) {
      return;
    }
    $target = NULL;
    if (isset($form['header']['asset_bulk_form']['action'])) {
      $target = 'asset_bulk_form';
    }
    if (isset($form['header']['log_bulk_form']['action'])) {
      $target = 'log_bulk_form';
    }
    // Alter action options for the target entity type bulk form.
    if ($target) {
      // Check for disabled quick forms.
      $disabled_quick_forms = $this->entityTypeManager->getStorage('quick_form')->getQuery()->accessCheck(TRUE)->condition('status', FALSE)->execute();
      if (empty($disabled_quick_forms)) {
        return;
      }
      // Remove system actions that end with quick_* for a disabled quick form.
      foreach (array_keys($form['header'][$target]['action']['#options']) as $option_id) {
        if (preg_match("/quick_(.*)/", $option_id, $matches) && in_array($matches[1], $disabled_quick_forms)) {
          unset($form['header'][$target]['action']['#options'][$option_id]);
        }
      }
    }
  }

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    return [
      'quick_form',
    ];
  }

}
