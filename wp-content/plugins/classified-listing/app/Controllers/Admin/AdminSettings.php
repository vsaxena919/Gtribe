<?php

namespace Rtcl\Controllers\Admin;


use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmail;
use Rtcl\Models\SettingsAPI;

class AdminSettings extends SettingsAPI {

	protected $tabs = array();
	protected $active_tab;
	protected $current_section;
	protected $gateway_temp_desc;


	public function __construct() {
		add_action( 'admin_init', array( $this, 'setTabs' ) );
		add_action( 'admin_init', array( $this, 'save' ) );
		add_action( 'admin_menu', array( $this, 'add_listing_types_menu' ), 1 );
		add_action( 'admin_menu', array( $this, 'add_settings_menu' ), 50 );
		add_action( 'admin_menu', array( $this, 'add_import_menu' ), 60 );
		add_action( 'rtcl_admin_settings_groups', array( $this, 'setup_settings' ) );
		add_filter( 'plugin_action_links_classified-listing/classified-listing.php', array( $this, 'rtcl_marketing' ) );
		add_action( 'admin_init', array( $this, 'preview_emails' ) );
	}

	function rtcl_marketing( $links ) {
		$links[] = '<a target="_blank" href="' . esc_url( 'https://radiustheme.com/demo/wordpress/classified' ) . '">' . __( 'Demo', 'classified-listing' ) . '</a>';
		$links[] = '<a target="_blank" href="' . esc_url( 'https://www.radiustheme.com/setup-configure-classified-listing-wordpress/' ) . '">' . __( 'Documentation', 'classified-listing' ) . '</a>';
		$links[] = '<a target="_blank" style="color: #39b54a;font-weight: 700;" href="' . esc_url( 'https://www.radiustheme.com/downloads/classified-listing-pro-wordpress/' ) . '">' . __( 'Get Pro', 'classified-listing' ) . '</a>';

		return $links;
	}

	public function add_listing_types_menu() {
		add_submenu_page(
			'edit.php?post_type=' . rtcl()->post_type,
			__( 'Listing Types', 'classified-listing' ),
			__( 'Listing Types', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-listing-type',
			array( $this, 'display_listing_type' )
		);
	}

	public function add_import_menu() {
		add_submenu_page(
			'edit.php?post_type=' . rtcl()->post_type,
			__( 'Import', 'classified-listing' ),
			__( 'Import', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-import-export',
			array( $this, 'display_import_export' )
		);
	}

	public function add_settings_menu() {

		add_submenu_page(
			'edit.php?post_type=' . rtcl()->post_type,
			__( 'Settings', 'classified-listing' ),
			__( 'Settings', 'classified-listing' ),
			'manage_rtcl_options',
			'rtcl-settings',
			array( $this, 'display_settings_form' )
		);

	}

	function display_listing_type() {
		require_once RTCL_PATH . 'views/settings/listing-type.php';
	}

	function display_settings_form() {
		require_once RTCL_PATH . 'views/settings/admin-settings-display.php';
	}

	function display_import_export() {
		require_once RTCL_PATH . 'views/settings/import-export.php';
	}


	function setup_settings() {
		if ( $this->current_section && $this->active_tab == 'payment' ) {
			$gateway = Functions::get_payment_gateway( $this->current_section );
			if ( $gateway ) {
				$gateway->init_form_fields();
				$gateway->option   = $this->option;
				$this->form_fields = $gateway->form_fields;
			}
		} else {
			$this->set_fields();
		}
		$this->admin_options();
	}

	function set_fields() {
		$field     = array();
		$file_name = RTCL_PATH . "views/settings/{$this->active_tab}-settings.php";
		if ( file_exists( $file_name ) ) {
			$field = include( $file_name );
		}

		$this->form_fields = apply_filters( 'rtcl_settings_option_fields', $field, $this->active_tab );
	}

	protected function payment_subsections() {
		$sections         = array(
			'' => __( "Checkout option", "classified-listing" )
		);
		$payment_gateways = rtcl()->payment_gateways();
		foreach ( $payment_gateways as $gateway ) {
			$title                                  = empty( $gateway->method_title ) ? ucfirst( $gateway->id ) : $gateway->method_title;
			$sections[ strtolower( $gateway->id ) ] = esc_html( $title );
		}
		$this->subtabs = $sections;
	}

	public function payment_sub_section_section_callback() {
		echo "<p>" . wp_kses( $this->gateway_temp_desc,
				array( 'a' => array( 'href' => array(), 'title' => array() ) ) ) . "</p>";
	}

	public function save() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD']
		     || ! isset( $_REQUEST['post_type'] )
		     || ! isset( $_REQUEST['page'] )
		     || ( isset( $_REQUEST['post_type'] ) && rtcl()->post_type !== $_REQUEST['post_type'] )
		     || ( isset( $_REQUEST['rtcl_settings'] ) && 'rtcl_settings' !== $_REQUEST['rtcl_settings'] )
		) {
			return;
		}
		if ( empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'rtcl-settings' ) ) {
			die( __( 'Action failed. Please refresh the page and retry.', 'classified-listing' ) );
		}
		if ( $this->current_section && $this->active_tab == 'payment' ) {
			$gateway = Functions::get_payment_gateway( $this->current_section );
			if ( $gateway ) {
				$gateway->init_form_fields();
				$gateway->option   = $this->option;
				$this->form_fields = $gateway->form_fields;
			}
		} else {
			$this->set_fields();
		}
		$this->process_admin_options();

