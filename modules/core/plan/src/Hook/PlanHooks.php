<?php

declare(strict_types=1);

namespace Drupal\plan\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\plan\Entity\PlanInterface;

/**
 * Hook implementations for plan.
 */
class PlanHooks {

  use AutowireTrait;

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';
    // Main module help for the plan module.
    if ($route_name == 'help.page.plan') {
      $output = '';
      $output .= '<h3>' . t('About') . '</h3>';
      $output .= '<p>' . t('Provides plan entity') . '</p>';
    }
    return $output;
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('plan_delete')]
  public function planDelete(PlanInterface $plan) {

    // Delete all plan_record entities associated with the plan.
    $plan_record_storage = $this->entityTypeManager->getStorage('plan_record');
    $plan_ids = $plan_record_storage->getQuery()
      ->condition('plan', $plan->id())
      ->accessCheck(FALSE)
      ->execute();
    if (count($plan_ids) < 1) {
      return;
    }
    foreach (array_chunk($plan_ids, 100) as $chunk) {
      $plan_records = $plan_record_storage->loadMultiple($chunk);
      $plan_record_storage->delete($plan_records);
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'plan' => [
        'render element' => 'elements',
        'initial preprocess' => static::class . '::preprocessPlan',
      ],
    ];
  }

  /**
   * Prepares variables for plan templates.
   *
   * Default template: plan.html.twig.
   *
   * @param array $variables
   *   An associative array containing:
   *   - elements: An associative array containing the plan information and any
   *     fields attached to the plan. Properties used:
   *     - #plan: A \Drupal\plan\Entity\Plan object. The plan entity.
   *   - attributes: HTML attributes for the containing element.
   */
  public function preprocessPlan(array &$variables) {
    $variables['plan'] = $variables['elements']['#plan'] ?? NULL;
    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
      $variables['content'][$key] = $variables['elements'][$key];
    }
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_plan')]
  public function themeSuggestionsPlan(array $variables) {
    $suggestions = [];
    $plan = $variables['elements']['#plan'];
    $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');
    $suggestions[] = 'plan__' . $sanitized_view_mode;
    $suggestions[] = 'plan__' . $plan->bundle();
    $suggestions[] = 'plan__' . $plan->bundle() . '__' . $sanitized_view_mode;
    $suggestions[] = 'plan__' . $plan->id();
    $suggestions[] = 'plan__' . $plan->id() . '__' . $sanitized_view_mode;
    return $suggestions;
  }

}
