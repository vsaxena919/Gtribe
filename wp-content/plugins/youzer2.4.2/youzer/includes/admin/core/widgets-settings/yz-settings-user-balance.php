<?php

/**
 * User Balance Settings.
 */
function yz_user_balance_widget_settings() {

    if ( ! defined( 'myCRED_VERSION' ) ) {
    	
		global $Yz_Settings;

        $Yz_Settings->get_field(
            array(
                'msg_type'  => 'info',
                'type'      => 'msgBox',
                'id'        => 'yz_msgbox_user_balance_widget_notice',
                'title'     => __( 'How to activate user balance widget?', 'youzer' ),
                'msg'       => sprintf( __( 'Please install the <a href="%1s"> MyCRED Plugin</a> to activate the user balance widget.'), 'https://wordpress.org/plugins/mycred/' )
            )
        );

	}
	
	do_action( 'yz_user_balance_widget_settings' );

}