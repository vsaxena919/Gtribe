<?php

namespace Rtcl\Controllers\Ajax;


use Rtcl\Helpers\Functions;
use Rtcl\Helpers\Link;
use Rtcl\Log\Logger;
use Rtcl\Models\RtclCFGField;
use Rtcl\Models\RtclEmail;
use Rtcl\Resources\Options;

class PublicUser {

	public function __construct() {
		add_action( "wp_ajax_rtcl_post_new_listing", array( $this, 'rtcl_post_new_listing' ) );
		add_action( "wp_ajax_rtcl_delete_listing", array( $this, 'rtcl_delete_listing' ) );
		add_action( 'wp_ajax_rtcl_public_add_remove_favorites', array( $this, 'rtcl_add_remove_favorites' ) );
		add_action( 'wp_ajax_rtcl_public_report_abuse', array( $this, 'rtcl_report_abuse' ) );
		add_action( 'wp_ajax_nopriv_rtcl_public_report_abuse', array( $this, 'rtcl_report_abuse' ) );
		add_action( 'wp_ajax_rtcl_public_send_contact_email', array( $this, 'send_contact_email' ) );
		add_action( 'wp_ajax_nopriv_rtcl_public_send_contact_email', array(
			$this,
			'send_contact_email'
		) );

		add_action( 'wp_ajax_rtcl_get_one_level_category_select_list_by_type', array(
			$this,
			'rtcl_get_one_level_category_select_list_by_type'
		) );
		// get dropdown terms
		add_action( 'wp_ajax_rtcl_child_dropdown_terms', array( $this, 'dropdown_terms' ) );
		add_action( 'wp_ajax_nopriv_rtcl_child_dropdown_terms', array( $this, 'dropdown_terms' ) );

		add_action( 'wp_ajax_rtcl_update_user_account', array( $this, 'rtcl_update_user_account' ) );

		add_action( 'wp_ajax_rtcl_get_price_units_ajax', array( $this, 'rtcl_get_price_units_ajax_cb' ) );
	}

	function rtcl_get_price_units_ajax_cb() {
		wp_send_json( array(
			'html' => isset( $_POST['term_id'] ) ? Functions::get_listing_form_price_unit_html( absint( $_POST['term_id'] ) ) : ''
		) );
	}

	function rtcl_get_one_level_category_select_list_by_type() {
		Functions::clear_notices();
		$success    = false;
		$message    = array();
		$type       = ( isset( $_POST['type'] ) && in_array( $_POST['type'], array_keys( Functions::get_listing_types() ) ) ) ? $_POST['type'] : null;
		$child_cats = null;
		if ( $type ) {
			$childCats = Functions::get_one_level_categories( 0, $type );
			if ( ! empty( $childCats ) ) {
				$success    = true;
				$child_cats .= "<option value=''>--" . __( "Select category", "classified-listing" ) . "--</option>";
				foreach ( $childCats as $child_cat ) {
					$child_cats .= "<option value='{$child_cat->term_id}'>{$child_cat->name}</option>";
				}
			} else {
				Functions::add_notice( __( "No category found.", "classified-listing" ), 'error' );
			}
		} else {
			Functions::add_notice( __( "Type is not selected.", "classified-listing" ), 'error' );
		}
		if ( Functions::notice_count( 'error' ) ) {
			$message = Functions::get_notices( 'error' );
		}
		Functions::clear_notices();
		$response = array(
			'message' => $message,
			'success' => $success,
			'cats'    => $child_cats,
		);
		wp_send_json( $response );
	}

