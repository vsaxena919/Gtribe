<?php

if (!class_exists('CFAdvanced')) {

    class CFAdvanced {

        public static function init() {
            //Payment tab settings hooks
            add_action('woocommerce_update_options_crowdfunding_advanced', array(__CLASS__, 'crowdfunding_process_advanced_update_settings'));
            add_action('init', array(__CLASS__, 'crowdfunding_advanced_default_settings'));
            add_action('woocommerce_cf_settings_tabs_crowdfunding_advanced', array(__CLASS__, 'crowdfunding_process_advanced_admin_settings'));
            add_filter('woocommerce_cf_settings_tabs_array', array(__CLASS__, 'crowdfunding_admin_advanced_tab'), 100);
            add_action('admin_init', array(__CLASS__, 'cf_advanced_reset_values'), 2);
        }

        public static function crowdfunding_admin_advanced_tab($settings_tabs) {
            if (!is_array($settings_tabs)) {
                $settings_tabs = (array) $settings_tabs;
            }
            $settings_tabs['crowdfunding_advanced'] = __('Advanced', 'galaxyfunder');
            return $settings_tabs;
        }

        public static function crowdfunding_advanced_admin_options() {
            return apply_filters('woocommerce_crowdfunding_advanced_settings', array(
                array(
                    'name' => __('Strict Mode Settings', 'galaxyfunder'),
                    'type' => 'title',
                    'desc' => '',
                    'id' => '_cf_strictmode_inbuilt_text'
                ),
                array(
                    'name' => __('Strict Mode:', 'galaxyfunder'),
                    'desc' => __('When set to allow contributors will not be able to contribute more than the target goal', 'galaxyfunder'),
                    'tip' => '',
                    'css' => 'min-width:150px;',
                    'id' => 'cf_strictmode_campaign_id',
                    'std' => '1', // WooCommerce < 2.0
                    'default' => '1', // WooCommerce >= 2.0
                    'type' => 'select',
                    'options' => array(
                            '1' => __('Do not Allow', 'galaxyfunder'),
                            '2' => __('Allow', 'galaxyfunder'),),
                    'newids' => 'cf_strictmode_campaign_id',
                    'desc_tip' => false,
                ),
                array('type' => 'sectionend', 'id' => '_cf_strictmode_inbuilt_text'),
            ));
        }

        public static function crowdfunding_process_advanced_admin_settings() {
            woocommerce_admin_fields(CFAdvanced::crowdfunding_advanced_admin_options());
        }

        public static function crowdfunding_process_advanced_update_settings() {
            woocommerce_update_options(CFAdvanced::crowdfunding_advanced_admin_options());
        }

        public static function crowdfunding_advanced_default_settings() {
            foreach (CFAdvanced::crowdfunding_advanced_admin_options() as $setting) {
                if (isset($setting['newids']) && ($setting['std'])) {
                    if (get_option($setting['newids']) == FALSE) {
                        add_option($setting['newids'], $setting['std']);
                    }
                }
            }
        }

        public static function cf_advanced_reset_values() {
            if (isset($_POST['reset'])) {
                if ($_POST['reset_hidden'] == 'crowdfunding_advanced') {
                    echo FP_GF_Common_Functions::reset_common_function(CFAdvanced::crowdfunding_advanced_admin_options());
                }
            }

            if (isset($_POST['resetall'])) {
                echo FP_GF_Common_Functions::reset_common_function(CFAdvanced::crowdfunding_advanced_admin_options());
            }
        }

    }

    CFAdvanced::init();
}
