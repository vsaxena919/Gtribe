<?php
/**
 * Order completed email to Admin
 * This template can be overridden by copying it to
 * yourtheme/classified-listing/emails/order-completed-email-to-admin.php
 *
 * @var Payment $order
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 */

use Rtcl\Helpers\Link;
use Rtcl\Models\Payment;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action('rtcl_email_header', $email); ?>
    <p><?php esc_html_e('Hi Administrator,', 'classified-listing'); ?></p>
    <p><?php printf(__('Order <strong>#%d</strong> is completed.', 'classified-listing'), $order->get_id()); ?></p>
<?php

/**
 * @hooked RtclEmails::order_details() Output the email order details
 */
do_action('rtcl_email_order_details', $order, $sent_to_admin, $email);

/**
 * @hooked RtclEmails::order_customer_details() Output the email order customer details
 */
do_action('rtcl_email_order_customer_details', $order, $sent_to_admin, $email);

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action('rtcl_email_footer', $email);
