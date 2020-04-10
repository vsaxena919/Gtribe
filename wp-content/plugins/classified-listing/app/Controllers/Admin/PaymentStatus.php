<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Payment;
use Rtcl\Models\RtclEmail;

class PaymentStatus {

	function __construct() {
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
	}


	public function transition_post_status( $new_status, $old_status, $post ) {

		if ( rtcl()->post_type_payment !== $post->post_type ) {
			return;
		}

		// TODO : need to add some logic

		if ( $new_status == 'rtcl-completed' && $old_status != 'rtcl-completed' && "membership" != get_post_meta( $post->ID, 'payment_type', true ) ) {
			$applied = absint( get_post_meta( $post->ID, '_applied', true ) );
			if ( ! $applied ) {
				$payment      = new Payment( $post->ID );
				$current_date = new \DateTime( current_time( 'mysql' ) );
				$visible      = $payment->pricing->getVisible();
				$expiry_date  = get_post_meta( $payment->get_listing_id(), 'expiry_date', true );
				if ( $expiry_date ) {
					$expiry_date = new \DateTime( Functions::datetime( 'mysql', trim( ( $expiry_date ) ) ) );
					if ( $current_date > $expiry_date ) {
						$current_date->add( new \DateInterval( "P{$visible}D" ) );
						$expDate = $current_date->format( 'Y-m-d H:i:s' );
					} else {
						$expiry_date->add( new \DateInterval( "P{$visible}D" ) );
						$expDate = $expiry_date->format( 'Y-m-d H:i:s' );
					}
					update_post_meta( $payment->get_listing_id(), 'expiry_date', $expDate );
				}

				if ( $payment->pricing->getFeatured() ) {
					update_post_meta( $payment->get_listing_id(), 'featured', 1 );
					$feature_expiry_date = get_post_meta( $payment->get_listing_id(), 'feature_expiry_date', true );
					if ( $feature_expiry_date ) {
						$feature_expiry_date = new \DateTime( Functions::datetime( 'mysql', trim( ( $feature_expiry_date ) ) ) );
						if ( $current_date > $feature_expiry_date ) {
							delete_post_meta( $payment->get_listing_id(), 'feature_expiry_date' );
						} else {
							$feature_expiry_date->add( new \DateInterval( "P{$visible}D" ) );
							$featureExpDate = $feature_expiry_date->format( 'Y-m-d H:i:s' );
							update_post_meta( $payment->get_listing_id(), 'feature_expiry_date', $featureExpDate );
						}
					}
				}

				if ( $payment->pricing->getTop() ) {
					update_post_meta( $payment->get_listing_id(), '_top', 1 );
					$top_expiry_date = get_post_meta( $payment->get_listing_id(), '_top_expiry_date', true );
					if ( $top_expiry_date ) {

						$top_expiry_date = new \DateTime( Functions::datetime( 'mysql', trim( ( $top_expiry_date ) ) ) );
						if ( $current_date > $top_expiry_date ) {
							delete_post_meta( $payment->get_listing_id(), '_top_expiry_date' );
						} else {
							$top_expiry_date->add( new \DateInterval( "P{$visible}D" ) );
							$topExpDate = $top_expiry_date->format( 'Y-m-d H:i:s' );
							update_post_meta( $payment->get_listing_id(), '_top_expiry_date', $topExpDate );
						}
					}
				}

				// Todo : need to add top feature
				update_post_meta( $payment->get_id(), '_applied', 1 );


				// Hook for developers
				do_action( 'rtcl_payment_completed', $post->ID );

				// send emails
				if ( Functions::get_option_item( 'rtcl_email_settings', 'notify_users', 'order_completed', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Order_Completed_Email_To_Customer']->trigger( $payment->get_id(), $payment );
				}

				if ( Functions::get_option_item( 'rtcl_email_settings', 'notify_admin', 'order_completed', 'multi_checkbox' ) ) {
					rtcl()->mailer()->emails['Order_Completed_Email_To_Admin']->trigger( $payment->get_id(), $payment );
				}
			}
		}

	}

}