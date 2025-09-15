<?php

declare(strict_types=1);

namespace Drupal\farm_flag\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for farm_flag.
 */
class Hooks {

  /**
   * Implements hook_farm_ui_theme_region_items().
   */
  #[Hook('farm_ui_theme_region_items')]
  public function farmUiThemeRegionItems(string $entity_type) {
    if (in_array($entity_type, ['asset', 'log', 'plan'])) {
      return [
        'second' => [
          'flag',
        ],
      ];
    }
    return [];
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'field__flag' => [
        'base hook' => 'field',
        'initial preprocess' => static::class . '::preprocessFieldFlag',
      ],
    ];
  }

  /**
   * Prepares variables for field--flag templates.
   *
   * Adds classes to each flag wrapper.
   *
   * Default template: field--flag.html.twig.
   *
   * @param array $variables
   *   An associative array containing:
   *   - element: An associative array containing render arrays for the list of
   *     flags.
   */
  public function preprocessFieldFlag(array &$variables) {

    // Preprocess list_string flag fields.
    if ($variables['element']['#field_type'] == 'list_string') {

      /** @var \Drupal\Core\Field\FieldItemListInterface $items */
      $items = $variables['element']['#items'];

      // Add classes to each flag.
      foreach ($items as $key => $list_item) {
        $classes = ['flag', 'flag--' . $list_item->getString()];
        $variables['items'][$key]['attributes']->addClass($classes);
      }
    }
  }

  /**
   * Implements hook_farm_api_allow_resource_types().
   */
  #[Hook('farm_api_allow_resource_types')]
  public function farmApiAllowResourceTypes() {
    return [
      'flag',
    ];
  }

}
