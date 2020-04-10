<?php
/**
 * New order email to user
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/order-created-email-to-user.php
 *
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
    <p><?php /** @var Payment $order */
        printf(esc_html__('Hi %s,', 'classified-listing'), $order->get_customer_full_name()); ?></p>
    <p><?php printf(__('This notification was for the order <strong>#%s</strong> on the website <strong>%s</strong>'), $order->get_id(), $email->get_placeholders_item('{site_link}')) ?></p>
    <p><?php printf(__('You can access the order details directly by clicking on the link below after logging in your account: %s'), Link::get_payment_receipt_page_link($order->get_id())) ?></p>
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
