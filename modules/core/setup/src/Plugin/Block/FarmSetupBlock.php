<?php

declare(strict_types=1);

namespace Drupal\farm_setup\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_setup\Form\FarmSetupBlockForm;
use Drupal\farm_setup\SetupFormPluginManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a farmOS setup block.
 */
#[Block(
  id: 'farm_setup',
  admin_label: new TranslatableMarkup('farmOS setup wizard'),
)]
class FarmSetupBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    #[Autowire(service: 'plugin.manager.setup_form')]
    protected SetupFormPluginManager $setupFormPluginManager,
    protected StateInterface $state,
    protected FormBuilderInterface $formBuilder,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.setup_form'),
      $container->get('state'),
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    // Get all setup form plugins.
    $plugins = $this->setupFormPluginManager->getDefinitions();

    // Get the current form plugin ID from state.
    $plugin_id = $this->state->get('farm_setup.block');

    // If the current setup form plugin ID is NULL, or if it does not exist,
    // then consider the setup process to be complete and show nothing.
    if (is_null($plugin_id) || !array_key_exists($plugin_id, $plugins)) {
      return [];
    }

    // Instantiate the plugin.
    /** @var \Drupal\farm_setup\Plugin\SetupForm\SetupFormInterface $plugin */
    $plugin = $this->setupFormPluginManager->createInstance($plugin_id);

    // Build the setup form.
    $build['form'] = [
      '#type' => 'details',
      '#title' => $plugin->getTitle(),
      '#description' => $plugin->getDescription(),
      '#open' => TRUE,
      'form' => $this->formBuilder->getForm(FarmSetupBlockForm::class, $plugin_id),
    ];

    return $build;
  }

}
