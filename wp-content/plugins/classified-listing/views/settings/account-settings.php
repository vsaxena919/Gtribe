<?php

use Rtcl\Helpers\Text;
use Rtcl\Helpers\Functions;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings for Payment
 */
$options = array(
    'enable_myaccount_registration' => array(
        'title'       => __('Account creation', 'classified-listing'),
        'type'        => 'checkbox',
        'default'     => 'yes',
        'description' => __('Allow customers to create an account on the "My account" page', 'classified-listing'),
    ),
    'user_role'                     => array(
        'title'      => __('New User Default Role', 'classified-listing'),
        'type'       => 'select',
        'class'      => 'rtcl-select2',
        'blank_text' => __("Default Role as wordpress", 'classified-listing'),
        'options'    => Functions::get_user_roles(),
        'css'        => 'min-width:300px;'
    ),
    'social_login_shortcode'        => array(
        'title'       => __('Social Login shortcode', 'classified-listing'),
        'type'        => 'text',
        'css'         => 'width:100%;',
        'description' => __('Add your social login shortcode, which will run at <em style="color:red">rtcl_login_form</em> hook. <br><strong style="color: green;">We will support shortcode from any third party plugin.</strong><br> <strong>Example: [TheChamp-Login], [miniorange_social_login theme="default"]</strong>', 'classified-listing'),
    ),
    'terms_conditions_section'           => array(
	    'title'       => __( 'Terms and conditions', 'classified-listing' ),
	    'type'        => 'title',
	    'description' => '',
    ),
    'enable_listing_terms_conditions'            => array(
	    'title'       => __( 'Enable Listing Terms and conditions', 'classified-listing' ),
	    'type'        => 'checkbox',
	    'description' => __( "Display and require user agreement to Terms and Conditions for Listing form.", 'classified-listing' )
    ),
    'enable_checkout_terms_conditions'            => array(
	    'title'       => __( 'Enable Terms and conditions at checkout page', 'classified-listing' ),
	    'type'        => 'checkbox',
	    'description' => __( "Display and require user agreement to Terms and Conditions at checkout page.", 'classified-listing' )
    ),
    'page_for_terms_and_conditions'      => array(
	    'title'       => esc_html__( 'Terms and conditions page', 'classified-listing' ),
	    'description' => esc_html__( "Choose a page to act as your Terms and conditions.", 'classified-listing' ),
	    'type'        => 'select',
	    'class'       => 'rtcl-select2',
	    'blank_text'  => __( "Select a page", 'classified-listing' ),
	    'options'     => Functions::get_pages(),
	    'css'         => 'min-width:300px;'
    ),
    'terms_and_conditions_checkbox_text' => array(
	    'title'         => __( 'Terms and conditions', 'classified-listing' ),
	    'type'          => 'textarea',
	    'wrapper_class' => Functions::get_option_item( 'rtcl_account_settings', 'enable_terms_conditions', null, 'checkbox' ) ? '' : 'hidden',
	    'default'       => Text::get_default_terms_and_conditions_checkbox_text(),
	    'description'   => __( 'Optionally add some text for the terms checkbox that customers must accept.', 'classified-listing' )
    ),
    'privacy_policy_section'             => array(
	    'title'       => esc_html__( 'Privacy policy', 'classified-listing' ),
	    'type'        => 'title',
	    'description' => esc_html__( "This section controls the display of your website privacy policy. The privacy notices below will not show up unless a privacy page is first set.", 'classified-listing' ),
    ),
    'page_for_privacy_policy'            => array(
	    'title'       => esc_html__( 'Privacy page', 'classified-listing' ),
	    'description' => esc_html__( "Choose a page to act as your privacy policy.", 'classified-listing' ),
	    'type'        => 'select',
	    'class'       => 'rtcl-select2',
	    'blank_text'  => __( "Select a page", 'classified-listing' ),
	    'options'     => Functions::get_pages(),
	    'css'         => 'min-width:300px;'
    ),
    'registration_privacy_policy_text'   => array(
	    'title'       => esc_html__( 'Registration privacy policy', 'classified-listing' ),
	    'type'        => 'textarea',
	    'description' => esc_html__( "Optionally add some text about your store privacy policy to show on account registration forms.", 'classified-listing' ),
	    'default'     => Text::get_default_registration_privacy_policy_text()
    ),
    'checkout_privacy_policy_text'       => array(
	    'title'       => esc_html__( 'Checkout privacy policy', 'classified-listing' ),
	    'type'        => 'textarea',
	    'description' => esc_html__( "Optionally add some text about your store privacy policy to show during checkout.", 'classified-listing' ),
	    'default'     => Text::get_default_checkout_privacy_policy_text(),
    )
);

return apply_filters('rtcl_account_settings_options', $options);