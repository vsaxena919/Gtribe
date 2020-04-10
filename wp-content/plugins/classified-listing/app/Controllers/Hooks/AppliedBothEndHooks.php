<?php

namespace Rtcl\Controllers\Hooks;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Text;
use Rtcl\Models\Listing;
use Rtcl\Models\Payment;
use Rtcl\Models\PaymentGateway;
use Rtcl\Models\Pricing;
use Rtcl\Resources\Options;

class AppliedBothEndHooks {

	static public function init() {
		add_action( 'rtcl_new_user_created', array( __CLASS__, 'new_user_notification_email_admin' ), 10 );
		add_action( 'rtcl_new_user_created', array( __CLASS__, 'new_user_notification_email_user' ), 10, 2 );
		add_action( 'rtcl_listing_form_after_save_or_update', array(
			__CLASS__,
			'new_post_notification_email_user_submitted'
		), 10, 4 );
		add_action( 'rtcl_listing_form_after_save_or_update', array(
			__CLASS__,
			'new_post_notification_email_user_published'
		), 20, 4 );
		add_action( 'rtcl_listing_form_after_save_or_update', array(
			__CLASS__,
			'new_post_notification_email_admin'
		), 30, 2 );
		add_action( 'rtcl_listing_form_after_save_or_update', array(
			__CLASS__,
			'update_post_notification_email_admin'
		), 40, 2 );

		add_filter( 'rtcl_my_account_endpoint', array( __CLASS__, 'my_account_end_point_filter' ), 10 );

		add_action( 'rtcl_listing_form_price_unit', array( __CLASS__, 'rtcl_listing_form_price_unit_cb' ) );
		add_filter( 'rtcl_formatted_price_html', array( __CLASS__, 'add_on_call_text_at_price' ), 10, 2 );
		add_action( 'rtcl_price_meta_html', array( __CLASS__, 'add_price_unit_to_price' ), 10 );
		add_action( 'rtcl_price_meta_html', array( __CLASS__, 'add_price_type_to_price' ), 20 );
		self::applyHook();


		add_filter( 'rtcl_checkout_process_validation', [ __CLASS__, 'add_rtcl_checkout_process_validation' ], 10, 4 );
		add_filter( 'rtcl_checkout_process_new_payment_args', [
			__CLASS__,
			'add_listing_id_at_regular_pricing'
		], 10, 4 );

		add_action( 'rtcl_checkout_process_success', [ __CLASS__, 'add_checkout_process_notice' ], 10 );
	}


	/**
	 * @param Payment $payment
	 */
	static function add_checkout_process_notice( $payment ) {
		if ( $payment->gateway ) {
			if ( 'paypal' === $payment->gateway->id ) {
				Functions::add_notice( __( "Redirecting to paypal.", "classified-listing" ), 'success' );
			} else if ( 'offline' === $payment->gateway->id ) {
				Functions::add_notice( __( "Payment made pending confirmation.", "classified-listing" ), 'success' );
			} else {
				Functions::add_notice( __( "Payment successfully made.", "classified-listing" ), 'success' );
			}
		}
	}

	/**
	 * @param bool $validation
	 * @param array $checkout_data
	 * @param Pricing $pricing
	 * @param PaymentGateway $gateway
	 *
	 * @return bool
	 */
	static function add_rtcl_checkout_process_validation( $validation, $checkout_data, $pricing, $gateway ) {
		if ( $pricing && $pricing->exists() && $gateway ) {
			$validation = true;
		}

		if ( 'regular' === $pricing->getType() && ( ! isset( $checkout_data['listing_id'] ) || ! rtcl()->factory->get_listing( $checkout_data['listing_id'] ) ) ) {
			$validation = false;
		}

		return $validation;
	}

	/**
	 * @param array $new_payment_args
	 * @param Pricing $pricing
	 * @param PaymentGateway $gateway
	 * @param array $checkout_data
	 *
	 * @return array
	 */
	static function add_listing_id_at_regular_pricing( $new_payment_args, $pricing, $gateway, $checkout_data ) {
		if ( 'regular' === $pricing->getType() ) {
			$new_payment_args['meta_input']['listing_id'] = isset( $checkout_data['listing_id'] ) ? absint( $checkout_data['listing_id'] ) : 0;
		}

		return $new_payment_args;
	}

	/**
	 * @param $formatted_price_html
	 * @param $listing Listing
	 *
	 * @return mixed|void
	 */
	public static function add_on_call_text_at_price( $formatted_price_html, $listing ) {
		if ( is_a( $listing, Listing::class ) ) {
			if ( $listing->get_price_type() == "on_call" ) {
				$formatted_price_html = sprintf( '<span class="rtcl-price-type-label rtcl-on_call">%s</span>', esc_html( Text::price_type_on_call() )
				);
			}
		}

		return $formatted_price_html;
	}

