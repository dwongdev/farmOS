<?php

declare(strict_types=1);

namespace Drupal\farm_setup\Plugin\SetupForm;

use Drupal\Core\Form\FormInterface;

/**
 * Interface for setup forms.
 */
interface SetupFormInterface extends FormInterface {

  /**
   * Returns the setup form title.
   *
   * @return string
   *   The setup form title.
   */
  public function getTitle();

  /**
   * Returns the setup form description.
   *
   * @return string
   *   The setup form description.
   */
  public function getDescription();

}
