<?php

declare(strict_types=1);

namespace Drupal\farm_setup\Form;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\farm_setup\SetupFormPluginManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Setup block form.
 *
 * @ingroup farm
 */
class FarmSetupBlockForm extends FarmSetupForm {

  use AutowireTrait;

  public function __construct(
    #[Autowire(service: 'plugin.manager.setup_form')]
    SetupFormPluginManager $setupFormPluginManager,
    protected StateInterface $state,
  ) {
    parent::__construct($setupFormPluginManager);
  }

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
    $plugin_id = $form_state->getValue('plugin_id');
    $next_plugin_id = $this->getNextPluginId($plugin_id);
    $this->state->set('farm_setup.block', $next_plugin_id);
    if (is_null($next_plugin_id)) {
      $this->completeMessage();
    }
  }

}
