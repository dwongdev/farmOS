<?php

declare(strict_types=1);

namespace Drupal\organization\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\organization\Entity\OrganizationInterface;
use Drupal\organization\Event\OrganizationEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Hook implementations for organization.
 */
class OrganizationHooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    #[Autowire(service: 'event_dispatcher')]
    protected EventDispatcherInterface $eventDispatcher,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    $output = '';

    // Main module help for the organization module.
    if ($route_name == 'help.page.organization') {
      $output = '';
      $output .= '<h3>' . $this->t('About') . '</h3>';
      $output .= '<p>' . $this->t('Provides organization entity') . '</p>';
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
    $this->eventDispatcher->dispatch($event, OrganizationEvent::PRESAVE);
  }

  /**
   * Implements hook_ENTITY_TYPE_insert().
   */
  #[Hook('organization_insert')]
  public function organizationInsert(OrganizationInterface $organization) {

    // Dispatch an event on organization insert.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $this->eventDispatcher->dispatch($event, OrganizationEvent::INSERT);
  }

  /**
   * Implements hook_ENTITY_TYPE_update().
   */
  #[Hook('organization_update')]
  public function organizationUpdate(OrganizationInterface $organization) {

    // Dispatch an event on organization update.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $this->eventDispatcher->dispatch($event, OrganizationEvent::UPDATE);
  }

  /**
   * Implements hook_ENTITY_TYPE_delete().
   */
  #[Hook('organization_delete')]
  public function organizationDelete(OrganizationInterface $organization) {

    // Dispatch an event on organization delete.
    // @todo Replace this with core event via https://www.drupal.org/node/2551893.
    $event = new OrganizationEvent($organization);
    $this->eventDispatcher->dispatch($event, OrganizationEvent::DELETE);
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'organization' => [
        'render element' => 'elements',
        'initial preprocess' => static::class . '::preprocessOrganization',
      ],
    ];
  }

  /**
   * Prepares variables for organization templates.
   *
   * Default template: organization.html.twig.
   *
   * @param array $variables
   *   An associative array containing:
   *   - elements: An associative array containing the organization information
   *     and any fields attached to the organization. Properties used:
   *     - #organization: A \Drupal\organization\Entity\Organization object. The
   *       organization entity.
   *   - attributes: HTML attributes for the containing element.
   */
  public function preprocessOrganization(array &$variables) {
    $variables['organization'] = $variables['elements']['#organization'];
    // Helpful $content variable for templates.
    foreach (Element::children($variables['elements']) as $key) {
      $variables['content'][$key] = $variables['elements'][$key];
    }
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
