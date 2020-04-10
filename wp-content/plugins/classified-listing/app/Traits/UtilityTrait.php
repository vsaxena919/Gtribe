<?php


namespace Rtcl\Traits;


use Rtcl\Controllers\Hooks\Comments;
use Rtcl\Helpers\Text;
use Rtcl\Helpers\Functions;

trait UtilityTrait
{

    /**
     * @param \WP_User $user
     *
     * @return string
     */
    static function get_author_name($user) {
        $author_name = '';
        if (is_object($user)) {
            $author[] = $user->first_name;
            $author[] = $user->last_name;
            $author = array_filter($author);
            if (!empty($author)) {
                $author_name = implode(' ', $author);
            } else {
                $author_name = $user->display_name;
            }
        }

        return $author_name;
    }

    static function format_content($raw_string) {
        return apply_filters('rtcl_format_content', apply_filters('rtcl_short_description', $raw_string), $raw_string);
    }

    static function format_listing_short_description($content) {
        // Add support for Jetpack Markdown.
        if (class_exists('WPCom_Markdown')) {
            $markdown = \WPCom_Markdown::get_instance();

            return wpautop(
                $markdown->transform(
                    $content,
                    array(
                        'unslash' => false,
                    )
                )
            );
        }

        return $content;
    }

    static function do_oembeds($content) {
        global $wp_embed;

        $content = $wp_embed->autoembed($content);

        return $content;
    }

    static function replace_policy_and_terms_page_link_placeholders($text) {
        $privacy_page_id = Functions::get_privacy_policy_page_id();
        $terms_page_id = Functions::get_terms_and_conditions_page_id();
        $privacy_link = $privacy_page_id ? '<a href="' . esc_url(get_permalink($privacy_page_id)) . '" class="rtcl-privacy-policy-link" target="_blank">' . __('privacy policy', 'classified-listing') . '</a>' : __('privacy policy', 'classified-listing');
        $terms_link = $terms_page_id ? '<a href="' . esc_url(get_permalink($terms_page_id)) . '" class="rtcl-terms-and-conditions-link" target="_blank">' . __('terms and conditions', 'classified-listing') . '</a>' : __('terms and conditions', 'classified-listing');

        $find_replace = array(
            '[terms]'          => $terms_link,
            '[privacy_policy]' => $privacy_link,
        );

        $updated_text = str_replace(array_keys($find_replace), array_values($find_replace), $text);

        return apply_filters('rtcl_replace_policy_and_terms_page_link_placeholders', $updated_text, $text, $privacy_page_id, $privacy_link, $terms_page_id, $terms_link);
    }

    static function privacy_policy_text($type = 'checkout') {

        if (!Functions::get_privacy_policy_page_id()) {
            return;
        }
        echo '<div class="rtcl-privacy-policy-text">';
        echo wp_kses_post(wpautop(Functions::replace_policy_and_terms_page_link_placeholders(Text::get_privacy_policy_text($type))));
        echo '</div>';
    }

    static function terms_and_conditions_checkbox_enabled() {
        $page_id = Functions::get_terms_and_conditions_page_id();
        $page = $page_id ? get_post($page_id) : false;
        $return = $page && Text::get_terms_and_conditions_checkbox_text();

        return apply_filters('rtcl_terms_and_conditions_checkbox_enabled', $return);
    }

    static function terms_and_conditions_checkbox_text() {
        $text = Text::get_terms_and_conditions_checkbox_text();

        if (!$text) {
            return;
        }

        echo wp_kses_post(Functions::replace_policy_and_terms_page_link_placeholders($text));
    }


    static function switch_to_site_locale() {
        if (function_exists('switch_to_locale')) {
            switch_to_locale(get_locale());

            // Filter on plugin_locale so load_plugin_textdomain loads the correct locale.
            add_filter('plugin_locale', 'get_locale');

            // Init WC locale.
            rtcl()->load_plugin_textdomain();
        }
    }

    static function get_theme_template_path($template) {
        return trailingslashit(get_stylesheet_directory()) . trailingslashit(rtcl()->get_template_path()) . $template;
    }

    static function get_theme_template_file($template) {
        return trailingslashit(basename(get_stylesheet_directory())) . trailingslashit(rtcl()->get_template_path()) . $template;
    }

    static function get_plugin_template_file($template) {
        return RTCL_SLUG . '/templates/' . $template;
    }

