<?php

namespace Rtcl\Shortcodes;


use Rtcl\Controllers\Shortcodes;
use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Pagination;
use Rtcl\Helpers\Text;
use Rtcl\Log\Logger;
use Rtcl\Resources\Options;

class MyAccount {

	/**
	 * Get the shortcode content.
	 *
	 * @param array $atts  Shortcode attributes.
	 *
	 * @return string
	 */
	public static function get( $atts ) {
		return Shortcodes::shortcode_wrapper( array( __CLASS__, 'output' ), $atts );
	}


	/**
	 * Output the shortcode.
	 *
	 * @param array $atts  Shortcode attributes.
	 */
	public static function output( $atts ) {
		global $wp;

		if ( ! is_user_logged_in() ) {
			$message = apply_filters( 'rtcl_my_account_message', '' );

			if ( ! empty( $message ) ) {
				Functions::add_notice( $message );
			}

			// After password reset, add confirmation message.
			if ( ! empty( $_GET['password-reset'] ) ) {
				Functions::add_notice( __( 'Your password has been reset successfully.', 'classified-listing' ) );
			}

            if ( isset( $wp->query_vars['lost-password'] ) || (isset( $wp->query_vars['rtcl_lost_password'] ) && $wp->query_vars['rtcl_lost_password'] == 'lost-password')) {
                self::lost_password();
            } else {
				Functions::get_template( "myaccount/form-login" );
			}
		} else {
			// Start output buffer since the html may need discarding for BW compatibility
			ob_start();

			Functions::get_template( 'myaccount/my-account', array(
				'user' => get_user_by( 'id', get_current_user_id() )
			) );

			// Send output buffer
			ob_end_flush();
		}
	}

	public static function favourite_listings() {
		wp_enqueue_script( 'rtcl-public' );

		// Define the query
		$paged            = Pagination::get_page_number();
		$favourite_posts  = get_user_meta( get_current_user_id(), 'rtcl_favourites', true );
		$general_settings = Functions::get_option( 'rtcl_general_settings' );
		$args             = array(
			'post_type'      => rtcl()->post_type,
			'post_status'    => 'publish',
			'posts_per_page' => isset( $general_settings['listings_per_page'] ) ? $general_settings['listings_per_page'] : 10,
			'paged'          => $paged,
			'post__in'       => ! empty( $favourite_posts ) ? $favourite_posts : array( 0 )
		);

		$rtcl_query = new \WP_Query( $args );

		Functions::get_template( "myaccount/favourite-listings", array(
			'rtcl_query' => $rtcl_query,
			'paged'      => $paged
		) );
	}

	public static function payments_history() {
		$general_settings = Functions::get_option( 'rtcl_general_settings' );

		wp_enqueue_script( 'rtcl-public' );

		// Define the query
		$paged = Pagination::get_page_number();

		$args = array(
			'post_type'      => rtcl()->post_type_payment,
			'post_status'    => array_keys( Options::get_payment_status_list( true ) ),
			'posts_per_page' => isset( $general_settings['listings_per_page'] ) ? $general_settings['listings_per_page'] : 10,
			'paged'          => $paged,
			'meta_query'     => array(
				array(
					'key'     => 'customer_id',
					'value'   => get_current_user_id(),
					'compare' => '=',
				),
			)
		);

		$rtcl_query = new \WP_Query( apply_filters( 'rtcl_payment_history_args', $args ) );
		Functions::get_template( "myaccount/payment-history", array(
			'rtcl_query' => $rtcl_query,
			'paged'      => $paged
		) );
	}

