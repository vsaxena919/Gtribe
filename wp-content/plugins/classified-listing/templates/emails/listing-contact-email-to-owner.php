<?php
/**
 * new listing email notification to owner
 * This template can be overridden by copying it to yourtheme/classified-listing/emails/new-post-notification-user.php
 * @author        RadiusTheme
 * @package       ClassifiedListing/Templates/Emails
 * @version       1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked RtclEmails::email_header() Output the email header
 */
do_action( 'rtcl_email_header', $email ); ?>
    <p><?php printf( esc_html__( 'Hi %s,', 'classified-listing' ), $listing->get_owner_name() ); ?></p>
    <p><?php printf( __( 'You have received a reply from your listing at <a href="%s">%s</a>' ), $listing->get_the_permalink(), $listing->get_the_title() ) ?></p>
    <p><?php printf( __( '<strong>Name:</strong> %s' ), $data['name'] ); ?></p>
    <p><?php printf( __( '<strong>Email:</strong> %s' ), $data['email'] ); ?></p>
    <p><?php printf( __( '<strong>Message:</strong> %s' ), $data['message'] ); ?></p>
<?php

/**
 * @hooked RtclEmails::email_footer() Output the email footer
 */
do_action( 'rtcl_email_footer', $email );
