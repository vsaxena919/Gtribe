<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Tools
 */
$options = array(
    'data_management_section' => array(
        'title' => __('Data Management', 'classified-listing'),
        'type'  => 'title',
    ),
    'delete_all_data'         => array(
        'title'       => __('Delete all data', 'classified-listing'),
        'type'        => 'checkbox',
        'description' => __('Allow to delete all all listing data during delete this plugin', 'classified-listing'),
    ),
);

return apply_filters('rtcl_tools_settings_options', $options);