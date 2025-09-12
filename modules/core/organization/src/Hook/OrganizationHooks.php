<?php

declare(strict_types=1);

namespace Drupal\organization\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\organization\Entity\OrganizationInterface;
use Drupal\organization\Event\OrganizationEvent;

/**
 * Hook implementations for organization.
 */
class OrganizationHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Main module help for the organization module.
    if ($route_name == 'help.page.organization') {
      $output = '';
      $output .= '<h3>' . t('About') . '</h3>';
      $output .= '<p>' . t('Provides organization entity') . '</p>';
    }

    return $output;
  }

  /**
   * Implements hook_ENTITY_TYPE_presave().
   */
  #[Hook('organization_presave')]
  public function organizationPresave(OrganizationInterface $organization) {

    // Dispatch an event on organization presave.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, OrganizationEvent::PRESAVE);
  }

  /**
   * Implements hook_ENTITY_TYPE_insert().
   */
  #[Hook('organization_insert')]
  public function organizationInsert(OrganizationInterface $organization) {

    // Dispatch an event on organization insert.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, OrganizationEvent::INSERT);
  }

  /**
   * Implements hook_ENTITY_TYPE_update().
   */
  #[Hook('organization_update')]
  public function organizationUpdate(OrganizationInterface $organization) {

    // Dispatch an event on organization update.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, OrganizationEvent::UPDATE);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('organization_delete')]
  public function organizationDelete(OrganizationInterface $organization) {

    // Dispatch an event on organization delete.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, OrganizationEvent::DELETE);
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'organization' => [
        'render element' => 'elements',
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK().
   */
  #[Hook('theme_suggestions_organization')]
  public function themeSuggestionsOrganization(array $variables) {
    $suggestions = [];
    $organization = $variables['elements']['#organization'];
    $sanitized_view_mode = strtr($variables['elements']['#view_mode'], '.', '_');
    $suggestions[] = 'organization__' . $sanitized_view_mode;
    $suggestions[] = 'organization__' . $organization->bundle();
    $suggestions[] = 'organization__' . $organization->bundle() . '__' . $sanitized_view_mode;
    $suggestions[] = 'organization__' . $organization->id();
    $suggestions[] = 'organization__' . $organization->id() . '__' . $sanitized_view_mode;
    return $suggestions;
  }

}
