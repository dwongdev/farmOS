<?php

declare(strict_types=1);

namespace Drupal\farm_api_oauth\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity\BundleFieldDefinition;

/**
 * Hook implementations for farm_api_oauth.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_entity_base_field_info().
   */
  #[Hook('entity_base_field_info')]
  public function entityBaseFieldInfo(EntityTypeInterface $entity_type) {
    $fields = [];

    // Add allowed_origins field to the consumer entity.
    if ($entity_type->id() == 'consumer') {
      $fields['allowed_origins'] = BundleFieldDefinition::create('string')->setLabel($this->t('Allowed origins'))->setDescription($this->t('Configure CORS origins for this consumer.'))->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'settings' => [
          'size' => 255,
          'placeholder' => 'https://example.com',
        ],
      ]);
    }

    return $fields;
  }

  /**
   * Implements hook_consumers_list_alter().
   *
   * Display the client_id in the list of consumers.
   */
  #[Hook('consumers_list_alter')]
  public function consumersListAlter(&$data, $context) {
    if ($context['type'] === 'header') {
      $data['client_id'] = $this->t('Client ID');
    }
    elseif ($context['type'] === 'row') {
      $entity = $context['entity'];
      $data['client_id'] = NULL;
      if ($client_id = $entity->get('client_id')->value) {
        $data['client_id'] = $client_id;
      }
    }
  }

}
