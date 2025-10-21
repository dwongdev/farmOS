<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_role\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\simple_oauth\Entity\Oauth2Scope;
use Drupal\simple_oauth\Oauth2ScopeProviderInterface;

/**
 * Tests the managed role permissions scope granularity.
 *
 * @group farm
 */
class ScopeGranularityTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'log',
    'state_machine',
    'farm_role',
    'farm_role_test',
    'consumers',
    'serialization',
    'simple_oauth',
    'simple_oauth_test',
    'user',
  ];

  /**
   * The OAuth2 scope provider used in this test.
   *
   * @var \Drupal\simple_oauth\Oauth2ScopeProviderInterface
   */
  protected Oauth2ScopeProviderInterface $scopeProvider;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('log');
    $this->installSchema('system', ['sequences']);
    $this->installConfig([
      'farm_role',
      'farm_role_test',
      'log',
      'simple_oauth',
    ]);

    $this->scopeProvider = $this->container->get('simple_oauth.oauth2_scope.provider');
  }

  /**
   * Tests permission checking of scope granularity.
   *
   * @dataProvider scopeHasPermissionProvider
   */
  public function testScopeHasPermission(string $role_id, array $allowed_permissions, array $forbidden_permissions): void {
    $scope = Oauth2Scope::create([
      'id' => $role_id,
      'name' => $role_id,
      'granularity_id' => 'managed_role',
      'granularity_configuration' => [
        'role' => $role_id,
      ],
    ]);
    foreach ($allowed_permissions as $permission) {
      $this->assertTrue($this->scopeProvider->scopeHasPermission($permission, $scope), "Scope has allowed permission: $permission");
    }
    foreach ($forbidden_permissions as $permission) {
      $this->assertFalse($this->scopeProvider->scopeHasPermission($permission, $scope), "Scope does not have forbidden permission: $permission");
    }
  }

  /**
   * Data provider for ::testScopeHasPermission.
   */
  public static function scopeHasPermissionProvider(): array {
    return [
      'farm_test' => [
        'farm_test',
        [
          'standard role permission',
          'default callback permission',
          'test default permission',
          'view any harvest log',
          'view any observation log',
          'create observation log',
          'update any observation log',
          'delete own observation log',
        ],
        [
          'test config access permission',
          'my manager permission',
          'create harvest log',
          'update any harvest log',
          'delete any harvest log',
          'delete any observation log',
        ],
      ],
      'farm_test_manager' => [
        'farm_test_manager',
        [
          'test default permission',
          'test config access permission',
          'default callback permission',
          'my manager permission',
          'view any harvest log',
          'view any observation log',
          'create harvest log',
          'create observation log',
          'update any harvest log',
          'update own harvest log',
          'delete any harvest log',
          'delete own harvest log',
        ],
        [],
      ],
    ];
  }

}
