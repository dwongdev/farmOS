<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Organization Type plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class OrganizationType extends Plugin {

  /**
   * Constructs an organization type attribute.
   *
   * @param string $id
   *   The organization type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The organization type label.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
