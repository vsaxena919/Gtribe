<?php

namespace Rtcl\Emails;

use Rtcl\Models\RtclEmail;
use Rtcl\Helpers\Functions;

class UserNewRegistrationEmailToAdmin extends RtclEmail {

	public $user = null;

	function __construct() {

		$this->id            = 'new_user_registration';
		$this->template_html = 'emails/user-new-registration-email-to-admin';

		// Call parent constructor.
		parent::__construct();
	}


	/**
	 * Get email subject.
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}] New User is registered', 'classified-listing' );
	}

	/**
	 * Get email heading.
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'New user is registered', 'classified-listing' );
	}


	/**
	 * Trigger the sending of this email.
	 *
	 * @param       $user_id
	 * @param array $data
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function trigger( $user_id, $data = array() ) {
		if ( ! $user_id ) {
			return;
		}

		$this->setup_locale();
		$this->user = get_userdata( $user_id );
		$this->set_recipient( Functions::get_admin_email_id_s() );

		if ( $this->get_recipient() ) {
			$this->send();
		}

		$this->restore_locale();

	}


	/**
	 * Get content html.
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return Functions::get_template_html(
			$this->template_html, array(
				'email' => $this,
				'user'  => $this->user
			)
		);
	}

}