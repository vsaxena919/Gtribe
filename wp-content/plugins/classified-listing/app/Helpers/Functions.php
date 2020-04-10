<?php

namespace Rtcl\Helpers;

use DateTime;
use DateTimeZone;
use Rtcl\Controllers\Admin\AddConfig;
use Rtcl\Helpers\SortImages as SortImages;
use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Models\RtclCFGField;
use Rtcl\Models\RtclDateTime;
use Rtcl\Resources\Options;
use Rtcl\Traits\ListingTrait;
use Rtcl\Traits\SettingsTrait;
use Rtcl\Traits\UtilityTrait;

class Functions
{

    use ListingTrait;
    use SettingsTrait;
    use UtilityTrait;

    public static function verify_nonce() {
        $nonce = isset($_REQUEST[rtcl()->nonceId]) ? $_REQUEST[rtcl()->nonceId] : null;
        $nonceText = rtcl()->nonceText;
        if (wp_verify_nonce($nonce, $nonceText)) {
            return true;
        }

        return false;
    }

    /**
     * Get data if set, otherwise return a default value or null. Prevents notices when data is not set.
     *
     * @param      $var
     * @param null $default
     *
     * @return null
     */
    public static function get_var(&$var, $default = null) {
        return isset($var) ? $var : $default;
    }

    public static function get_raw_referer() {
        if (function_exists('wp_get_raw_referer')) {
            return wp_get_raw_referer();
        }

        if (!empty($_REQUEST['_wp_http_referer'])) { // WPCS: input var ok, CSRF ok.
            return wp_unslash($_REQUEST['_wp_http_referer']); // WPCS: input var ok, CSRF ok, sanitization ok.
        } elseif (!empty($_SERVER['HTTP_REFERER'])) { // WPCS: input var ok, CSRF ok.
            return wp_unslash($_SERVER['HTTP_REFERER']); // WPCS: input var ok, CSRF ok, sanitization ok.
        }

        return false;
    }

    public static function get_status_i18n($status) {
        $status_list = Options::get_status_list() + Options::get_payment_status_list();

        return !empty($status_list[$status]) ? $status_list[$status] : false;
    }

    public static function get_single_term_title() {
        $location = get_query_var('rtcl_location');
        $category = get_query_var('rtcl_category');
        $term = null;
        if ($location) {
            $term = get_term_by('slug', $location, rtcl()->location);
        }
        if ($category) {
            $term = get_term_by('slug', $category, rtcl()->category);
        }
        if ($term) {
            return $term->name;
        }

        return false;
    }

    public static function meta_exist($post_id = null, $meta_key, $type = "post") {
        if (!$post_id) {
            return false;
        }

        return metadata_exists($type, $post_id, $meta_key);
    }

    public static function get_favourites_link($post_id) {
        if (is_user_logged_in()) {

            if ($post_id == 0) {
                global $post;
                $post_id = $post->ID;
            }

            $favourites = (array)get_user_meta(get_current_user_id(), 'rtcl_favourites', true);

            if (in_array($post_id, $favourites)) {
                return '<a href="javascript:void(0)" class="rtcl-favourites rtcl-active" data-id="' . $post_id . '"><span class="rtcl-icon rtcl-icon-heart"></span><span class="favourite-label">' . Text::remove_from_favourite() . '</span></a>';
            } else {
                return '<a href="javascript:void(0)" class="rtcl-favourites" data-id="' . $post_id . '"><span class="rtcl-icon rtcl-icon-heart-empty"></span><span class="favourite-label">' . Text::add_to_favourite() . '</span></a>';
            }

        } else {

            return '<a href="javascript:void(0)" class="rtcl-require-login"><span class="rtcl-icon rtcl-icon-heart-empty"></span><span class="favourite-label">' . Text::add_to_favourite() . '</span></a>';
        }
    }

    public static function post_content_has_shortcode($tag = '') {
        global $post;

        return is_singular() && is_a($post, '\WP_Post') && has_shortcode($post->post_content, $tag);
    }

    /**
     * @param $endpoint
     *
     * @return bool
     */
    public static function is_account_page($endpoint = null) {
        $is_account_page = is_page(self::get_page_id('myaccount')) || self::post_content_has_shortcode('rtcl_my_account') || apply_filters('rtcl_is_account_page', false);
        if ($is_account_page && $endpoint) {
            global $wp;

            return isset($wp->query_vars[$endpoint]) ? true : false;
        }

        return $is_account_page;

    }

    static function is_listings_page() {
        return is_page(self::get_page_id('listings')) || self::post_content_has_shortcode('rtcl_listings') || apply_filters('rtcl_is_listings_page', false);
    }

    /**
     * @return bool
     */
    static function is_listing_form_page() {
        return is_page(self::get_page_id('listing_form')) || self::post_content_has_shortcode('rtcl_listing_form') || apply_filters('rtcl_is_listing_form_page', false);
    }

    /**
     * @param null $endpoint
     *
     * @return bool
     */
    static function is_checkout_page($endpoint = null) {
        $is_checkout_page = is_page(self::get_page_id('checkout')) || self::post_content_has_shortcode('rtcl_checkout') || apply_filters('rtcl_is_checkout_page', false);

        if ($is_checkout_page && $endpoint) {
            global $wp;

            return isset($wp->query_vars[$endpoint]) ? true : false;
        }

        return $is_checkout_page;
    }

    public static function get_my_account_page_endpoints() {

        $endpoints = array(
            // My account actions.
            'listings'      => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_listings_endpoint', 'listings'),
            'favourites'    => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_favourites_endpoint', 'favourites'),
            'payments'      => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_payments_endpoint', 'payments'),
            'edit-account'  => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_edit_account_endpoint', 'edit-account'),
            'lost-password' => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_lost_password_endpoint', 'lost-password'),
            'logout'        => Functions::get_option_item('rtcl_advanced_settings', 'myaccount_logout_endpoint', 'logout')
        );

        return apply_filters('rtcl_my_account_endpoint', $endpoints);
    }

    public static function get_checkout_page_endpoints() {
        $endpoints = array(
            'submission'      => self::get_option_item('rtcl_advanced_settings', 'checkout_submission_endpoint', 'submission'),
            'promote'         => self::get_option_item('rtcl_advanced_settings', 'received', 'promote'),
            'payment-receipt' => self::get_option_item('rtcl_advanced_settings', 'checkout_payment_receipt_endpoint', 'payment-receipt'),
            'payment-failure' => self::get_option_item('rtcl_advanced_settings', 'checkout_payment_failure_endpoint', 'payment-failure')
        );

        return apply_filters('rtcl_checkout_endpoints', $endpoints);
    }

    public static function get_page_id($page) {
        if ('pay' === $page || 'thanks' === $page || 'promote' === $page) {
            $page = 'checkout';
        }
        if ('change_password' === $page || 'edit_address' === $page || 'lost_password' === $page) {
            $page = 'myaccount';
        }

        $page = apply_filters('rtcl_get_' . $page . '_page_id', self::get_option_item('rtcl_advanced_settings', $page));

        return $page ? absint($page) : -1;
    }

    public static function is_human($form) {

        $misc_settings = Functions::get_option('rtcl_misc_settings');

        $has_captcha = false;
        if (isset($misc_settings['recaptcha_forms']) && '' !== $misc_settings['recaptcha_site_key'] && '' !== $misc_settings['recaptcha_secret_key']) {
            if (in_array($form, $misc_settings['recaptcha_forms'])) {
                $has_captcha = true;
            }
        }

        if ($has_captcha) {

            $response = isset($_POST['g-recaptcha-response']) ? esc_attr($_POST['g-recaptcha-response']) : '';

            if ('' !== $response) {

                // make a GET request to the Google reCAPTCHA Server
                $request = wp_remote_get('https://www.google.com/recaptcha/api/siteverify?secret=' . $misc_settings['recaptcha_secret_key'] . '&response=' . $response . '&remoteip=' . $_SERVER["REMOTE_ADDR"]);

                // get the request response body
                $response_body = wp_remote_retrieve_body($request);

                $result = json_decode($response_body, true);

                // return true or false, based on users input
                return (true == $result['success']) ? true : false;

            } else {
                return false;
            }

        }

        return true;

    }


