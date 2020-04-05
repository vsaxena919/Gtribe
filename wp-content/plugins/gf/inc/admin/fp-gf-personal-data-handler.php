<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
add_filter('wp_privacy_personal_data_exporters', 'show_galaxy_campaigns_action');
add_filter('wp_privacy_personal_data_erasers', 'remove_galaxy_campaigns_action');

function show_galaxy_campaigns_action($datas) {
    $datas['fp-galaxyfunder'] = array('exporter_friendly_name' => 'Galaxy Funder', 'callback' => 'fp_gf_campaigns_personal_data_exporter');
    $data['fp-galaxyfunder-user-data'] = array('exporter_friendly_name' => 'Galaxy Funder Customer Data', 'callback' => 'fp_gf_customer_personal_data_exporter');
    return $datas;
}

function remove_galaxy_campaigns_action($datas) {
    $datas['fp-galaxyfunder'] = array('eraser_friendly_name' => 'Galaxy Funder', 'callback' => 'fp_gf_campaigns_personal_data_eraser');
    return $datas;
}

function fp_gf_campaigns_personal_data_exporter($email_address) {
    $email_address_trimmed = trim($email_address);

    $data_to_export = array();

    $user = get_user_by('email', $email_address_trimmed);
    if (!$user) {
        return array(
            'data' => array(),
            'done' => true,
        );
    }
    $args = array(
        'posts_per_page' => -1,
        'post_type' => 'product',
        'author' => $user->ID,
        'post_status' => array('publish', 'pending', 'draft'),
        'meta_query' => array(
            'relation' => 'OR',
            '_crowdfundingcheckboxvalue' => array(
                'key' => '_crowdfundingcheckboxvalue',
                'value' => 'yes',
                'compare' => '=',
            ),
        ),
        'fields' => 'ids',
    );

    $my_campaigns = get_posts($args);

    foreach ($my_campaigns as $campaign_id) {
        $funding_type = get_post_meta($campaign_id, '_crowdfunding_options', true);
        $crowd_funding_type = $funding_type == '1' ? __('Fundraising by CrowdFunding', 'galaxyfunder') : __('Product Purchase by CrowdFunding', 'galaxyfunder');
        $campaign_end_method = get_post_meta($campaign_id, '_target_end_selection', true);
        if ($campaign_end_method == '1') {
            $target_end_method = __('Target Date', 'galaxyfunder');
            $target_goal = get_post_meta($campaign_id, '_crowdfundinggettargetprice', true);
            $target_reached = get_post_meta($campaign_id, '_crowdfundingtotalprice', true);
        } else if ($campaign_end_method == '2') {
            $target_end_method = __('Campaign Never Ends', 'galaxyfunder');
            $target_goal = get_post_meta($campaign_id, '_crowdfundinggettargetprice', true);
            $target_reached = get_post_meta($campaign_id, '_crowdfundingtotalprice', true);
        } else if ($campaign_end_method == '3') {
            $target_end_method = __('Target Goal', 'galaxyfunder');
            $target_goal = get_post_meta($campaign_id, '_crowdfundinggettargetprice', true);
            $target_reached = get_post_meta($campaign_id, '_crowdfundingtotalprice', true);
        } else if ($campaign_end_method == '4') {
            $target_end_method = __('Target Goal & Date', 'galaxyfunder');
            $target_goal = get_post_meta($campaign_id, '_crowdfundinggettargetprice', true);
            $target_reached = get_post_meta($campaign_id, '_crowdfundingtotalprice', true);
        } else {
            $target_end_method = __('Target Quantity', 'galaxyfunder');
            $target_goal = get_post_meta($campaign_id, '_crowdfundingquantity', true);
            $target_reached = get_post_meta($campaign_id, '_update_total_funders', true);
        }
        $campaign_goal = $campaign_end_method == '5' ? $target_goal : wc_price($target_goal);
        $paypal_email = get_post_meta($campaign_id, 'cf_campaigner_paypal_id', true);
        $campaign_goal_reached = $campaign_end_method == '5' ? $target_reached : wc_price($target_reached);
        $post_data_to_export = array(
            array('name' => __('Campaign name', 'galaxyfunder'), 'value' => get_the_title($campaign_id)),
            array('name' => __('Created on', 'galaxyfunder'), 'value' => get_the_date(get_option('date_format') . ' ' . get_option('time_format'), $campaign_id)),
            array('name' => __('Funding type', 'galaxyfunder'), 'value' => $crowd_funding_type),
            array('name' => __('Target End method', 'galaxyfunder'), 'value' => $target_end_method),
            array('name' => __('Target Goal', 'galaxyfunder'), 'value' => $campaign_goal),
            array('name' => __('Target reached', 'galaxyfunder'), 'value' => $campaign_goal_reached),
            array('name' => __('Campaign status', 'galaxyfunder'), 'value' => get_post_status($campaign_id)),
            array('name' => __('Paypal email to receive payment', 'galaxyfunder'), 'value' => $paypal_email),
        );

        $data_to_export[] = array(
            'group_id' => 'fp-galaxyfunder',
            'group_label' => __('User Created Campaigns', 'galaxyfunder'),
            'item_id' => "post-{$campaign_id}",
            'data' => $post_data_to_export,
        );
    }

    return array(
        'data' => $data_to_export,
        'done' => true,
    );
}

function fp_gf_customer_personal_data_exporter($email_address) {
    $email_address_trimmed = trim($email_address);

    $data_to_export = array();

    $user = get_user_by('email', $email_address_trimmed);
    if (!$user) {
        return array(
            'data' => array(),
            'done' => true,
        );
    }

    $post_data_to_export = array(
        array('name' => __('User Id', 'galaxyfunder'), 'value' => $user->ID),
    );

    $data_to_export[] = array(
        'group_id' => 'fp-gf-user-data',
        'group_label' => __('Galaxy Funder User Data', 'galaxyfunder'),
        'item_id' => "post-{$user->ID}",
        'data' => $post_data_to_export,
    );

    return array(
        'data' => $data_to_export,
        'done' => true,
    );
}

function fp_gf_campaigns_personal_data_eraser($email_address) {
    $user = get_user_by('email', $email_address); // Check if user has an ID in the DB to load stored personal data.
    $response = array(
        'items_removed' => false,
        'items_retained' => false,
        'messages' => array(),
        'done' => true,
    );
    if ($user) {
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'product',
            'author' => $user->ID,
            'post_status' => array('publish', 'pending', 'draft'),
            'meta_query' => array(
                'relation' => 'OR',
                '_crowdfundingcheckboxvalue' => array(
                    'key' => '_crowdfundingcheckboxvalue',
                    'value' => 'yes',
                    'compare' => '=',
                ),
            ),
            'fields' => 'ids',
        );
    }

    $my_campaigns = get_posts($args);

    if (0 < count($my_campaigns)) {
        foreach ($my_campaigns as $campaign_id) {
            wp_delete_post($campaign_id, true);
            /* Translators: %s Order number. */
            $response['messages'][] = sprintf(__('Removed personal data from Campaign %s.', 'galaxyfunder'), $campaign_id);
            $response['items_removed'] = true;
        }
        $response['done'] = 10 > count($my_campaigns);
    } else {
        $response['done'] = true;
    }

    return $response;
}
