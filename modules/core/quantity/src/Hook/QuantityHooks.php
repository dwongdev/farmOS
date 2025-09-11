<?php

declare(strict_types=1);

namespace Drupal\quantity\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for quantity.
 */
class QuantityHooks {

  /**
   * Implements hook_farm_api_meta_alter().
   */
  #[Hook('farm_api_meta_alter')]
  public function farmApiMetaAlter(&$data) {

    // Add the quantity system of measurement.
    $data['system_of_measurement'] = \Drupal::config('quantity.settings')->get('system_of_measurement');
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'quantity' => [
        'render element' => 'elements',
      ],
      'field__quantity__field' => [
        'template' => 'field--quantity--field',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_field')]
  public function themeSuggestionsField(array $variables) {
    $suggestions = [];

    // Add a theme hook suggestion for theming all fields on quantity entities.
    // Note that the field__quantity theme hook is used for any entity with
    // a field called "quantity", such as the log.quantity entity reference.
    if ($variables['element']['#entity_type'] == 'quantity') {
      $suggestions[] = 'field__quantity__field';
    }

    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_quantity')]
  public function themeSuggestionsQuantity(array $variables) {
    $suggestions = [];
    $quantity = $variables['elements']['#quantity'];
    $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');
    $suggestions[] = 'quantity__' . $sanitized_view_mode;
    $suggestions[] = 'quantity__' . $quantity->bundle();
    $suggestions[] = 'quantity__' . $quantity->bundle() . '__' . $sanitized_view_mode;
    $suggestions[] = 'quantity__' . $quantity->id();
    $suggestions[] = 'quantity__' . $quantity->id() . '__' . $sanitized_view_mode;
    return $suggestions;
  }

}
