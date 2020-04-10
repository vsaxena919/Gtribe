<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;

class Cron
{

    function __construct() {

        add_action('wp', array($this, 'schedule_events'));
        add_action('rtcl_hourly_scheduled_events', array($this, 'hourly_scheduled_events'));

    }

    function schedule_events() {

        if (!wp_next_scheduled('rtcl_hourly_scheduled_events')) {
            wp_schedule_event(current_time('timestamp'), 'hourly', 'rtcl_hourly_scheduled_events');
        }

    }

    function hourly_scheduled_events() {
        // TODO : Active all this function to active
        $this->sent_renewal_email_to_published_listings();
        $this->move_listings_publish_to_expired();
        $this->send_renewal_reminders();
        $this->delete_expired_listings();

        do_action('rtcl_scheduled_events');

    }

    function sent_renewal_email_to_published_listings() {
        $email_settings = Functions::get_option('rtcl_email_settings');
        $email_threshold = (int)$email_settings['renewal_email_threshold'];

        if ($email_threshold > 0) {

            $email_threshold_date = date('Y-m-d H:i:s', strtotime("+" . $email_threshold . " days"));

            // Define the query
            $args = array(
                'post_type'      => rtcl()->post_type,
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'expiry_date',
                        'value'   => $email_threshold_date,
                        'compare' => '<',
                        'type'    => 'DATETIME'
                    ),
                    array(
                        'key'     => 'renewal_reminder_sent',
                        'compare' => 'NOT EXISTS'
                    ),
                    array(
                        'key'     => 'never_expires',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );

            $rtcl_query = new \WP_Query($args);

            // Start the Loop
            global $post;

            if ($rtcl_query->have_posts()) {

                while ($rtcl_query->have_posts()) {

                    $rtcl_query->the_post();

                    do_action("rtcl_renewal_listing", $post->ID); // TODO : make sure to sue this hook

                    // Send emails to user
                    if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_renewal', 'multi_checkbox')) {
                        if (rtcl()->mailer()->emails['Listing_Renewal_Email_To_Owner']->trigger($post->ID)) {
                            update_post_meta($post->ID, 'renewal_reminder_sent', 1);
                        }
                    }

                }

            }

            // Use reset postdata to restore original query
            wp_reset_postdata();

        }
    }

    function move_listings_publish_to_expired() {

        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $email_settings = Functions::get_option('rtcl_email_template_renewal_reminder');
        $renewal_reminder_threshold = isset($email_settings['renewal_reminder_threshold']) ? (int)$email_settings['renewal_reminder_threshold'] : 0;
        $delete_expired_listings = isset($moderation_settings['delete_expired_listings']) ? (int)$moderation_settings['delete_expired_listings'] : 0;
        $delete_threshold = $renewal_reminder_threshold + $delete_expired_listings;

        // Define the query
        $args = array(
            'post_type'      => rtcl()->post_type,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'expiry_date',
                    'value'   => current_time('mysql'),
                    'compare' => '<',
                    'type'    => 'DATETIME'
                ),
                array(
                    'key'     => 'never_expires',
                    'compare' => 'NOT EXISTS',
                )
            )
        );

        $rtcl_query = new \WP_Query($args);

        // Start the Loop
        global $post;

        if ($rtcl_query->have_posts()) {

            while ($rtcl_query->have_posts()) {

                $rtcl_query->the_post();

                // Update the post into the database
                $newData = array(
                    'ID'          => $post->ID,
                    'post_status' => 'rtcl-expired'
                );

                wp_update_post($newData);      // Update post status to
                update_post_meta($post->ID, 'featured', 0);
                update_post_meta($post->ID, '_top', 0);
                delete_post_meta($post->ID, 'expiry_date');
                delete_post_meta($post->ID, 'never_expired');
                delete_post_meta($post->ID, 'feature_expiry_date');
                delete_post_meta($post->ID, '_top_expiry_date');
                update_post_meta($post->ID, 'renewal_reminder_sent', 0);

                if ($delete_threshold > 0) {
                    $deletion_date_time = date('Y-m-d H:i:s', strtotime("+" . $delete_threshold . " days"));
                    update_post_meta($post->ID, 'deletion_date', $deletion_date_time); // TODO : Need to check from where it to make action
                }

                // Hook for developers
                do_action('rtcl_expired_listing', $post->ID);

                if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'listing_expired', 'multi_checkbox')) {
                    rtcl()->mailer()->emails['Listing_Expired_Email_To_Owner']->trigger($post->ID);
                }

                if (Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'listing_expired', 'multi_checkbox')) {
                    rtcl()->mailer()->emails['Listing_Expired_Email_To_Admin']->trigger($post->ID);
                }

            }

        }

        // Use reset post data to restore original query
        wp_reset_postdata();

    }

    function delete_expired_listings() {

        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $email_settings = Functions::get_option('rtcl_email_template_renewal_reminder');
        $renewal_reminder_threshold = isset($email_settings['renewal_reminder_threshold']) ? (int)$email_settings['renewal_reminder_threshold'] : 0;
        $delete_expired_listings = isset($moderation_settings['delete_expired_listings']) ? (int)$moderation_settings['delete_expired_listings'] : 0;
        $delete_threshold = $renewal_reminder_threshold + $delete_expired_listings;

        if ($delete_threshold > 0) {

            // Define the query
            $args = array(
                'post_type'      => rtcl()->post_type,
                'posts_per_page' => -1,
                'post_status'    => 'rtcl-expired',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'deletion_date',
                        'value'   => current_time('mysql'),
                        'compare' => '<',
                        'type'    => 'DATETIME'
                    ),
                    array(
                        'key'     => 'never_expires',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );

            $rtcl_query = new \WP_Query($args);

            // Start the Loop
            global $post;

            if ($rtcl_query->have_posts()) {

                while ($rtcl_query->have_posts()) {
                    $rtcl_query->the_post();
                    Functions::delete_post($post->ID);
                    do_action("rtcl_delete_listing", $post->ID); // TODO : make task
                }

            }

            // Use reset postdata to restore original query
            wp_reset_postdata();
        }
    }

    /**
     * Renewal Reminders
     *
     * @return void
     */
    function send_renewal_reminders() {
        $email_settings = Functions::get_option('rtcl_email_settings');
        $reminder_threshold = isset($email_settings['renewal_reminder_threshold']) ? (int)$email_settings['renewal_reminder_threshold'] : 0;

        if ($reminder_threshold > 0) {

            // Define the query
            $args = array(
                'post_type'      => rtcl()->post_type,
                'posts_per_page' => -1,
                'post_status'    => 'rtcl-expired',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'renewal_reminder_sent',
                        'value'   => 0,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'never_expires',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );

            $rtcl_query = new \WP_Query($args);

            // Start the Loop
            global $post;

            if ($rtcl_query->have_posts()) {

                while ($rtcl_query->have_posts()) {

                    $rtcl_query->the_post();


                    $expiration_date = get_post_meta($post->ID, 'expiry_date', true);
                    $expiration_date_time = strtotime($expiration_date);
                    $reminder_date_time = strtotime("+" . $reminder_threshold . " days", strtotime($expiration_date_time));

                    if (current_time('timestamp') > $reminder_date_time) {

                        // Send renewal reminder emails to listing owner
                        update_post_meta($post->ID, 'renewal_reminder_sent', 1);
                        if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'remind_renewal', 'multi_checkbox')) {
                            rtcl()->mailer()->emails['Listing_Renewal_Reminder_Email_To_Owner']->trigger($post->ID);
                        }
                    }

                }

            }

            // Use reset postdata to restore original query
            wp_reset_postdata();

        }
    }

}