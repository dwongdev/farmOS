<?php

namespace Drupal\farm_worker\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for farm_worker.
 */
class FarmWorkerHooks
{
    /**
     * Implements hook_oauth2_scope_info_alter().
     */
    #[Hook('oauth2_scope_info_alter')]
    public function oauth2ScopeInfoAlter(array &$scopes)
    {
        // Enable the password grant for static role scopes.
        if (\Drupal::moduleHandler()->moduleExists('simple_oauth_password_grant')) {
            $target_scopes = [
                'farm_worker',
            ];
            foreach ($target_scopes as $scope_id) {
                if (isset($scopes[$scope_id])) {
                    $scopes[$scope_id]['grant_types']['password'] = [
                        'status' => TRUE,
                    ];
                }
            }
        }
    }
}
