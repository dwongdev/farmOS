<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_farm\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\farm_farm\Plugin\Validation\Constraint\AssetGroupAssignmentFarm;
use Drupal\farm_farm\Plugin\Validation\Constraint\AssetMovementFarm;
use Drupal\farm_farm\Plugin\Validation\Constraint\AssetParentFarm;
use Drupal\farm_farm\Plugin\Validation\Constraint\LogGroupAssignmentFarm;
use Drupal\farm_farm\Plugin\Validation\Constraint\LogMovementFarm;

/**
 * Field constraint tests for Farm organization module.
 *
 * @group farm
 */
class FieldConstraintsTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset',
    'entity',
    'entity_reference_validators',
    'farm_entity',
    'farm_entity_access',
    'farm_farm',
    'farm_farm_test',
    'farm_field',
    'farm_group',
    'farm_location',
    'farm_log',
    'farm_log_asset',
    'farm_map',
    'farm_parent',
    'geofield',
    'log',
    'organization',
    'state_machine',
    'system',
    'user',
    'views',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('asset');
    $this->installEntitySchema('log');
    $this->installEntitySchema('organization');
    $this->installEntitySchema('user');
    $this->installConfig([
      'farm_farm',
      'farm_farm_test',
      'farm_group',
      'farm_location',
      'farm_log_asset',
    ]);

    // Create and login a user with access to view any organization. This is
    // necessary to validate the asset entities below, because the entity
    // module's query access handler enforces view access to referenced entities
    // during validation.
    $user = $this->createUser(['view any organization']);
    $this->container->get('current_user')->setAccount($user);
  }

  /**
   * Test AssetParentFarm constraint.
   */
  public function testAssetParentFarmConstraint() {
    $entity_type_manager = $this->container->get('entity_type.manager');
    $asset_storage = $entity_type_manager->getStorage('asset');
    $organization_storage = $entity_type_manager->getStorage('organization');

    // Create two farm organizations.
    $farm1 = $organization_storage->create([
      'type' => 'farm',
      'name' => $this->randomMachineName(),
    ]);
    $farm1->save();
    $farm2 = $organization_storage->create([
      'type' => 'farm',
      'name' => $this->randomMachineName(),
    ]);
    $farm2->save();

    // Create two assets, one in each farm.
    $asset1 = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'farm' => [$farm1],
    ]);
    $asset1->save();
    $asset2 = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'farm' => [$farm2],
    ]);
    $asset2->save();

    // Attempt to make the first asset a parent of the second.
    $asset2->set('parent', [$asset1]);
    $violations = $asset2->validate();

    // Confirm that a AssetParentFarm constraint violation was added because the
    // asset has a parent in a different farm.
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(AssetParentFarm::class, $violations->get(0)->getConstraint());

    // Move the second asset to the same farm as the first.
    $asset2->set('farm', [$farm1]);
    $asset2->save();

    // Confirm that it now validates.
    $violations = $asset1->validate();
    $this->assertEquals(0, $violations->count());

    // Save the parent hierarchy.
    $asset2->save();

    // Attempt to move the first asset to the second farm.
    $asset1->set('farm', [$farm2]);
    $violations = $asset1->validate();

    // Confirm that a AssetParentFarm constraint violation was added because the
    // asset has a child in a different farm.
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(AssetParentFarm::class, $violations->get(0)->getConstraint());

    // Reset the first asset to the first farm and confirm that it validates.
    $asset1->set('farm', [$farm1]);
    $violations = $asset1->validate();
    $this->assertEquals(0, $violations->count());

    // Remove farm assignment from both assets, save them, and confirm that they
    // both validate.
    $asset1->set('farm', []);
    $asset1->save();
    $asset2->set('farm', []);
    $asset2->save();
    $violations = $asset1->validate();
    $this->assertEquals(0, $violations->count());
    $violations = $asset2->validate();
    $this->assertEquals(0, $violations->count());
  }

  /**
   * Test movement constraints.
   */
  public function testMovementConstraints() {
    $entity_type_manager = $this->container->get('entity_type.manager');
    $asset_storage = $entity_type_manager->getStorage('asset');
    $log_storage = $entity_type_manager->getStorage('log');
    $organization_storage = $entity_type_manager->getStorage('organization');

    // Create two farm organizations.
    $farm1 = $organization_storage->create([
      'type' => 'farm',
      'name' => $this->randomMachineName(),
    ]);
    $farm1->save();
    $farm2 = $organization_storage->create([
      'type' => 'farm',
      'name' => $this->randomMachineName(),
    ]);
    $farm2->save();

    // Create two location assets, one in each farm.
    $location1 = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'is_location' => TRUE,
      'farm' => [$farm1],
    ]);
    $location1->save();
    $location2 = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'is_location' => TRUE,
      'farm' => [$farm2],
    ]);
    $location2->save();

    // Create a movable asset in the first farm.
    $asset = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'is_fixed' => FALSE,
      'farm' => [$farm1],
    ]);

    // Confirm that the asset validates.
    $violations = $asset->validate();
    $this->assertEquals(0, $violations->count());

    // Save the asset.
    $asset->save();

    // Create a log that moves the asset to location 2.
    $log = $log_storage->create([
      'type' => 'test',
      'asset' => [$asset],
      'location' => [$location2],
      'is_movement' => TRUE,
    ]);

    // Confirm that a LogMovementFarm constraint violation was added because
    // the asset is not in the same farm as location 2.
    $violations = $log->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(LogMovementFarm::class, $violations->get(0)->getConstraint());

    // Change the log location to location 1.
    $log->set('location', [$location1]);

    // Confirm that the log validates.
    $violations = $log->validate();
    $this->assertEquals(0, $violations->count());

    // Save the log.
    $log->save();

    // Attempt to change the asset to the second farm and confirm that an
    // AssetMovementFarm constraint violation was added because the asset has
    // a movement log associated with it.
    $asset->set('farm', [$farm2]);
    $violations = $asset->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(AssetMovementFarm::class, $violations->get(0)->getConstraint());

    // Attempt to change location 1 to the second farm and confirm that an
    // AssetMovementFarm constraint violation was added because the location
    // asset has a movement log associated with it.
    $location1->set('farm', [$farm2]);
    $violations = $location1->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(AssetMovementFarm::class, $violations->get(0)->getConstraint());

    // Delete the movement log and confirm that both assets now validate.
    $log->delete();
    $violations = $asset->validate();
    $this->assertEquals(0, $violations->count());
    $violations = $location1->validate();
    $this->assertEquals(0, $violations->count());
  }

  /**
   * Test group assignment constraints.
   */
  public function testGroupAssignmentConstraints() {
    $entity_type_manager = $this->container->get('entity_type.manager');
    $asset_storage = $entity_type_manager->getStorage('asset');
    $log_storage = $entity_type_manager->getStorage('log');
    $organization_storage = $entity_type_manager->getStorage('organization');

    // Create two farm organizations.
    $farm1 = $organization_storage->create([
      'type' => 'farm',
      'name' => $this->randomMachineName(),
    ]);
    $farm1->save();
    $farm2 = $organization_storage->create([
      'type' => 'farm',
      'name' => $this->randomMachineName(),
    ]);
    $farm2->save();

    // Create two group assets, one in each farm.
    $group1 = $asset_storage->create([
      'type' => 'group',
      'name' => $this->randomMachineName(),
      'farm' => [$farm1],
    ]);
    $group1->save();
    $group2 = $asset_storage->create([
      'type' => 'group',
      'name' => $this->randomMachineName(),
      'farm' => [$farm2],
    ]);
    $group2->save();

    // Create an asset in the first farm.
    $asset = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'farm' => [$farm1],
    ]);

    // Confirm that the asset validates.
    $violations = $asset->validate();
    $this->assertEquals(0, $violations->count());

    // Save the asset.
    $asset->save();

    // Create a log that assigns the asset to group 2.
    $log = $log_storage->create([
      'type' => 'test',
      'asset' => [$asset],
      'group' => [$group2],
      'is_group_assignment' => TRUE,
    ]);

    // Confirm that a LogGroupAssignmentFarm constraint violation was added
    // because the asset is not in the same farm as group 2.
    $violations = $log->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(LogGroupAssignmentFarm::class, $violations->get(0)->getConstraint());

    // Change the log group to group 1.
    $log->set('group', [$group1]);

    // Confirm that the log validates.
    $violations = $log->validate();
    $this->assertEquals(0, $violations->count());

    // Save the log.
    $log->save();

    // Attempt to change the asset to the second farm and confirm that an
    // AssetGroupAssignmentFarm constraint violation was added because the asset
    // has a group assignment log associated with it.
    $asset->set('farm', [$farm2]);
    $violations = $asset->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(AssetGroupAssignmentFarm::class, $violations->get(0)->getConstraint());

    // Attempt to change group 1 to the second farm and confirm that an
    // AssetGroupAssignmentFarm constraint violation was added because the
    // group asset has a group assignment log associated with it.
    $group1->set('farm', [$farm2]);
    $violations = $group1->validate();
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(AssetGroupAssignmentFarm::class, $violations->get(0)->getConstraint());

    // Delete the group assignment log and confirm that both assets now
    // validate.
    $log->delete();
    $violations = $asset->validate();
    $this->assertEquals(0, $violations->count());
    $violations = $group1->validate();
    $this->assertEquals(0, $violations->count());
  }

}
