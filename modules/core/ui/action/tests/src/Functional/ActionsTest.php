<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_ui_action\Functional;

use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;
use Drupal\asset\Entity\Asset;
use Drupal\log\Entity\Log;

/**
 * Tests the farmOS action functionality.
 *
 * @group farm
 */
class ActionsTest extends FarmBrowserTestBase {

  /**
   * Test user.
   *
   * @var \Drupal\user\Entity\User|bool
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_ui_action',
    'farm_ui_action_test',
    'farm_ui_dashboard',
    'farm_ui_views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Add the local actions block.
    $this->drupalPlaceBlock('local_actions_block');

    // Create and login a user with necessary permissions.
    $this->user = $this->createUser([
      'access asset collection',
      'access farm dashboard',
      'access log collection',
      'access organization collection',
      'access plan collection',
      'create test asset',
      'create test log',
      'create test organization',
      'create test plan',
      'view any asset',
      'view any log',
      'view any organization',
      'view any plan',
    ]);
    $this->drupalLogin($this->user);
  }

  /**
   * Test that action buttons are added.
   */
  public function testActionButtons() {

    // Test dashboard buttons.
    $this->drupalGet('/dashboard');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Asset');
    $this->assertSession()->linkExists('Add Log');
    $this->assertSession()->linkExists('Add Organization');
    $this->assertSession()->linkExists('Add Plan');

    // Test entity lists.
    $this->drupalGet('/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Asset');
    $this->drupalGet('/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Log');
    $this->drupalGet('/organizations');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Organization');
    $this->drupalGet('/plans');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Plan');

    // Test per-bundle entity lists.
    $this->drupalGet('/assets/test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Asset: Test');
    $this->drupalGet('/logs/test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Log: Test');
    $this->drupalGet('/organizations/test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Organization: Test');
    $this->drupalGet('/plans/test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Plan: Test');

    // Test /user/%uid/logs.
    $this->drupalGet('/user/' . $this->user->id() . '/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Log');

    // Test links to /log/add/[bundle]?asset=[id] on asset pages.
    /** @var \Drupal\asset\Entity\AssetInterface $asset */
    $asset = Asset::create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
    ]);
    $asset->save();
    /** @var \Drupal\log\Entity\LogInterface $log */
    $log = Log::create([
      'type' => 'test',
      'asset' => [$asset],
    ]);
    $log->save();
    $this->drupalGet('/asset/' . $asset->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Log: Test');
    $this->drupalGet('/asset/' . $asset->id() . '/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Log: Test');
    $this->drupalGet('/asset/' . $asset->id() . '/logs/test');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->linkExists('Add Log: Test');
  }

}
