<?php

namespace Rtcl\Helpers;


class CacheHelper {

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once.
	 *
	 * @param  string $group Group of cache to get.
	 * @return string
	 */
	public static function get_cache_prefix( $group ) {
		// Get cache key - uses cache key rtcl_orders_cache_prefix to invalidate when needed.
		$prefix = wp_cache_get( 'rtcl_' . $group . '_cache_prefix', $group );

		if ( false === $prefix ) {
			$prefix = 1;
			wp_cache_set( 'rtcl_' . $group . '_cache_prefix', $prefix, $group );
		}

		return 'rtcl_cache_' . $prefix . '_';
	}

}