    /**
     * Check Moderation (rtcl_moderation_settings) hide_form_fields $field is hide
     *
     * @param $field
     *
     * @return bool
     */
    static function is_field_disabled($field) {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', $field, 'multi_checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_payment_disabled() {

        if (!Functions::get_option_item('rtcl_payment_settings', 'payment', false, 'checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_favourites_disabled() {

        if (!Functions::get_option_item('rtcl_moderation_settings', 'has_favourites', false, 'checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_price_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'price', 'multi_checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_gallery_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'gallery', 'multi_checkbox')) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    static function is_description_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'description', 'multi_checkbox')) {
            return true;
        }

        return false;
    }


    /**
     * @return bool
     */
    static function is_ad_type_disabled() {
        if (Functions::get_option_item('rtcl_moderation_settings', 'hide_form_fields', 'ad_type', 'multi_checkbox')) {
            return true;
        }

        return false;
    }

    static function get_regular_pricing_options() {
        $regular_pricing = get_posts(array(
            'post_type'        => rtcl()->post_type_pricing,
            'posts_per_page'   => -1,
            'post_status'      => 'publish',
            'sort_column'      => 'menu_order',
            'meta_query'       => array(
                array(
                    'key'   => 'pricing_type',
                    'value' => 'regular'
                ),
                array(
                    'key'     => 'pricing_type',
                    'compare' => 'NOT EXISTS',
                ),
                'relation' => 'OR'
            ),
            'suppress_filters' => false
        ));

        return apply_filters('rtcl_get_regular_pricing_options', $regular_pricing);

    }

    /**
     * @param      $price
     * @param bool $payment
     *
     * @return string
     */
    static function get_formatted_price($price, $payment = false) {
        $price = self::get_formatted_amount($price);
        $currency = self::get_currency($payment);
        $currency_symbol = self::get_currency_symbol($currency, $payment);
        $price_format = self::get_price_format($payment);
        if ($payment) {
            $formatted_payment_price = apply_filters('rtcl_formatted_payment_price_html',
                sprintf($price_format, '<span class="rtcl-price-currencySymbol">' . $currency_symbol . '</span>', $price),
                $price_format, $currency_symbol, $price);
            ob_start();
            do_action('rtcl_payment_price_meta_html', $price_format, $currency_symbol, $price);
            $payment_price_meta_html = ob_get_clean();
            $payment_price_meta_html = $payment_price_meta_html ? apply_filters('rtcl_payment_price_meta_wrap_html', sprintf('<span class="rtcl-payment-price-meta">%s</span>', $payment_price_meta_html), $payment_price_meta_html) : null;
            $payment_price_html_format = apply_filters('rtcl_payment_price_amount_html_format', '<span class="rtcl-price-amount amount">%1$s</span>%2$s');
            $payment_price_html = sprintf($payment_price_html_format, $formatted_payment_price, $payment_price_meta_html);
            return apply_filters('rtcl_get_payment_formatted_price', $payment_price_html, $price, $payment, $currency, $currency_symbol, $price_format);
        }

        global $post;
        $listing = is_object($post) && rtcl()->post_type == $post->post_type ? new Listing($post->ID) : '';
        $formatted_price = apply_filters('rtcl_formatted_price_html',
            sprintf($price_format, '<span class="rtcl-price-currencySymbol">' . $currency_symbol . '</span>', $price),
            $listing, $price_format, $currency_symbol, $price);
        ob_start();
        do_action('rtcl_price_meta_html', $listing, $price_format, $currency_symbol, $price);
        $price_meta_html = ob_get_clean();
        $price_meta_html = $price_meta_html ? apply_filters('rtcl_price_meta_wrap_html', sprintf('<span class="rtcl-price-meta">%s</span>', $price_meta_html), $price_meta_html) : null;
        $price_html_format = apply_filters('rtcl_price_amount_html_format', '<span class="rtcl-price-amount amount">%1$s</span>%2$s');
        $price_html = sprintf($price_html_format, $formatted_price, $price_meta_html);

        return apply_filters('rtcl_get_formatted_price', $price_html, $price, $payment, $currency, $currency_symbol, $price_format);
    }

    static function get_price_format($payment = false) {
        $currency_settings = Functions::get_option_item('rtcl_general_settings', 'currency_position');
        if ($payment) {
            $currency_settings = Functions::get_option_item('rtcl_payment_settings', 'currency_position');
        }
        $currency_pos = !empty($currency_settings) ? $currency_settings : 'left';
        $format = '%1$s%2$s';

        switch ($currency_pos) {
            case 'left' :
                $format = '%1$s%2$s';
                break;
            case 'right' :
                $format = '%2$s%1$s';
                break;
            case 'left_space' :
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space' :
                $format = '%2$s&nbsp;%1$s';
                break;
        }

        return apply_filters('rtcl_get_price_format', $format, $currency_pos, $payment);
    }

    static function get_formatted_amount($amount, $payment = false) {

        $thousands_sep = Functions::get_thousands_separator($payment);
        $decimal_sep = Functions::get_decimal_separator($payment);
        $decimals = self::currency_decimal_count();

        $un_formatted_price = $amount;
        $negative = $amount < 0;
        $amount = apply_filters('rtcl_raw_amount', floatval($negative ? $amount * -1 : $amount));
        $amount = apply_filters('rtcl_formatted_amount', number_format($amount, $decimals, $decimal_sep, $thousands_sep));

        if (apply_filters('rtcl_price_trim_zeros', true) && $decimals > 0) {
            $amount = self::trim_zeros($amount, $decimal_sep);
        }

        return apply_filters('rtcl_get_formatted_amount', $amount, $un_formatted_price, $decimals, $decimal_sep, $thousands_sep);

    }

    static function trim_zeros($amount, $decimal_sep) {
        return preg_replace('/' . preg_quote($decimal_sep, '/') . '0++$/', '', $amount);
    }

    static function currency_decimal_count() {
        return apply_filters('rtcl_currency_decimal_count', 2);
    }


    /**
     * Format decimal numbers ready for DB storage.
     * Sanitize, remove decimals, and optionally round + trim off zeros.
     * This function does not remove thousands - this should be done before passing a value to the function.
     *
     * @param float|string $number      Expects either a float or a string with a decimal separator only (no
     *                                  thousands).
     * @param mixed        $dp          number  Number of decimal points to use, blank to use rtcl_price_num_decimals,
     *                                  or false to avoid all rounding.
     * @param bool         $trim_zeros  From end of string.
     *
     * @return string
     */
    static function format_decimal($number, $dp = false, $trim_zeros = false) {
        $locale = localeconv();
        $decimals = self::get_decimal_separator_both() + [$locale['decimal_point'], $locale['mon_decimal_point']];

        // Remove locale from string.
        if (!is_float($number)) {
            $number = str_replace($decimals, '.', $number);
            $number = preg_replace('/[^0-9\.,-]/', '', self::clean($number));
        }

        if (false !== $dp) {
            $dp = intval('' === $dp ? self::currency_decimal_count() : $dp);
            $number = number_format(floatval($number), $dp, '.', '');
        } elseif (is_float($number)) {
            // DP is false - don't use number format, just return a string using whatever is given. Remove scientific notation using sprintf.
            $number = str_replace($decimals, '.', sprintf('%.' . self::get_rounding_precision() . 'f', $number));
            // We already had a float, so trailing zeros are not needed.
            $trim_zeros = true;
        }

        if ($trim_zeros && strstr($number, '.')) {
            $number = rtrim(rtrim($number, '0'), '.');
        }

        return apply_filters('rtcl_format_localized_decimal', $number);
    }

    function trim_string($string, $chars = 200, $suffix = '...') {
        if (strlen($string) > $chars) {
            if (function_exists('mb_substr')) {
                $string = mb_substr($string, 0, ($chars - mb_strlen($suffix))) . $suffix;
            } else {
                $string = substr($string, 0, ($chars - strlen($suffix))) . $suffix;
            }
        }

        return $string;
    }

    static function request($key, $default = null) {
        if (isset($_POST[$key])) {
            return stripslashes_deep($_POST[$key]);
        } elseif (isset($_GET[$key])) {
            return stripslashes_deep($_GET[$key]);
        } else {
            return $default;
        }
    }

    static function get_temp_listing_status() {
        return apply_filters("rtcl_get_temp_listing_status", "rtcl-temp");
    }

    static function delete_post($post_id, $skip_trash = true) {

        $skip_trash = apply_filters("rtcl_skip_trash_to_delete", $skip_trash, $post_id);

        if ($skip_trash) {
            $result = wp_delete_post($post_id);
        } else {
            $result = wp_trash_post($post_id);
        }

        return $result;
    }

    static function user_can_edit_image() {
        $cap = rtcl()->gallery['image_edit_cap'];

        if ((!empty($cap) && $cap === true) || is_admin()) {
            return true;
        }

        return false;
    }


    /**
     * Formats information about specific attachment
     *
     * @param int     $attach_id WP_Post ID
     * @param boolean $is_new
     *
     * @return array
     */
    static function upload_item_data($attach_id, $is_new = false) {
        try {
            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            // Generate the metadata for the attachment, and update the database record.
            $sizes = array();
            $image_keys = array("url", "width", "height", "is_intermidiate");


            $image_defaults = array(
                "full" => array(
                    "enabled" => 1,
                    "width"   => null,
                    "height"  => null,
                    "crop"    => false
                )
            );

            $image_sizes = array_merge($image_defaults, rtcl()->gallery['image_sizes']);

            foreach ($image_sizes as $image_key => $image_size) {
                if ($image_key !== "full" && !has_image_size($image_key)) {
                    continue;
                }

                $src = wp_get_attachment_image_src($attach_id, $image_key);

                if ($image_key !== "full" && isset($src[3]) && $src[3] === false) {
                    $src[1] = $sizes["full"]["width"];
                    $src[2] = $sizes["full"]["height"];
                }

                if ($src === false) {
                    $src = array(
                        "url"    => null,
                        "width"  => $image_size["width"],
                        "height" => $image_size["height"],
                        "crop"   => $image_size["crop"]
                    );
                } else {
                    $src = array_combine($image_keys, $src);
                }

                $sizes[str_replace("-", "_", $image_key)] = $src;
            }

            $featured = 0;
            $caption = "";
            $content = "";

            if (!$is_new) {
                $post = get_post($attach_id);
                $parent_id = wp_get_post_parent_id($post->ID);
                $caption = $post->post_excerpt;
                $content = $post->post_content;

                $featured = intval(get_post_meta($parent_id, '_thumbnail_id', true));
                if ($featured == $post->ID) {
                    $featured = 1;
                } else {
                    $featured = 0;
                }
            }

            $data = array(
                "post_id"   => $post->post_parent,
                "attach_id" => $attach_id,
                "guid"      => $post->guid,
                "mime_type" => $post->post_mime_type,
                "featured"  => $featured,
                "caption"   => $caption,
                "content"   => $content,
                "sizes"     => $sizes,
                "readable"  => array(
                    "name"     => basename($post->guid),
                    "type"     => $post->post_mime_type,
                    "uploaded" => date_i18n(get_option("date_format"), strtotime($post->post_date_gmt)),
                    "size"     => size_format(filesize(get_attached_file($attach_id))),
                    "length"   => null
                )
            );

            $meta = wp_get_attachment_metadata($attach_id);

            if (isset($meta["width"]) && isset($meta["height"])) {
                $data["readable"]["dimensions"] = sprintf("%d x %d", $meta["width"], $meta["height"]);
                $data["dimensions"] = $meta;
            }
            if (isset($meta["length_formatted"])) {
                $data["readable"]["length"] = $meta["length_formatted"];
            }

            return $data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    static function get_all_cf_fields_by_cfg_id($post_id) {
        $fields = get_posts(array(
            'post_type'        => 'rtcl_cf',
            'posts_per_page'   => -1,
            'post_parent'      => $post_id,
            'post_status'      => 'any',
            'orderby'          => 'menu_order',
            'order'            => 'asc',
            'suppress_filters' => false
        ));

        return $fields;
    }

    static function get_one_level_locations($loc_id = 0) {
        $terms = array();
        $args = array(
            'taxonomy'   => rtcl()->location,
            'orderby'    => 'title',
            'order'      => 'ASC',
            'hide_empty' => false,
            'parent'     => $loc_id
        );
        $settings = self::get_option('rtcl_general_settings');
        if (!empty($settings['taxonomy_orderby']) && $settings['taxonomy_orderby'] == '_rtcl_order') {
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_rtcl_order';
        }
        if (!empty($settings['taxonomy_order']) && strtolower($settings['taxonomy_order']) == 'desc') {
            $args['order'] = 'DESC';
        }
        $termObjs = get_terms($args);
        if (!empty($termObjs) && !is_wp_error($termObjs)) {
            $terms = $termObjs;
        }

        return $terms;
    }

    static function get_one_level_categories($cat_id = 0, $type = null, $only_ids = false) {
        $settings = self::get_option('rtcl_general_settings');
        $terms = $term_ids = array();
        $args = array(
            'taxonomy'   => rtcl()->category,
            'orderby'    => 'title',
            'order'      => 'ASC',
            'hide_empty' => false,
            'parent'     => $cat_id
        );
        $settings = self::get_option('rtcl_general_settings');
        if (!empty($settings['taxonomy_orderby']) && $settings['taxonomy_orderby'] == '_rtcl_order') {
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_rtcl_order';
        }
        if (!empty($settings['taxonomy_order']) && strtolower($settings['taxonomy_order']) == 'desc') {
            $args['order'] = 'DESC';
        }
        if ($type) {
            $args['meta_query'] = array(
                array(
                    'key'   => '_rtcl_types',
                    'value' => $type,
                )
            );
        }
        $termObjs = get_terms($args);
        if (!empty($termObjs) && !is_wp_error($termObjs)) {
            if ($only_ids) {
                foreach ($termObjs as $term) {
                    $term_ids[] = $term->term_id;
                }

                return $term_ids;
            } else {
                return $termObjs;
            }
        }

        return $terms;
    }

    static function get_all_category($hierarchical = false) {
        $terms = array();
        $args = array(
            'taxonomy'   => rtcl()->category,
            'orderby'    => 'meta_value_num',
            'meta_key'   => '_rtcl_order',
            'order'      => 'ASC',
            'hide_empty' => false,
        );

        if ($hierarchical) {
            $args['parent'] = 0;
            $temp_terms = get_terms($args);
            if (!empty($temp_terms) && !is_wp_error($temp_terms)) {
                foreach ($temp_terms as $term) {
                    $termObj = new \stdClass();
                    $termObj->id = $term->term_id;
                    $termObj->name = $term->name;
                    $termObj->count = $term->count;
                    $args['parent'] = absint($term->term_id);
                    $child_terms = get_terms($args);
                    if (!empty($child_terms) && !is_wp_error($child_terms)) {
                        $childTerms = array();
                        foreach ($child_terms as $child_term) {
                            $childTermObj = new \stdClass();
                            $childTermObj->id =
                            $childTermObj->name = $child_term->name;
                            $childTermObj->count = $child_term->count;
                            $childTerms[] = $childTermObj;
                        }
                        $termObj->child = $childTerms;
                    }
                    $terms[] = $termObj;
                }
            }
        } else {
            $termObjs = get_terms($args);
            if (!empty($termObjs) && !is_wp_error($termObjs)) {
                foreach ($termObjs as $term) {
                    $termObj = new \stdClass();
                    $termObj->id = $term->term_id;
                    $termObj->name = $term->name;
                    $termObj->count = $term->count;
                    $terms[] = $termObj;
                }
            }
        }


        return $terms;
    }

    /**
     * @param     $capability
     * @param int $post_id
     *
     * @return bool
     */
    static function current_user_can($capability, $post_id = 0) {
        $user_id = get_current_user_id();

        // If editing, deleting, or reading a listing, get the post and post type object.
        if ('add_' . rtcl()->post_type == $capability || 'edit_' . rtcl()->post_type == $capability || 'delete_' . rtcl()->post_type == $capability || 'read_' . rtcl()->post_type == $capability) {
            $listing = get_post($post_id);
            if (is_object($listing) && rtcl()->post_type === $listing->post_type) {
                // If editing a listing, assign the required capability.
                if ('edit_' . rtcl()->post_type == $capability) {
                    if ($user_id == $listing->post_author) {
                        $capability = 'edit_' . rtcl()->post_type . 's';
                    } else {
                        $capability = 'edit_others_' . rtcl()->post_type . 's';
                    }
                } // If deleting a listing, assign the required capability.
                else if ('delete_' . rtcl()->post_type == $capability) {
                    if ($user_id == $listing->post_author) {
                        $capability = 'delete_' . rtcl()->post_type . 's';
                    } else {
                        $capability = 'delete_others_' . rtcl()->post_type . 's';
                    }
                } // If reading a private listing, assign the required capability.
                else if ('read_' . rtcl()->post_type == $capability) {
                    if ('private' != $listing->post_status) {
                        $capability = 'read';
                    } else if ($user_id == $listing->post_author) {
                        $capability = 'read';
                    } else {
                        $capability = 'read_private_' . rtcl()->post_type . 's';
                    }
                }
            } else {
                if ('add_' . rtcl()->post_type !== $capability) {
                    $capability = $capability . "s";
                }
            }
        }

        return apply_filters('rtcl_current_user_can', current_user_can($capability), $capability);

    }

    static function dropdown_terms($args = array(), $echo = true) {

        // Vars
        $args = array_merge(array(
            'show_option_none'  => '-- ' . __('Select a category', 'classified-listing') . ' --',
            'option_none_value' => '',
            'taxonomy'          => rtcl()->category,
            'name'              => 'rtcl_category',
            'class'             => 'form-control',
            'required'          => false,
            'base_term'         => 0,
            'parent'            => 0,
            'orderby'           => 'name',
            'order'             => 'ASC',
            'value_field'       => 'id',
            'selected'          => 0
        ), $args);

        if (!empty($args['selected'])) {
            $ancestors = get_ancestors($args['selected'], $args['taxonomy']);
            $ancestors = array_merge(array_reverse($ancestors), array($args['selected']));
        } else {
            $ancestors = array();
        }

        // Build data
        $html = '';

        if (isset($args['walker'])) {

            $selected = count($ancestors) >= 2 ? (int)$ancestors[1] : 0;

            $html .= '<div class="rtcl-terms">';
            $html .= sprintf('<input type="hidden" name="%s" class="rtcl-term-hidden" value="%d" />', $args['name'],
                $selected);

            $term_args = array(
                'show_option_none'  => $args['show_option_none'],
                'option_none_value' => $args['option_none_value'],
                'taxonomy'          => $args['taxonomy'],
                'child_of'          => $args['parent'],
                'orderby'           => $args['orderby'],
                'order'             => $args['order'],
                'selected'          => $selected,
                'hierarchical'      => true,
                'depth'             => 2,
                'show_count'        => false,
                'hide_empty'        => false,
                'walker'            => $args['walker'],
                'echo'              => 0
            );

            unset($args['walker']);

            $select = wp_dropdown_categories($term_args);
            $required = $args['required'] ? ' required' : '';
            $replace = sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d"%s>', $args['class'],
                $args['taxonomy'], $args['parent'], $required);

            $html .= preg_replace('#<select([^>]*)>#', $replace, $select);

            if ($selected > 0) {
                $args['parent'] = $selected;
                $html .= self::dropdown_terms($args, false);
            }

            $html .= '</div>';

        } else {

            $has_children = 0;
            $child_of = 0;

            $term_args = array(
                'parent'       => $args['parent'],
                'orderby'      => 'meta_value_num',
                'meta_key'     => '_rtcl_order',
                'order'        => 'ASC',
                'hide_empty'   => false,
                'hierarchical' => false
            );
            $terms = get_terms($args['taxonomy'], $term_args);

            if (!empty($terms) && !is_wp_error($terms)) {

                if ($args['parent'] == $args['base_term']) {
                    $required = $args['required'] ? ' required' : '';
                    $sSlug = "";
                    if ($args['selected']) {
                        $sTerm = get_term_by('id', $args['selected'], $args['taxonomy']);
                        $sSlug = $sTerm->slug;
                    }
                    $html .= '<div class="rtcl-terms">';
                    $html .= sprintf('<input type="hidden" class="rtcl-term-hidden rtcl-term-%s" data-slug="%s" value="%d" />',
                        $args['taxonomy'], $sSlug, $args['selected']);
                    $html .= sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d"%s>', $args['class'],
                        $args['taxonomy'], $args['parent'], $required);
                    $html .= sprintf('<option value="%s">%s</option>', $args['option_none_value'],
                        $args['show_option_none']);
                } else {
                    $html .= sprintf('<div class="rtcl-child-terms rtcl-child-terms-%d">', $args['parent']);
                    $html .= sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d">', $args['class'],
                        $args['taxonomy'], $args['parent']);
                    $html .= sprintf('<option value="%d">%s</option>', $args['parent'], '---');
                }

                foreach ($terms as $term) {
                    $selected = '';
                    if (in_array($term->term_id, $ancestors)) {
                        $has_children = 1;
                        $child_of = $term->term_id;
                        $selected = ' selected';
                    } else if ($term->term_id == $args['selected']) {
                        $selected = ' selected';
                    }
                    $html .= sprintf('<option data-slug="%s" value="%s"%s>%s</option>',
                        $term->slug,
                        ($args['value_field'] == "slug") ? $term->slug : $term->term_id,
                        $selected, $term->name);
                }

                $html .= '</select>';
                if ($has_children) {
                    $args['parent'] = $child_of;
                    $html .= self::dropdown_terms($args, false);
                }
                $html .= '</div>';

            } else {

                if ($args['parent'] == $args['base_term']) {
                    $required = $args['required'] ? ' required' : '';

                    $html .= '<div class="rtcl-terms">';
                    $html .= sprintf('<input type="hidden" name="%s" class="rtcl-term-hidden" value="%d" />',
                        $args['name'], $args['selected']);
                    $html .= sprintf('<select class="%s" data-taxonomy="%s" data-parent="%d"%s>', $args['class'],
                        $args['taxonomy'], $args['parent'], $required);
                    $html .= sprintf('<option value="%s">%s</option>', $args['option_none_value'],
                        $args['show_option_none']);
                    $html .= '</select>';
                    $html .= '</div>';
                }

            }

        }

        // Echo or Return
        if ($echo) {
            echo $html;

            return '';
        } else {
            return $html;
        }

    }

