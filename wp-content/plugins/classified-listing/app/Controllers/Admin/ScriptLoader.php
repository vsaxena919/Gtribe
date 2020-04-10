<?php

namespace Rtcl\Controllers\Admin;

use Rtcl\Helpers\Functions;

class ScriptLoader
{

    private $suffix;
    private $version;
    private $ajaxurl;
    private static $wp_localize_scripts = [];

    function __construct() {
        $this->suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        $this->version = (defined('WP_DEBUG') && WP_DEBUG) ? time() : rtcl()->version();

        $this->ajaxurl = admin_url('admin-ajax.php');
        if ($current_lang = apply_filters('wpml_current_language', null)) {
            $this->ajaxurl = add_query_arg('wpml_lang', $current_lang, $this->ajaxurl);
        }

        add_action('wp_enqueue_scripts', array($this, 'register_script'), 1);
        add_action('admin_init', array($this, 'register_admin_script'), 1);
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_setting_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_post_type_listing'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_payment'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_pricing'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_listing_types_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_ie_page'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_page_custom_fields'));
        add_action('admin_enqueue_scripts', array($this, 'load_admin_script_taxonomy'));
    }

    function register_script_both_end() {
        wp_register_script('rtcl-select2', rtcl()->get_assets_uri("vendor/select2/select2.min.js"), array('jquery'));
        wp_register_script('jquery-payment', rtcl()->get_assets_uri("vendor/jquery.payment.min.js"), array('jquery'), '3.0.0');
        wp_register_script('jquery-validator', rtcl()->get_assets_uri("vendor/jquery.validate.min.js"),
            array('jquery'), '1.19.1');
        wp_register_script('rtcl-validator', rtcl()->get_assets_uri("js/rtcl-validator{$this->suffix}.js"),
            array('jquery-validator'), $this->version);
        wp_register_script('jquery-blockui', rtcl()->get_assets_uri("vendor/jquery-blockui/jquery.blockUI.js"),
            array('jquery'), '2.70', true);
        wp_register_style('rtcl-gallery', rtcl()->get_assets_uri("css/rtcl-gallery{$this->suffix}.css"), array(), $this->version);
        wp_register_script('rtcl-gallery',
            rtcl()->get_assets_uri("js/rtcl-gallery{$this->suffix}.js"),
            array(
                'jquery',
                'plupload-all',
                'jquery-ui-sortable',
                'jquery-effects-core',
                'jquery-effects-fade',
                'wp-util',
                'jcrop'
            ), $this->version, true
        );
        wp_localize_script('rtcl-gallery', 'rtcl_gallery_lang', array(
            "ajaxurl"               => $this->ajaxurl,
            "edit_image"            => __("Edit Image", "classified-listing"),
            "delete_image"          => __("Delete Image", "classified-listing"),
            "view_image"            => __("View Full Image", "classified-listing"),
            "featured"              => __("Main", "classified-listing"),
            "error_common"          => __("Error while upload image", "classified-listing"),
            "error_image_size"      => sprintf(__("Image size is more then %s.", "classified-listing"), Functions::formatBytes(Functions::get_max_upload())),
            "error_image_limit"     => __("Image limit is over.", "classified-listing"),
            "error_image_extension" => __("File extension not supported.", "classified-listing"),
        ));

        wp_localize_script('rtcl-validator', 'rtcl_validator', apply_filters('rtcl_validator_localize', array(
            "messages"   => array(
                "required"     => __("This field is required.", "classified-listing"),
                "remote"       => __("Please fix this field.", "classified-listing"),
                "email"        => __("Please enter a valid email address.", "classified-listing"),
                "url"          => __("Please enter a valid URL.", "classified-listing"),
                "date"         => __("Please enter a valid date.", "classified-listing"),
                "dateISO"      => __("Please enter a valid date (ISO).", "classified-listing"),
                "number"       => __("Please enter a valid number.", "classified-listing"),
                "digits"       => __("Please enter only digits.", "classified-listing"),
                "equalTo"      => __("Please enter the same value again.", "classified-listing"),
                "maxlength"    => __("Please enter no more than {0} characters.", "classified-listing"),
                "minlength"    => __("Please enter at least {0} characters.", "classified-listing"),
                "rangelength"  => __("Please enter a value between {0} and {1} characters long.", "classified-listing"),
                "range"        => __("Please enter a value between {0} and {1}.", "classified-listing"),
                "pattern"      => __("Invalid format.", "classified-listing"),
                "maxWords"     => __("Please enter {0} words or less.", "classified-listing"),
                "minWords"     => __("Please enter at least {0} words.", "classified-listing"),
                "rangeWords"   => __("Please enter between {0} and {1} words.", "classified-listing"),
                "alphanumeric" => __("Letters, numbers, and underscores only please", "classified-listing"),
                "lettersonly"  => __("Only alphabets and spaces are allowed.", "classified-listing"),
                "accept"       => __("Please enter a value with a valid mimetype.", "classified-listing"),
                "max"          => __("Please enter a value less than or equal to {0}.", "classified-listing"),
                "min"          => __("Please enter a value greater than or equal to {0}.", "classified-listing"),
                "step"         => __("Please enter a multiple of {0}.", "classified-listing"),
                "extension"    => __("Please Select a value file with a valid extension.", "classified-listing"),
                "cc"           => array(
                    "number" => __("Please enter a valid credit card number.", "classified-listing"),
                    "cvc"    => __("Enter a valid cvc number.", "classified-listing"),
                    "expiry" => __("Enter a valid expiry date", "classified-listing"),
                )
            ),
            "scroll_top" => 200,
        )));
    }

