<?php

declare(strict_types=1);

namespace Drupal\organization\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks that an organization name is unique.
 */
#[Constraint(
  id: 'UniqueOrganizationName',
  label: new TranslatableMarkup('Unique organization name', ['context' => 'Validation']),
)]
class UniqueOrganizationName extends SymfonyConstraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public string $message = 'An organization by this name already exists. Organization names must be unique.';

}