	public function rtcl_update_user_account() {
		$error      = true;
		$msg        = array();
		$user_id    = get_current_user_id();
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name  = sanitize_text_field( $_POST['last_name'] );

		// Validate email
		$email = sanitize_email( $_POST['email'] );

		if ( ! is_email( $email ) ) {
			$msg[] = __( 'Invalid email address.', 'classified-listing' );
		}

		if ( $id = email_exists( $email ) ) {

			if ( $id != $user_id ) {
				$msg[] = __( 'Sorry, that email address already exists!', 'classified-listing' );
			}

		}

		// Validate password
		$password = '';

		if ( isset( $_POST['change_password'] ) && $_POST['change_password'] === "true" ) {
			$password = sanitize_text_field( $_POST['pass1'] );

			if ( empty( $password ) ) {
				// Password is empty
				$msg[] = __( 'The password field is empty.', 'classified-listing' );
			}

			if ( $password != $_POST['pass2'] ) {
				// Passwords don't match
				$msg[] = __( "The two passwords you entered don't match.", 'classified-listing' );
			}
		}

		// Generate the password so that the subscriber will have to check email...
		$user_data = array(
			'ID'         => $user_id,
			'user_email' => $email,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'nickname'   => $first_name
		);

		if ( ! empty( $password ) ) {
			$user_data['user_pass'] = $password;
		}
		if ( empty( $msg ) ) {
			$user_id   = wp_update_user( $user_data );
			$user_meta = array();

			$user_meta['_rtcl_phone']          = ! empty( $_POST['phone'] ) ? esc_attr( $_POST['phone'] ) : null;
			$user_meta['_rtcl_website']        = ! empty( $_POST['website'] ) ? esc_url_raw( $_POST['website'] ) : null;
			$user_meta['_rtcl_zipcode']        = ! empty( $_POST['zipcode'] ) ? esc_attr( $_POST['zipcode'] ) : null;
			$user_meta['_rtcl_address']        = ! empty( $_POST['address'] ) ? esc_textarea( $_POST['address'] ) : null;
			$user_meta['_rtcl_latitude']       = ! empty( $_POST['latitude'] ) ? esc_attr( $_POST['latitude'] ) : null;
			$user_meta['_rtcl_longitude']      = ! empty( $_POST['longitude'] ) ? esc_attr( $_POST['longitude'] ) : null;
			$location                          = array();
			if ( ! empty( $_POST['location'] ) && $state = absint( $_POST['location'] ) ) {
				array_push( $location, $state );
			}
			if ( ! empty( $_POST['sub_location'] ) && $city = absint( $_POST['sub_location'] ) ) {
				array_push( $location, $city );
			}
			if ( ! empty( $_POST['sub_sub_location'] ) && $town = absint( $_POST['sub_sub_location'] ) ) {
				array_push( $location, $town );
			}
			$user_meta['_rtcl_location'] = $location;

			foreach ( $user_meta as $metaKey => $metaValue ) {
				update_user_meta( $user_id, $metaKey, $metaValue );
			}

			$error = false;
			$msg   = __( "Your account has been updated.", "classified-listing" );
		}
		if ( is_array( $msg ) && count( $msg ) ) {
			$m = null;
			foreach ( $msg as $message ) {
				$m .= sprintf( "<p>%s</p>", $message );
			}
			$msg = $m;
		}

		wp_send_json( array(
			'error'   => $error,
			'message' => $msg,
		) );
	}

	public function dropdown_terms() {

		if ( isset( $_POST['taxonomy'] ) && isset( $_POST['parent'] ) ) {

			$args = array(
				'taxonomy'  => sanitize_text_field( $_POST['taxonomy'] ),
				'base_term' => 0,
				'parent'    => (int) $_POST['parent']
			);

			if ( isset( $_POST['class'] ) && '' != trim( $_POST['class'] ) ) {
				$args['class'] = sanitize_text_field( $_POST['class'] );
			}

			if ( $args['parent'] != $args['base_term'] ) {
				ob_start();
				Functions::dropdown_terms( $args );
				$output = ob_get_clean();
				echo $output;
			}

		}

		wp_die();

	}

