<?php

namespace Rtcl\Controllers;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Resources\Options;

class Query {

	/**
	 * Query vars to add to wp.
	 * @var array
	 */
	public $query_vars = array();

	/**
	 * Reference to the main product query on the page.
	 * @var array
	 */
	private static $product_query;

	/**
	 * Stores chosen attributes.
	 * @var array
	 */
	private static $_chosen_attributes;

	/**
	 * Constructor for the query class. Hooks in methods.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'add_endpoints' ) );
		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );
		}
	}

	/**
	 * Get any errors from querystring.
	 */
	public function get_errors() {
		$error = ! empty( $_GET['rtcl_error'] ) ? sanitize_text_field( wp_unslash( $_GET['rtcl_error'] ) ) : ''; // WPCS: input var ok, CSRF ok.

		if ( $error && ! Functions::has_notice( $error, 'error' ) ) {
			Functions::add_notice( $error, 'error' );
		}
	}

	/**
	 * Init query vars by loading options.
	 */
	public function init_query_vars() {

		// Query vars to add to WP.
		$this->query_vars = array_merge(
			Functions::get_my_account_page_endpoints(),
			Functions::get_checkout_page_endpoints()
		);
	}

	/**
	 * Endpoint mask describing the places the endpoint should be added.
	 * @return int
	 * @since 2.6.2
	 */
	public function get_endpoints_mask() {
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$page_on_front     = get_option( 'page_on_front' );
			$myaccount_page_id = Functions::get_option_item( 'rtcl_advanced_settings', 'myaccount' );
			$checkout_page_id  = Functions::get_option_item( 'rtcl_advanced_settings', 'checkout' );

			if ( in_array( $page_on_front, array( $myaccount_page_id, $checkout_page_id ), true ) ) {
				return EP_ROOT | EP_PAGES;
			}
		}

		return EP_PAGES;
	}

	/**
	 * Add endpoints for query vars.
	 */
	public function add_endpoints() {
		$this->init_query_vars();
		$mask = $this->get_endpoints_mask();
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( ! empty( $var ) ) {
				add_rewrite_endpoint( $var, $mask );
			}

		}

		$this->add_rewrites();
	}

	private function add_rewrites() {

		$page_settings = Functions::get_page_ids();
		$url           = home_url();


		// Listings Page
		if ( $id = Link::get_listing_page_id() ) {
			$category_slug = Link::get_category_slug();
			if ( Link::is_front_page( $id ) ) {
				$listing_page_slug = Link::get_listing_page_slug( $id );
				add_rewrite_rule( "$listing_page_slug/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&paged=$matches[1]', 'top' );
				add_rewrite_rule( "$listing_page_slug/$category_slug/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&rtcl_category=$matches[1]&paged=$matches[2]', 'top' );
				add_rewrite_rule( "$listing_page_slug/$category_slug/([^/]+)/?$", 'index.php?page_id=' . $id . '&rtcl_category=$matches[1]&lang=ar', 'top' );
				add_rewrite_rule( "$listing_page_slug/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]&rtcl_category=$matches[2]&paged=$matches[3]', 'top' );
				add_rewrite_rule( "$listing_page_slug/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]&paged=$matches[2]', 'top' );
				add_rewrite_rule( "$listing_page_slug/([^/]+)/([^/]+)/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]&rtcl_category=$matches[2]', 'top' );
				add_rewrite_rule( "$listing_page_slug/([^/]+)/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]', 'top' );
			} else {
				$link = str_replace( $url, '', get_permalink( $id ) );
				$link = trim( $link, '/' );
				add_rewrite_rule( "$link/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&paged=$matches[1]', 'top' );
				add_rewrite_rule( "$link/$category_slug/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&rtcl_category=$matches[1]&paged=$matches[2]', 'top' );
				add_rewrite_rule( "$link/$category_slug/([^/]+)/?$", 'index.php?page_id=' . $id . '&rtcl_category=$matches[1]', 'top' );
				add_rewrite_rule( "$link/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]&rtcl_category=$matches[2]&paged=$matches[3]', 'top' );
				add_rewrite_rule( "$link/([^/]+)/page/?([0-9]{1,})/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]&paged=$matches[2]', 'top' );
				add_rewrite_rule( "$link/([^/]+)/([^/]+)/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]&rtcl_category=$matches[2]', 'top' );
				add_rewrite_rule( "$link/([^/]+)/?$", 'index.php?page_id=' . $id . '&rtcl_location=$matches[1]', 'top' );
			}
		}

		// Listings Edit, Delete (or) Renew Pages
		$id = isset( $page_settings['listing_form'] ) ? absint( $page_settings['listing_form'] ) : 0;
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );
			$link = trim( $link, '/' );
			add_rewrite_rule( "$link/([^/]+)/([0-9]{1,})/?$",
				'index.php?page_id=' . $id . '&rtcl_action=$matches[1]&rtcl_listing_id=$matches[2]', 'top' );

		}
		$id = isset( $page_settings['myaccount'] ) ? absint( $page_settings['myaccount'] ) : 0;
		if ( $id > 0 ) {
			$link = str_replace( $url, '', get_permalink( $id ) );
			$link = trim( $link, '/' );
			foreach ( $this->get_query_vars() as $key => $var ) {
				if ( $key === "listings" || $key === "favourites" || $key === "payments" ) {
					add_rewrite_rule( "$link/$key/page/?([0-9]{1,})/?$", 'index.php?' . $var . '=&page_id=' . $id . '&paged=$matches[1]', 'top' );
				} elseif ( Functions::is_wc_active() && $key === "edit-account" ) {
					add_rewrite_rule( "$link/($var)/?$", 'index.php?page_id=' . $id . '&rtcl_edit_account=$matches[1]', 'top' );
					add_rewrite_tag( '%rtcl_edit_account%', '([^/]+)' );
				} elseif ( Functions::is_wc_active() && $key === "lost-password" ) {
					add_rewrite_rule( "$link/($var)/?$", 'index.php?page_id=' . $id . '&rtcl_lost_password=$matches[1]', 'top' );
					add_rewrite_tag( '%rtcl_lost_password%', '([^/]+)' );
				}
			}

		}

		// Rewrite tags
		add_rewrite_tag( '%rtcl_location%', '([^/]+)' );
		add_rewrite_tag( '%rtcl_category%', '([^/]+)' );
		add_rewrite_tag( '%rtcl_listing_id%', '([0-9]{1,})' );
		add_rewrite_tag( '%rtcl_action%', '([^&]+)' );
		add_rewrite_tag( '%rtcl_payment_id%', '([0-9]{1,})' );

		do_action( 'rtcl_add_rewrites', $page_settings );
	}

	/**
	 * Add query vars.
	 * @access public
	 *
	 * @param array $vars Query vars.
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;
	}

	/**
	 * Get query vars.
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'rtcl_get_query_vars', $this->query_vars );
	}

	/**
	 * Get query current active query var.
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;

		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}

		return '';
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

		// Map query vars to their keys, or get them if endpoints are not supported.
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) { // WPCS: input var ok, CSRF ok.
				$wp->query_vars[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $var ] ) ); // WPCS: input var ok, CSRF ok.
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}


	/**
	 * @return string  Link category Slug
	 */
	public static function get_category_slug() {
		$category_slug = apply_filters( 'rtcl_link_category_slug', 'category' );

		return $category_slug ? $category_slug : 'category';
	}


}