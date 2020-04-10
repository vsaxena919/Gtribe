<?php

namespace Rtcl\Controllers\Hooks;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;

class AppliedHooks
{

    public static function init() {
        add_filter('rtcl_listing_get_the_price', array(__CLASS__, 'price_for_job_type'), 10, 2);
        add_action('rtcl_login_form_end', array(__CLASS__, 'social_login_shortcode'), 10);

        add_action('rtcl_register_form', array(__CLASS__, 'registration_privacy_policy_text'), 20);


        add_action('rtcl_before_checkout_form', array(__CLASS__, 'add_checkout_form_instruction'), 10);
        add_action('rtcl_checkout_form_start', array(__CLASS__, 'add_checkout_form_promotion_options'), 10, 2);
        add_action('rtcl_checkout_form', array(__CLASS__, 'add_checkout_payment_method'), 10);
        add_action('rtcl_checkout_form', array(__CLASS__, 'checkout_terms_and_conditions'), 50);
        add_action('rtcl_checkout_form_submit_button', array(__CLASS__, 'checkout_form_submit_button'), 50);
        add_action('rtcl_checkout_form_end', array(__CLASS__, 'add_checkout_hidden_field'), 50, 2);
        add_action('rtcl_checkout_form_end', array(__CLASS__, 'add_submission_checkout_hidden_field'), 60, 2);


        add_action('rtcl_checkout_terms_and_conditions', array(__CLASS__, 'checkout_privacy_policy_text'), 20);
        add_action('rtcl_checkout_terms_and_conditions', array(
            __CLASS__,
            'checkout_terms_and_conditions_page_content'
        ), 30);
    }

    static function add_checkout_form_instruction() {
        ?>
        <p><?php esc_html_e('Please review your order, and click Purchase once you are ready to proceed.', 'classified-listing'); ?></p>
        <?php
    }

    static function add_checkout_form_promotion_options($type, $listing_id) {
        if ('submission' === $type) {
            if ($listing_id && rtcl()->post_type === get_post_type($listing_id)) {
                $pricing_options = Functions::get_regular_pricing_options();
                Functions::get_template("checkout/promotions", array(
                    'pricing_options' => $pricing_options,
                    'listing_id'      => $listing_id
                ));
            } else {
                Functions::add_notice(__("Given Listing Id is not a valid listing", "classified-listing"), "error");
                Functions::get_template("checkout/error");
            }
        }
    }

    static function add_checkout_payment_method() {
        Functions::get_template("checkout/payment-methods");
    }

    static function checkout_form_submit_button() {
        ?>
        <div class="rtcl-submit-btn-wrap d-md-flex justify-content-between">
            <a class="btn btn-primary"
               href="<?php echo esc_url(Link::get_my_account_page_link()) ?>"><?php esc_html_e("Go to My Account"); ?></a>
            <button type="submit" id="rtcl-checkout-submit-btn" name="rtcl-checkout" class="btn btn-primary"
                    value="1"><?php esc_html_e('Proceed to payment', 'classified-listing'); ?></button>
        </div>
        <?php
    }

    static function add_checkout_hidden_field($type) {
        wp_nonce_field('rtcl_checkout', 'rtcl_checkout_nonce');
        printf('<input type="hidden" name="type" value="%s"/>', esc_attr($type));
        ?><input type="hidden" name="action" value="rtcl_ajax_checkout_action"/><?php
    }

    static function add_submission_checkout_hidden_field($type, $listing_id) {
        if ('submission' === $type) {
            printf('<input type="hidden" name="listing_id" value="%d"/>', absint($listing_id));
        }
    }

    static function social_login_shortcode() {
        if (!apply_filters('rtcl_social_login_shortcode_disabled', false)) {
            $shortcode = apply_filters('rtcl_social_login_shortcode', Functions::get_option_item('rtcl_account_settings', 'social_login_shortcode', ''));
            if ($shortcode) {
                echo sprintf('<div class="rtcl-social-login-wrap">%s</div>', do_shortcode($shortcode));
            }
        }
    }


    public static function price_for_job_type($html_price, $listing_id) {
        $ad_type = get_post_meta($listing_id, 'ad_type', true);
        if ($ad_type == 'job') {
            $html_price = '';
        } elseif ($ad_type == 'to_let' && is_singular(rtcl()->post_type) && $position = strrpos($html_price, '</span>', -1)) {
            $html_price = substr_replace($html_price, sprintf("<span class='rtcl-per-unit'> / %s</span>", __("Month", "classified-listing")), $position, 0);
        }

        return $html_price;
    }


    /**
     * Render privacy policy text on the register forms.
     */
    public static function registration_privacy_policy_text() {
        Functions::privacy_policy_text('registration');
    }

    /**
     * Render privacy policy text on the checkout.
     */
    static function checkout_privacy_policy_text() {
        Functions::privacy_policy_text('checkout');
    }

    static function checkout_terms_and_conditions() {
        Functions::get_template("checkout/terms-conditions");
    }

    static function checkout_terms_and_conditions_page_content() {
        $terms_page_id = Functions::get_terms_and_conditions_page_id();

        if (!$terms_page_id) {
            return;
        }

        $page = get_post($terms_page_id);

        if ($page && 'publish' === $page->post_status && $page->post_content && !has_shortcode($page->post_content, 'rtcl_checkout')) {
            echo '<div class="rtcl-terms-and-conditions" style="display: none; max-height: 200px; overflow: auto;">' . wp_kses_post(Functions::format_content($page->post_content)) . '</div>';
        }
    }

}