<?php

use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Payment
 */
$options = array(
	'gs_section'                       => array(
		'title'       => __( 'General settings', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),
	'listing_duration'                 => array(
		'title'       => __( 'Listing duration (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 15,
		'description' => __( 'Use a value of "0" to keep a listing alive indefinitely.', 'classified-listing' ),
	),
	'new_listing_threshold'            => array(
		'title'       => __( 'New listing threshold (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 3,
		'description' => __( 'Enter the number of days the listing will be tagged as "New" from the day it is published.',
			'classified-listing' )
	),
	'new_listing_label'                => array(
		'title'       => __( 'Label text for new listings', 'classified-listing' ),
		'type'        => 'text',
		'default'     => __( "New", 'classified-listing' ),
		'description' => __( 'Enter the text you want to use inside the "New" tag.', 'classified-listing' )
	),
	'listing_featured_label'           => array(
		'title'       => __( 'Label text for Feature listings', 'classified-listing' ),
		'type'        => 'text',
		'default'     => __( "Featured", 'classified-listing' ),
		'description' => __( 'Enter the text you want to use inside the "Featured" tag.', 'classified-listing' )
	),
	'display_options'                  => array(
		'title'   => __( 'Show / Hide (in listing)', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => array( 'category', 'location', 'date', 'excerpt', 'price', 'user', 'views' ),
		'options' => Options::get_listing_display_options()
	),
	'display_options_detail'           => array(
		'title'   => __( 'Show / Hide (in listing detail page)', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'default' => array( 'category', 'location', 'date', 'price', 'user', 'views' ),
		'options' => Options::get_listing_detail_page_display_options()
	),
	'detail_page_sidebar_position'     => array(
		'title'   => __( 'Detail page sidebar position', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'default' => 'right',
		'options' => Options::detail_page_sidebar_position()
	),
	'has_favourites'                   => array(
		'title'   => __( 'Add to favourites', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => __( 'Check this to enable Favourite', 'classified-listing' )
	),
	'has_report_abuse'                 => array(
		'title'   => __( 'Report abuse', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => __( 'Check this to enable Report abuse', 'classified-listing' )
	),
	'has_contact_form'                 => array(
		'title'   => __( 'Contact form', 'classified-listing' ),
		'type'    => 'checkbox',
		'default' => 'yes',
		'label'   => __( 'Allows visitors to contact listing authors privately. Authors will receive the messages via email.',
			'classified-listing' )
	),
	'has_comment_form'                 => array(
		'title' => __( 'Comment form', 'classified-listing' ),
		'type'  => 'checkbox',
		'label' => __( 'Allow visitors to discuss listings using the standard WordPress comment form. Comments are public.',
			'classified-listing' )
	),
	'maximum_images_per_listing'       => array(
		'title'   => __( 'Maximum images allowed per listing', 'classified-listing' ),
		'type'    => 'number',
		'default' => 5
	),
	'delete_expired_listings'          => array(
		'title'       => __( 'Delete expired Listings (in days)', 'classified-listing' ),
		'type'        => 'number',
		'default'     => 15,
		'description' => __( 'If you have the renewal option enabled, this will be the number of days after the "Renewal Reminder" email was sent.',
			'classified-listing' )
	),
	'new_listing_status'               => array(
		'title'       => __( 'New Listing status', 'classified-listing' ),
		'type'        => 'select',
		'class'       => 'rtcl-select2',
		'default'     => 'pending',
		'options'     => Options::get_status_list(),
		'description' => __( 'Listing status at new listing', 'classified-listing' )
	),
	'edited_listing_status'            => array(
		'title'   => __( 'Listing status after edit', 'classified-listing' ),
		'type'    => 'select',
		'class'   => 'rtcl-select2',
		'default' => 'pending',
		'options' => Options::get_status_list()
	),
	'form_section'                     => array(
		'title'       => __( 'Listing Form', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),
	'hide_form_fields'                 => array(
		'title'   => __( 'Hide form fields', 'classified-listing' ),
		'type'    => 'multi_checkbox',
		'options' => array(
			'ad_type'     => __( 'Ad Type', 'classified-listing' ),
			'price_type'  => __( 'Price Type', 'classified-listing' ),
			'price'       => __( 'Price', 'classified-listing' ),
			'gallery'     => __( 'Gallery', 'classified-listing' ),
			'description' => __( 'Description', 'classified-listing' ),
			'location'    => __( 'Location', 'classified-listing' ),
			'zipcode'     => __( 'Zip Code', 'classified-listing' ),
			'address'     => __( 'Address', 'classified-listing' ),
			'phone'       => __( 'Phone', 'classified-listing' ),
			'email'       => __( 'Email', 'classified-listing' ),
			'website'     => __( 'Website URL', 'classified-listing' ),
		)
	),
	'redirect_new_listing'             => array(
		'title'       => __( 'Redirect after new listing', 'classified-listing' ),
		'type'        => 'select',
		'default'     => 'submission',
		'options'     => Options::get_redirect_page_list(),
		'description' => __( 'Redirect after successfully post a new listing', 'classified-listing' )
	),
	'redirect_new_listing_custom'      => array(
		'title'      => __( 'Custom redirect url after new listing', 'classified-listing' ),
		'type'       => 'url',
		'dependency' => array(
			'rules' => array(
				'#rtcl_moderation_settings-redirect_new_listing' => array(
					'type'  => 'equal',
					'value' => 'custom'
				)
			)
		)
	),
	'redirect_update_listing'          => array(
		'title'       => __( 'Redirect after update listing', 'classified-listing' ),
		'type'        => 'select',
		'class'       => 'rtcl-select2',
		'default'     => 'account',
		'options'     => Options::get_redirect_page_list(),
		'description' => __( 'Redirect after successfully post a new listing', 'classified-listing' )
	),
	'redirect_update_listing_custom'   => array(
		'title'      => __( 'Custom redirect url after update listing', 'classified-listing' ),
		'type'       => 'url',
		'dependency' => array(
			'rules' => array(
				'#rtcl_moderation_settings-redirect_update_listing' => array(
					'type'  => 'equal',
					'value' => 'custom'
				)
			)
		)
	)
);

return apply_filters( 'rtcl_moderation_settings_options', $options );