	function send_contact_email() {

		$data    = array( 'error' => 1 );
		$post_id = (int) $_POST["post_id"];
		$name    = sanitize_text_field( $_POST["name"] );
		$email   = sanitize_email( $_POST["email"] );
		$message = stripslashes( esc_textarea( $_POST["message"] ) );
		if ( is_object( get_post( $post_id ) ) && $name && $email && $message ) {
			if ( Functions::is_human( 'contact' ) ) {

				$sender_data = array(
					'name'    => $name,
					'email'   => $email,
					'message' => $message
				);

				if ( ! Functions::get_option_item( 'rtcl_email_settings', 'notify_users', 'disable_contact_email', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Listing_Contact_Email_To_Owner']->trigger( $post_id, $sender_data );
				}
				if ( Functions::get_option_item( 'rtcl_email_settings', 'notify_admin', 'listing_contact', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Listing_Contact_Email_To_Admin']->trigger( $post_id, $sender_data );
				}
				$notification = absint( get_post_meta( $post_id, '_notification_by_visitor', true ) ) + 1;
				update_post_meta( $post_id, '_notification_by_visitor', $notification );
				$data['error']   = 0;
				$data['message'] = __( 'Your message sent successfully.', 'classified-listing' );

			} else {
				$data['message'] = __( 'Invalid Captcha: Please try again.', 'classified-listing' );
			}
		} else {
			$data['message'] = __( 'Need to fill all the required field.', 'classified-listing' );
		}

		wp_send_json( $data );
	}

	function rtcl_delete_listing() {
		$success = false;
		$message = $msg_class = $redirect_url = $post_id = null;
		if ( Functions::verify_nonce() ) {
			$post_id = absint( Functions::request( 'post_id' ) );
			if ( $post_id && Functions::current_user_can( 'delete_' . rtcl()->post_type, $post_id ) ) {

				$children = get_children( array(
					'post_parent'    => $post_id,
					'post_type'      => 'attachment',
					'posts_per_page' => - 1,
					'post_status'    => 'inherit'
				) );

				if ( ! empty( $children ) ) {
					foreach ( $children as $child ) {
						wp_delete_attachment( $child->ID, true );
					}
				}
				Functions::delete_post( $post_id );

				// Success
				$success = true;
				// Message
				$message .= __( "Successfully deleted.", "classified-listing" );
				// redirect
				$redirect_url = Link::get_account_endpoint_url( "listings" );
			} else {
				$message .= __( "Permission Error.", "classified-listing" );
			}
		} else {
			$message .= __( "Session expired.", "classified-listing" );
		}

		wp_send_json( array(
			'success'      => $success,
			'post_id'      => $post_id,
			'message'      => $message,
			'redirect_url' => $redirect_url
		) );
	}

	function rtcl_report_abuse() {
		$data    = array( 'error' => 1 );
		$post_id = (int) $_POST["post_id"];
		$message = esc_textarea( $_POST["message"] );
		if ( is_object( get_post( $post_id ) ) && $message ) {
			if ( Functions::is_human( 'report_abuse' ) ) {
				$sender_data = array(
					'message' => $message
				);
				$is_send     = rtcl()->mailer()->emails['Report_Abuse_Email_To_Admin']->trigger( $post_id, $sender_data );
				if ( $is_send ) {

					$notification = absint( get_post_meta( $post_id, '_abuse_report_by_visitor', true ) ) + 1;
					update_post_meta( $post_id, '_abuse_report_by_visitor', $notification );
					$data['error']   = 0;
					$data['message'] = __( 'Your message sent successfully.', 'classified-listing' );

				} else {
					$data['message'] = __( 'Sorry! Please try again.', 'classified-listing' );
				}

			} else {
				$data['message'] = __( 'Invalid Captcha: Please try again.', 'classified-listing' );

			}
		} else {
			$data['message'] = __( 'Need to fill all the required field.', 'classified-listing' );
		}

		wp_send_json( $data );
	}

	function rtcl_add_remove_favorites() {
		$success = false;
		$message = null;
		$post_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		if ( Functions::verify_nonce() ) {
			if ( $post_id ) {
				$favourites = (array) get_user_meta( get_current_user_id(), 'rtcl_favourites', true );

				if ( in_array( $post_id, $favourites ) ) {
					if ( ( $key = array_search( $post_id, $favourites ) ) !== false ) {
						unset( $favourites[ $key ] );
					}
				} else {
					$favourites[] = $post_id;
				}

				$favourites = array_filter( $favourites );
				$favourites = array_values( $favourites );

				delete_user_meta( get_current_user_id(), 'rtcl_favourites' );
				update_user_meta( get_current_user_id(), 'rtcl_favourites', $favourites );
				$success = true;
				$message = __( "Successfully removed", "classified-listing" );
			} else {
				$message = __( "Add post id to remove", "classified-listing" );
			}
		} else {
			$message = __( "Session Expired!", "classified-listing" );
		}
		wp_send_json( array(
			"success" => $success,
			"data"    => Functions::get_favourites_link( $post_id ),
			"message" => $message
		) );
	}

	function rtcl_post_new_listing() {
		Functions::clear_notices();
		$success = false;
		$post_id = 0;
		$type    = 'new';
		$message = null;
		if ( Functions::verify_nonce() ) {
			$agree = isset( $_POST['rtcl_agree'] ) ? 1 : null;
			if ( Functions::is_enable_terms_conditions() && ! $agree ) {
				Functions::add_notice(
					apply_filters( 'rtcl_listing_form_terms_conditions_text_responses', __( "Please agree with the terms and conditions.", "Classified-listing" ), $_REQUEST ),
					'error' );
			} else {
				$ad_type = isset( $_POST['ad_type'] ) ? in_array( $_POST['ad_type'], array_keys( Functions::get_listing_types() ) ) ? esc_attr( $_POST['ad_type'] ) : 'sell' : '';
				if ( ! $ad_type && ! Functions::is_ad_type_disabled() ) {
					Functions::add_notice(
						apply_filters( 'rtcl_listing_form_ad_type_responses', __( "Please select an ad type.", "Classified-listing" ) ),
						'error' );
				} else {
					$post_id = absint( Functions::request( '_post_id' ) );
					$cats    = array();
					if ( $cat = Functions::request( 'category' ) ) {
						array_push( $cats, absint( $cat ) );
					};
					if ( $cat = Functions::request( 'sub_category' ) ) {
						array_push( $cats, absint( $cat ) );
					};
					$locations = array();
					if ( $loc = Functions::request( 'location' ) ) {
						array_push( $locations, absint( $loc ) );
					};
					if ( $loc = Functions::request( 'sub_location' ) ) {
						array_push( $locations, absint( $loc ) );
					};
					if ( $loc = Functions::request( 'sub_sub_location' ) ) {
						array_push( $locations, absint( $loc ) );
					};
					$meta            = array();
					$meta['ad_type'] = $ad_type;
					if ( Functions::is_enable_terms_conditions() && $agree ) {
						$meta['rtcl_agree'] = 1;
					}
					if ( isset( $_POST['price_type'] ) ) {
						$meta['price_type'] = Functions::sanitize( $_POST['price_type'] );
					}
					if ( isset( $_POST['price'] ) ) {
						$meta['price'] = Functions::format_decimal( $_POST['price'] );
					}
					if ( isset( $_POST['zipcode'] ) ) {
						$meta['zipcode'] = Functions::sanitize( $_POST['zipcode'] );
					}
					if ( isset( $_POST['address'] ) ) {
						$meta['address'] = Functions::sanitize( $_POST['address'], 'textarea' );
					}
					if ( isset( $_POST['phone'] ) ) {
						$meta['phone'] = Functions::sanitize( $_POST['phone'] );
					}
					if ( isset( $_POST['email'] ) ) {
						$meta['email'] = Functions::sanitize( $_POST['email'], 'email' );
					}
					if ( isset( $_POST['website'] ) ) {
						$meta['website'] = Functions::sanitize( $_POST['website'], 'url' );
					}
					if ( isset( $_POST['latitude'] ) ) {
						$meta['latitude'] = Functions::sanitize( $_POST['latitude'] );
					}
					if ( isset( $_POST['longitude'] ) ) {
						$meta['longitude'] = Functions::sanitize( $_POST['longitude'] );
					}
					if ( isset( $_POST['_rtcl_price_unit'] ) ) {
						$meta['_rtcl_price_unit'] = Functions::sanitize( $_POST['_rtcl_price_unit'] );
					}
					$meta['hide_map']   = isset( $_POST['hide_map'] ) ? 1 : null;
					$title              = isset( $_POST['title'] ) ? Functions::sanitize( $_POST['title'], 'title' ) : '';
					$new_listing_status = Functions::get_option_item( 'rtcl_moderation_settings', 'new_listing_status', 'pending' );
					$post_arg           = array(
						'post_title'   => $title,
						'post_content' => isset( $_POST['description'] ) ? Functions::sanitize( $_POST['description'], 'content' ) : '',
						'post_status'  => $new_listing_status,
					);
					$post               = get_post( $post_id );
					if ( $post_id && is_object( $post ) && $post->post_author == get_current_user_id() && $post->post_type == rtcl()->post_type ) {
						if ( $post->post_status === "rtcl-temp" ) {
							$post_arg['post_name']   = $title;
							$post_arg['post_status'] = $new_listing_status;
						} else {
							$type                    = 'update';
							$post_arg['post_status'] = Functions::get_option_item( 'rtcl_moderation_settings', 'edited_listing_status', 'pending' );
						}
						$post_arg['ID'] = $post_id;
						$success        = wp_update_post( $post_arg );

					} else {
						$post_arg['post_author'] = get_current_user_id();
						$post_arg['post_type']   = rtcl()->post_type;
						$post_id                 = $success = wp_insert_post( $post_arg );
					}

					wp_set_object_terms( $post_id, $cats, rtcl()->category );
					wp_set_object_terms( $post_id, $locations, rtcl()->location );

					// Custom Meta field
					if ( isset( $_POST['rtcl_fields'] ) ) {
						foreach ( $_POST['rtcl_fields'] as $key => $value ) {
							$field_id = (int) str_replace( '_field_', '', $key );
							$field    = new RtclCFGField( $field_id );
							if ( $field_id && $field ) {
								$field->saveSanitizedValue( $post_id, $value );
							}
						}
					}

					/* meta data */

					if ( ! empty( $meta ) ) {
						foreach ( $meta as $key => $value ) {
							update_post_meta( $post_id, $key, $value );
						}
					}
					// send emails
					if ( $success && $post_id ) {
						if ( $type == 'new' ) {
							update_post_meta( $post_id, 'featured', 0 );
							update_post_meta( $post_id, '_top', 0 );
							update_post_meta( $post_id, '_views', 0 );
							$current_user_id = get_current_user_id();
							$ads             = absint( get_user_meta( $current_user_id, '_rtcl_ads', true ) );
							update_user_meta( $current_user_id, '_rtcl_ads', $ads + 1 );
							Functions::add_notice(
								apply_filters( 'rtcl_listing_success_message', __( "Thank you for submitting your ad!", "classified-listing" ), $post_id, $type, $_REQUEST ),
								'success' );
						} else if ( $type == 'update' ) {
							Functions::add_notice(
								apply_filters( 'rtcl_listing_success_message', __( "Successfully updated !!!", "classified-listing" ), $post_id, $type, $_REQUEST ), 'success' );
						}
						do_action( 'rtcl_listing_form_after_save_or_update', $post_id, $type, $cat, $new_listing_status );

					} else {
						Functions::add_notice( apply_filters( 'rtcl_listing_error_message', __( "Error!!", "Classified-listing" ), $_REQUEST ), 'error' );
					}
				}
			}
		} else {
			Functions::add_notice( apply_filters( 'rtcl_listing_session_error_message', __( "Session Error !!", "Classified-listing" ), $_REQUEST ), 'error' );
		}


		$error_message   = Functions::get_notices( 'error' );
		$success_message = Functions::get_notices( 'success' );
		Functions::clear_notices();

		wp_send_json( apply_filters( 'rtcl_listing_form_after_save_or_update_responses',
				array(
					'error_message'   => $error_message,
					'success_message' => $success_message,
					'success'         => $success,
					'post_id'         => $post_id,
					'message'         => $message,
					'redirect_url'    => apply_filters( 'rtcl_listing_form_after_save_or_update_responses_redirect_url',
						Functions::get_listing_redirect_url_after_edit_post( $type, $post_id, $success ),
						$type, $post_id, $success, $message
					)
				)
			)
		);
	}
}