<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclCFGField;

class SaveListingMetaData
{

    public function __construct() {
        add_action('save_post', array($this, 'save_listing_meta_data'), 10, 2);
    }

    public function save_listing_meta_data($post_id, $post) {

        if (!isset($_POST['post_type'])) {
            return $post_id;
        }

        if (rtcl()->post_type != $post->post_type) {
            return $post_id;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check the logged in user has permission to edit this post
        if (!Functions::current_user_can('edit_' . rtcl()->post_type, $post_id)) {
            return $post_id;
        }

        if (!Functions::verify_nonce()) {
            return $post_id;
        }
        if (isset($_POST['overwrite'])) {
            if (!isset($_POST['never_expires']) && isset($_POST['expiry_date'])) {
                $expiry_date = Functions::datetime('mysql', trim(($_POST['expiry_date'])));
                update_post_meta($post_id, 'expiry_date', $expiry_date);
            }
            if (isset($_POST['never_expires'])) {
                update_post_meta($post_id, 'never_expires', 1);
            } else {
                delete_post_meta($post_id, 'never_expires');
            }

            // Feature
            $featured = isset($_POST['featured']) ? 1 : 0;
            $old_featured = get_post_meta($post_id, "featured", true);
            if ($old_featured != $featured) {
                update_post_meta($post_id, 'featured', $featured);
                do_action("rtcl_listing_featured_change", $featured, $old_featured, $post_id);
            }
            // Top
            $top = isset($_POST['_top']) ? 1 : 0;
            $old_top = get_post_meta($post_id, "_top", true);
            if ($old_top != $top) {
                update_post_meta($post_id, '_top', $top);
                do_action("rtcl_listing_top_change", $top, $old_top, $post_id);
            }

            do_action("rtcl_listing_overwrite_change", $post_id, $_POST);
        }

        // Update view
        if (isset($_POST['_views'])) {
            update_post_meta($post_id, '_views', absint($_POST['_views']));
        }

        // Category
        $cats = array();
        if (isset($_POST['rtcl_category'])) {
            $cats[] = absint($_POST['rtcl_category']);
        }
        if (isset($_POST['rtcl_sub_category'])) {
            $cats[] = absint($_POST['rtcl_sub_category']);
        }
        wp_set_object_terms($post_id, $cats, rtcl()->category);
        if (isset($_POST['ad_type'])) {
            $ad_type = sanitize_text_field($_POST['ad_type']);
            update_post_meta($post_id, 'ad_type', $ad_type);
        }
        // Price type
        if (isset($_POST['price_type'])) {
            $price_type = sanitize_text_field($_POST['price_type']);
            update_post_meta($post_id, 'price_type', $price_type);
        }

        // Price
        if (isset($_POST['price'])) {
            $price = Functions::format_decimal($_POST['price']);
            update_post_meta($post_id, 'price', $price);
        }

        /* Listing field data */
        if (isset($_POST['rtcl_fields'])) {

            foreach ($_POST['rtcl_fields'] as $key => $value) {
                $field_id = (int)str_replace('_field_', '', $key);
                $field = new RtclCFGField($field_id);
                if ($field_id && $field) {
                    $field->saveSanitizedValue($post_id, $value);
                }
            }

        }

        /* Save location meta data */
        $address = esc_textarea($_POST['address']);
        update_post_meta($post_id, 'address', $address);

        // Location
        $locations = array();
        if (isset($_POST['location'])) {
            $locations[] = absint($_POST['location']);
        }
        if (isset($_POST['sub_location'])) {
            $locations[] = absint($_POST['sub_location']);
        }
        if (isset($_POST['sub_sub_location'])) {
            $locations[] = absint($_POST['sub_sub_location']);
        }
        wp_set_object_terms($post_id, $locations, rtcl()->location);

        if (isset($_POST['zipcode'])) {
            $zipcode = sanitize_text_field($_POST['zipcode']);
            update_post_meta($post_id, 'zipcode', $zipcode);
        }
        if (isset($_POST['phone'])) {
            $phone = sanitize_text_field($_POST['phone']);
            update_post_meta($post_id, 'phone', $phone);
        }

        if (isset($_POST['email'])) {
            $email = sanitize_email($_POST['email']);
            update_post_meta($post_id, 'email', $email);
        }

        if (isset($_POST['website'])) {
            $website = esc_url_raw($_POST['website']);
            update_post_meta($post_id, 'website', $website);
        }

        $latitude = isset($_POST['latitude']) ? sanitize_text_field($_POST['latitude']) : '';
        update_post_meta($post_id, 'latitude', $latitude);

        $longitude = isset($_POST['longitude']) ? sanitize_text_field($_POST['longitude']) : '';
        update_post_meta($post_id, 'longitude', $longitude);

        $hide_map = isset($_POST['hide_map']) ? 1 : 0;
        update_post_meta($post_id, 'hide_map', $hide_map);


    }
}