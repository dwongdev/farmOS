<?php

declare(strict_types=1);

namespace Drupal\plan\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Hook implementations for plan.
 */
class PlanHooks {

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
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'plan' => [
        'render element' => 'elements',
      ],
    ];
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
