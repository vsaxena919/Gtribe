<?php

namespace Rtcl\Controllers;

use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Models\RtclEmail;

class RtclPublic {

	public function __construct() {

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'wp_loaded', array( $this, 'maybe_flush_rules' ) );
		add_action( 'wp_title', array( $this, 'wp_title' ), 99, 3 );
		add_action( 'wp_head', array( $this, 'og_metatags' ) );

		add_filter( 'force_ssl', array( $this, 'force_ssl_https' ), 10, 2 );
		add_filter( 'pre_get_document_title', array( $this, 'pre_get_document_title' ), 999 );
		add_filter( 'document_title_parts', array( $this, 'document_title_parts' ) );
		add_filter( 'the_title', array( $this, 'the_title' ), 99 );
		add_filter( 'lostpassword_url', array( __CLASS__, 'lostpassword_url' ), 10, 1 );
		add_action( 'the_content', array( __CLASS__, 'the_content' ), 20 );
		add_action( 'wp_head', array( __CLASS__, 'add_views_counter' ) );
	}

	public static function add_views_counter() {
		if ( is_singular( rtcl()->post_type ) && is_main_query() ) {
			global $post;
			Functions::update_listing_views_count( $post->ID );
		}
	}


	public static function the_content( $content ) {

		if ( is_singular( rtcl()->post_type ) && in_the_loop() && is_main_query() ) {
			global $post;
			ob_start();
			do_action( 'rtcl_before_listing_content' );
			$sidebar_position = Functions::get_option_item( 'rtcl_moderation_settings', 'detail_page_sidebar_position', 'right' );
			$sidebar_class    = array(
				'col-md-3',
				'order-2'
			);
			$content_class    = array(
				'col-md-9',
				'order-1',
				'listing-content'
			);
			if ( $sidebar_position == "left" ) {
				$sidebar_class   = array_diff( $sidebar_class, array( 'order-2' ) );
				$sidebar_class[] = 'order-1';
				$content_class   = array_diff( $content_class, array( 'order-1' ) );
				$content_class[] = 'order-2';
			} else if ( $sidebar_position == "bottom" ) {
				$content_class   = array_diff( $content_class, array( 'col-md-9' ) );
				$sidebar_class   = array_diff( $sidebar_class, array( 'col-md-3' ) );
				$content_class[] = 'col-sm-12';
				$sidebar_class[] = 'rtcl-listing-bottom-sidebar';
			}
			Functions::get_template( "listings/single/single-content", array(
				'listing_id'       => $post->ID,
				'sidebar_position' => $sidebar_position,
				'content'          => $content,
				'sidebar_class'    => implode( ' ', $sidebar_class ),
				'content_class'    => implode( ' ', $content_class )
			) );

			do_action( 'rtcl_after_listing_content' );
			$content = ob_get_clean();

		}

		return $content;
	}

	/**
	 * Returns the url to the lost password endpoint url.
	 *
	 * @param string $default_url Default lost password URL.
	 *
	 * @return string
	 */
	public static function lostpassword_url( $default_url = '' ) {
		return Link::lostpassword_url( $default_url );
	}


	public function template_redirect() {
		$redirect_url = '';

		if ( ! is_feed() ) {

			// If Listings Page
			if ( is_post_type_archive( rtcl()->post_type ) ) {

				$redirect_url = Link::get_listings_page_link( true );

			} // If Locations Page
			else if ( is_tax( rtcl()->location ) ) {
				$term         = get_queried_object();
				$redirect_url = Link::get_location_page_link( $term );

			} // If Categories Page
			else if ( is_tax( rtcl()->category ) ) {

				$term         = get_queried_object();
				$redirect_url = Link::get_category_page_link( $term );

			}
		}


		// Logout
		global $wp;
		if ( isset( $wp->query_vars['logout'] ) && ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'logout' ) ) {
			wp_safe_redirect( str_replace( '&amp;', '&', wp_logout_url( Link::get_my_account_page_link() ) ) );
			exit;
		}

		// Redirect
		if ( ! empty( $redirect_url ) ) {

			wp_redirect( $redirect_url );
			exit();

		}
	}


	public function maybe_flush_rules() {

		$rewrite_rules = get_option( 'rewrite_rules' );

		if ( $rewrite_rules ) {

			global $wp_rewrite;
			$rewrite_rules_array = array();
			foreach ( $rewrite_rules as $rule => $rewrite ) {
				$rewrite_rules_array[ $rule ]['rewrite'] = $rewrite;
			}
			$rewrite_rules_array = array_reverse( $rewrite_rules_array, true );

			$maybe_missing = $wp_rewrite->rewrite_rules();
			$missing_rules = false;

			foreach ( $maybe_missing as $rule => $rewrite ) {
				if ( ! array_key_exists( $rule, $rewrite_rules_array ) ) {
					$missing_rules = true;
					break;
				}
			}

			if ( true === $missing_rules ) {
				flush_rewrite_rules();
			}

		}

	}

	public function wp_title( $title, $sep, $seplocation ) {

		global $post;

		if ( ! isset( $post ) ) {
			return $title;
		}

		$page_settings = Functions::get_page_ids();
		$custom_title  = '';
		$site_name     = get_bloginfo( 'name' );

		// Get Location page title
		if ( $post->ID == $page_settings['location'] ) {

			if ( $slug = get_query_var( 'rtcl_location' ) ) {
				$term         = get_term_by( 'slug', $slug, rtcl()->location );
				$custom_title = $term->name;
			}

		}

		// Get Category page title
		if ( $post->ID == $page_settings['category'] ) {

			if ( $slug = get_query_var( 'rtcl_category' ) ) {
				$term         = get_term_by( 'slug', $slug, 'rtcl_categories' );
				$custom_title = $term->name;
			}

		}

		// Get User Listings page title
		if ( $post->ID == $page_settings['user_listings'] ) {

			if ( $slug = get_query_var( 'rtcl_user' ) ) {
				$user         = get_user_by( 'slug', $slug );
				$custom_title = Functions::get_author_name($user);
			}

		}

		// ...
		if ( ! empty( $custom_title ) ) {
			$title = ( 'left' == $seplocation ) ? "$site_name $sep $custom_title" : "$custom_title $sep $site_name";
		}

		return $title;

	}

	public function og_metatags() {
		global $post;

		if ( ! isset( $post ) ) {
			return;
		}

		$page_settings = Functions::get_page_ids();
		$page          = '';
		if ( is_singular( rtcl()->post_type ) ) {

			$page = 'listing';

		} else {
			if ( $page_settings['listings'] == $post->ID ) {
				$page = 'listings';
			}

		}

		if ( Functions::get_option_item( 'rtcl_misc_settings', 'social_pages', $page, 'multi_checkbox' ) ) {

			$title = get_the_title();

			// Get Location page title
			if ( get_query_var( 'rtcl_location' ) || get_query_var( 'rtcl_category' ) ) {

				$title = Functions::get_single_term_title();
			}


			echo '<meta property="og:url" content="' . Link::get_current_url() . '" />';
			echo '<meta property="og:type" content="article" />';
			echo '<meta property="og:title" content="' . $title . '" />';
			if ( 'listing' == $page ) {
				if ( ! empty( $post->post_content ) ) {
					echo '<meta property="og:description" content="' . wp_trim_words( $post->post_content,
							150 ) . '" />';
				}

				$attachments = get_children( array(
					'post_parent'    => $post->ID,
					'post_type'      => 'attachment',
					'posts_per_page' => - 1,
					'post_status'    => 'inherit'
				) );
				if ( ! empty( $attachments ) ) {
					$thumbnail = wp_get_attachment_image_src( reset( $attachments )->ID, 'full' );
					if ( ! empty( $thumbnail ) ) {
						echo '<meta property="og:image" content="' . $thumbnail[0] . '" />';
					}
				}
			}
			echo '<meta property="og:site_name" content="' . get_bloginfo( 'name' ) . '" />';
			echo '<meta name="twitter:card" content="summary">';

		}
	}

	public function force_ssl_https( $force_ssl, $post_id ) {

		$page_settings = Functions::get_page_ids();

		if ( $post_id == ( int ) $page_settings['checkout'] ) {

			$payment_settings = Functions::get_option( 'rtcl_payment_settings' );

			if ( ! empty( $payment_settings['use_https'] ) ) {
				return true;
			}

		}

		return $force_ssl;

	}

	public function pre_get_document_title( $title ) {

		global $post;

		if ( ! isset( $post ) ) {
			return $title;
		}

		return $title;

	}

	public function document_title_parts( $title ) {

		global $post;

		if ( ! isset( $post ) ) {
			return $title;
		}

		$page_settings = Functions::get_page_ids();
		// Get Category page title
		if ( $post->ID == $page_settings['listings'] ) {

			if ( $slug = get_query_var( 'rtcl_category' ) ) {
				$term = get_term_by( 'slug', $slug, rtcl()->category );
				if ( is_object( $term ) ) {
					$title['title'] = $term->name;
				}
			}


			if ( $slug = get_query_var( 'rtcl_location' ) ) {
				$term = get_term_by( 'slug', $slug, rtcl()->location );
				if ( is_object( $term ) ) {
					$title['title'] = $term->name;
				}
			}

		}

		// ...
		return $title;

	}

	public function the_title( $title ) {

		if ( ! in_the_loop() || ! is_main_query() ) {
			return $title;
		}

//		if ( is_singular( rtcl()->post_type ) ) {
//			return '';  // TODO : Need to add this 
//		}

		global $post;

		$page_settings = Functions::get_page_ids();

		// Change Location page title
		if ( ! empty( $page_settings['listings'] ) && $post->ID == $page_settings['listings'] ) {
			$inTitle = null;
			if ( $slug = get_query_var( 'rtcl_location' ) ) {
				$term = get_term_by( 'slug', $slug, rtcl()->location );
				if ( is_object( $term ) ) {
					$inTitle[] = $term->name;
				}
			}

			if ( $slug = get_query_var( 'rtcl_category' ) ) {
				$term = get_term_by( 'slug', $slug, rtcl()->category );
				if ( is_object( $term ) ) {
					$inTitle [] = $term->name;
				}
			}

			if ( ! empty( $inTitle ) ) {
				$title = implode( " > ", $inTitle );
			}

		}

		return $title;

	}

}