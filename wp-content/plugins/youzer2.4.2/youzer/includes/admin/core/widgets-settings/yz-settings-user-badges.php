<?php

/**
 * # Widget Settings.
 */
function yz_user_badges_widget_settings() {

	if ( ! defined( 'myCRED_BADGE_VERSION' ) ) {
		global $Yz_Settings;

        $Yz_Settings->get_field(
            array(
                'msg_type'  => 'info',
                'type'      => 'msgBox',
                'id'        => 'yz_msgbox_user_badges_widget_notice',
                'title'     => __( 'How to activate user badges widget?', 'youzer' ),
                'msg'       => sprintf( __( 'Please install the <a href="%1s"> MyCRED Plugin</a> and <strong>MyCRED Badges Extension</strong> to activate the user badges widget.'), 'https://wordpress.org/plugins/mycred/' )
            )
        );
	}
	
    do_action( 'yz_user_badges_widget_settings' );

}