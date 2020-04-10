<?php

use Rtcl\Models\Roles;
use Rtcl\Helpers\Functions;

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
$settings = get_option( 'rtcl_tools_settings' );
if ( ! empty( $settings['delete_all_data'] ) && 'yes' === $settings['delete_all_data'] ) {
	// Delete All the Custom Post Types
	$rtcl_post_types = array(
		rtcl()->post_type,
		rtcl()->post_type_cf,
		rtcl()->post_type_cfg,
		rtcl()->post_type_payment,
		rtcl()->post_type_pricing
	);

	foreach ( $rtcl_post_types as $post_type ) {

		$items = get_posts( array(
			'post_type'   => $post_type,
			'post_status' => 'any',
			'numberposts' => - 1,
			'fields'      => 'ids'
		) );

		if ( $items ) {
			foreach ( $items as $item ) {
				// Delete the actual post
				wp_delete_post( $item, true );
			}
		}

	}

	// Delete All the Terms & Taxonomies
	$rtcl_taxonomies = array( rtcl()->category, rtcl()->location );

	global $wpdb;

	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}rtcl_sessions" );
	$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_type IN ( 'rtcl_payment_note' );" );
	$wpdb->query( "DELETE meta FROM {$wpdb->commentmeta} meta LEFT JOIN {$wpdb->comments} comments ON comments.comment_ID = meta.comment_id WHERE comments.comment_ID IS NULL;" );


	foreach ( $rtcl_taxonomies as $taxonomy ) {

		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
			}
		}

		// Delete Taxonomies
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );

	}


	// Delete the Plugin Pages
	$rtcl_created_pages = Functions::get_page_ids();
	if ( ! empty( $rtcl_created_pages ) ) {

		foreach ( $rtcl_created_pages as $page => $id ) {

			if ( $id > 0 ) {
				wp_delete_post( $id, true );
			}

		}

	}


// Delete all the Plugin Options
	$rtcl_settings = array(
		'rtcl_general_settings',
		'rtcl_moderation_settings',
		'rtcl_payment_settings',
		'rtcl_payment_offline',
		'rtcl_email_settings',
		'rtcl_account_settings',
		'rtcl_misc_settings',
		'rtcl_style_settings',
		'rtcl_advanced_settings',
		'rtcl_tools_settings',
	);

	foreach ( $rtcl_settings as $settings ) {
		delete_option( $settings );
	}

	delete_option( 'rtcl_version' );

	$roles = new Roles();
	$roles->remove_default_caps();

	// Clear any cached data that has been removed
	wp_cache_flush();
}