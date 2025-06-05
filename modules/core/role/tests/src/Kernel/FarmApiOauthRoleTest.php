<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_role\Kernel;

use Drupal\Tests\farm_api_oauth\Kernel\FarmApiOauthTestBase;
use Drupal\asset\Entity\Asset;
use Drupal\log\Entity\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests farmOS API OAuth features.
 *
 * @group farm
 */
class FarmApiOauthRoleTest extends FarmApiOauthTestBase {

  /**
   * Test viewer role permissions for farmOS API requests.
   */
  public function testApiRolePermissions() {

    // Test using the farm_viewer role.
    // The standard apiTest() case already tests standard API operations with
    // the farm_manager role.
    $this->scope = 'farm_viewer';
    $this->user = $this->createUser();
    $this->user->addRole('farm_viewer');
    $this->user->save();

    // Test that the API root path is /api and it contains meta.farm info.
    $data = $this->apiRequest('/api');
    $this->assertNotEmpty($data['meta']['farm']);
    $this->assertEquals('API Test', $data['meta']['farm']['name']);

    // Test creating an asset.
    $asset_type = 'asset--test';
    $payload = [
      'type' => $asset_type,
      'attributes' => [
        'name' => 'Test asset',
      ],
    ];
    $this->apiRequest('/api/asset/test', 'POST', $payload, Response::HTTP_FORBIDDEN);

    // Create the asset.
    $asset = Asset::create([
      'type' => 'test',
      'name' => 'Test asset',
    ]);
    $asset->save();
    $asset_id = $asset->uuid();

    // Test creating a log that references the asset.
    $log_type = 'log--test';
    $payload = [
      'type' => $log_type,
      'relationships' => [
        'asset' => [
          'data' => [
            [
              'id' => $asset_id,
              'type' => $asset_type,
            ],
          ],
        ],
      ],
    ];
    $this->apiRequest('/api/log/test', 'POST', $payload, Response::HTTP_FORBIDDEN);

    // Create the log.
    $log = Log::create([
      'type' => 'test',
      'name' => 'Test log',
      'asset' => $asset,
    ]);
    $log->save();
    $log_id = $log->uuid();

    // Test that the asset and log appear in collection endpoints.
    $data = $this->apiRequest('/api/asset/test');
    $this->assertCount(1, $data['data']);
    $this->assertEquals($asset_id, $data['data'][0]['id']);
    $data = $this->apiRequest('/api/log/test');
    $this->assertCount(1, $data['data']);
    $this->assertEquals($log_id, $data['data'][0]['id']);

    // Test retrieving both asset and log individually by UUID.
    $data = $this->apiRequest('/api/asset/test/' . $asset_id);
    $this->assertEquals($asset_id, $data['data']['id']);
    $data = $this->apiRequest('/api/log/test/' . $log_id);
    $this->assertEquals($log_id, $data['data']['id']);

    // Test updating assets and logs.
    $payload = [
      'type' => $asset_type,
      'id' => $asset_id,
      'attributes' => [
        'name' => 'Updated asset name',
      ],
    ];
    $this->apiRequest('/api/asset/test/' . $asset_id, 'PATCH', $payload, Response::HTTP_FORBIDDEN);
    $payload = [
      'type' => $log_type,
      'id' => $log_id,
      'attributes' => [
        'name' => 'Updated log name',
      ],
    ];
    $this->apiRequest('/api/log/test/' . $log_id, 'PATCH', $payload, Response::HTTP_FORBIDDEN);

    // Test deleting logs and assets.
    $this->apiRequest('/api/log/test/' . $log_id, 'DELETE', [], Response::HTTP_FORBIDDEN);
    $this->apiRequest('/api/asset/test/' . $asset_id, 'DELETE', [], Response::HTTP_FORBIDDEN);
  }

}
