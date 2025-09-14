<?php

declare(strict_types=1);

namespace Drupal\farm_log_quantity\Hook;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\farm_field\FarmFieldFactoryInterface;
use Drupal\farm_log_quantity\FarmLogQuantityHelper;

/**
 * Hook implementations for farm_log_quantity.
 */
class FarmLogQuantityHooks {

  use AutowireTrait;

  public function __construct(
    protected FarmFieldFactoryInterface $farmFieldFactory,
  ) {}

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {

    // We only care about log entities.
    if ($entity_type->id() != 'log') {
      return [];
    }

    // Add a quantity reference field to logs.
    $field_info = [
      'quantity' => [
        'type' => 'entity_reference_revisions',
        'label' => t('Quantity'),
        'description' => t('Add quantity measurements to this log.'),
        'target_type' => 'quantity',
        'multiple' => TRUE,
        'weight' => [
          'form' => 0,
          'view' => 50,
        ],
      ],
    ];
    $fields = [];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->baseFieldDefinition($info);
    }

    return $fields;
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_log_form_alter')]
  public function formLogFormAlter(&$form, FormStateInterface $form_state, $form_id) {

    // Alter the Quantity inline entity form to set the default quantity type.
    if (!empty($form['quantity']['widget']['actions']['bundle']['#options'])) {
      $bundle_select = &$form['quantity']['widget']['actions']['bundle'];

      // Load the log type storage.
      assert($form_state->getFormObject() instanceof EntityFormInterface);
      /** @var \Drupal\log\Entity\Log $entity */
      $entity = $form_state->getFormObject()->getEntity();

      // Determine the default quantity type.
      $default_type = FarmLogQuantityHelper::defaultQuantityType($entity->bundle());

      // Set the default value.
      if (array_key_exists($default_type, $bundle_select['#options'])) {
        $bundle_select['#default_value'] = $default_type;
      }
    }
  }

}
