<?php

namespace Rtcl\Widgets;


use Rtcl\Helpers\Functions;

class Search extends \WP_Widget {

	protected $widget_slug;

	public function __construct() {

		$this->widget_slug = 'rtcl-widget-search';

		parent::__construct(
			$this->widget_slug,
			__( 'Classified Listing Search', 'classified-listing' ),
			array(
				'classname'   => 'rtcl ' . $this->widget_slug . '-class',
				'description' => __( 'A Search feature', 'classified-listing' )
			)
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 11 );

	}

	public function widget( $args, $instance ) {
		$data       = array();
		$data['id'] = wp_rand();

		$style = 'vertical';
		if ( ! empty( $instance['style'] ) && $instance['style'] === 'inline' ) {
			$style = 'inline';
		}

		$data['can_search_by_category']      = ! empty( $instance['search_by_category'] ) ? 1 : 0;
		$data['can_search_by_location']      = ! empty( $instance['search_by_location'] ) ? 1 : 0;
		$data['can_search_by_listing_types'] = ! empty( $instance['search_by_listing_types'] ) ? 1 : 0;
		$data['can_search_by_price']         = ! empty( $instance['search_by_price'] ) ? 1 : 0;

		$data['active_count'] = $data['can_search_by_category'] + $data['can_search_by_location'] + $data['can_search_by_listing_types'];

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		Functions::get_template( "widgets/search/search-form-{$style}", $data );

		echo $args['after_widget'];

	}

	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']              = ! empty( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['style']              = ! empty( $new_instance['style'] ) ? strip_tags( $new_instance['style'] ) : 'vertical';
		$instance['search_by_category'] = isset( $new_instance['search_by_category'] ) ? 1 : 0;
		$instance['search_by_location'] = isset( $new_instance['search_by_location'] ) ? 1 : 0;
		$instance['search_by_ad_type']  = isset( $new_instance['search_by_ad_type'] ) ? 1 : 0;
		$instance['search_by_price']    = isset( $new_instance['search_by_price'] ) ? 1 : 0;

		return $instance;

	}

	public function form( $instance ) {

		// Define the array of defaults
		$defaults = array(
			'title'                   => __( 'Search Listings', 'classified-listing' ),
			'style'                   => 'vertical',
			'search_by_category'      => 1,
			'search_by_location'      => 1,
			'search_by_listing_types' => 0,
			'search_by_price'         => 0
		);

		// Parse incoming $instance into an array and merge it with $defaults
		$instance = wp_parse_args(
			(array) $instance,
			$defaults
		);

		// Display the admin form
		include( RTCL_PATH . "views/widgets/search.php" );

	}

	public function enqueue_styles_scripts() {

		if ( is_active_widget( false, $this->id, $this->id_base, true ) ) {

			wp_enqueue_style( 'rtcl-public' );

		}

	}

}