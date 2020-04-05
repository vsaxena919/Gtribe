<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit ; // Exit if accessed directly.
}

function fp_gf_plugin_get_default_privacy_content() {
    return
            '<p>' . __( 'This includes the basics of what personal data your store may be collecting, storing and sharing. Depending on what settings are enabled and which additional plugins are used, the specific information shared by your store will vary.' , 'galaxyfunder' ) . '</p>' .
            '<h2>' . __( 'What the plugin does' , 'galaxyfunder' ) . '</h2>' .
            '<p>' . __( "- This plugin allows users to create “Keep what You Raise Campaigns” on your WooCommerce Shop" , 'galaxyfunder' ) . '</p>' .
            '<p>' . __( "- The user can create a crowdfunding campaign by using the Campaign Submission Form" , 'galaxyfunder' ) . '</p>' .
            '<p>' . __( "- Once the Admin has approved and published the campaign, users can contribute to the campaign" , 'galaxyfunder' ) . '</p>' .
            '<p>' . __( "- The Contribution amount will be charged immediately from the user as soon as the contribution is made" , 'galaxyfunder' ) . '</p>' .
            '<h2>' . __( 'What we collect and store' , 'galaxyfunder' ) . '</h2>' .
            '<h3>' . __( "User ID" , 'galaxyfunder' ) . '</h3>' .
            '<p>' . __( "We use User ID to Identify the User Who Created the Campain" , 'galaxyfunder' ) . '</p>' .
            '<h3>' . __( "Payment Details" , 'galaxyfunder' ) . '</h3>' .
            '<p>' . __( "We record the Campaign Creator's Payment Details. These details are provided by the users while creating the Campaign. This payment details will be used by the Site Admin to pay the Campaign Creators." , 'galaxyfunder' ) . '</p>' ;
}

/**
 * Add the suggested privacy policy text to the policy postbox.
 */
function fp_gf_plugin_add_suggested_privacy_content() {
    global $wp_version ;
    if (function_exists('wp_add_privacy_policy_content')) {
        $content = fp_gf_plugin_get_default_privacy_content() ;
        wp_add_privacy_policy_content( __( 'Galaxy Funder' , 'galaxyfunder' ) , $content ) ;
    }
}

// Not sure why but core registers their default text at priority 15, so to be after them (which I think would be the idea, you need to be 20+.
add_action( 'admin_init' , 'fp_gf_plugin_add_suggested_privacy_content' , 20 ) ;