		self::add_message( __( 'Your settings have been saved.', 'classified-listing' ) );

		do_action( 'rtcl_admin_settings_saved', $this->option, $this );
	}

	function setTabs() {
		$this->tabs = array(
			'general'    => __( 'General', 'classified-listing' ),
			'moderation' => __( 'Moderation', 'classified-listing' ),
			'payment'    => __( 'Payment', 'classified-listing' ),
			'email'      => __( 'Email', 'classified-listing' ),
			'account'    => __( 'Account & Policy', 'classified-listing' ),
			'style'      => __( 'Style', 'classified-listing' ),
			'misc'       => __( 'Misc', 'classified-listing' ),
			'advanced'   => __( 'Advanced', 'classified-listing' ),
			'tools'      => __( 'Tools', 'classified-listing' ),
		);

		// Hook to register custom tabs
		$this->tabs = apply_filters( 'rtcl_register_settings_tabs', $this->tabs );
		// Find the active tab
		$this->option = $this->active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'],
			$this->tabs ) ? $_GET['tab'] : 'general';
		if ( $this->active_tab == 'payment' ) {
			$this->payment_subsections();
		}
		if ( ! empty( $this->subtabs ) ) {
			$this->current_section = isset( $_GET['section'] ) && in_array( $_GET['section'],
				array_filter( array_keys( $this->subtabs ) ) ) ? $_GET['section'] : '';
			$this->option          = ! empty( $this->current_section ) ? $this->option . '_' . $this->current_section : $this->active_tab . "_settings";
		} else {
			$this->option = $this->option . "_settings";
		}

	}

	public function preview_emails() {
		if ( isset( $_GET['preview_rtcl_mail'] ) ) {
			if ( ! ( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'preview-mail' ) ) ) {
				die( 'Security check' );
			}

			// load the mailer class.
			$mailer = rtcl()->mailer();

			// get the preview email subject.
			$email_heading = __( 'HTML email template', 'classified-listing' );

			// get the preview email content.
			ob_start();
			include( RTCL_PATH . "views/html-email-template-preview.php" );
			$message = ob_get_clean();

			// create a new email.
			$email = new RtclEmail();
			$email->set_heading( $email_heading );

			// wrap the content with the email template and then add styles.
			$message = apply_filters( 'rtcl_mail_content', $email->style_inline( $mailer->wrap_message( $message, $email ) ) );

			// print the preview email.
			// phpcs:ignore WordPress.Security.EscapeOutput
			echo $message;
			// phpcs:enable
			exit;
		}
	}

}