<?php

/**
 * @file
 * Provides Views data for farm_farm.module.
 */

declare(strict_types=1);

/**
 * Implements hook_views_data_alter().
 */
function farm_farm_views_data_alter(array &$data) {

  // Provide a farm_organization_asset argument for views of logs.
  if (isset($data['log_field_data'])) {
    $data['log_field_data']['farm_organization_asset'] = [
      'title' => t('Farm organization asset'),
      'help' => t('Assets that are associated with the farm organization.'),
      'argument' => [
        'id' => 'farm_organization_asset',
      ],
    ];
  }
}