    static function listing_expiry_date($post_id, $start_date = null) {

        // Get number of days to add
        $general_settings = Functions::get_option('rtcl_moderation_settings');
        $days = apply_filters('rtcl_get_listing_duration', absint($general_settings['listing_duration']),
            $post_id);

        if ($days <= 0) {
            update_post_meta($post_id, 'never_expires', 1);
            $days = 999;
        } else {
            delete_post_meta($post_id, 'never_expires');
        }

        if ($start_date == null) {
            // Current time
            $start_date = current_time('mysql');
        }

        // Calculate new date
        $date = new \DateTime($start_date);
        $date->add(new \DateInterval("P{$days}D"));

        // return
        return $date->format('Y-m-d H:i:s');

    }

    static function dummy_expiry_date($days = null, $start_date = null) {
        $general_settings = Functions::get_option('rtcl_moderation_settings');
        if (!$days) {
            $days = absint($general_settings['listing_duration']);
            $days = $days <= 0 ? 999 : $days;
        }
        if ($start_date == null) {
            $start_date = current_time('mysql');
        }
        $date = new \DateTime($start_date);
        $date->add(new \DateInterval("P{$days}D"));

        return $date->format('Y-m-d H:i:s');
    }

    static function get_decimal_separator($payment = false) {

        if ($payment) {
            $currency_settings = Functions::get_option('rtcl_payment_settings');
        } else {
            $currency_settings = Functions::get_option('rtcl_general_settings');
        }

        return !empty($currency_settings['currency_decimal_separator']) ? stripslashes($currency_settings['currency_decimal_separator']) : '.';
    }


