<?php

/**
 * @file
 * Provide Views data for data_stream.module.
 */

declare(strict_types=1);

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\data_stream\Hook\DataStreamViewsHooks;

/**
 * Implements hook_views_data().
 */
#[LegacyHook]
function data_stream_views_data()
{
    return \Drupal::service(DataStreamViewsHooks::class)->viewsData();
}
