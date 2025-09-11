<?php

namespace Drupal\organization\Hook;

use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\organization\Entity\OrganizationInterface;
use Drupal\organization\Event\OrganizationEvent;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for organization.
 */
class OrganizationHooks
{
    /**
     * Implements hook_help().
     */
    #[Hook('help')]
    public function help($route_name, \Drupal\Core\Routing\RouteMatchInterface $route_match)
    {
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
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme()
    {
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
    public function themeSuggestionsOrganization(array $variables)
    {
        $suggestions = [
        ];
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
