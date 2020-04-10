<?php


namespace Rtcl\Traits;


use Rtcl\Helpers\Functions;
use Rtcl\Models\Listing;
use Rtcl\Resources\Options;

trait ListingTrait {


	/**
	 * @param       $cat_id
	 * @param Listing/null $listing
	 *
	 * @return mixed|void
	 * @var Listing $listing
	 */
	static function get_listing_form_price_unit_html( $cat_id, $listing = null ) {
		if ( ! $cat_id && ! $listing ) {
			return;
		}

		$price_unit  = null;
		$price_units = array();
		if ( is_a( $listing, Listing::class ) ) {
			$price_units = $listing->get_price_units();
			$price_unit  = $listing->get_price_unit();
		} else if ( $cat_id ) {
			$price_units = self::get_category_price_units( $cat_id );
		}

		$price_unit_list = Options::get_price_unit_list();
		$html            = Functions::get_template_html( 'listing-form/price-unit', compact( 'price_unit_list', 'price_units', 'price_unit', 'cat_id', 'listing' ) );

		return apply_filters( 'rtcl_get_listing_form_price_unit_html', $html, $cat_id, $listing );
	}


	/**
	 * @param $cat_id
	 *
	 * @return array
	 */
	static function get_category_price_units( $cat_id ) {
		$price_units = get_term_meta( $cat_id, '_rtcl_price_units' );
		if ( empty( $price_units ) && $term = get_term( $cat_id, rtcl()->category ) ) {
			if ( $term->parent ) {
				$price_units = get_term_meta( $term->parent, '_rtcl_price_units' );
			}
		}

		return $price_units;
	}


	/**
	 * @param $cat_id
	 *
	 * @return boolean
	 */
	static function category_has_price_units( $cat_id ) {

		return count( self::get_category_price_units( $cat_id ) ) > 0;
	}


}