<?php

namespace Rtcl\Widgets;


class Widget {

	public function __construct() {
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}

	public function register_widget() {
		register_widget( Categories::class );
		register_widget( Filter::class );
		register_widget( Search::class );
		register_widget( Listings::class );
	}

}