    static function get_blogname() {
        return wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    }

    static function get_home_url() {
        return wp_parse_url(home_url(), PHP_URL_HOST);
    }

    static function restore_locale() {
        if (function_exists('restore_previous_locale')) {
            restore_previous_locale();

            // Remove filter.
            remove_filter('plugin_locale', 'get_locale');

            // Init WC locale.
            rtcl()->load_plugin_textdomain();
        }
    }


    //    Color
    static function rgb_from_hex($color) {
        $color = str_replace('#', '', $color);
        // Convert shorthand colors to full format, e.g. "FFF" -> "FFFFFF".
        $color = preg_replace('~^(.)(.)(.)$~', '$1$1$2$2$3$3', $color);

        $rgb = array();
        $rgb['R'] = hexdec($color{0} . $color{1});
        $rgb['G'] = hexdec($color{2} . $color{3});
        $rgb['B'] = hexdec($color{4} . $color{5});

        return $rgb;
    }

    static function hex_lighter($color, $factor = 30) {
        $base = self::rgb_from_hex($color);
        $color = '#';

        foreach ($base as $k => $v) {
            $amount = 255 - $v;
            $amount = $amount / 100;
            $amount = round($amount * $factor);
            $new_decimal = $v + $amount;

            $new_hex_component = dechex($new_decimal);
            if (strlen($new_hex_component) < 2) {
                $new_hex_component = '0' . $new_hex_component;
            }
            $color .= $new_hex_component;
        }

        return $color;
    }

    static function hex_darker($color, $factor = 30) {
        $base = self::rgb_from_hex($color);
        $color = '#';

        foreach ($base as $k => $v) {
            $amount = $v / 100;
            $amount = round($amount * $factor);
            $new_decimal = $v - $amount;

            $new_hex_component = dechex($new_decimal);
            if (strlen($new_hex_component) < 2) {
                $new_hex_component = '0' . $new_hex_component;
            }
            $color .= $new_hex_component;
        }

        return $color;
    }

