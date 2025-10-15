<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_entity\Functional;

use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;

/**
 * Tests that entity revisions cannot be reverted.
 *
 * @group farm
 */
class FarmEntityRevisionsTest extends FarmBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_entity',
    'farm_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create and login a user with access to entity revisions.
    $user = $this->createuser([
      'view any asset',
      'view any log',
      'view any plan',
      'view all asset revisions',
      'view all log revisions',
      'view all plan revisions',
      'revert all asset revisions',
      'revert all log revisions',
      'revert all plan revisions',
    ]);
    $this->drupalLogin($user);
  }

  /**
   * Test that entity revisions cannot be reverted.
   */
  public function testFarmEntityRevisions() {
    $entity_types = [
      'asset',
      'log',
      'plan',
    ];
    foreach ($entity_types as $entity_type) {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
      /** @var \Drupal\Core\Entity\RevisionableInterface $entity */
      $entity = $storage->create([
        'name' => $this->randomMachineName(),
        'type' => 'test',
      ]);
      $entity->save();
      $first_revision_id = $entity->getRevisionId();
      $entity->setNewRevision();
      $entity->save();
      $second_revision_id = $entity->getRevisionId();
      $this->assertNotEquals($first_revision_id, $second_revision_id);
      $this->drupalGet($entity_type . '/' . $entity->id() . '/revisions');
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->responseNotContains('Revert');
      $this->drupalGet($entity_type . '/' . $entity->id() . '/revisions/' . $first_revision_id . '/revert');
      $this->assertSession()->statusCodeEquals(404);
    }
  }

}
