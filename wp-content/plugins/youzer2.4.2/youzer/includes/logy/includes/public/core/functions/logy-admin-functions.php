<?php

/**
 * # Get Option Array Values
 */
function logy_get_select_options( $option_id ) {

	// Set Up Variables
    $array_values = array();
    $option_value = yz_option( $option_id );

    // Get Default Value
    if ( ! $option_value ) {
        global $Logy_default_options;
        $option_value = $Logy_default_options[ $option_id ];
    }

    foreach ( $option_value as $key => $value ) {
    	$array_values[ $value ] = $value;
    }

    return $array_values;
}

/**
 * Register & Load widgets
 */
function logy_load_widgets() {

    // Register Login Widget
    register_widget( 'logy_login_widget' );
    
    // Register Registration Widget
    register_widget( 'logy_register_widget' );

    // Reset Password Widget
    register_widget( 'logy_reset_password_widget' );
}

add_action( 'widgets_init', 'logy_load_widgets' );
