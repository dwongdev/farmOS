<?php

declare(strict_types=1);

namespace Drupal\farm_ui_views\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\farm_flag\FarmFlagHelper;
use Drupal\farm_ui_views\FarmUiViewsHelper;

/**
 * Hook implementations for farm_ui_views.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Define common route names and URLs for primary entity types.
    $entity_routes = [
      'asset' => 'entity.asset.collection',
      'log' => 'entity.log.collection',
      'quantity' => 'view.farm_log_quantity.page',
      'people' => 'view.farm_people.page',
    ];
    $entity_urls = [
      'asset' => Url::fromRoute($entity_routes['asset'])->toString(),
      'log' => Url::fromRoute($entity_routes['log'])->toString(),
      'quantity' => Url::fromRoute($entity_routes['quantity'])->toString(),
      'people' => Url::fromRoute($entity_routes['people'])->toString(),
    ];

    // Assets View.
    if ($route_name == $entity_routes['asset']) {
      $output .= '<p>' . $this->t('Assets represent things that are being tracked or managed. They store high-level information, but most historical data is stored in the <a href=":logs">logs</a> that reference them.', [
        ':logs' => $entity_urls['log'],
      ]) . '</p>';
      $output .= '<p>' . $this->t('Assets that are no longer actively managed can be archived. Archived assets will be hidden from most lists, but are preserved and searchable for posterity.') . '</p>';
    }

    // Logs View.
    if ($route_name == $entity_routes['log']) {
      $output .= '<p>' . $this->t('Logs represent events that take place in relation to <a href=":assets">assets</a> and other records. They have a timestamp to represent when they take place, and a status to designate that they are "Done", "Pending", or "Abandoned".', [
        ':assets' => $entity_urls['asset'],
      ]) . '</p>';
      $output .= '<p>' . $this->t('Logs can be assigned to <a href=":people">people</a> for task management purposes.', [
        ':people' => $entity_urls['people'],
      ]) . '</p>';
    }

    // Quantities View.
    if ($route_name == $entity_routes['quantity']) {
      $output .= '<p>' . $this->t('Quantities are granular units of quantitative data that represent a single data point within a <a href=":logs">log</a>.', [
        ':logs' => $entity_urls['log'],
      ]) . '</p>';
      $output .= '<p>' . $this->t('All quantities can optionally include a measure, value, units, and label. Specific quantity types may collect additional information.') . '</p>';
    }

    // Plans View.
    if ($route_name == 'entity.plan.collection') {
      $output .= '<p>' . $this->t('Plans provide features for planning, managing, and organizing <a href=":assets">assets</a>, <a href=":logs">logs</a>, and <a href=":people">people</a> around a particular goal.', [
        ':assets' => $entity_urls['asset'],
        ':logs' => $entity_urls['log'],
        ':people' => $entity_urls['people'],
      ]) . '</p>';
    }
    return $output;
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_views_exposed_form_alter')]
  public function formViewsExposedFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Load form state storage and bail if the View is not stored.
    $storage = $form_state->getStorage();
    if (empty($storage['view'])) {
      return;
    }

    /** @var \Drupal\views\ViewExecutable $view */
    $view = $storage['view'];

    // Check if the view display has the collapsible_filter extender enabled.
    $extenders = $view->getDisplay()->getExtenders();
    if (array_key_exists('collapsible_filter', $extenders) && $extenders['collapsible_filter']->options['collapsible']) {

      // Render filters in collapsible details element.
      // Only open the filters if a non-default filter value is provided.
      // This is needed to prevent the filter from opening when click sort is
      // applied and all default filter values are passed as query parameters.
      $open_input = FALSE;
      foreach ($view->getExposedInput() as $filter_name => $filter_value) {
        /**
         * @var string $filter_name
         * @var string|array $filter_value
         */

        // Check if the exposed input is for a filter.
        if (isset($view->filter[$filter_name])) {

          // Get the default filter value.
          $default_filter_value = $view->filter[$filter_name]->value;

          // If the default is an array with one value and a single string value
          // is provided in input, consider the default filter to be a string
          // instead of an array. This fixes the status filter.
          if (is_array($default_filter_value) && count($default_filter_value) == 1 && !is_array($filter_value)) {
            $default_filter_value = reset($default_filter_value);
          }

          // Open the filters if the filter value does not equal the default.
          if ($filter_value != $default_filter_value) {
            $open_input = TRUE;
            break;
          }
        }
      }
      $form['#theme_wrappers']['details'] = [
        '#title' => $this->t('Filter'),
        '#attributes' => [

          // Open if there is exposed input.
          'open' => $open_input,
        ],
        '#summary_attributes' => [],
      ];
      $form['#attached']['library'][] = 'farm_ui_views/views_collapsible_filter';
    }

    // We only want to alter the Views we provide.
    if (!in_array($storage['view']->id(), ['farm_asset', 'farm_log', 'farm_plan'])) {
      return;
    }

    // If there is no exposed filter for flags, bail.
    if (empty($form['flag_value'])) {
      return;
    }

    // Get the entity type and (maybe) bundle.
    $entity_type = $storage['view']->getBaseEntityType()->id();
    $bundle = FarmUiViewsHelper::getBundleArgument($storage['view'], $storage['display']['id'], $storage['view']->args);
    $bundles = !empty($bundle) ? [$bundle] : [];
    $allowed_options = FarmFlagHelper::flagOptions($entity_type, $bundles, TRUE);
    $form['flag_value']['#options'] = $allowed_options;
  }

}
