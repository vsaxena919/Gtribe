<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;

class RestApi {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'add_custom_users_api' ) );
	}

	function add_custom_users_api() {
		register_rest_route( 'rtcl/v1', '/receive-payment/', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'api_callback' ),
		) );
	}

	function api_callback( $data ) {
		$getData  = wp_unslash( $_GET );
		if ( ! empty( $getData['id'] ) ) {
			$gateway = Functions::get_payment_gateway($getData['id']);
			if($gateway){
				$gateway->check_callback_response();
			}
		}
	}

}