    /**
     * @return array
     */
    static function get_decimal_separator_both() {
        $payment_currency_settings = Functions::get_option('rtcl_payment_settings');
        $currency_settings = Functions::get_option('rtcl_general_settings');

        return [
            isset($payment_currency_settings['currency_decimal_separator']) ? stripslashes($payment_currency_settings['currency_decimal_separator']) : '.',
            isset($currency_settings['currency_decimal_separator']) ? stripslashes($currency_settings['currency_decimal_separator']) : '.'
        ];
    }

    public static function get_currency($payment = false) {
        if ($payment) {
            $currency_settings = Functions::get_option_item('rtcl_payment_settings', 'currency');
        } else {
            $currency_settings = Functions::get_option_item('rtcl_general_settings', 'currency');
        }
        $currency = !empty($currency_settings) ? $currency_settings : 'USD';

        return apply_filters('rtcl_get_currency', $currency, $payment);
    }

    public static function get_currency_symbol($currency = '', $payment = false) {

        if (!$currency) {
            $currency = self::get_currency($payment);
        }
        $symbols = Options::get_currency_symbols();
        $currency_symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';

        return apply_filters('rtcl_get_currency_symbol', $currency_symbol, $currency, $payment);
    }

    static function get_thousands_separator($payment = false) {
        if ($payment) {
            $currency_settings = Functions::get_option('rtcl_currency_settings');
        } else {
            $currency_settings = Functions::get_option('rtcl_general_settings');
        }

        return !empty($currency_settings['currency_thousands_separator']) ? stripslashes($currency_settings['currency_thousands_separator']) : ',';
    }

    static function sanitize_title_with_underscores($title) {
        return rawurldecode(str_replace('-', '_', sanitize_title_with_dashes($title)));
    }

    static function get_custom_group_ids($category = 0) {

        $group_ids = array();

        // Get category fields
        if ($category > 0) {

            // Get global fields
            $args = array(
                'post_type'        => rtcl()->post_type_cfg,
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'suppress_filters' => false,
                'meta_query'       => array(
                    array(
                        'key'   => 'associate',
                        'value' => 'all'
                    ),
                )
            );

            $group_ids = get_posts($args);

            $args = array(
                'post_type'        => rtcl()->post_type_cfg,
                'post_status'      => 'publish',
                'posts_per_page'   => -1,
                'fields'           => 'ids',
                'suppress_filters' => false,
                'tax_query'        => array(
                    array(
                        'taxonomy'         => rtcl()->category,
                        'field'            => 'term_id',
                        'terms'            => $category,
                        'include_children' => false,
                    ),
                ),
                'meta_query'       => array(
                    array(
                        'key'   => 'associate',
                        'value' => 'categories'
                    ),
                )
            );

            $category_groups = get_posts($args);

            $group_ids = array_merge($group_ids, $category_groups);
            $group_ids = array_unique($group_ids);

        }

        return $group_ids;
    }

    static function get_custom_field_ids($category = 0) {
        $group_ids = self::get_custom_group_ids($category);
        $field_ids = array();
        if (!empty($group_ids)) {
            foreach ($group_ids as $group_id) {
                $args = array(
                    'post_type'        => rtcl()->post_type_cf,
                    'post_status'      => 'publish',
                    'posts_per_page'   => -1,
                    'fields'           => 'ids',
                    'post_parent'      => $group_id,
                    'orderby'          => 'menu_order',
                    'order'            => 'ASC',
                    'suppress_filters' => false
                );

                $temp_ids = get_posts($args);
                $field_ids = array_merge($field_ids, $temp_ids);
            }
        }
        if (!empty($field_ids)) {
            $field_ids = array_unique($field_ids);
        }

        return $field_ids;
    }

    static function get_custom_field_html($field_id, $post_id = null) {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $html = '';
        $field = new RtclCFGField($field_id);
        if ($field_id && $field) {
            $id = "rtcl_{$field->getType()}_{$field->getFieldId()}";
            $required_label = $required_attr = '';
            $field_attr = " data-type='{$field->getType()}' data-id='_field_{$field->getFieldId()}'";
            if (1 == $field->getRequired()) {
                $required_label = '<span class="require-star">*</span>';
                $required_attr = ' required';
            }
            $field_html = null;
            $value = $field->getValue($post_id);
            switch ($field->getType()) {
                case 'text':
                    $field_html = sprintf('<input type="text" class="rtcl-text form-control" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" value="%s"%s />',
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        esc_attr($value),
                        $required_attr
                    );
                    break;
                case 'textarea' :
                    $field_html = sprintf('<textarea class="rtcl-textarea form-control" id="%s" name="rtcl_fields[_field_%d]" rows="%d" placeholder="%s"%s>%s</textarea>',
                        $id,
                        absint($field->getFieldId()),
                        absint($field->getRows()),
                        esc_attr($field->getPlaceholder()),
                        $required_attr,
                        esc_textarea($value)
                    );
                    break;
                case 'select' :
                    $options = $field->getOptions();
                    $choices = !empty($options['choices']) && is_array($options['choices']) ? $options['choices'] : array();
                    $options_html = null;
                    if (true) {
                        $options_html .= sprintf('<option value="">%s</option>',
                            '- ' . __('Select an Option', 'classified-listing') . ' -');
                    }
                    if (!empty($choices)) {
                        foreach ($choices as $key => $choice) {
                            $_selected = '';
                            if (trim($key) == $value) {
                                $_selected = ' selected="selected"';
                            }

                            $options_html .= sprintf('<option value="%s"%s>%s</option>', $key, $_selected, $choice);
                        }
                    }
                    $field_html = sprintf('<select name="rtcl_fields[_field_%d]" id="%s" class="rtcl-select2 form-control"%s>%s</select>',
                        absint($field->getFieldId()),
                        $id,
                        $required_attr,
                        $options_html
                    );
                    break;
                case 'checkbox' :
                    $options = $field->getOptions();
                    $value = !empty($value) && is_array($value) ? $value : array();
                    $choices = !empty($options['choices']) && is_array($options['choices']) ? $options['choices'] : array();
                    $check_options = null;
                    if (!empty($choices)) {
                        $i = 0;
                        foreach ($choices as $key => $choice) {
                            $_attr = '';
                            if (in_array($key, $value)) {
                                $_attr .= ' checked="checked"';
                            }
                            $_attr .= " data-foo='yes' " . $required_attr;

                            $check_options .= sprintf('<div class="form-check"><input class="form-check-input" id="%s" type="checkbox" name="rtcl_fields[_field_%d][]" value="%s"%s><label class="form-check-label" for="%s">%s</label></div>',
                                $id . $key,
                                absint($field->getFieldId()),
                                $key,
                                $_attr,
                                $id . $key,
                                $choice
                            );
                        }
                    }
                    $field_html = sprintf('<div class="rtcl-check-list">%s</div>', $check_options);
                    break;
                case 'radio' :
                    $options = $field->getOptions();
                    $choices = !empty($options['choices']) && is_array($options['choices']) ? $options['choices'] : array();
                    $check_options = null;
                    if (!empty($choices)) {
                        foreach ($choices as $key => $choice) {
                            $_attr = '';
                            if (trim($key) == $value) {
                                $_attr .= ' checked="checked"';
                            }
                            $_attr .= $required_attr;

                            $check_options .= sprintf('<div class="form-check"><input class="form-check-input" id="%s" type="radio" name="rtcl_fields[_field_%d]" value="%s"%s><label class="form-check-label" for="%s">%s</label></div>',
                                $id . $key,
                                absint($field->getFieldId()),
                                $key,
                                $_attr,
                                $id . $key,
                                $choice
                            );
                        }
                    }
                    $field_html = sprintf('<div class="rtcl-check-list">%s</div>', $check_options);
                    break;
                case 'number':
                    $field_html = sprintf('<input type="number" class="rtcl-number form-control" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" value="%s" step="%s" min="%s" max="%s"%s />',
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        esc_attr($value),
                        $field->getStepSize() ? esc_attr($field->getStepSize()) : 'any',
                        !empty($field->getMin()) || $field->getMin() == 0 ? absint($field->getMin()) : '',
                        !empty($field->getMax()) ? absint($field->getMax()) : '',
                        $required_attr
                    );
                    break;
                case 'url':
                    $field_html = sprintf('<input type="url" class="rtcl-text form-control" id="%s" name="rtcl_fields[_field_%d]" placeholder="%s" value="%s"%s />',
                        $id,
                        absint($field->getFieldId()),
                        esc_attr($field->getPlaceholder()),
                        esc_url($value),
                        $required_attr
                    );
                    break;
            }

            if (isset($_REQUEST['is_admin']) && $_REQUEST['is_admin'] == 1) {
                $description = $field->getDescription();

                $html .= sprintf('<div class="form-group row"%s>
										    <label for="%s" class="col-2 col-form-label">%s %s</label>
										    <div class="col-10">
										        %s
										        <div class="help-block with-errors"></div>
										        %s
										    </div>
										</div>',
                    $field_attr,
                    $id,
                    $field->getLabel(),
                    $required_label,
                    $field_html,
                    $description ? '<small class="help-block">' . esc_html($description) . '</small>' : null
                );
            } else {
                $html .= self::get_template_html('listing-form/custom-field', array(
                    'field_attr'     => $field_attr,
                    'id'             => $id,
                    'label'          => $field->getLabel(),
                    'required_label' => $required_label,
                    'description'    => $field->getDescription(),
                    'field'          => $field_html
                ));
            }
        }

        return $html;
    }

    static function get_custom_fields_html($term_id = 0, $post_id = null) {
        $field_ids = self::get_custom_field_ids($term_id);
        $html = '';
        if (!empty($field_ids)) {
            foreach ($field_ids as $field_id) {
                $html .= self::get_custom_field_html($field_id, $post_id);
            }
        }

        return $html;
    }

