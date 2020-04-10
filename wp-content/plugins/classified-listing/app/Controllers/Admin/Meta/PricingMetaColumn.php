<?php

namespace Rtcl\Controllers\Admin\Meta;


use Rtcl\Helpers\Functions;
use Rtcl\Resources\Options;

class PricingMetaColumn {

	public function __construct() {
		add_action( 'manage_edit-' . rtcl()->post_type_pricing . '_columns',
			array( $this, 'listing_get_columns' ) );
		add_action( 'manage_' . rtcl()->post_type_pricing . '_posts_custom_column',
			array( $this, 'listing_column_content' ), 10, 2 );
	}


	function listing_get_columns( $columns ) {

		$new_columns   = array(
			'price'    => __( 'Price', 'classified-listing' ),
			'featured' => __( 'Featured', 'classified-listing' ),
			'visible'  => __( 'Visible', 'classified-listing' )
		);
		$target_column = 'title';

		return Functions::array_insert_after( $target_column, $columns, $new_columns );
	}

	function listing_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'price' :
				$price = get_post_meta( $post_id, 'price', true );
				echo $price ? Functions::get_formatted_price( $price, $price ) : __( "Free", "classified-listing" );
				break;
			case 'featured' :
				$value = absint( get_post_meta( $post_id, 'featured', true ) );
				echo '<span class="rtcl-tick-cross">' . ( $value == 1 ? '&#x2713;' : '&#x2717;' ) . '</span>';
				break;
			case 'visible' :
				echo absint( get_post_meta( $post_id, 'visible', true ) );
				break;

		}
	}

}