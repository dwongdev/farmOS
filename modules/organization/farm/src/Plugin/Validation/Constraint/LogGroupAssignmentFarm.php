<?php

declare(strict_types=1);

namespace Drupal\farm_farm\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks that assets can only be assigned to groups in the same farm.
 */
#[Constraint(
  id: 'LogGroupAssignmentFarm',
  label: new TranslatableMarkup('Assets can only be assigned to groups in the same farm', ['context' => 'Validation']),
)]
class LogGroupAssignmentFarm extends SymfonyConstraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public string $message = 'Assets can only be assigned to groups in the same farm.';

}
