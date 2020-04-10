<?php

namespace Rtcl\Controllers\Hooks;


use Rtcl\Helpers\Functions;
use Rtcl\Shortcodes\Checkout;
use Rtcl\Shortcodes\MyAccount;

class TemplateHooks
{

    public static function init() {
        add_action('rtcl_account_navigation', array(__CLASS__, 'account_navigation'));
        add_action('rtcl_account_content', array(__CLASS__, 'account_content'));
        add_action('rtcl_account_listings_endpoint', array(__CLASS__, 'account_listings_endpoint'));
        add_action('rtcl_account_favourites_endpoint', array(__CLASS__, 'account_favourites_endpoint'));
        if (Functions::is_wc_active()) {
            add_action('rtcl_account_rtcl_edit_account_endpoint', array(__CLASS__, 'account_edit_account_endpoint'));
        } else {
            add_action('rtcl_account_edit-account_endpoint', array(__CLASS__, 'account_edit_account_endpoint'));
        }
        add_action('rtcl_account_payments_endpoint', array(__CLASS__, 'account_payments_endpoint'));

        add_action('rtcl_checkout_content', array(__CLASS__, 'checkout_content'));
        add_action('rtcl_checkout_submission_endpoint', array(__CLASS__, 'checkout_submission_endpoint'), 10, 2);
        add_action('rtcl_checkout_payment-receipt_endpoint', array(__CLASS__, 'checkout_payment_receipt_endpoint'), 10, 2);
    }

    public static function account_navigation() {
        Functions::get_template('myaccount/navigation');
    }

    public static function account_content() {
        global $wp;

        if (!empty($wp->query_vars)) {

            foreach ($wp->query_vars as $key => $value) {
                // Ignore pagename param.
                if ('pagename' === $key) {
                    continue;
                }

                if (has_action('rtcl_account_' . $key . '_endpoint')) {
                    do_action('rtcl_account_' . $key . '_endpoint', $value);

                    return;
                }
            }
        }

        // No endpoint found? Default to dashboard.
        Functions::get_template('myaccount/dashboard', array(
            'current_user' => get_user_by('id', get_current_user_id()),
        ));
    }

    public static function checkout_submission_endpoint($type, $listing_id) {
        Checkout::checkout_form($type, $listing_id);
    }

    public static function checkout_payment_receipt_endpoint($type, $payment_id) {
        Checkout::payment_receipt($payment_id);
    }

    public static function checkout_content() {
        global $wp;

        if (!empty($wp->query_vars)) {
            foreach ($wp->query_vars as $key => $value) {
                // Ignore pagename param.
                if ('pagename' === $key) {
                    continue;
                }

                if (has_action('rtcl_checkout_' . $key . '_endpoint')) {
                    do_action('rtcl_checkout_' . $key . '_endpoint', $key, $value);

                    return;
                }
            }
        }

        // No endpoint found? Default to error.
        Functions::get_template('checkout/error');
    }


    public static function account_edit_account_endpoint() {
        MyAccount::edit_account();
    }

    public static function account_listings_endpoint() {
        MyAccount::my_listings();
    }

    public static function account_favourites_endpoint() {
        MyAccount::favourite_listings();
    }

    public static function account_payments_endpoint() {
        MyAccount::payments_history();
    }

}