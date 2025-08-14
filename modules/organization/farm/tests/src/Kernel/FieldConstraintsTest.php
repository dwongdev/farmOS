<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_farm\Kernel;

use Drupal\farm_farm\Plugin\Validation\Constraint\LocationAssetParentFarm;
use Drupal\KernelTests\KernelTestBase;

/**
 * Field constraint tests for Farm organization module.
 *
 * @group farm
 */
class FieldConstraintsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset',
    'entity',
    'entity_reference_validators',
    'farm_entity',
    'farm_farm',
    'farm_farm_test',
    'farm_field',
    'farm_location',
    'farm_log',
    'farm_log_asset',
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
    $this->installConfig([
      'farm_farm',
      'farm_farm_test',
      'farm_log_asset',
    ]);
  }

  /**
   * Test LocationAssetParentFarm constraint.
   */
  public function testLocationAssetParentFarmConstraint() {
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

    // Create two location assets, one in each farm.
    $asset1 = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'is_location' => TRUE,
      'farm' => [$farm1],
    ]);
    $asset1->save();
    $asset2 = $asset_storage->create([
      'type' => 'test',
      'name' => $this->randomMachineName(),
      'is_location' => TRUE,
      'farm' => [$farm2],
    ]);
    $asset2->save();

    // Attempt to make the first asset a parent of the second.
    $asset2->set('parent', [$asset1]);
    $violations = $asset2->validate();

    // Confirm that a LocationAssetParentFarm constraint violation was added
    // because the asset has a parent in a different farm.
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(LocationAssetParentFarm::class, $violations->get(0)->getConstraint());

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

    // Confirm that a LocationAssetParentFarm constraint violation was added
    // because the asset has a child in a different farm.
    $this->assertEquals(1, $violations->count());
    $this->assertInstanceOf(LocationAssetParentFarm::class, $violations->get(0)->getConstraint());
  }

}
