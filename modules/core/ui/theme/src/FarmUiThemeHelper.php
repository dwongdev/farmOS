<?php

declare(strict_types=1);

namespace Drupal\farm_ui_theme;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Helper methods for farm_ui_theme.
 */
class FarmUiThemeHelper {

  /**
   * Adds a warning message to entities that are archived.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   */
  public static function setArchivedMessage(FieldableEntityInterface $entity) {
    if ($entity->get('archived')->value) {
      \Drupal::messenger()->addWarning(t('This @entity_type is archived. Archived @entity_types should only be edited if they need corrections.', ['@entity_type' => strtolower($entity->getEntityType()->getLabel()->render()), '@entity_types' => strtolower($entity->getEntityType()->getPluralLabel()->render())]));
    }
  }

}
