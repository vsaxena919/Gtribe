<?php

namespace Rtcl\Controllers;


use Exception;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Shortcodes\MyAccount;

class FormHandler
{

    public static function init() {
        add_action('template_redirect', array(__CLASS__, 'redirect_reset_password_link'));
        add_action('wp_loaded', array(__CLASS__, 'process_checkout'), 20);
        add_action('wp_loaded', array(__CLASS__, 'process_login'), 20);
        add_action('wp_loaded', array(__CLASS__, 'process_registration'), 20);
        add_action('wp_loaded', array(__CLASS__, 'process_lost_password'), 20);
        add_action('wp_loaded', array(__CLASS__, 'process_reset_password'), 20);
    }


    /**
     * @throws \Exception
     */
    static function process_checkout() {
        if (wp_doing_ajax()) {
            return false;
        }
        if (isset($_POST['rtcl-checkout']) && isset($_POST['rtcl_checkout_nonce']) && wp_verify_nonce($_POST['rtcl_checkout_nonce'], 'rtcl_checkout')) {

            $pricing_id = isset($_REQUEST['pricing_id']) ? absint($_REQUEST['pricing_id']) : 0;
            $payment_method = isset($_REQUEST['payment_method']) ? sanitize_key($_POST['payment_method']) : '';
            $gateway = Functions::get_payment_gateway($payment_method);
            $pricing = rtcl()->factory->get_pricing($pricing_id);
            $checkout_data = apply_filters('rtcl_checkout_process_data', wp_parse_args($_REQUEST, [
                'type'           => '',
                'listing_id'     => 0,
                'pricing_id'     => $pricing_id,
                'payment_method' => $payment_method
            ]));
            $validation = apply_filters('rtcl_checkout_process_validation', false, $checkout_data, $pricing, $gateway);
            if (!$validation) {
                Functions::add_notice(__("Please select required field.", "classified-listing"), 'error');

                return false;
            }
            rtcl()->session->set('order_awaiting_payment', '');
            if ($pricing->getPrice() > 0) {
                try {
                    $cart = rtcl()->cart;
                    $cart->empty_cart();
                    if ($cart_id = $cart->add_to_cart($pricing->getId(), 1, $checkout_data)) {
                        do_action("rtcl_process_checkout_handler", $pricing, $cart_id, $checkout_data);
                    }

                } catch (Exception $e) {
                    if ($e->getMessage()) {
                        Functions::add_notice($e->getMessage(), 'error');
                    }

                    return false;
                }
            } else {
                $gateway = Functions::get_payment_gateway('offline');
                $new_payment_args = array(
                    'post_title'  => __('Order on', 'classified-listing') . ' ' . current_time("l jS F Y h:i:s A"),
                    'post_status' => 'rtcl-created',
                    'post_parent' => '0',
                    'ping_status' => 'closed',
                    'post_author' => 1,
                    'post_type'   => rtcl()->post_type_payment,
                    'meta_input'  => [
                        'customer_id'           => get_current_user_id(),
                        'customer_ip_address'   => Functions::get_ip_address(),
                        '_order_key'            => apply_filters('rtcl_generate_order_key', uniqid('rtcl_oder_')),
                        '_pricing_id'           => $pricing->getId(),
                        'amount'                => Functions::get_formatted_amount($pricing->getPrice(), true),
                        '_payment_method'       => $gateway->id,
                        '_payment_method_title' => $gateway->method_title,
                    ]
                );
                $payment_id = wp_insert_post(apply_filters('rtcl_checkout_process_new_payment_args', $new_payment_args, $pricing, $gateway, $checkout_data));

                if ($payment_id) {
                    $payment = rtcl()->factory->get_order($payment_id);
                    $payment->payment_complete(wp_generate_password(12, true));
                    $redirect_url = Link::get_payment_receipt_page_link($payment_id);
                    Functions::add_notice(__("Payment successfully made.", "classified-listing"), 'success');
                    do_action('rtcl_checkout_process_success_no_amount', $payment);
                    wp_redirect($redirect_url);
                    exit();
                }
            }
        }


        return true;
    }

    /**
     * Remove key and login from query string, set cookie, and redirect to account page to show the form.
     */
    public static function redirect_reset_password_link() {
        if (Functions::is_account_page() && !empty($_GET['key']) && !empty($_GET['login'])) {
            $value = sprintf('%s:%s', wp_unslash($_GET['login']), wp_unslash($_GET['key']));
            MyAccount::set_reset_password_cookie($value);

            wp_safe_redirect(add_query_arg('show-reset-form', 'true', Link::get_my_account_page_link('lost-password')));
            exit;
        }
    }

