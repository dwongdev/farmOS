<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_ui_views\Functional;

use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;
use Drupal\asset\Entity\Asset;
use Drupal\log\Entity\Log;

/**
 * Tests the farm_ui_views Views.
 *
 * @group farm
 */
class FarmUiViewsTest extends FarmBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_activity',
    'farm_equipment',
    'farm_observation',
    'farm_water',
    'farm_ui_views',
    'farm_ui_views_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create and login a user with permission to view assets and logs.
    $user = $this->createUser(['view any asset', 'view any log']);
    $this->drupalLogin($user);

    // Disable entity_reference_integrity_enforce module's protections, so we
    // can delete all entities easily.
    $erie_config = \Drupal::configFactory()->getEditable('entity_reference_integrity_enforce.settings');
    $erie_config->set('enabled_entity_type_ids', []);
    $erie_config->save();
  }

  /**
   * Run all tests.
   */
  public function testAll() {
    $this->doTestAssetViews();
    $this->doTestLogViews();
    $this->doTestAssetsByLocationView();
    $this->doTestAssetChildrenView();
    $this->doTestAssetLogsView();
  }

  /**
   * Test farm_asset View's page and page_type displays.
   */
  public function doTestAssetViews() {

    // Create two assets of different types.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $equipment->save();
    $water = Asset::create([
      'name' => 'Water asset',
      'type' => 'water',
      'status' => 'active',
    ]);
    $water->save();

    // Check that both assets are visible in /assets.
    $this->drupalGet('/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());

    // Check that only water assets are visible in /assets/water.
    $this->drupalGet('/assets/water');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());

    // Check that /assets/equipment includes equipment-specific columns.
    $this->drupalGet('/assets/equipment');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manufacturer');
    $this->assertSession()->pageTextContains('Model');
    $this->assertSession()->pageTextContains('Serial number');

    // Delete all entities.
    $this->deleteAllEntities();
  }

  /**
   * Test farm_log View's page and page_type displays.
   */
  public function doTestLogViews() {

    // Create two activity logs with different test_string values.
    $activity1 = Log::create([
      'name' => 'Foo activity',
      'type' => 'activity',
      'status' => 'done',
      'test_string' => 'foo',
    ]);
    $activity1->save();
    $activity2 = Log::create([
      'name' => 'Baz activity',
      'type' => 'activity',
      'status' => 'done',
      'test_string' => 'bar',
    ]);
    $activity2->save();

    // Check that /logs and /logs/activity include the "Test string" column.
    $this->drupalGet('/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Test string');
    $this->drupalGet('/logs/activity');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Test string');

    // Check that both activity logs are present.
    $this->assertSession()->pageTextContains('Foo activity');
    $this->assertSession()->pageTextContains('Baz activity');

    // Check that filtering by "Test string" works.
    $this->drupalGet('/logs/activity', ['query' => ['test_string' => 'foo']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Foo activity');
    $this->assertSession()->pageTextNotContains('Baz activity');

    // Delete all entities.
    $this->deleteAllEntities();
  }

  /**
   * Test farm_asset View's page_location display.
   */
  public function doTestAssetsByLocationView() {

    // Create two assets of different types.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $equipment->save();
    $water = Asset::create([
      'name' => 'Water asset',
      'type' => 'water',
      'status' => 'active',
    ]);
    $water->save();

    // Check that the equipment does not appear in /asset/%/assets.
    $this->drupalGet('/asset/' . $water->id() . '/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($equipment->label());

    // Move the equipment asset into the water asset via an activity log.
    $movement = Log::create([
      'type' => 'activity',
      'asset' => [$equipment],
      'location' => [$water],
      'status' => 'done',
      'is_movement' => TRUE,
    ]);
    $movement->save();

    // Check that the equipment appears in /asset/%/assets.
    $this->drupalGet('/asset/' . $water->id() . '/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($equipment->label());

    // Change is_location to FALSE on the water asset.
    $water->set('is_location', FALSE);
    $water->save();

    // Check that /asset/%/assets returns a 403.
    $this->drupalGet('/asset/' . $water->id() . '/assets');
    $this->assertSession()->statusCodeEquals(403);

    // Delete all entities.
    $this->deleteAllEntities();
  }

  /**
   * Test farm_asset View's page_children display.
   */
  public function doTestAssetChildrenView() {

    // Create a parent asset.
    $parent = Asset::create([
      'name' => 'Parent asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $parent->save();

    // Check that /asset/%/children returns a 403.
    $this->drupalGet('/asset/' . $parent->id() . '/children');
    $this->assertSession()->statusCodeEquals(403);

    // Create a child asset.
    $child = Asset::create([
      'name' => 'Child asset',
      'type' => 'equipment',
      'parent' => [$parent],
      'status' => 'active',
    ]);
    $child->save();

    // Check that the child appears in /asset/%/children.
    $this->drupalGet('/asset/' . $parent->id() . '/children');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($child->label());

    // Set is_location to TRUE on the parent.
    $parent->set('is_location', TRUE);
    $parent->save();

    // Check that /asset/%/children returns a 403.
    $this->drupalGet('/asset/' . $parent->id() . '/children');
    $this->assertSession()->statusCodeEquals(403);

    // Delete all entities.
    $this->deleteAllEntities();
  }

  /**
   * Test farm_log View's page_asset display.
   */
  public function doTestAssetLogsView() {

    // Create an equipment asset.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $equipment->save();

    // Check that /asset/%/logs shows "No logs found.".
    $this->drupalGet('/asset/' . $equipment->id() . '/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No logs found.');

    // Create two logs of different types that reference the asset.
    $activity = Log::create([
      'name' => 'Equipment activity',
      'type' => 'activity',
      'asset' => [$equipment],
      'status' => 'done',
    ]);
    $activity->save();
    $observation = Log::create([
      'name' => 'Equipment observation',
      'type' => 'observation',
      'asset' => [$equipment],
      'status' => 'done',
    ]);
    $observation->save();

    // Create a third log that does not reference the asset.
    $unrelated = Log::create([
      'name' => 'Generic activity',
      'type' => 'activity',
      'status' => 'done',
    ]);
    $unrelated->save();

    // Check that only 2 logs appear in /asset/%/logs and /asset/%/logs/all.
    $this->drupalGet('/asset/' . $equipment->id() . '/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/all');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());

    // Check that the appropriate logs appear in /asset/%/logs/%log_type.
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/activity');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextNotContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/observation');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());

    // Delete all entities.
    $this->deleteAllEntities();
  }

  /**
   * Delete all entities.
   */
  protected function deleteAllEntities() {
    foreach (['asset', 'log'] as $type) {
      $storage = \Drupal::entityTypeManager()->getStorage($type);
      $storage->delete($storage->loadMultiple());
    }
  }

}
