<?php

declare(strict_types=1);

namespace Drupal\data_stream\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for data_stream.
 */
class DataStreamViewsHooks {

  /**
   * Implements hook_views_data().
   */
  #[Hook('views_data')]
  public function viewsData() {
    $data = [];
    /** @var \Drupal\data_stream\DataStreamTypeManager $manager */
    $manager = \Drupal::service('plugin.manager.data_stream_type');
    // Collect views data from all data stream type plugins.
    $data_stream_types = $manager->getDefinitions();
    foreach (array_keys($data_stream_types) as $plugin_id) {
      /** @var \Drupal\data_stream\Plugin\DataStream\DataStreamType\DataStreamTypeInterface $plugin */
      $plugin = $manager->createInstance($plugin_id);
      $data = array_replace_recursive($data, $plugin->getViewsData());
    }
    return $data;
  }

}
