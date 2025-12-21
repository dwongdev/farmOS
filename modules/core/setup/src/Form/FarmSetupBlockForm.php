<?php

declare(strict_types=1);

namespace Drupal\farm_setup\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Setup block form.
 *
 * @ingroup farm
 */
class FarmSetupBlockForm extends FarmSetupForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_setup_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function proceed(FormStateInterface $form_state): void {

    // This performs the same logic as the parent method, but does not perform
    // any redirections. Instead, it saves the next plugin to state so that it
    // is shown next time the block is loaded.
    $next_plugin_id = $this->setupWizard->getNextPluginId($form_state->getValue('plugin_id'));
    $this->setupWizard->setBlockPluginId($next_plugin_id);
    if (is_null($next_plugin_id)) {
      $this->completeMessage();
    }
  }

}
