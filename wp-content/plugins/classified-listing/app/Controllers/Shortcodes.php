<?php

namespace Rtcl\Controllers;


use Rtcl\Shortcodes\Categories;
use Rtcl\Shortcodes\Checkout;
use Rtcl\Shortcodes\FilterListings;
use Rtcl\Shortcodes\ListingForm;
use Rtcl\Shortcodes\Listings;
use Rtcl\Shortcodes\MyAccount;

class Shortcodes {

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public static function init() {
		$shortcodes = array(
			'rtcl_my_account'      => __CLASS__ . '::my_account',
			'rtcl_checkout'        => __CLASS__ . '::checkout',
			'rtcl_categories'      => __CLASS__ . '::categories',
			'rtcl_listing_form'    => __CLASS__ . '::listing_form',
			'rtcl_listings'        => __CLASS__ . '::listings',
			'rtcl_filter_listings' => __CLASS__ . '::filter_listings',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}

	}

	public static function shortcode_wrapper(
		$function,
		$atts = array(),
		$wrapper = array(
			'class'  => 'rtcl',
			'before' => null,
			'after'  => null,
		)
	) {
		ob_start();

		// @codingStandardsIgnoreStart
		echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
		call_user_func( $function, $atts );
		echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

		// @codingStandardsIgnoreEnd

		return ob_get_clean();
	}

	/**
	 * My account page shortcode.
	 *
	 * @param array $atts Attributes.
	 *
	 * @return string
	 */
	public static function my_account( $atts ) {
		return self::shortcode_wrapper( array( MyAccount::class, 'output' ), $atts );
	}

	public static function checkout( $atts ) {
		return self::shortcode_wrapper( array( Checkout::class, 'output' ), $atts );
	}

	public static function categories( $atts ) {
		return self::shortcode_wrapper( array( Categories::class, 'output' ), $atts );
	}

	public static function listing_form( $atts ) {
		return self::shortcode_wrapper( array( ListingForm::class, 'output' ), $atts );
	}

	public static function listings( $atts ) {
		return self::shortcode_wrapper( array( Listings::class, 'output' ), $atts );
	}

	public static function filter_listings( $atts ) {
		return self::shortcode_wrapper( array( FilterListings::class, 'output' ), $atts );
	}

}