<?php

namespace Rtcl\Helpers;


class Pagination {

	/**
	 * @param string $default_order
	 *
	 * @return string
	 */
	static function get_listings_current_order( $default_order = '' ) {

		$order = $default_order;

		if ( isset( $_GET['sort'] ) ) {
			$order = sanitize_text_field( $_GET['sort'] );
		} else if ( isset( $_GET['order'] ) ) {
			$order = sanitize_text_field( $_GET['order'] );
		}

		return $order;

	}


	/**
	 * @return int
	 */
	public static function get_page_number() {

		global $paged;

		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} else if ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}

		return absint( $paged );

	}

	/**
	 * @param $rtcl_query \WP_Query
	 */
	public static function pagination( $rtcl_query = null ) {
		$range     = 2;
		$showItems = ( $range * 2 ) + 1;
		$paged     = self::get_page_number();
		if ( ! empty( $rtcl_query ) ) {
			$pages = $rtcl_query->max_num_pages;
		}
		if ( ! isset( $pages ) ) {
			global $wp_query;
			$pages = $wp_query->max_num_pages;

			if ( ! $pages ) {
				$pages = 1;
			}
		}


		Functions::get_template( "global/pagination", compact( 'paged', 'showItems', 'pages' ) );

	}
}