    function register_script() {
        $this->register_script_both_end();
        $moderation_settings = Functions::get_option('rtcl_moderation_settings');
        $misc_settings = Functions::get_option('rtcl_misc_settings');
        $general_settings = Functions::get_option('rtcl_general_settings');

        wp_register_script('rtcl-credit-card-form', rtcl()->get_assets_uri("js/credit-card-form{$this->suffix}.js"), array(
            'jquery-payment',
            'rtcl-validator'
        ), $this->version);

        $depsStyle = array();
        if (!empty($general_settings['load_bootstrap']) && in_array('css', $general_settings['load_bootstrap'])) {
            wp_register_style('rtcl-bootstrap', rtcl()->get_assets_uri("css/rtcl-bootstrap{$this->suffix}.css"), array(), '4.1.1');
            $depsStyle[] = 'rtcl-bootstrap';
        }
        $depsScript = array('jquery');
        if (!empty($general_settings['load_bootstrap']) && in_array('js', $general_settings['load_bootstrap'])) {
            wp_register_script('rtcl-bootstrap', rtcl()->get_assets_uri("vendor/bootstrap/bootstrap.bundle.min.js"), array('jquery'), '4.1.3', true);
            $depsScript[] = 'rtcl-bootstrap';
        }

        wp_register_script('rtcl-owl-carousel', rtcl()->get_assets_uri("vendor/owl.carousel/owl.carousel.min.js"), array(
            'jquery',
            'imagesloaded'
        ));
        wp_register_style('rtcl-owl-carousel', rtcl()->get_assets_uri("vendor/owl.carousel/owl.carousel.min.css"), array(), $this->version);

        wp_register_script('rtcl-single-listing', rtcl()->get_assets_uri("js/single-listing{$this->suffix}.js"), array('rtcl-owl-carousel'), $this->version, true);

        wp_register_script('rtcl-public-add-post', rtcl()->get_assets_uri("js/public-add-post{$this->suffix}.js"), array('jquery'),
            $this->version, true);
        wp_register_script("rtcl-recaptcha",
            "https://www.google.com/recaptcha/api.js?onload=rtcl_on_recaptcha_load&render=explicit", '', $this->version,
            true);
        if (is_singular(rtcl()->post_type)) {
            $depsStyle[] = 'rtcl-owl-carousel';
            wp_enqueue_script('rtcl-single-listing');
            self::localize_script('rtcl-single-listing');
        }
        wp_register_style('rtcl-public', rtcl()->get_assets_uri("css/rtcl-public{$this->suffix}.css"), $depsStyle, $this->version);
        wp_register_script('rtcl-public', rtcl()->get_assets_uri("js/rtcl-public{$this->suffix}.js"), $depsScript, $this->version, true);


        if (wp_script_is('rtcl-bootstrap', 'registered')) {
            wp_enqueue_script('rtcl-bootstrap');
        }
        if (wp_style_is('rtcl-bootstrap', 'registered')) {
            wp_enqueue_style('rtcl-bootstrap');
        }
        wp_enqueue_style('rtcl-public');

        $rtcl_public_script = false;
        $validator_script = false;
        if (Functions::is_account_page()) {
            global $wp;
            if (isset($wp->query_vars['edit-account'])) {
                $validator_script = true;
                wp_enqueue_script('rtcl-public-add-post');
            }
            if (isset($wp->query_vars['listings']) || isset($wp->query_vars['favourites']) || isset($wp->query_vars['payments'])) {
                $rtcl_public_script = true;
            }
            if (Functions::get_option_item('rtcl_misc_settings', 'recaptcha_forms', 'registration', 'multi_checkbox')) {
                wp_enqueue_script('rtcl-recaptcha');
            }
        }

        if (Functions::is_checkout_page()) {
            $validator_script = true;
        }
        if (Functions::is_listing_form_page()) {
            if (Functions::get_option_item('rtcl_misc_settings', 'recaptcha_forms', 'listing', 'multi_checkbox')) {
                wp_enqueue_script('rtcl-recaptcha');
            }
            $validator_script = true;
            wp_enqueue_style('rtcl-gallery');
            wp_enqueue_script('rtcl-gallery');
            wp_enqueue_script('rtcl-select2');
            wp_enqueue_script('rtcl-public-add-post');
            $rtcl_public_script = true;
        }

        if (is_singular(rtcl()->post_type)) {
            $validator_script = true;
            if (Functions::get_option_item('rtcl_misc_settings', 'recaptcha_forms', 'contact', 'multi_checkbox')
                || Functions::get_option_item('rtcl_misc_settings', 'recaptcha_forms', 'report_abuse', 'multi_checkbox')) {
                wp_enqueue_script('rtcl-recaptcha');
            }
            $rtcl_public_script = true;
        }


        if ($validator_script = true) {
            wp_enqueue_script('rtcl-validator');
        }

        wp_enqueue_script('rtcl-public'); // TODO make a condition for loading

        $rtcl_style_opt = Functions::get_option("rtcl_style_settings");

        if (is_array($rtcl_style_opt) && !empty($rtcl_style_opt)) {
            $style = null;
            $primary = !empty($rtcl_style_opt['primary']) ? $rtcl_style_opt['primary'] : null;
            if ($primary) {
                $style .= ".rtcl .rtcl-price-block .rtcl-price-amount{ background-color: $primary; border-color: $primary;}";
                $style .= ".rtcl .rtcl-price-block .rtcl-price-amount:before{border-right-color: $primary;}";
                $style .= ".rtcl .rtcl-listable .rtcl-listable-item{color: $primary;}";
                $style .= ".rtcl .rtcl-icon{color: $primary;}";
            }
            $link = !empty($rtcl_style_opt['link']) ? $rtcl_style_opt['link'] : null;
            if ($link) {
                $style .= ".rtcl a{ color: $link}";
            }
            $linkHover = !empty($rtcl_style_opt['link_hover']) ? $rtcl_style_opt['link_hover'] : null;
            if ($link) {
                $style .= ".rtcl a:hover{ color: $linkHover}";
            }
            // Button
            $button = !empty($rtcl_style_opt['button']) ? $rtcl_style_opt['button'] : null;
            if ($button) {
                $style .= ".rtcl .btn{ background-color: $button; border-color:$button; }";
                $style .= ".rtcl .owl-carousel .owl-nav [class*=owl-],.rtcl .rtcl-slider .rtcl-listing-gallery__trigger{ background-color: $button; }";
            }
            $buttonText = !empty($rtcl_style_opt['button_text']) ? $rtcl_style_opt['button_text'] : null;
            if ($buttonText) {
                $style .= ".rtcl .btn{ color:$buttonText; }";
                $style .= ".rtcl .owl-carousel .owl-nav [class*=owl-],.rtcl .rtcl-slider .rtcl-listing-gallery__trigger{ color: $buttonText; }";
            }

            // Button hover
            $buttonHover = !empty($rtcl_style_opt['button_hover']) ? $rtcl_style_opt['button_hover'] : null;
            if ($buttonHover) {
                $style .= ".rtcl .btn:hover{ background-color: $buttonHover; border-color:$buttonHover; }";
                $style .= ".rtcl .owl-carousel .owl-nav [class*=owl-]:hover,.rtcl .rtcl-slider .rtcl-listing-gallery__trigger:hover{ background-color: $buttonHover; }";
            }
            $buttonHoverText = !empty($rtcl_style_opt['button_hover_text']) ? $rtcl_style_opt['button_hover_text'] : null;
            if ($buttonHoverText) {
                $style .= ".rtcl .btn:hover{ color: $buttonHoverText}";
                $style .= ".rtcl .owl-carousel .owl-nav [class*=owl-]:hover,.rtcl .rtcl-slider .rtcl-listing-gallery__trigger:hover{ color: $buttonHoverText; }";
            }

            // Top
            $top = !empty($rtcl_style_opt['top']) ? $rtcl_style_opt['top'] : null;
            if ($top) {
                $style .= ".rtcl .top-badge{ background-color: $top; }";
            }
            $topText = !empty($rtcl_style_opt['top_text']) ? $rtcl_style_opt['top_text'] : null;
            if ($topText) {
                $style .= ".rtcl .top-badge{ color: $topText; }";
            }
            // Popular
            $popular = !empty($rtcl_style_opt['popular']) ? $rtcl_style_opt['popular'] : null;
            if ($popular) {
                $style .= ".rtcl .popular-badge{ background-color: $popular; }";
            }
            $popularText = !empty($rtcl_style_opt['popular_text']) ? $rtcl_style_opt['popular_text'] : null;
            if ($popularText) {
                $style .= ".rtcl .popular-badge{ color: $popularText; }";
            }
            // Feature
            $feature = !empty($rtcl_style_opt['feature']) ? $rtcl_style_opt['feature'] : null;
            if ($feature) {
                $style .= ".rtcl .feature-badge{ background-color: $feature; }";
            }
            $featureText = !empty($rtcl_style_opt['feature_text']) ? $rtcl_style_opt['feature_text'] : null;
            if ($featureText) {
                $style .= ".rtcl .feature-badge{ color: $featureText; }";
            }

            if ($style) {
                wp_add_inline_style('rtcl-public', $style);
            }

        }

        if (!empty($misc_settings['recaptcha_site_key']) && !empty($misc_settings['recaptcha_forms'])) {
            $recaptcha_site_key = $misc_settings['recaptcha_site_key'];
            $recaptchas = $misc_settings['recaptcha_forms'];
            $recaptchas_condition['has_contact_form'] = !empty($moderation_settings['has_contact_form']) && $moderation_settings['has_contact_form'] == 'yes' ? 1 : 0;
            $recaptchas_condition['has_report_abuse'] = !empty($moderation_settings['has_report_abuse']) && $moderation_settings['has_report_abuse'] == 'yes' ? 1 : 0;
            $recaptchas_condition['listing'] = in_array('listing', $misc_settings['recaptcha_forms']) ? 1 : 0;

        } else {
            $recaptcha_site_key = '';
            $recaptchas = $recaptchas_condition = array();
        }

        $decimal_separator = Functions::get_decimal_separator();
        $localize = array(
            'decimal_point'                => $decimal_separator,
            /* translators: %s: decimal */
            'i18n_decimal_error'           => sprintf(__('Please enter in decimal(%s) format without thousand separators . ', 'classified - listing'), $decimal_separator),
            /* translators: %s: price decimal separator */
            'i18n_mon_decimal_error'       => sprintf(__('Please enter in monetary decimal(%s) format without thousand separators and currency symbols . ', 'classified - listing'), $decimal_separator),
            'is_rtl'                       => is_rtl(),
            'is_admin'                     => is_admin(),
            "ajaxurl"                      => $this->ajaxurl,
            'confirm_text'                 => __("Are you sure to delete?", "classified-listing"),
            rtcl()->nonceId                => wp_create_nonce(rtcl()->nonceText),
            'recaptchas'                   => $recaptchas,
            'recaptchas_condition'         => $recaptchas_condition,
            'recaptcha_site_key'           => $recaptcha_site_key,
            'recaptcha_responce'           => array(
                'registration' => 0,
                'listing'      => 0,
                'contact'      => 0,
                'report_abuse' => 0
            ),
            'recaptcha_invalid_message'    => __("You can't leave Captcha Code empty", 'classified-listing'),
            'user_login_alert_message'     => __('Sorry, you need to login first.', 'classified-listing'),
            'upload_limit_alert_message'   => __('Sorry, you have only %d images pending.', 'classified-listing'),
            'delete_label'                 => __('Delete Permanently', 'classified-listing'),
            'proceed_to_payment_btn_label' => __('Proceed to payment', 'classified-listing'),
            'finish_submission_btn_label'  => __('Finish submission', 'classified-listing')
        );
        if (is_singular(rtcl()->post_type)) {
            global $post;
            $localize['post_id'] = $post->ID;
        }
        wp_localize_script('rtcl-public', 'rtcl', $localize);
    }

