<?php

declare(strict_types=1);

namespace Drupal\quantity\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\quantity\Entity\QuantityInterface;
use Drupal\quantity\Event\QuantityEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Hook implementations for quantity.
 */
class QuantityHooks {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher,
    protected ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_presave().
   */
  #[Hook('quantity_presave')]
  public function quantityPresave(QuantityInterface $quantity) {

    // Dispatch an event on quantity presave.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new QuantityEvent($quantity);
    $this->eventDispatcher->dispatch($event, QuantityEvent::PRESAVE);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('quantity_delete')]
  public function quantityDelete(QuantityInterface $quantity) {

    // Dispatch an event on quantity delete.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new QuantityEvent($quantity);
    $this->eventDispatcher->dispatch($event, QuantityEvent::DELETE);
  }

  /**
   * Implements hook_farm_api_meta_alter().
   */
  #[Hook('farm_api_meta_alter')]
  public function farmApiMetaAlter(&$data) {

    // Add the quantity system of measurement.
    $data['system_of_measurement'] = $this->configFactory->get('quantity.settings')->get('system_of_measurement');
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'quantity' => [
        'render element' => 'elements',
        'initial preprocess' => static::class . '::preprocessQuantity',
      ],
      'field__quantity__field' => [
        'template' => 'field--quantity--field',
        'base hook' => 'field',
      ],
    ];
  }

  /**
   * Prepares variables for quantity templates.
   *
   * Default template: quantity.html.twig.
   *
   * @param array $variables
   *   An associative array containing:
   *   - elements: An associative array containing the quantity information and
   *     any fields attached to the quantity. Properties used:
   *     - #quantity: A \Drupal\quantity\Entity\Quantity object. Quantity
   *       entity.
   *   - attributes: HTML attributes for the containing element.
   */
  public function preprocessQuantity(array &$variables) {
    $variables['quantity'] = $variables['elements']['#quantity'];

    // Helpful $content variable for templates.
    $variables['content'] = [];
    foreach (Element::children($variables['elements']) as $key) {
      if (!empty($variables['elements'][$key]['#items'])) {
        $variables['content'][$key] = $variables['elements'][$key];
      }
    }
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
