<?php

declare(strict_types=1);

namespace Drupal\organization\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the UniqueOrganizationName constraint.
 */
class UniqueOrganizationNameValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a UniqueBirthLogConstraintValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\StringItem $value */
    /** @var \Drupal\organization\Plugin\Validation\Constraint\UniqueOrganizationName $constraint */

    // Query for existing organizations with the same name, and add a violation
    // if any are found.
    $count = $this->entityTypeManager->getStorage('organization')->getAggregateQuery()
      ->accessCheck(FALSE)
      ->condition('name', $value->value)
      ->count()
      ->execute();
    if ($count > 0) {
      $this->context->addViolation($constraint->message);
    }
  }

}
