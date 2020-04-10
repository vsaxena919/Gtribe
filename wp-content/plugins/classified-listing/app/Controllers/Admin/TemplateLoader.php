<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;

class TemplateLoader {

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		// Supported themes.
		if ( current_theme_supports( 'rtcl' ) ) {
			add_filter( 'template_include', array( $this, 'template_loader' ) );
		}

		add_filter( 'redirect_canonical', array( $this, 'disable_canonical_redirect_for_front_page' ) );
	}

	public function disable_canonical_redirect_for_front_page( $redirect ) {
		if ( is_page() ) {
			$listing_page_id = Functions::get_option_item( 'rtcl_advanced_settings', 'listings', 0 );
			$front_page      = get_option( 'page_on_front', 0 );
			if ( is_page( $front_page ) && $front_page == $listing_page_id ) {
				$redirect = false;
			}
		}

		return $redirect;
	}

	function template_loader( $template ) {
		if ( is_embed() ) {
			return $template;
		}

		$default_file = $this->get_template_loader_default_file();

		if ( $default_file ) {

			$search_files = $this->get_template_loader_files( $default_file );
			$template     = locate_template( $search_files );


			if ( ! $template ) {
				$template = RTCL_PATH . "templates/listings/" . $default_file;
				$template = apply_filters( 'rtcl_template_loader_fallback_file', $template, $default_file );
			}
		}

		return $template;
	}

	private function get_template_loader_default_file() {
		$default_file = '';

		if ( is_singular( rtcl()->post_type ) ) {
			$default_file = 'single-' . rtcl()->post_type . '.php';
		}

		$default_file = apply_filters( 'rtcl_template_loader_default_file', $default_file );

		return $default_file;
	}

	private function get_template_loader_files( $default_file ) {

		if ( is_page_template() ) {
			$templates[] = get_page_template_slug();
		}

		if ( is_singular( rtcl()->post_type ) ) {
			$object       = get_queried_object();
			$name_decoded = urldecode( $object->post_name );
			if ( $name_decoded !== $object->post_name ) {
				$templates[] = "single-" . rtcl()->post_type . "-{$name_decoded}.php";
			}
			$templates[] = "single-" . rtcl()->post_type . "-{$object->post_name}.php";
		}

		$templates[] = $default_file;
		$templates[] = "classified-listing/listings/" . $default_file;

		$templates = apply_filters( 'rtcl_template_loader_files', $templates, $default_file );

		return array_unique( $templates );
	}

}