    function register_admin_script() {
        $this->register_script_both_end();

        wp_register_script('rtcl-timepicker', rtcl()->get_assets_uri("vendor/jquery-ui-timepicker-addon{$this->suffix}.js"), array('jquery'), $this->version, true);
        wp_register_script('rtcl-admin', rtcl()->get_assets_uri("js/rtcl-admin{$this->suffix}.js"), array('jquery', 'jquery-blockui'), $this->version, true);
        wp_register_script('rt-field-dependency', rtcl()->get_assets_uri("js/rt-field-dependency{$this->suffix}.js"), array('jquery'), $this->version, true);
        wp_register_script('rtcl-admin-settings', rtcl()->get_assets_uri("js/rtcl-admin-settings{$this->suffix}.js"), array(
            'jquery',
            'wp-color-picker'
        ), $this->version, true);
        wp_register_script('rtcl-admin-ie', rtcl()->get_assets_uri("js/rtcl-admin-ie{$this->suffix}.js"), array(
            'jquery',
            'rtcl-validator'
        ), $this->version, true);
        wp_register_script('rtcl-admin-listing-type', rtcl()->get_assets_uri("js/rtcl-admin-listing-type{$this->suffix}.js"), array(
            'jquery'
        ), $this->version, true);
        wp_register_script('rtcl-admin-taxonomy', rtcl()->get_assets_uri("js/rtcl-admin-taxonomy{$this->suffix}.js"), array('jquery'),
            $this->version, true);
        wp_register_script('rtcl-admin-custom-fields', rtcl()->get_assets_uri("js/rtcl-admin-custom-fields{$this->suffix}.js"),
            array(
                'jquery',
                'jquery-ui-dialog',
                'jquery-ui-sortable',
                'jquery-ui-draggable',
                'jquery-ui-tabs'
            ), $this->version, true);

        $decimal_separator = Functions::get_decimal_separator();
        $pricing_decimal_separator = Functions::get_decimal_separator(true);
        wp_localize_script('rtcl-admin', 'rtcl', array(
            "ajaxurl"                        => $this->ajaxurl,
            'is_admin'                       => is_admin(),
            'decimal_point'                  => $decimal_separator,
            'pricing_decimal_point'          => $pricing_decimal_separator,
            'i18n_decimal_error'             => sprintf(__('Please enter in decimal (%s) format without thousand separators.', 'classified-listing'), $decimal_separator),
            'i18n_pricing_decimal_error'     => sprintf(__('Please enter in decimal (%s) format without thousand separators.', 'classified-listing'), $pricing_decimal_separator),
            'i18n_mon_decimal_error'         => sprintf(__('Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'classified-listing'), $decimal_separator),
            'i18n_mon_pricing_decimal_error' => sprintf(__('Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'classified-listing'), $pricing_decimal_separator),
            rtcl()->nonceId                  => wp_create_nonce(rtcl()->nonceText),
            'expiredOn'                      => __('Expired on:', 'classified-listing'),
            /* translators: 1: month, 2: day, 3: year, 4: hour, 5: minute */
            'dateFormat'                     => __('%1$s %2$s, %3$s @ %4$s:%5$s', 'classified-listing'),
            'i18n_delete_note'               => __('Are you sure you wish to delete this note? This action cannot be undone.', 'classified-listing')
        ));

        wp_register_style('rtcl-bootstrap', rtcl()->get_assets_uri("css/rtcl-bootstrap{$this->suffix}.css"), array(), '4.1.0');
        wp_register_style('rtcl-admin', rtcl()->get_assets_uri("css/rtcl-admin{$this->suffix}.css"), array('rtcl-bootstrap'), $this->version);
        wp_register_style('rtcl-gallery', rtcl()->get_assets_uri("css/rtcl-gallery{$this->suffix}.css"));
        wp_register_style('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css');
        wp_register_style('rtcl-admin-custom-fields', rtcl()->get_assets_uri("css/rtcl-admin-custom-fields{$this->suffix}.css"), '', $this->version);
    }

    function load_admin_script_page_custom_fields() {
        global $pagenow, $post_type;
        if (!in_array($pagenow, array('post.php', 'post-new.php', 'edit.php'))) {
            return;
        }
        if (rtcl()->post_type_cfg != $post_type) {
            return;
        }
        wp_enqueue_style('rtcl-admin');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('rtcl-admin-custom-fields');
        wp_enqueue_script('rtcl-admin-custom-fields');

        wp_localize_script('rtcl-admin-custom-fields', 'rtcl_cfg', array(
            "ajaxurl"       => admin_url("admin-ajax.php"),
            rtcl()->nonceId => wp_create_nonce(rtcl()->nonceText),
        ));

    }

    function load_admin_script_setting_page() {
        if (!empty($_GET['post_type']) && $_GET['post_type'] == rtcl()->post_type && !empty($_GET['page']) && $_GET['page'] == 'rtcl-settings') {
            wp_enqueue_media();
            wp_enqueue_style('rtcl-admin');
            wp_enqueue_script('rtcl-select2');
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('rt-field-dependency');
            wp_enqueue_script('rtcl-admin-settings');
        }
    }

    function load_admin_script_listing_types_page() {
        if (!empty($_GET['post_type']) && $_GET['post_type'] == rtcl()->post_type && !empty($_GET['page']) && $_GET['page'] == 'rtcl-listing-type') {
            wp_enqueue_style('rtcl-bootstrap');
            wp_enqueue_style('rtcl-admin');
            wp_enqueue_script('rtcl-admin-listing-type');
            wp_localize_script('rtcl-admin-listing-type', 'rtcl', array(
                "ajaxurl" => $this->ajaxurl,
                "nonceId" => rtcl()->nonceId,
                "nonce"   => wp_create_nonce(rtcl()->nonceText)
            ));
        }
    }

    function load_admin_script_ie_page() {
        if (!empty($_GET['post_type']) && $_GET['post_type'] == rtcl()->post_type && !empty($_GET['page']) && $_GET['page'] == 'rtcl-import-export') {
            wp_enqueue_style('rtcl-bootstrap');
            wp_enqueue_style('rtcl-admin');
            // Add the color picker css file
            wp_enqueue_script('rtcl-xlsx', rtcl()->get_assets_uri("vendor/xlsx.full.min.js"), array('jquery'),
                $this->version, true);
            wp_enqueue_script('rtcl - xml2json', rtcl()->get_assets_uri("vendor/xml2json.min.js"), array('jquery'),
                $this->version, true);
            wp_enqueue_script('rtcl-admin-ie');
            wp_localize_script('rtcl-admin-ie', 'rtcl', array(
                "ajaxurl"       => $this->ajaxurl,
                rtcl()->nonceId => wp_create_nonce(rtcl()->nonceText)
            ));
        }
    }

    function load_admin_script_post_type_listing() {
        global $pagenow, $post_type;
        // validate page
        if (!in_array($pagenow, array('post.php', 'post-new.php', 'edit.php'))) {
            return;
        }
        if (rtcl()->post_type != $post_type) {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('rtcl-timepicker');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('rtcl-validator');
        wp_enqueue_script('rtcl-select2');
        wp_enqueue_script('rtcl-admin');
        wp_enqueue_script('rtcl-gallery');
        wp_enqueue_script('suggest');

        wp_enqueue_style('jquery-ui');
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_style('rtcl-bootstrap');
        wp_enqueue_style('rtcl-admin');

    }

    function load_admin_script_payment() {
        global $pagenow, $post_type;
        // validate page
        if (!in_array($pagenow, array('post.php', 'post-new.php', 'edit.php'))) {
            return;
        }
        if (rtcl()->post_type_payment != $post_type) {
            return;
        }
        wp_enqueue_script('jquery');
        wp_enqueue_style('rtcl-admin');
        wp_enqueue_script('rtcl-validator');
        wp_enqueue_script('rtcl-select2');
        wp_enqueue_script('rtcl-admin');
    }

    function load_admin_script_pricing() {
        global $pagenow, $post_type;
        // validate page
        if (!in_array($pagenow, array('post.php', 'post-new.php', 'edit.php'))) {
            return;
        }
        if (rtcl()->post_type_pricing != $post_type) {
            return;
        }

        wp_enqueue_style('rtcl-bootstrap');
        wp_enqueue_style('rtcl-admin');

        wp_enqueue_script('rtcl-select2');
        wp_enqueue_script('rtcl-validator');
        wp_enqueue_script('rtcl-admin');
    }

    function load_admin_script_taxonomy() {
        global $pagenow, $post_type;
        // validate page
        if (!in_array($pagenow, array('term.php', 'edit-tags.php'))) {
            return;
        }
        if (rtcl()->post_type != $post_type) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('rtcl-admin');
        wp_enqueue_script('rtcl-select2');
        wp_enqueue_script('rtcl-admin-taxonomy');
    }


    private static function localize_script($handle) {
        if (!in_array($handle, self::$wp_localize_scripts, true) && wp_script_is($handle)) {
            $data = self::get_script_data($handle);

            if (!$data) {
                return;
            }

            $name = str_replace('-', '_', $handle) . '_params';
            self::$wp_localize_scripts[] = $handle;
            wp_localize_script($handle, $name, apply_filters($name, $data));
        }
    }

    /**
     * Return data for script handles.
     *
     * @param string $handle Script handle the data will be attached to.
     *
     * @return array|bool
     */
    private static function get_script_data($handle) {
        global $wp;

        switch ($handle) {
            case 'rtcl-public':
                $params = array();
                break;
            case 'rtcl-single-listing':
                $params = array(
                    'slider_options' => apply_filters(
                        'rtcl_single_listing_slider_options', array(
                            'rtl' => is_rtl()
                        )
                    )
                );
                break;
            default:
                $params = false;
        }

        return apply_filters('rtcl_get_script_data', $params, $handle);
    }


    /**
     * Localize scripts only when enqueued.
     */
    public static function localize_printed_scripts() {
        foreach (self::$scripts as $handle) {
            self::localize_script($handle);
        }
    }

}