    public static function process_login() {

        $nonce_value = Functions::get_var($_REQUEST['rtcl-login-nonce']);
        if (!empty($_POST['rtcl-login']) && wp_verify_nonce($nonce_value, 'rtcl-login')) {

            try {
                $creds = array(
                    'user_login'    => trim($_POST['username']),
                    'user_password' => $_POST['password'],
                    'remember'      => isset($_POST['rememberme']),
                );

                $validation_error = new \WP_Error();
                $validation_error = apply_filters('rtcl_process_login_errors', $validation_error, $_POST['username'], $_POST['password']);

                if ($validation_error->get_error_code()) {
                    throw new \Exception('<strong>' . __('Error:', 'classified-listing') . '</strong> ' . $validation_error->get_error_message());
                }

                if (empty($creds['user_login'])) {
                    throw new \Exception('<strong>' . __('Error:', 'classified-listing') . '</strong> ' . __('Username is required.', 'woocommerce'));
                }

                // On multisite, ensure user exists on current site, if not add them before allowing login.
                if (is_multisite()) {
                    $user_data = get_user_by(is_email($creds['user_login']) ? 'email' : 'login', $creds['user_login']);

                    if ($user_data && !is_user_member_of_blog($user_data->ID, get_current_blog_id())) {
                        add_user_to_blog(get_current_blog_id(), $user_data->ID, 'customer');
                    }
                }

                // Perform the login
                $user = wp_signon(apply_filters('rtcl_login_credentials', $creds), is_ssl());

                if (is_wp_error($user)) {
                    $message = $user->get_error_message();
                    $message = str_replace('<strong>' . esc_html($creds['user_login']) . '</strong>', '<strong>' . esc_html($creds['user_login']) . '</strong>', $message);
                    throw new \Exception($message);
                } else {

                    if (!empty($_POST['redirect'])) {
                        $redirect = $_POST['redirect'];
                    } elseif (Functions::get_raw_referer()) {
                        $redirect = Functions::get_raw_referer();
                    } else {
                        $redirect = Link::get_my_account_page_link();
                    }

                    wp_redirect(wp_validate_redirect(apply_filters('rtcl_login_redirect', remove_query_arg('wc_error', $redirect), $user), Link::get_my_account_page_link()));
                    exit;
                }
            } catch (\Exception $e) {
                Functions::add_notice(apply_filters('login_errors', $e->getMessage()), 'error');
                do_action('rtcl_login_failed');
            }
        }
    }

    public static function process_registration() {
        $nonce_value = isset($_POST['rtcl-register-nonce']) ? $_POST['rtcl-register-nonce'] : null;

        if (!empty($_POST['rtcl-register']) && wp_verify_nonce($nonce_value, 'rtcl-register')) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $email = $_POST['email'];

            try {
                $validation_error = new \WP_Error();
                $validation_error = apply_filters('rtcl_process_registration_errors', $validation_error, $username, $password, $email);

                if ($validation_error->get_error_code()) {
                    throw new \Exception($validation_error->get_error_message());
                }

                $new_user = Functions::create_new_user(sanitize_email($email), Functions::clean($username), $password);

                if (is_wp_error($new_user)) {
                    throw new \Exception($new_user->get_error_message());
                }

                if (apply_filters('rtcl_registration_auth_new_user', true, $new_user)) {
                    Functions::set_customer_auth_cookie($new_user);
                }

                if (!empty($_POST['redirect'])) {
                    $redirect = wp_sanitize_redirect($_POST['redirect']);
                } elseif (Functions::get_raw_referer()) {
                    $redirect = Functions::get_raw_referer();
                } else {
                    $redirect = Link::get_page_permalink('myaccount');
                }

                wp_redirect(wp_validate_redirect(apply_filters('rtcl_registration_redirect', $redirect), Link::get_page_permalink('myaccount')));
                exit;

            } catch (\Exception $e) {
                Functions::add_notice('<strong>' . __('Error:', 'classified-listing') . '</strong> ' . $e->getMessage(), 'error');
            }
        }
    }


    /**
     * Handle lost password form.
     */
    public static function process_lost_password() {
        $nonce_value = Functions::get_var($_REQUEST['rtcl-lost-password-nonce']);
        if (isset($_POST['rtcl-lost-password']) && isset($_POST['user_login']) && $nonce_value && wp_verify_nonce($nonce_value, 'rtcl-lost-password')) {
            $success = MyAccount::retrieve_password();

            // If successful, redirect to my account with query arg set.
            if ($success) {
                wp_redirect(add_query_arg('reset-link-sent', 'true', Link::get_account_endpoint_url('lost-password')));
                exit;
            }
        }
    }

    /**
     * Handle reset password form.
     */
    public static function process_reset_password() {
        $posted_fields = array(
            'rtcl-reset-password',
            'password_1',
            'password_2',
            'reset_key',
            'reset_login',
            '_wpnonce'
        );
        foreach ($posted_fields as $field) {
            if (!isset($_POST[$field])) {
                return;
            }
            $posted_fields[$field] = $_POST[$field];
        }

        if (!wp_verify_nonce($posted_fields['_wpnonce'], 'reset_password')) {
            return;
        }

        $user = MyAccount::check_password_reset_key($posted_fields['reset_key'], $posted_fields['reset_login']);

        if ($user instanceof \WP_User) {
            if (empty($posted_fields['password_1'])) {
                Functions::add_notice(__('Please enter your password.', 'classified-listing'), 'error');
            }

            if ($posted_fields['password_1'] !== $posted_fields['password_2']) {
                Functions::add_notice(__('Passwords do not match.', 'classified-listing'), 'error');
            }

            $errors = new \WP_Error();

            do_action('validate_password_reset', $errors, $user);

            Functions::add_wp_error_notices($errors);

            if (0 === Functions::notice_count('error')) {
                MyAccount::reset_password($user, $posted_fields['password_1']);

                do_action('rtcl_reset_password', $user);

                wp_redirect(add_query_arg('password-reset', 'true', Link::get_page_permalink('myaccount')));
                exit;
            }
        }
    }


}