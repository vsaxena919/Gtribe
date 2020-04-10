<?php

namespace Rtcl\Controllers;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;

class ListingHook
{

    public static function init() {
        add_action("rtcl_listing_form", array(__CLASS__, 'listing_information'), 10);
        add_action("rtcl_listing_form", array(__CLASS__, 'listing_gallery'), 20);
        add_action("rtcl_listing_form", array(__CLASS__, 'listing_contact'), 30);
        add_action("rtcl_listing_form", array(__CLASS__, 'listing_recaptcha'), 40);
        add_action("rtcl_listing_form", array(__CLASS__, 'listing_terms_conditions'), 50);

        add_action("rtcl_listing_form", array(__CLASS__, 'add_wpml_support'), 60);
        add_action("rtcl_widget_filter_form", array(__CLASS__, 'add_wpml_support'));
        add_action("rtcl_widget_search_inline_form", array(__CLASS__, 'add_wpml_support'));
        add_action("rtcl_widget_search_vertical_form", array(__CLASS__, 'add_wpml_support'));
    }

    public static function listing_information($post_id) {

        $general_settings = Functions::get_option('rtcl_general_settings');
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $editor = !empty($general_settings['text_editor']) ? $general_settings['text_editor'] : 'wp_editor';
        $selected_categories = array();
        $listing = null;
        $price = $post_content = $price_type = $title = $ad_type = '';
        if ($post_id > 0) {
            $listing = new Listing($post_id);
            $selected_categories = wp_get_object_terms($post_id, rtcl()->category, array('fields' => 'ids'));
            $price_type = get_post_meta($post_id, 'price_type', true);
            $price = get_post_meta($post_id, 'price', true);
            $ad_type = get_post_meta($post_id, 'ad_type', true);
            global $post;
            $post = get_post($post_id);
            setup_postdata($post);
            $title = get_the_title();
            $post_content = get_the_content();
            wp_reset_postdata();
        }
        Functions::get_template("listing-form/information", apply_filters('rtcl_listing_form_information_data', array(
            'post_id'             => $post_id,
            'listing'             => $listing,
            'title'               => $title,
            'post_content'        => $post_content,
            'ad_type'             => $ad_type,
            'price'               => $price,
            'price_type'          => $price_type,
            'editor'              => $editor,
            'selected_categories' => $selected_categories,
            'parent_cat_id'       => 0,
            'child_cat_id'        => 0,
            'category_id'         => 0,
            'hidden_fields'       => (!empty($moderation_settings['hide_form_fields'])) ? $moderation_settings['hide_form_fields'] : array()
        )));
    }

    public static function listing_gallery($post_id) {
        if (!Functions::is_gallery_disabled()) {
            Functions::get_template("listing-form/gallery", compact('post_id'));
        }
    }

    public static function listing_recaptcha($post_id) {
        $settings = Functions::get_option_item('rtcl_misc_settings', 'recaptcha_forms', array());
        if (!empty($settings) && is_array($settings) && in_array('listing', $settings)) {
            Functions::get_template("listing-form/recaptcha", compact('post_id'));
        }
    }

    public static function listing_terms_conditions($post_id) {
        $agreed = get_post_meta($post_id, 'rtcl_agree', true);
        Functions::get_template("listing-form/terms-conditions", compact('post_id', 'agreed'));
    }

    public static function listing_contact($post_id) {

        $location_id = $sub_location_id = $sub_sub_location_id = 0;
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $email = $user->user_email;
        $phone = get_user_meta($user_id, '_rtcl_phone', true);
        $website = get_user_meta($user_id, '_rtcl_website', true);
        $selected_locations = (array)get_user_meta($user_id, '_rtcl_location', true);
        $zipcode = get_user_meta($user_id, '_rtcl_zipcode', true);
        $address = get_user_meta($user_id, '_rtcl_address', true);


        if ($post_id) {
            $selected_locations = wp_get_object_terms($post_id, rtcl()->location, array('fields' => 'ids'));
            $zipcode = get_post_meta($post_id, 'zipcode', true);
            $address = get_post_meta($post_id, 'address', true);
            $phone = get_post_meta($post_id, 'phone', true);
            $email = get_post_meta($post_id, 'email', true);
            $website = get_post_meta($post_id, 'website', true);
        }
        $generalSettings = Functions::get_option('rtcl_general_settings');
        $state_text = Text::location_level_first();
        $city_text = Text::location_level_second();
        $town_text = Text::location_level_third();
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        Functions::get_template("listing-form/contact", array(
            'post_id'             => $post_id,
            'state_text'          => $state_text,
            'city_text'           => $city_text,
            'town_text'           => $town_text,
            'selected_locations'  => $selected_locations,
            'zipcode'             => $zipcode,
            'address'             => $address,
            'phone'               => $phone,
            'email'               => $email,
            'website'             => $website,
            'location_id'         => $location_id,
            'sub_location_id'     => $sub_location_id,
            'sub_sub_location_id' => $sub_sub_location_id,
            'hidden_fields'       => (!empty($moderation_settings['hide_form_fields'])) ? $moderation_settings['hide_form_fields'] : array()
        ));
    }

    public static function add_wpml_support() {
        if (function_exists('icl_object_id') && isset($_REQUEST['lang'])) {
            echo sprintf('<input type="hidden" name="lang" value="%s" />', esc_attr($_REQUEST['lang']));
        }
    }

}