    static function hex_is_light($color) {
        $hex = str_replace('#', '', $color);

        $c_r = hexdec(substr($hex, 0, 2));
        $c_g = hexdec(substr($hex, 2, 2));
        $c_b = hexdec(substr($hex, 4, 2));

        $brightness = (($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000;

        return $brightness > 155;
    }

    static function light_or_dark($color, $dark = '#000000', $light = '#FFFFFF') {
        return self::hex_is_light($color) ? $dark : $light;
    }

    static function format_product_short_description($content) {
        // Add support for Jetpack Markdown.
        if (class_exists('WPCom_Markdown')) {
            $markdown = \WPCom_Markdown::get_instance();

            return wpautop(
                $markdown->transform(
                    $content,
                    array(
                        'unslash' => false,
                    )
                )
            );
        }

        return $content;
    }


    public static function is_listing_taxonomy() {
        return is_tax(get_object_taxonomies(rtcl()->post_type));
    }

    public static function get_theme_slug_for_templates() {
        return apply_filters('rtcl_theme_slug_for_templates', get_option('template'));
    }

    public static function listing_loop_start() {
        if (!function_exists('rtcl_listing_loop_start')) {
            Functions::get_template('listings/loop/loop-start');
        }
    }

    public static function listing_loop_end() {
        if (!function_exists('rtcl_listing_loop_end')) {
            Functions::get_template('listings/loop/loop-end');
        }
    }

    /**
     * Get an order note.
     *
     * @param int|WP_Comment $data Note ID (or WP_Comment instance for internal use only).
     *
     * @return stdClass|null        Object with order note details or null when does not exists.
     * @since  1.4.0
     */
    static function get_payment_note($data) {
        if (is_numeric($data)) {
            $data = get_comment($data);
        }

        if (!is_a($data, 'WP_Comment')) {
            return null;
        }

        return (object)apply_filters(
            'rtcl_get_payment_note',
            array(
                'id'            => (int)$data->comment_ID,
                'date_created'  => self::string_to_datetime($data->comment_date),
                'content'       => $data->comment_content,
                'customer_note' => (bool)get_comment_meta($data->comment_ID, 'is_customer_note', true),
                'added_by'      => __('RtclListing', 'Classified-listing') === $data->comment_author ? 'system' : $data->comment_author,
            ),
            $data
        );
    }

    /**
     * Get order notes.
     *
     * @param array $args          Query arguments {
     *                             Array of query parameters.
     *
     * @type string $limit         Maximum number of notes to retrieve.
     *                                 Default empty (no limit).
     * @type int    $order_id      Limit results to those affiliated with a given order ID.
     *                                 Default 0.
     * @type array  $order__in     Array of order IDs to include affiliated notes for.
     *                                 Default empty.
     * @type array  $order__not_in Array of order IDs to exclude affiliated notes for.
     *                                 Default empty.
     * @type string $orderby       Define how should sort notes.
     *                                 Accepts 'date_created', 'date_created_gmt' or 'id'.
     *                                 Default: 'id'.
     * @type string $order         How to order retrieved notes.
     *                                 Accepts 'ASC' or 'DESC'.
     *                                 Default: 'DESC'.
     * @type string $type          Define what type of note should retrieve.
     *                                 Accepts 'customer', 'internal' or empty for both.
     *                                 Default empty.
     * }
     * @return stdClass[]              Array of stdClass objects with order notes details.
     * @since  1.4.0
     */
    public static function get_payment_notes($args) {
        $key_mapping = array(
            'limit'         => 'number',
            'order_id'      => 'post_id',
            'order__in'     => 'post__in',
            'order__not_in' => 'post__not_in',
        );

        foreach ($key_mapping as $query_key => $db_key) {
            if (isset($args[$query_key])) {
                $args[$db_key] = $args[$query_key];
                unset($args[$query_key]);
            }
        }

        // Define orderby.
        $orderby_mapping = array(
            'date_created'     => 'comment_date',
            'date_created_gmt' => 'comment_date_gmt',
            'id'               => 'comment_ID',
        );

        $args['orderby'] = !empty($args['orderby']) && in_array($args['orderby'], array(
            'date_created',
            'date_created_gmt',
            'id'
        ), true) ? $orderby_mapping[$args['orderby']] : 'comment_ID';

        // Set Classified Listing payment note type.
        if (isset($args['type']) && 'customer' === $args['type']) {
            $args['meta_query'] = array( // WPCS: slow query ok.
                array(
                    'key'     => 'is_customer_note',
                    'value'   => 1,
                    'compare' => '=',
                ),
            );
        } elseif (isset($args['type']) && 'internal' === $args['type']) {
            $args['meta_query'] = array( // WPCS: slow query ok.
                array(
                    'key'     => 'is_customer_note',
                    'compare' => 'NOT EXISTS',
                ),
            );
        }

        // Set correct comment type.
        $args['type'] = 'rtcl_payment_note';

        // Always approved.
        $args['status'] = 'approve';

        // Does not support 'count' or 'fields'.
        unset($args['count'], $args['fields']);

        remove_filter('comments_clauses', array(Comments::class, 'exclude_payment_comments'), 10);

        $notes = get_comments($args);

        add_filter('comments_clauses', array(Comments::class, 'exclude_payment_comments'), 10, 1);

        return array_filter(array_map(array(self::class, 'get_payment_note'), $notes));
    }

    /**
     * Sanitize a string destined to be a tooltip.
     *
     * @param string $var Data to sanitize.
     *
     * @return string
     * @since  1.4.0 Tooltips are encoded with htmlspecialchars to prevent XSS. Should not be used in conjunction with esc_attr()
     */
    static function sanitize_tooltip($var) {
        return htmlspecialchars(
            wp_kses(
                html_entity_decode($var),
                array(
                    'br'     => array(),
                    'em'     => array(),
                    'strong' => array(),
                    'small'  => array(),
                    'span'   => array(),
                    'ul'     => array(),
                    'li'     => array(),
                    'ol'     => array(),
                    'p'      => array(),
                )
            )
        );
    }

    /**
     * Display a classified Listing help tip.
     *
     * @param string $tip        Help tip text.
     * @param bool   $allow_html Allow sanitized HTML if true or escape.
     *
     * @return string
     * @since  2.5.0
     *
     */
    static function help_tip($tip, $allow_html = false) {
        if ($allow_html) {
            $tip = self::sanitize_tooltip($tip);
        } else {
            $tip = esc_attr($tip);
        }

        return '<span class="rtcl-help-tip" data-tip="' . $tip . '"></span>';
    }
}