	public static function my_listings() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'rtcl-public' );
		$general_settings = Functions::get_option( 'rtcl_general_settings' );

		// Enqueue style dependencies
		wp_enqueue_script( 'rtcl-public' );

		// Define the query
		$paged = Pagination::get_page_number();

		$args = array(
			'post_type'      => rtcl()->post_type,
			'post_status'    => 'any',
			'posts_per_page' => isset( $general_settings['listings_per_page'] ) ? absint( $general_settings['listings_per_page'] ) : 10,
			'paged'          => $paged,
			's'              => isset( $_REQUEST['u'] ) ? sanitize_text_field( $_REQUEST['u'] ) : '',
			'author'         => get_current_user_id()
		);
		//
		$rtcl_query = new \WP_Query( $args );

		Functions::get_template( "myaccount/my-listings", array( 'rtcl_query' => $rtcl_query, 'paged' => $paged ) );
	}

	public static function edit_account() {

		$user_id                = get_current_user_id();
		$user                   = get_userdata( $user_id );
		$data['username']       = $user->user_login;
		$data['email']          = $user->user_email;
		$data['first_name']     = $user->first_name;
		$data['last_name']      = $user->last_name;
		$data['location_id']    = $data['sub_location_id'] = 0;
		$data['phone']          = get_user_meta( $user_id, '_rtcl_phone', true );
		$data['website']        = get_user_meta( $user_id, '_rtcl_website', true );
		$data['user_locations'] = (array) get_user_meta( $user_id, '_rtcl_location', true );
		$data['zipcode']        = get_user_meta( $user_id, '_rtcl_zipcode', true );
		$data['address']        = get_user_meta( $user_id, '_rtcl_address', true );
		$generalSettings        = Functions::get_option( 'rtcl_general_settings' );
		$data['state_text']     = Text::location_level_first();
		$data['city_text']      = Text::location_level_second();
		$data['town_text']      = Text::location_level_third();
		Functions::get_template( 'myaccount/form-edit-account', $data );
	}


	/**
	 * Lost password page handling.
	 */
	public static function lost_password() {
		/**
		 * After sending the reset link, don't show the form again.
		 */
		if ( ! empty( $_GET['reset-link-sent'] ) ) {
			Functions::add_notice( __( 'Password reset email has been sent.', 'classified-listing' ), 'success' );

			return Functions::get_template( 'myaccount/lost-password-confirmation' );

			/**
			 * Process reset key / login from email confirmation link
			 */
		} elseif ( ! empty( $_GET['show-reset-form'] ) ) {

			if ( isset( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ) && 0 < strpos( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ], ':' ) ) {
				list( $rp_login, $rp_key ) = array_map( array(
					Functions::class,
					'clean'
				), explode( ':', wp_unslash( $_COOKIE[ 'wp-resetpass-' . COOKIEHASH ] ), 2 ) );
				$user = self::check_password_reset_key( $rp_key, $rp_login );

				// reset key / login is correct, display reset password form with hidden key / login values
				if ( is_object( $user ) ) {
					return Functions::get_template( 'myaccount/form-reset-password', array(
						'key'   => $rp_key,
						'login' => $rp_login,
					) );
				}
			}
		}

		// Show lost password form by default
		Functions::get_template( 'myaccount/form-lost-password', array(
			'form' => 'lost_password',
		) );
	}


	/**
	 * Retrieves a user row based on password reset key and login.
	 *
	 * @param string $key    Hash to validate sending user's password
	 * @param string $login  The user login
	 *
	 * @return WP_User|bool User's database row on success, false for invalid keys
	 * @uses $wpdb WordPress Database object
	 */
	public static function check_password_reset_key( $key, $login ) {
		// Check for the password reset key.
		// Get user data or an error message in case of invalid or expired key.
		$user = check_password_reset_key( $key, $login );

		if ( is_wp_error( $user ) ) {
			Functions::add_notice( __( 'This key is invalid or has already been used. Please reset your password again if needed.', 'classified-listing' ), 'error' );

			return false;
		}

		return $user;
	}


	/**
	 * Set or unset the cookie.
	 *
	 * @param string $value
	 */
	public static function set_reset_password_cookie( $value = '' ) {
		$rp_cookie = 'wp-resetpass-' . COOKIEHASH;
		$rp_path   = current( explode( '?', wp_unslash( $_SERVER['REQUEST_URI'] ) ) );

		if ( $value ) {
			setcookie( $rp_cookie, $value, 0, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		} else {
			setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true );
		}
	}

	/**
	 * Handles sending password retrieval email to customer.
	 * Based on retrieve_password() in core wp-login.php.
	 * @return bool True: when finish. False: on error
	 * @uses $wpdb WordPress Database object
	 */
	public static function retrieve_password() {
		$login = trim( $_POST['user_login'] );

		if ( empty( $login ) ) {

			Functions::add_notice( __( 'Enter a username or email address.', 'classified-listing' ), 'error' );

			return false;

		} else {
			// Check on username first, as customers can use emails as usernames.
			$user_data = get_user_by( 'login', $login );
		}

		// If no user found, check if it login is email and lookup user based on email.
		if ( ! $user_data && is_email( $login ) && apply_filters( 'rtcl_get_username_from_email', true ) ) {
			$user_data = get_user_by( 'email', $login );
		}

		$errors = new \WP_Error();

		do_action( 'lostpassword_post', $errors );

		if ( $errors->get_error_code() ) {
			Functions::add_notice( $errors->get_error_message(), 'error' );

			return false;
		}

		if ( ! $user_data ) {
			Functions::add_notice( __( 'Invalid username or email.', 'classified-listing' ), 'error' );

			return false;
		}

		if ( is_multisite() && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
			Functions::add_notice( __( 'Invalid username or email.', 'classified-listing' ), 'error' );

			return false;
		}

		// redefining user_login ensures we return the right case in the email
		$user_login = $user_data->user_login;

		do_action( 'retrieve_password', $user_login );

		$allow = apply_filters( 'rtcl_allow_password_reset', true, $user_data->ID );

		if ( ! $allow ) {

			Functions::add_notice( __( 'Password reset is not allowed for this user', 'classified-listing' ), 'error' );

			return false;

		} elseif ( is_wp_error( $allow ) ) {

			Functions::add_notice( $allow->get_error_message(), 'error' );

			return false;
		}

		// Get password reset key (function introduced in WordPress 4.4).
		$reset_key = get_password_reset_key( $user_data );

		// Send reset link to user
		rtcl()->mailer()->emails['User_Reset_Password_Email_To_User']->trigger( $user_data, $reset_key );

		do_action( 'rtcl_reset_password_notification', $user_data, $reset_key );

		return true;
	}


	/**
	 * Handles resetting the user's password.
	 *
	 * @param object $user      The user
	 * @param string $new_pass  New password for the user in plaintext
	 */
	public static function reset_password( $user, $new_pass ) {
		do_action( 'password_reset', $user, $new_pass );

		wp_set_password( $new_pass, $user->ID );
		self::set_reset_password_cookie();

		wp_password_change_notification( $user );
	}
}