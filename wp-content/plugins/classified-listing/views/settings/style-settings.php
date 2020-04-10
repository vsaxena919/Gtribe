<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for Style
 */
$options = array(
	'gs_section'           => array(
		'title'       => __( 'Global Style', 'classified-listing' ),
		'type'        => 'title',
		'description' => '',
	),
	'primary'               => array(
		'title'   => __( 'Primary', 'classified-listing' ),
		'type'    => 'color',
	),
	'link'               => array(
		'title'   => __( 'Link color', 'classified-listing' ),
		'type'    => 'color',
	),
	'link_hover'               => array(
		'title'   => __( 'Link color on hover', 'classified-listing' ),
		'type'    => 'color',
	),
	'button'               => array(
		'title'   => __( 'Button color', 'classified-listing' ),
		'type'    => 'color',
	),
	'button_hover'               => array(
		'title'   => __( 'Button color on hover', 'classified-listing' ),
		'type'    => 'color',
	),
	'button_text'               => array(
		'title'   => __( 'Button text color', 'classified-listing' ),
		'type'    => 'color',
	),
	'button_hover_text'               => array(
		'title'   => __( 'Button text color on hover', 'classified-listing' ),
		'type'    => 'color',
	),
	'lbl_section'           => array(
		'title'       => __( 'Label Style', 'classified-listing' ),
		'type'        => 'title',
	),
	'feature'               => array(
		'title'   => __( 'Feature label background color', 'classified-listing' ),
		'type'    => 'color',
	),
	'feature_text'               => array(
		'title'   => __( 'Feature label text color', 'classified-listing' ),
		'type'    => 'color',
	)
);

return apply_filters( 'rtcl_style_settings_options', $options );
