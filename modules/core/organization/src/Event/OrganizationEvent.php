<?php

declare(strict_types=1);

namespace Drupal\organization\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\organization\Entity\OrganizationInterface;

/**
 * Event that is fired by organization save, delete and clone operations.
 */
class OrganizationEvent extends Event {

  const PRESAVE = 'organization_presave';
  const INSERT = 'organization_insert';
  const UPDATE = 'organization_update';
  const DELETE = 'organization_delete';

  public function __construct(
    protected OrganizationInterface $organization,
  ) {}

}