    static function sort_images($images, $post_id) {
        $images_order = json_decode(get_post_meta($post_id, '_rtcl_attachments_order', true));
        if (!is_null($images_order)) {
            $post_thumbnail_id = get_post_thumbnail_id($post_id);
            if ($post_thumbnail_id) {
                array_unshift($images_order, $post_thumbnail_id);
                $images_order = array_unique($images_order);
            }
            uksort($images, array(new SortImages($images_order), "sort"));
        }

        return $images;
    }

    static function get_listing_image_ids($post_id) {
        $ids = array();
        if ($post_id) {
            $children = get_children(array(
                'post_parent'    => $post_id,
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
                'post_status'    => 'inherit'
            ));
            if (!empty($children)) {
                $ids = Functions::sort_images($children, $post_id);
            }
        }

        return $ids;
    }


    public static function listing_feature_thumbnail($post_id) {

        $img_url = '';

        if (has_post_thumbnail($post_id)) {
            $img_url = get_the_post_thumbnail_url($post_id, 'rtcl-thumbnail');
        } else {
            $images = self::get_listing_image_ids($post_id);
            if (!empty($images)) {
                $images = array_slice($images, 0, 1);
                $img_url = wp_get_attachment_image_src($images[0]->ID, 'rtcl-thumbnail');
                $img_url = $img_url[0];
            }

        }

        return $img_url ? sprintf("<img class='rtcl-thumbnail' src='%s' />", $img_url) : null;

    }

    public static function get_pages() {
        $page_list = array();
        $pages = get_pages(
            array(
                'sort_column'  => 'menu_order',
                'sort_order'   => 'ASC',
                'hierarchical' => 0,
            )
        );
        foreach ($pages as $page) {
            $page_list[$page->ID] = !empty($page->post_title) ? $page->post_title : '#' . $page->ID;
        }

        return $page_list;
    }

    static function update_listing_views_count($post_id) {

        $user_ip = $_SERVER['REMOTE_ADDR']; // retrieve the current IP address of the visitor
        $key = $user_ip . '_rtcl_' . $post_id; // combine post ID & IP to form unique key
        $value = array($user_ip, $post_id); // store post ID & IP as separate values (see note)
        $visited = get_transient($key); // get transient and store in variable

        // check to see if the Post ID/IP ($key) address is currently stored as a transient
        if (false === ($visited)) {

            // store the unique key, Post ID & IP address for 12 hours if it does not exist
            set_transient($key, $value, 60 * 60 * 12);

            // now run post views function
            $count_key = '_views';
            $count = get_post_meta($post_id, $count_key, true);
            if ('' == $count) {
                update_post_meta($post_id, $count_key, 0);
            } else {
                $count = absint($count);
                $count++;
                update_post_meta($post_id, $count_key, $count);
            }

        }

    }

    static function allowed_html_for_term_and_conditions() {

        $tags = array(
            'a'      => array(
                'href'  => array(),
                'title' => array()
            ),
            'br'     => array(),
            'em'     => array(),
            'strong' => array(),
        );

        return apply_filters('rtcl_allowed_html_for_term_and_conditions', $tags);
    }

    static function of_kses_data($data, $allowed_Tags = array()) {
        return wp_kses($data, $allowed_Tags);
    }


    /**
     * @param $id
     *
     * @return bool|mixed|void
     */
    static function get_option($id) {
        if (!$id) {
            return false;
        }
        $settings = get_option($id, array());

        return apply_filters($id, $settings);
    }

    static function get_option_item($id, $item, $default = null, $type = null) {
        if (!$item) {
            return false;
        }
        $settings = self::get_option($id);

        if ($type == 'checkbox') {
            return (isset($settings[$item]) && $settings[$item] == 'yes') ? true : false;
        } elseif ($type == 'multi_checkbox') {
            return (isset($settings[$item]) && is_array($settings[$item]) && in_array($default, $settings[$item])) ? true : false;
        } elseif ($type == 'number') {
            return isset($settings[$item]) ? absint($settings[$item]) : absint($default);
        }

        return isset($settings[$item]) && !empty($settings[$item]) ? $settings[$item] : $default;
    }

    public static function get_listing_types() {

        $default_types = Options::get_default_listing_types();
        $types = Functions::get_option(rtcl()->get_listing_types_option_id());
        $types = !empty($types) ? $types : $default_types;
        $types = apply_filters_deprecated('rtcl_ad_type', array($types), '1.2.17', 'rtcl_get_listing_types');

        return apply_filters('rtcl_get_listing_types', !empty($types) ? $types : $default_types);
    }