	/**
	 * @param $listing Listing
	 *
	 */
	public static function add_price_type_to_price( $listing ) {
		if ( is_a( $listing, Listing::class ) ) {
			$is_single  = Functions::get_option_item( 'rtcl_moderation_settings', 'display_options_detail', 'price_type', 'multi_checkbox' );
			$is_listing = Functions::get_option_item( 'rtcl_moderation_settings', 'display_options', 'price_type', 'multi_checkbox' );
			if ( ( $is_single && is_singular( rtcl()->post_type ) ) || ( $is_listing && ! is_singular( rtcl()->post_type ) ) ) {
				$price_type      = $listing->get_price_type();
				$price_type_html = null;
				if ( $price_type == "negotiable" ) {
					$price_type_html = sprintf( '<span class="rtcl-price-type-label rtcl-price-type-negotiable">(%s)</span>', esc_html( Text::price_type_negotiable() ) );
				} elseif ( $price_type == "fixed" ) {
					$price_type_html = sprintf( '<span class="rtcl-price-type-label rtcl-price-type-fixed">(%s)</span>', esc_html( Text::price_type_fixed() ) );
				}
				echo apply_filters( 'rtcl_add_price_type_to_price', $price_type_html, $price_type, $listing );
			}
		}
	}

	/**
	 * @param $listing Listing
	 */
	public static function add_price_unit_to_price( $listing ) {
		if ( is_a( $listing, Listing::class ) && $listing->get_price_type() !== 'on_call' && $price_unit = $listing->get_price_unit() ) {
			$price_unit_html = null;
			$price_units     = Options::get_price_unit_list();
			if ( in_array( $price_unit, array_keys( $price_units ) ) ) {
				$price_unit_html = sprintf( '<span class="rtcl-price-unit-label rtcl-price-unit-%s">%s</span>', $price_unit, $price_units[ $price_unit ]['short'] );
			}
			echo apply_filters( 'rtcl_add_price_unit_to_price', $price_unit_html, $price_unit, $listing );
		}
	}

	public static function my_account_end_point_filter( $endpoints ) {
		// Remove payment endpoint
		if ( Functions::is_payment_disabled() ) {
			unset( $endpoints['payments'] );
		}

		// Remove favourites endpoint
		if ( Functions::is_favourites_disabled() ) {
			unset( $endpoints['favourites'] );
		}

		return $endpoints;
	}

	static public function new_user_notification_email_admin( $user_id ) {
		if ( Functions::get_option_item( 'rtcl_email_settings', 'notify_admin', 'register_new_user', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['User_New_Registration_Email_To_Admin']->trigger( $user_id );
		}
	}

	static public function update_post_notification_email_admin( $post_id, $type ) {
		if ( $type == 'update' && Functions::get_option_item( 'rtcl_email_settings', 'notify_admin', 'listing_edited', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['Listing_Update_Email_To_Admin']->trigger( $post_id );
		}
	}

	static public function new_post_notification_email_admin( $post_id, $type ) {
		if ( $type == 'new' && Functions::get_option_item( 'rtcl_email_settings', 'notify_admin', 'listing_submitted', 'multi_checkbox' ) ) {
			rtcl()->mailer()->emails['Listing_Submitted_Email_To_Admin']->trigger( $post_id );
		}
	}


	static public function new_post_notification_email_user_submitted( $post_id, $type, $cat_id, $new_listing_status ) {
		if ( $type == 'new' && Functions::get_option_item( 'rtcl_email_settings', 'notify_users', 'listing_submitted', 'multi_checkbox' ) && $new_listing_status !== 'publish' ) {
			rtcl()->mailer()->emails['Listing_Submitted_Email_To_Owner']->trigger( $post_id );
		}
	}

	static public function new_post_notification_email_user_published( $post_id, $type, $cat_id, $new_listing_status ) {
		if ( $type == 'new' && Functions::get_option_item( 'rtcl_email_settings', 'notify_users', 'listing_published', 'multi_checkbox' ) && $new_listing_status === 'publish' ) {
			rtcl()->mailer()->emails['Listing_Published_Email_To_Owner']->trigger( $post_id );
		}
	}

	/**
	 * @param     $listing Listing
	 * @param int $category_id
	 */
	static public function rtcl_listing_form_price_unit_cb( $listing, $category_id = 0 ) {
		echo Functions::get_listing_form_price_unit_html( $category_id, $listing );
	}

	private static function applyHook() {
		/**
		 * Short Description (excerpt).
		 */
		if ( function_exists( 'do_blocks' ) ) {
			add_filter( 'rtcl_short_description', 'do_blocks', 9 );
		}
		add_filter( 'rtcl_short_description', 'wptexturize' );
		add_filter( 'rtcl_short_description', 'convert_smilies' );
		add_filter( 'rtcl_short_description', 'convert_chars' );
		add_filter( 'rtcl_short_description', 'wpautop' );
		add_filter( 'rtcl_short_description', 'shortcode_unautop' );
		add_filter( 'rtcl_short_description', 'prepend_attachment' );
		add_filter( 'rtcl_short_description', 'do_shortcode', 11 ); // After wpautop().
		add_filter( 'rtcl_short_description', array(
			Functions::class,
			'format_product_short_description'
		), 9999999 );
		add_filter( 'rtcl_short_description', array( Functions::class, 'do_oembeds' ) );
		add_filter( 'rtcl_short_description', array(
			$GLOBALS['wp_embed'],
			'run_shortcode'
		), 8 ); // Before wpautop().
	}

}