<?php

namespace Rtcl\Shortcodes;


use Rtcl\Controllers\Shortcodes;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;

class Checkout {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}


	/**
	 * Output the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 */
	public static function output( $atts ) {
		global $wp;

		if ( ! is_user_logged_in() ) {
			$message = apply_filters( 'rtcl_checkout_message', '' );

			if ( ! empty( $message ) ) {
				Functions::add_notice( $message );
			}

			Functions::add_notice( __( "Need to login to access this page", 'classified-listing' ), 'error' );

			Functions::login_form();
		} else {
			// Start output buffer since the html may need discarding for BW compatibility
			ob_start();

			Functions::get_template( 'checkout/checkout' );

			// Send output buffer
			ob_end_flush();
		}
	}

	public static function submission_payment( $listing_id ) {
		if ( $listing_id && rtcl()->post_type === get_post_type( $listing_id ) ) {
			$pricing_options = get_posts( array(
				'post_type'        => rtcl()->post_type_pricing,
				'posts_per_page'   => - 1,
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
			) );

			$moderation_settings = Functions::get_option( 'rtcl_moderation_settings' );
			Functions::get_template( "checkout/submission", array(
				'pricing_options'     => $pricing_options,
				'moderation_settings' => $moderation_settings,
				'post_id'             => $listing_id
			) );
		} else {
			Functions::add_notice( __( "Given Listing Id is not a valid listing", "classified-listing" ), "error" );
			Functions::get_template( "checkout/error" );
		}
	}

	public static function checkout_form( $type, $value ) {
		Functions::get_template( 'checkout/form', compact( 'type', 'value' ) );
	}

	public static function payment_receipt( $payment_id ) {
		if ( $payment_id && ( $payment = rtcl()->factory->get_order( $payment_id ) ) && $payment->exists() ) {
			Functions::get_template( "checkout/payment-receipt", compact( 'payment' ) );
		} else {
			Functions::add_notice( __( "Given Payment Id is not a valid payment.", "classified-listing" ), "error" );
			Functions::get_template( "checkout/error" );
		}
	}
}