    /**
     * Convert a date string to a WC_DateTime.
     *
     * @param string $time_string Time string.
     *
     * @return RtclDateTime
     * @throws \Exception
     * @since  3.1.0
     */
    static function string_to_datetime($time_string) {
        // Strings are defined in local WP timezone. Convert to UTC.
        if (1 === preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $time_string, $date_bits)) {
            $offset = !empty($date_bits[7]) ? iso8601_timezone_to_offset($date_bits[7]) : self::timezone_offset();
            $timestamp = gmmktime($date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1]) - $offset;
        } else {
            $timestamp = self::string_to_timestamp(get_gmt_from_date(gmdate('Y-m-d H:i:s', self::string_to_timestamp($time_string))));
        }
        $datetime = new RtclDateTime("@{$timestamp}", new DateTimeZone('UTC'));

        // Set local timezone or offset.
        if (get_option('timezone_string')) {
            $datetime->setTimezone(new DateTimeZone(self::timezone_string()));
        } else {
            $datetime->set_utc_offset(self::timezone_offset());
        }

        return $datetime;
    }

    /**
     * Convert mysql datetime to PHP timestamp, forcing UTC. Wrapper for strtotime.
     *
     * @param string   $time_string    Time string.
     * @param int|null $from_timestamp Timestamp to convert from.
     *
     * @return int
     * @since  1.0.0
     */
    static function string_to_timestamp($time_string, $from_timestamp = null) {
        $original_timezone = date_default_timezone_get();

        // @codingStandardsIgnoreStart
        date_default_timezone_set('UTC');

        if (null === $from_timestamp) {
            $next_timestamp = strtotime($time_string);
        } else {
            $next_timestamp = strtotime($time_string, $from_timestamp);
        }

        date_default_timezone_set($original_timezone);

        // @codingStandardsIgnoreEnd

        return $next_timestamp;
    }


    /**
     * Get timezone offset in seconds.
     *
     * @return float
     * @throws \Exception
     * @since  3.0.0
     */
    static function timezone_offset() {
        $timezone = get_option('timezone_string');

        if ($timezone) {
            $timezone_object = new DateTimeZone($timezone);

            return $timezone_object->getOffset(new DateTime('now'));
        } else {
            return floatval(get_option('gmt_offset', 0)) * HOUR_IN_SECONDS;
        }
    }

    /**
     * Timezone - helper to retrieve the timezone string for a site until.
     *
     * @return string PHP timezone string for the site
     * @since 1.0.0
     */
    static function timezone_string() {
        // If site timezone string exists, return it.
        $timezone = get_option('timezone_string');
        if ($timezone) {
            return $timezone;
        }

        // Get UTC offset, if it isn't set then return UTC.
        $utc_offset = intval(get_option('gmt_offset', 0));
        if (0 === $utc_offset) {
            return 'UTC';
        }

        // Adjust UTC offset from hours to seconds.
        $utc_offset *= 3600;

        // Attempt to guess the timezone string from the UTC offset.
        $timezone = timezone_name_from_abbr('', $utc_offset);
        if ($timezone) {
            return $timezone;
        }

        // Last try, guess timezone string manually.
        foreach (timezone_abbreviations_list() as $abbr) {
            foreach ($abbr as $city) {
                if ((bool)date('I') === (bool)$city['dst'] && $city['timezone_id'] && intval($city['offset']) === $utc_offset) {
                    return $city['timezone_id'];
                }
            }
        }

        // Fallback to UTC.
        return 'UTC';
    }

    /**
     * Date Format.
     *
     * @return string
     */
    static function date_format() {
        return apply_filters('rtcl_date_format', get_option('date_format'));
    }

    /**
     * Time Format.
     *
     * @return string
     */
    static function time_format() {
        return apply_filters('rtcl_time_format', get_option('time_format'));
    }

    static function datetime($format = 'mysql', $date = null, $gmt = false) {
        if (is_null($date) || strlen($date) === 0) {
            $timestamp = current_time('timestamp', $gmt);
        } else if (is_string($date)) {
            $timestamp = strtotime($date);
        } else {
            $timestamp = $date;
        }

        switch ($format) {
            case 'mysql':
                return date('Y-m-d H:i:s', $timestamp);
            case 'timestamp':
                return $timestamp;
            case 'time-elapsed':
                return sprintf(__('%s ago', 'classified-listing'), human_time_diff(strtotime($date), current_time('timestamp', $gmt)));
            case 'rtcl':
                return date_i18n(get_option('date_format'),
                        $timestamp) . ' @ ' . date_i18n(get_option('time_format'), $timestamp);
            case 'rtcl-date':
                return date_i18n(get_option('date_format'), $timestamp);
            case 'rtcl-time':
                return date_i18n(get_option('time_format'), $timestamp);
            default:
                return date_i18n($format, $timestamp);
        }
    }

    static function set_datetime_date($datetime, $date) {
        $base_timestamp = strtotime($datetime);
        $base_year_month_day_timestamp = strtotime(date('Y-m-d', strtotime($datetime)));
        $time_of_the_day_in_seconds = $base_timestamp - $base_year_month_day_timestamp;

        $target_year_month_day_timestamp = strtotime(date('Y-m-d', strtotime($date)));

        $new_datetime_timestamp = $target_year_month_day_timestamp + $time_of_the_day_in_seconds;

        return self::datetime('mysql', $new_datetime_timestamp);
    }

    static function extend_date_to_end_of_the_day($datetime) {
        $next_day = strtotime('+ 1 days', $datetime);
        $zero_hours_next_day = strtotime(date('Y-m-d', $next_day));
        $end_of_the_day = $zero_hours_next_day - 1;

        return $end_of_the_day;
    }

    static function is_mysql_date($date) {
        $regexp = '/^\d{4}-\d{1,2}-\d{1,2}(\s\d{1,2}:\d{1,2}(:\d{1,2})?)?$/';

        return preg_match($regexp, $date) === 1;
    }

    static function get_page_ids() {
        $pages = AddConfig::get_custom_page_list();
        $newPages = array();
        foreach ($pages as $pKey => $p) {
            if ($id = self::get_option_item('rtcl_advanced_settings', $pKey)) {
                $newPages[$pKey] = $id;
            }
        }

        return $newPages;
    }

    static function insert_custom_pages() {

        // Vars
        $page_settings = self::get_page_ids();
        $page_definitions = AddConfig::get_custom_page_list();
        // ...
        $pages = array();
        foreach ($page_definitions as $slug => $page) {
            $id = 0;
            if (array_key_exists($slug, $page_settings)) {
                $id = (int)$page_settings[$slug];
            }
            if (!$id) {
                $id = wp_insert_post(
                    array(
                        'post_title'     => $page['title'],
                        'post_content'   => $page['content'],
                        'post_status'    => 'publish',
                        'post_author'    => 1,
                        'post_type'      => 'page',
                        'comment_status' => 'closed'
                    )
                );
            }
            $pages[$slug] = $id;
        }

        return $pages;

    }

    static function sanitize($value, $type = null) {
        $sanitize_value = null;
        switch ($type) {
            case 'title':
                $sanitize_value = sanitize_text_field($value);
                break;
            case 'content':
                $sanitize_value = wp_kses_post($value);

                break;
            case 'textarea' :
                $sanitize_value = esc_textarea($value);
                break;
            case 'checkbox' :
                $sanitize_value = array_map('esc_attr', is_array($value) ? $value : array());
                break;
            case 'url' :
                $sanitize_value = esc_url_raw($value);
                break;
            default:
                $sanitize_value = sanitize_text_field($value);
                break;
        }

        return apply_filters('rtcl_sanitize', $sanitize_value, $value, $type);
    }

    /**
     * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
     * Non-scalar values are ignored.
     *
     * @param string|array $var Data to sanitize.
     *
     * @return string|array
     */
    static function clean($var) {
        if (is_array($var)) {
            return array_map(array(self::class, 'clean'), $var);
        } else {
            return is_scalar($var) ? sanitize_text_field($var) : $var;
        }
    }

    static function is_registration_enabled() {

        $enable = Functions::get_option_item('rtcl_account_settings', 'enable_myaccount_registration', false, 'checkbox');

        if ($enable && get_option('users_can_register')) {
            return true;
        }

        return false;

    }

    /**
     * @param array $args
     */
    static function login_form($args = array()) {

        $defaults = array(
            'message'  => '',
            'redirect' => '',
            'hidden'   => false,
        );

        $args = wp_parse_args($args, $defaults);

        Functions::get_template('global/form-login', $args);
    }


    public static function get_account_menu_items() {
        $endpoints = self::get_my_account_page_endpoints();

        $items = array();
        $menu_items = array(
            'dashboard'    => __('Dashboard', 'classified-listing'),
            'listings'     => __('My Listings', 'classified-listing'),
            'favourites'   => __('Favourites', 'classified-listing'),
            'payments'     => __('Payments', 'classified-listing'),
            'edit-account' => __('Account details', 'classified-listing'),
            'logout'       => __('Logout', 'classified-listing'),
        );

        // Remove missing endpoints.
        foreach ($endpoints as $endpoint_id => $endpoint) {
            if (empty($endpoint)) {
                unset($menu_items[$endpoint_id]);
            }
        }

        // Remove unused endpoints.
        foreach ($menu_items as $item_id => $item) {
            if ($item_id == "dashboard" || in_array($item_id, array_keys($endpoints))) {
                $items[$item_id] = $item;
            }
        }

        return apply_filters('rtcl_account_menu_items', $items);
    }

    public static function get_account_menu_item_classes($endpoint) {
        global $wp;

        $classes = array(
            'rtcl-MyAccount-navigation-link',
            'rtcl-MyAccount-navigation-link--' . $endpoint,
        );

        // Set current item class.
        $current = isset($wp->query_vars[$endpoint]);
        if ('dashboard' === $endpoint && (isset($wp->query_vars['page']) || empty($wp->query_vars))) {
            $current = true; // Dashboard is not an endpoint, so needs a custom check.
        }

        if ($current) {
            $classes[] = 'is-active';
        }

        $classes = apply_filters('rtcl_account_menu_item_classes', $classes, $endpoint);

        return implode(' ', array_map('sanitize_html_class', $classes));
    }

    public static function remove_query_arg($key, $query = false) {

        if (is_array($key)) { // removing multiple keys
            foreach ($key as $k) {
                $query = str_replace('#038;', '&', $query);
                $query = add_query_arg($k, false, $query);
            }

            return $query;
        }

        return add_query_arg($key, false, $query);

    }

    public static function locate_template($name) {
        // Look within passed path within the theme - this is priority.
        $template = array(
            rtcl()->get_template_path() . $name . ".php"
        );

        if (!$template_file = locate_template(apply_filters('rtcl_locate_template_names', $template))) {
            $template_file = RTCL_PATH . "templates/$name.php";
        }

        return apply_filters('rtcl_locate_template', $template_file, $name);
    }

    static function get_template($fileName, $args = null) {

        if (!empty($args) && is_array($args)) {
            extract($args); // @codingStandardsIgnoreLine
        }

        $located = self::locate_template($fileName);


        if (!file_exists($located)) {
            /* translators: %s template */
            self::doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'classified-listing'), '<code>' . $located . '</code>'), '1.0');

            return;
        }

        // Allow 3rd party plugin filter template file from their plugin.
        $located = apply_filters('rtcl_get_template', $located, $fileName, $args);

        do_action('rtcl_before_template_part', $fileName, $located, $args);

        include $located;

        do_action('rtcl_after_template_part', $fileName, $located, $args);

    }

    static public function get_template_html($template_name, $args = null) {
        ob_start();
        self::get_template($template_name, $args);

        return ob_get_clean();

    }

    static function get_payment_gateway($id) {
        $payment_gateways = rtcl()->payment_gateways();
        $gateway = array_filter($payment_gateways, function ($gateway) use ($id) {
            return $gateway->id == $id;
        });
        if (!empty($gateway)) {
            return reset($gateway);
        }

        return null;
    }


    static function get_payment_method_list() {
        $gateways = rtcl()->payment_gateways();
        $list = array();
        foreach ($gateways as $gateway) {
            if ('yes' === $gateway->enabled) {
                $html = '<li class="list-group-item rtcl-no-margin-left rtcl-payment-method">';
                $html .= sprintf('<label for="gateway-%1$s"><input type="radio" name="payment_method" id="gateway-%1$s" value="%1$s" required> %2$s %3$s</label>',
                    $gateway->id,
                    $gateway->get_title(),
                    $gateway->get_icon()
                );
                if ($gateway->has_fields() || $gateway->get_description()) {
                    $html .= sprintf('<div class="payment_box payment_method_%s" %s>%s</div>',
                        $gateway->id,
                        !$gateway->chosen ? 'style="display:none;"' : null,
                        $gateway->payment_fields()
                    );
                }

                $html .= '</li>';
                $list[] = $html;
            }

        }

        if (count($list)) {
            return '<ul class="list-group">' . implode("\n", $list) . '</ul>';
        }

    }


    /**
     * @param       $payment Payment
     * @param array $data
     *
     * @throws \Exception
     */
    static function rtcl_payment_completed($payment, $data = array()) {
        if (is_a($payment, Payment::class)) {

            // update order details
            wp_update_post(array(
                'ID'                => $payment->get_id(),
                'post_status'       => 'rtcl-completed',
                'post_modified'     => current_time('mysql'),
                'post_modified_gmt' => current_time('mysql', 1),
            ));
            if (!empty($data['transaction_id'])) {
                update_post_meta($payment->get_id(), 'transaction_id', $data['transaction_id']);
            } else {
                update_post_meta($payment->get_id(), 'transaction_id', wp_generate_password(12, false));
            }

            if ('publish' == get_post_status($payment->get_listing_id())) {
                $current_date = new \DateTime(current_time('mysql'));
                $visible = $payment->pricing->getVisible();
                $expiry_date = get_post_meta($payment->get_listing_id(), 'expiry_date', true);
                if ($expiry_date) {
                    $expiry_date = new \DateTime(Functions::datetime('mysql', trim(($expiry_date))));
                    if ($current_date > $expiry_date) {
                        $current_date->add(new \DateInterval("P{$visible}D"));
                        $expDate = $current_date->format('Y-m-d H:i:s');
                    } else {
                        $expiry_date->add(new \DateInterval("P{$visible}D"));
                        $expDate = $expiry_date->format('Y-m-d H:i:s');
                    }
                    update_post_meta($payment->get_listing_id(), 'expiry_date', $expDate);
                }

                if ($payment->pricing->getFeatured()) {
                    update_post_meta($payment->get_listing_id(), 'featured', 1);
                    $feature_expiry_date = get_post_meta($payment->get_listing_id(), 'feature_expiry_date', true);
                    if ($feature_expiry_date) {
                        $feature_expiry_date = new \DateTime(Functions::datetime('mysql',
                            trim(($feature_expiry_date))));
                        if ($current_date > $feature_expiry_date) {
                            delete_post_meta($payment->get_listing_id(), 'feature_expiry_date');
                        } else {
                            $feature_expiry_date->add(new \DateInterval("P{$visible}D"));
                            $featureExpDate = $feature_expiry_date->format('Y-m-d H:i:s');
                            update_post_meta($payment->get_listing_id(), 'feature_expiry_date', $featureExpDate);
                        }
                    }
                }
                update_post_meta($payment->get_id(), '_applied', 1);
            }


            // Hook for developers
            do_action('rtcl_payment_completed', $payment->get_id());

            // send emails
            if (Functions::get_option_item('rtcl_email_settings', 'notify_users', 'order_completed', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Order_Completed_Email_To_Customer']->trigger($payment->get_id(), $payment);
            }
            // send emails
            if (Functions::get_option_item('rtcl_email_settings', 'notify_admin', 'order_completed', 'multi_checkbox')) {
                rtcl()->mailer()->emails['Order_Completed_Email_To_Admin']->trigger($payment->get_id(), $payment);
            }
        }

    }

    public static function get_ip_address() {
        if (isset($_SERVER['HTTP_X_REAL_IP'])) { // WPCS: input var ok, CSRF ok.
            return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));  // WPCS: input var ok, CSRF ok.
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) { // WPCS: input var ok, CSRF ok.
            // Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
            // Make sure we always only send through the first IP in the list which should always be the client IP.
            return (string)rest_is_ip_address(trim(current(preg_split('/[,:]/',
                sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR'])))))); // WPCS: input var ok, CSRF ok.
        } elseif (isset($_SERVER['REMOTE_ADDR'])) { // @codingStandardsIgnoreLine
            return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])); // @codingStandardsIgnoreLine
        }

        return '';
    }

    static function doing_it_wrong($function, $message, $version) {
        // @codingStandardsIgnoreStart
        $message .= ' Backtrace: ' . wp_debug_backtrace_summary();

//		if ( is_ajax() ) {
//			do_action( 'doing_it_wrong_run', $function, $message, $version );
//			error_log( "{$function} was called incorrectly. {$message}. This message was added in version {$version}." );
//		} else {
        _doing_it_wrong($function, $message, $version);
        //}
    }

    /**
     * @param        $term_id
     * @param string $taxonomy
     * @param bool   $pad_counts
     *
     * @return int
     */
    static function get_listings_count_by_taxonomy($term_id, $taxonomy = null, $pad_counts = true) {

        $taxonomy = $taxonomy ? $taxonomy : rtcl()->category;
        $args = array(
            'fields'           => 'ids',
            'posts_per_page'   => -1,
            'post_type'        => rtcl()->post_type,
            'post_status'      => 'publish',
            'suppress_filters' => false,
            'tax_query'        => array(
                array(
                    'taxonomy'         => $taxonomy,
                    'field'            => 'term_id',
                    'terms'            => $term_id,
                    'include_children' => $pad_counts
                )
            )
        );

        return count(get_posts($args));

    }

    public static function sorting_action() {
        Functions::get_template("listings/sorting-action");
    }

    public static function print_notices() {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }

        $all_notices = rtcl()->session->get('rtcl_notices', array());
        $notice_types = apply_filters('rtcl_notice_types', array('error', 'success', 'notice'));

        foreach ($notice_types as $notice_type) {
            if (self::notice_count($notice_type) > 0) {
                Functions::get_template("notices/{$notice_type}", array(
                    'messages' => array_filter($all_notices[$notice_type])
                ));
            }
        }

        self::clear_notices();
    }

    public static function clear_notices() {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }
        rtcl()->session->set('rtcl_notices', null);
    }

    public static function notice_count($notice_type = '') {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'woocommerce'), '1.0');

            return;
        }

        $notice_count = 0;
        $all_notices = rtcl()->session->get('rtcl_notices', array());

        if (isset($all_notices[$notice_type])) {

            $notice_count = count($all_notices[$notice_type]);

        } elseif (empty($notice_type)) {

            foreach ($all_notices as $notices) {
                $notice_count += count($notices);
            }
        }

        return $notice_count;
    }

    public static function has_notice($message, $notice_type = 'success') {
        if (!did_action('rtcl_init')) {
            self::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'woocommerce'), '1.0');

            return false;
        }

        $notices = rtcl()->session->get('rtcl_notices', array());
        $notices = isset($notices[$notice_type]) ? $notices[$notice_type] : array();

        return array_search($message, $notices, true) !== false;
    }

    public static function add_notice($message, $notice_type = 'success') {
        if (!did_action('rtcl_init')) {
            Functions::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }

        $notices = rtcl()->session->get('rtcl_notices', array());

        // Backward compatibility.
        if ('success' === $notice_type) {
            $message = apply_filters('rtcl_add_message', $message);
        }

        $notices[$notice_type][] = apply_filters('rtcl_add_' . $notice_type, $message);

        rtcl()->session->set('rtcl_notices', $notices);
    }


    /**
     * Returns all queued notices, optionally filtered by a notice type.
     *
     * @param string $notice_type Optional. The singular name of the notice type - either error, success or notice.
     *
     * @return array|mixed
     * @since  1.0
     */
    public static function get_notices($notice_type = '') {
        if (!did_action('rtcl_init')) {
            self::doing_it_wrong(__FUNCTION__, __('This function should not be called before rtcl_init.', 'classified-listing'), '1.0');

            return;
        }

        $all_notices = rtcl()->session->get('rtcl_notices', array());

        if (empty($notice_type)) {
            $notices = $all_notices;
        } elseif (isset($all_notices[$notice_type])) {
            $notices = $all_notices[$notice_type];
        } else {
            $notices = array();
        }

        return $notices;
    }

    public static function add_wp_error_notices($errors) {
        if (is_wp_error($errors) && $errors->get_error_messages()) {
            foreach ($errors->get_error_messages() as $error) {
                self::add_notice($error, 'error');
            }
        }
    }

    public static function setcookie($name, $value, $expire = 0, $secure = false) {
        if (!headers_sent()) {
            setcookie($name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, apply_filters('rtcl_cookie_httponly', false, $name, $value, $expire, $secure));
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            trigger_error("{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE); // @codingStandardsIgnoreLine
        }
    }

    public static function create_new_user($email, $username = '', $password = '') {

        // Check the email address.
        if (empty($email) || !is_email($email)) {
            return new \WP_Error('registration-error-invalid-email', __('Please provide a valid email address.', 'classified-listing'));
        }

        if (email_exists($email)) {
            return new \WP_Error('registration-error-email-exists', apply_filters('rtcl_registration_error_email_exists', __('An account is already registered with your email address. Please log in.', 'classified-listing'), $email));
        }

        // Handle username creation.
        if (!empty($username)) {
            $username = sanitize_user($username);

            if (empty($username) || !validate_username($username)) {
                return new \WP_Error('registration-error-invalid-username', __('Please enter a valid account username.', 'classified-listing'));
            }

            if (username_exists($username)) {
                return new \WP_Error('registration-error-username-exists', __('An account is already registered with that username. Please choose another.', 'classified-listing'));
            }
        } else {
            $username = sanitize_user(current(explode('@', $email)), true);

            // Ensure username is unique.
            $append = 1;
            $o_username = $username;

            while (username_exists($username)) {
                $username = $o_username . $append;
                $append++;
            }
        }

        // Handle password creation.
        if (empty($password)) {
            $password = wp_generate_password();
            $password_generated = true;
        } elseif (empty($password)) {
            return new \WP_Error('registration-error-missing-password', __('Please enter an account password.', 'classified-listing'));
        } else {
            $password_generated = false;
        }

        // Use WP_Error to handle registration errors.
        $errors = new \WP_Error();

        do_action('rtcl_register_data', $username, $email, $errors);

        $errors = apply_filters('rtcl_registration_errors', $errors, $username, $email);

        if ($errors->get_error_code()) {
            return $errors;
        }
        $role = Functions::get_option_item('rtcl_account_settings', 'user_role', get_option('default_role'));
        $new_user_data = apply_filters('rtcl_new_user_data', array(
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
            'role'       => $role
        ));

        $user_id = wp_insert_user($new_user_data);

        if (is_wp_error($user_id)) {
            return new \WP_Error('registration-error', '<strong>' . __('Error:', 'classified-listing') . '</strong> ' . __('Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'classified-listing'));
        }

        do_action('rtcl_new_user_created', $user_id, $new_user_data);

        return $user_id;
    }

    public static function set_customer_auth_cookie($user_id) {
        global $current_user;

        $current_user = get_user_by('id', $user_id);

        wp_set_auth_cookie($user_id, true);
    }

    static function in_array_any($needles, $haystack) {
        return !!array_intersect($needles, $haystack);
    }

    static function in_array_all($needles, $haystack) {
        return !array_diff($needles, $haystack);
    }

    public static function array_insert(&$array, $position, $insert_array) {
        $first_array = array_splice($array, 0, $position + 1);
        $array = array_merge($first_array, $insert_array, $array);
    }


    static function array_insert_after($key, $array, $new_array) {

        if (array_key_exists($key, $array)) {
            $new = array();
            foreach ($array as $k => $value) {
                $new[$k] = $value;
                if ($k === $key) {
                    foreach ($new_array as $new_key => $new_value) {
                        $new[$new_key] = $new_value;
                    }
                }
            }

            return $new;
        }

        return $array;
    }

    /**
     * @param $listing_id
     *
     * @throws \Exception
     */
    static function apply_payment_pricing($listing_id) {
        $args = array(
            'post_type'        => rtcl()->post_type_payment,
            'post_status'      => 'rtcl-completed',
            'posts_per_page'   => -1,
            'suppress_filters' => false,
            'meta_query'       => array(
                'relation' => 'AND',
                array(
                    'key'     => '_applied',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'     => 'listing_id',
                    'value'   => $listing_id,
                    'compare' => '='
                )
            )
        );
        $publish_count = absint(get_post_meta($listing_id, 'publish_count', true)) + 1;
        update_post_meta($listing_id, 'publish_count', $publish_count);
        $payments = get_posts($args);
        if (!empty($payments)) {
            $totalVisible = 0;
            $featuredVisible = 0;
            $topVisible = 0;
            $featured = false;
            $top = false;
            foreach ($payments as $p) {
                $payment = new Payment($p->ID);
                $visible = $payment->pricing->getVisible();
                $totalVisible += absint($visible);
                if ($payment->pricing->getFeatured()) {
                    $featured = true;
                    if ($visible > $featuredVisible) {
                        $featuredVisible = $visible;
                    }
                }
                if ($payment->pricing->getTop()) {
                    $top = true;
                    if ($visible > $topVisible) {
                        $topVisible = $visible;
                    }
                }
                update_post_meta($p->ID, '_applied', 1);
            }


            // Calculate new date
            $date = new \DateTime(current_time('mysql'));
            if ($featured) {
                update_post_meta($listing_id, 'featured', 1);
                if ($featuredVisible && $totalVisible != $featuredVisible) {
                    $date->add(new \DateInterval("P{$featuredVisible}D"));
                    $featuredDate = $date->format('Y-m-d H:i:s');
                    update_post_meta($listing_id, 'feature_expiry_date', $featuredDate);
                }
            }
            if ($top) {
                update_post_meta($listing_id, '_top', 1);
                if ($topVisible && $totalVisible != $topVisible) {
                    $date->add(new \DateInterval("P{$topVisible}D"));
                    $topDate = $date->format('Y-m-d H:i:s');
                    update_post_meta($listing_id, '_top_expiry_date', $topDate);
                }
            }
            if ($totalVisible) {
                $date->add(new \DateInterval("P{$totalVisible}D"));
                $visibleDate = $date->format('Y-m-d H:i:s');
                update_post_meta($listing_id, 'expiry_date', $visibleDate);
            }

        } else {
            self::add_default_expiry_date($listing_id);
        }
    }


    /**
     * @param $listing_id
     *
     * @return bool
     * @throws \Exception
     */
    static function add_default_expiry_date($listing_id) {
        $days = apply_filters('rtcl_get_listing_duration', absint(Functions::get_option_item('rtcl_moderation_settings', 'listing_duration', 0)), $listing_id);

        if ($days <= 0) {
            update_post_meta($listing_id, 'never_expires', 1);
            $days = 999;
        } else {
            delete_post_meta($listing_id, 'never_expires');
        }


        // Calculate new date
        $date = new \DateTime(current_time('mysql'));
        $date->add(new \DateInterval("P{$days}D"));

        // return
        $expDate = $date->format('Y-m-d H:i:s');
        update_post_meta($listing_id, 'expiry_date', $expDate);
        return true;
    }

    /**
     * @return array|string|void
     */
    public static function get_admin_email_id_s() {
        $to = '';
        $admin_emails = self::get_option_item('rtcl_email_settings', 'admin_notice_emails');

        if (!empty($admin_emails)) {
            $to = explode("\n", $admin_emails);
            $to = array_map('trim', $to);
            $to = array_filter($to);
        }

        if (empty($to)) {
            $to = get_bloginfo('admin_email');
        }

        return $to;
    }

    public static function all_ids_for_remove_attachment() {
        $excluded_ids = get_posts([
            'post_type'        => rtcl()->post_type,
            'post_status'      => 'any',
            'posts_per_page'   => -1,
            'fields'           => 'ids',
            'suppress_filters' => false
        ]);

        $excluded_ids = apply_filters('rtcl_all_ids_for_remove_attachment', $excluded_ids);

        return $excluded_ids;
    }


    public static function get_max_upload() {
        $max_size = absint(self::get_option_item('rtcl_misc_settings', 'image_allowed_memory', 2));

        return $max_size * (1024 * 1024);
    }

    public static function get_wp_max_upload() {
        if (function_exists('wp_max_upload_size')) {
            return wp_max_upload_size();
        } else {
            return ini_get('upload_max_filesize');
        }
    }

    public static function formatBytes($size, $precision = 2) {
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }

    public static function the_offline_payment_instructions() {
        $settings = self::get_option_item('rtcl_payment_offline', 'instructions');
        echo $settings ? '<p>' . nl2br($settings) . '</p>' : null;
    }

    /**
     * @param array $terms
     *
     * @return array|int|mixed
     */
    public static function get_term_child_id_for_a_post($terms) {

        $child_ids = array();
        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                if ($term->parent) {
                    $child_ids[] = $term->term_id;
                }
            }
            $child_ids = array_unique($child_ids);
        }
        if (empty($child_ids) && !empty($terms)) {
            $child_ids[] = $terms[0]->term_id;
        }

        return !empty($child_ids) ? $child_ids[0] : 0;
    }

    public static function print_html($html, $allHtml = false) {
        if ($allHtml) {
            echo stripslashes_deep($html);
        } else {
            echo wp_kses_post(stripslashes_deep($html));
        }
    }

    public static function get_default_placeholder_url() {
        $placeholder_url = RTCL_URL . '/assets/images/placeholder.png';

        return apply_filters('rtcl_default_placeholder_url', $placeholder_url);
    }

    public static function get_user_roles() {
        global $wp_roles;
        $roles = array();
        foreach ($wp_roles->roles as $key => $value) {
            $roles[$key] = $value['name'];
        }

        return $roles;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    static function is_enable_terms_conditions($type = 'listing') {
        if (in_array($type, array('checkout', 'listing'))) {
            return Functions::get_option_item('rtcl_account_settings', 'enable_' . $type . '_terms_conditions', null, 'checkbox') ? true : false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function is_wc_active() {
        return class_exists('WooCommerce');
    }


    /**
     * @param $url
     *
     * @return bool
     */
    public static function is_external($url) {
        $site_url = str_replace('www.', '', parse_url(site_url(), PHP_URL_HOST));
        $url = str_replace('www.', '', parse_url($url, PHP_URL_HOST));
        if (empty($url)) {
            return false;
        }
        if (strcasecmp($url, $site_url) === 0) {
            return false;
        }

        return true;
    }


    /**
     * @param $type
     * @param $post_id
     * @param $success
     *
     * @return mixed|string|null
     */
    static function get_listing_redirect_url_after_edit_post($type, $post_id, $success) {
        $redirect_url = null;
        $account_listings_url = Link::get_account_endpoint_url('listings');
        if ($success) {
            $submission_url = Link::get_regular_submission_end_point($post_id);
            if ($type == 'new') {
                $rNewListing = Functions::get_option_item('rtcl_moderation_settings', 'redirect_new_listing', 'submission');
                if ($rNewListing == 'submission') {
                    $redirect_url = $submission_url;
                } elseif ($rNewListing == 'custom') {
                    $cUrl = Functions::get_option_item('rtcl_moderation_settings', 'redirect_new_listing_custom');
                    $redirect_url = esc_url($cUrl);
                }
            } elseif ($type == 'update') {
                $rUpdateListing = Functions::get_option_item('rtcl_moderation_settings', 'redirect_update_listing', 'submission');
                if ($rUpdateListing == 'submission') {
                    $redirect_url = $submission_url;
                } elseif ($rUpdateListing == 'custom') {
                    $cUrl = Functions::get_option_item('rtcl_moderation_settings', 'redirect_update_listing_custom');
                    $redirect_url = esc_url($cUrl);
                }
            }
        }

        return apply_filters('rtcl_get_listing_redirect_url_after_edit_post',
            $redirect_url ? $redirect_url : $account_listings_url,
            $type,
            $post_id,
            $success
        );
    }

    static function touch_time($name, $date = null, $tab_index = 0) {
        global $wp_locale;
        $edit = ($date && '0000-00-00 00:00:00' != $date);

        $tab_index_attribute = '';
        if ((int)$tab_index > 0) {
            $tab_index_attribute = " tabindex=\"$tab_index\"";
        }
        $formatted_date = date_i18n(__('M j, Y @ H:i', 'classified-listing'), strtotime($date));

        $time_adj = current_time('timestamp');
        $jj = ($edit) ? mysql2date('d', $date, false) : gmdate('d', $time_adj);
        $mm = ($edit) ? mysql2date('m', $date, false) : gmdate('m', $time_adj);
        $aa = ($edit) ? mysql2date('Y', $date, false) : gmdate('Y', $time_adj);
        $hh = ($edit) ? mysql2date('H', $date, false) : gmdate('H', $time_adj);
        $mn = ($edit) ? mysql2date('i', $date, false) : gmdate('i', $time_adj);
        $ss = ($edit) ? mysql2date('s', $date, false) : gmdate('s', $time_adj);

        $cur_jj = gmdate('d', $time_adj);
        $cur_mm = gmdate('m', $time_adj);
        $cur_aa = gmdate('Y', $time_adj);
        $cur_hh = gmdate('H', $time_adj);
        $cur_mn = gmdate('i', $time_adj);

        $month = '<label><span class="screen-reader-text">' . __('Month') . '</span><select class="rtcl-mm" name="' . $name . '-mm"' . $tab_index_attribute . ">\n";
        for ($i = 1; $i < 13; $i = $i + 1) {
            $monthnum = zeroise($i, 2);
            $monthtext = $wp_locale->get_month_abbrev($wp_locale->get_month($i));
            $month .= "\t\t\t" . '<option value="' . $monthnum . '" data-text="' . $monthtext . '" ' . selected($monthnum, $mm, false) . '>';
            /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
            $month .= sprintf(__('%1$s-%2$s'), $monthnum, $monthtext) . "</option>\n";
        }
        $month .= '</select></label>';

        $day = '<label><span class="screen-reader-text">' . __('Day') . '</span><input type="text" class="rtcl-jj" name="' . $name . '-jj" value="' . $jj . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
        $year = '<label><span class="screen-reader-text">' . __('Year') . '</span><input type="text" class="rtcl-aa" name="' . $name . '-aa" value="' . $aa . '" size="4" maxlength="4"' . $tab_index_attribute . ' autocomplete="off" /></label>';
        $hour = '<label><span class="screen-reader-text">' . __('Hour') . '</span><input type="text" class="rtcl-hh" name="' . $name . '-hh" value="' . $hh . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';
        $minute = '<label><span class="screen-reader-text">' . __('Minute') . '</span><input type="text" class="rtcl-mn" name="' . $name . '-mn" value="' . $mn . '" size="2" maxlength="2"' . $tab_index_attribute . ' autocomplete="off" /></label>';

        echo '<div class="rtcl-timestamp-wrapper">';
        echo sprintf('<span class="rtcl-timestamp">%s</span>', sprintf(__('Expired on: <b>%1$s</b>', 'classified-listing'), $formatted_date));

        echo sprintf('<a href="#edit_timestamp" class="edit-timestamp hide-if-no-js" role="button"><span
                            aria-hidden="true">%s</span> <span class="screen-reader-text">%s</span></a>',
            esc_html__('Edit', 'classified-listing'),
            esc_html__('Edit date and time', 'classified-listing')
        );
        echo '<fieldset class="rtcl-timestamp-div hide-if-js">';
        echo sprintf('<legend class="screen-reader-text">%s</legend>', esc_html__('Date and time', 'classified-listing'));
        echo '<div class="timestamp-wrap">';
        /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
        printf(__('%1$s %2$s, %3$s @ %4$s:%5$s'), $month, $day, $year, $hour, $minute);

        echo '</div><input type="hidden" class="rtcl-ss" name="' . $name . '-ss" value="' . $ss . '" />';


        echo "\n\n";
        $map = array(
            'mm' => array($mm, $cur_mm),
            'jj' => array($jj, $cur_jj),
            'aa' => array($aa, $cur_aa),
            'hh' => array($hh, $cur_hh),
            'mn' => array($mn, $cur_mn),
        );
        foreach ($map as $timeunit => $value) {
            list($unit, $curr) = $value;

            echo '<input type="hidden" class="rtcl-hidden_' . $timeunit . '" name="hidden_' . $name . '-' . $timeunit . '" value="' . $unit . '" />' . "\n";
            $cur_timeunit = 'cur_' . $timeunit;
            echo '<input type="hidden" class="rtcl-' . $cur_timeunit . '" name="' . $name . '-' . $cur_timeunit . '" value="' . $curr . '" />' . "\n";
        }
        ?>

        <p>
            <a href="#edit_timestamp"
               class="save-timestamp hide-if-no-js button"><?php _e('OK', 'classified-listing'); ?></a>
            <a href="#edit_timestamp"
               class="cancel-timestamp hide-if-no-js button-cancel"><?php _e('Cancel', 'classified-listing'); ?></a>
        </p>
        </fieldset>
        </div>
        <?php
    }
}