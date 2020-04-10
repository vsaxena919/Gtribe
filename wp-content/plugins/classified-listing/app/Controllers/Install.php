<?php

namespace Rtcl\Controllers;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Roles;

class Install
{

    public static function deactivate() {

        // Un-schedules all previously-scheduled cron jobs
        wp_clear_scheduled_hook('rtcl_hourly_scheduled_events');

    }


    public static function activate() {

        if (!is_blog_installed()) {
            return;
        }

        // Check if we are not already running this routine.
        if ('yes' === get_transient('rtcl_installing')) {
            return;
        }

        // If we made it till here nothing is running yet, lets set the transient now.
        set_transient('rtcl_installing', 'yes', MINUTE_IN_SECONDS * 10);

        self::create_options();
        self::create_tables();
        self::create_roles();
        self::update_rtcl_version();

        delete_transient('rtcl_installing');

        do_action('rtcl_flush_rewrite_rules');
        do_action('rtcl_installed');

    }

    private static function update_rtcl_version() {
        delete_option('rtcl_version');
        add_option('rtcl_version', RTCL_VERSION);
    }

    private static function create_options() {
        // Insert plugin settings and default values for the first time
        $options = array(
            'rtcl_general_settings'    => array(
                'load_bootstrap'               => array('css', 'js'),
                'include_results_from'         => array('child_categories', 'child_locations'),
                'listings_per_page'            => 20,
                'related_posts_per_page'       => 4,
                'orderby'                      => 'date',
                'order'                        => 'desc',
                'taxonomy_orderby'             => 'title',
                'taxonomy_order'               => 'asc',
                'text_editor'                  => 'wp_editor',
                'location_level_first'         => __("State", 'classified-listing'),
                'location_level_second'        => __("City", 'classified-listing'),
                'location_level_third'         => __("Town", 'classified-listing'),
                'currency'                     => 'USD',
                'currency_position'            => 'right',
                'currency_thousands_separator' => ',',
                'currency_decimal_separator'   => '.',
            ),
            'rtcl_moderation_settings' => array(
                'listing_duration'             => 15,
                'new_listing_threshold'        => 3,
                'new_listing_label'            => __("New", 'classified-listing'),
                'listing_featured_label'       => __("Featured", 'classified-listing'),
                'listing_top_label'            => __("Top", 'classified-listing'),
                'display_options'              => array(
                    'category',
                    'location',
                    'date',
                    'user',
                    'price',
                    'views',
                    'new',
                    'featured'
                ),
                'display_options_detail'       => array(
                    'favourites',
                    'category',
                    'location',
                    'date',
                    'user',
                    'price',
                    'views',
                    'new',
                    'featured'
                ),
                'detail_page_sidebar_position' => 'right',
                'has_favourites'               => 'yes',
                'has_report_abuse'             => 'yes',
                'has_contact_form'             => 'yes',
                'maximum_images_per_listing'   => 5,
                'delete_expired_listings'      => 15,
                'new_listing_status'           => 'pending',
                'edited_listing_status'        => 'pending'
            ),
            'rtcl_payment_settings'    => array(
                'payment'                      => 'yes',
                'use_https'                    => 'no',
                'currency'                     => 'USD',
                'currency_position'            => 'right',
                'currency_thousands_separator' => ',',
                'currency_decimal_separator'   => '.',
            ),
            'rtcl_payment_offline'     => array(
                'enabled'      => 'yes',
                'title'        => __('Direct Bank Transfer', 'classified-listing'),
                'description'  => __("Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won't get approved until the funds have cleared in our account.",
                    'classified-listing'),
                'instructions' => __('Make your payment directly in our bank account. Please use your Order ID as payment reference. Your order won\'t get approved until the funds have cleared in our account.
Account details :
		
Account Name : YOUR ACCOUNT NAME
Account Number : YOUR ACCOUNT NUMBER
Bank Name : YOUR BANK NAME
		
If we don\'t receive your payment within 48 hrs, we will cancel the order.', 'classified-listing'),
            ),
            'rtcl_email_settings'      => array(
                'from_name'                  => get_option('blogname'),
                'from_email'                 => get_option('admin_email'),
                'admin_notice_emails'        => get_option('admin_email'),
                'notify_admin'               => array('register_new_user', 'listing_submitted', 'order_created', 'payment_received'),
                'notify_users'               => array(
                    'listing_submitted',
                    'listing_published',
                    'listing_renewal',
                    'listing_expired',
                    'remind_renewal',
                    'order_created',
                    'order_completed'
                ),
                'listing_submitted_subject'  => __('[{site_title}] Listing "{listing_title}" is received', 'classified-listing'),
                'listing_submitted_heading'  => __('Your listing is received', 'classified-listing'),
                'listing_published_subject'  => __('[{site_title}] Listing "{listing_title}" is published', 'classified-listing'),
                'listing_published_heading'  => __('Your listing is published', 'classified-listing'),
                'renewal_email_threshold'    => 3,
                'renewal_subject'            => __('[{site_name}] {listing_title} - Expiration notice', 'classified-listing'),
                'renewal_heading'            => __('Expiration notice', 'classified-listing'),
                'expired_subject'            => __('[{site_title}] {listing_title} - Expiration notice', 'classified-listing'),
                'expired_heading'            => __('Expiration notice', 'classified-listing'),
                'renewal_reminder_threshold' => 3,
                'renewal_reminder_subject'   => __('[{site_title}] {listing_title} - Renewal reminder', 'classified-listing'),
                'renewal_reminder_heading'   => __('Renewal reminder', 'classified-listing'),
                'order_created_subject'      => __('[{site_title}] #{order_number} Thank you for your order', 'classified-listing'),
                'order_created_heading'      => __('New Order: #{order_number}', 'classified-listing'),
                'order_completed_subject'    => __('[{site_title}] : #{order_number} Order is completed.', 'classified-listing'),
                'order_completed_heading'    => __('Payment is completed: #{order_number}', 'classified-listing'),
                'contact_subject'            => __('[{site_title}] Contact via "{listing_title}"', 'classified-listing'),
                'contact_heading'            => __('Thank you for mail', 'classified-listing')
            ),
            'rtcl_account_settings'    => array(
                'enable_myaccount_registration' => "yes"
            ),
            'rtcl_misc_settings'       => array(
                'image_size_gallery'           => array('width' => 924, 'height' => 462, 'crop' => 'yes'),
                'image_size_gallery_thumbnail' => array('width' => 150, 'height' => 105, 'crop' => 'yes'),
                'image_size_thumbnail'         => array('width' => 320, 'height' => 240, 'crop' => 'yes'),
                'image_allowed_type'           => array('png', 'jpg', 'jpeg'),
                'image_allowed_memory'         => 2,
                'image_edit_cap'               => 'yes',
                'social_services'              => array('facebook', 'twitter', 'gplus'),
                'social_pages'                 => array('listing')
            ),
            'rtcl_advanced_settings'   => array(
                'permalink'                         => 'rtcl_listing',
                'myaccount_listings_endpoint'       => 'listings',
                'myaccount_favourites_endpoint'     => 'favourites',
                'myaccount_edit_account_endpoint'   => 'edit-account',
                'myaccount_payments_endpoint'       => 'payments',
                'myaccount_lost_password_endpoint'  => 'lost-password',
                'myaccount_logout_endpoint'         => 'logout',
                'checkout_submission_endpoint'      => 'submission',
                'checkout_promote_endpoint'         => 'promote',
                'checkout_payment_receipt_endpoint' => 'payment-receipt',
                'checkout_payment_failure_endpoint' => 'payment-failure'
            )
        );

        foreach ($options as $option_name => $defaults) {
            if (false == get_option($option_name)) {
                add_option($option_name, apply_filters($option_name . '_defaults', $defaults));
            }
        }

        $pages = Functions::insert_custom_pages();
        if (!empty($pages)) {
            $pSettings = get_option('rtcl_advanced_settings', array());
            foreach ($pages as $pSlug => $pId) {
                $pSettings[$pSlug] = $pId;
            }
            update_option('rtcl_advanced_settings', $pSettings);
        }
    }

    private static function create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta(self::get_schema());
    }

    private static function get_schema() {
        global $wpdb;

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $tables = "CREATE TABLE {$wpdb->prefix}rtcl_sessions (
						  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
						  session_key char(32) NOT NULL,
						  session_value longtext NOT NULL,
						  session_expiry BIGINT UNSIGNED NOT NULL,
						  PRIMARY KEY  (session_key),
						  UNIQUE KEY session_id (session_id)
						) $collate;";

        return $tables;
    }

    private static function create_roles() {
        $role = new Roles();
        $role->add_default_caps();
    }
}