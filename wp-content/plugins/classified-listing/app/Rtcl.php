<?php

require_once __DIR__ . './../vendor/autoload.php';

use Rtcl\Controllers\Ajax\Ajax;
use Rtcl\Controllers\Hooks\Actions;
use Rtcl\Controllers\Hooks\AdminHooks;
use Rtcl\Controllers\Hooks\FrontEndHooks;
use Rtcl\Controllers\Install;
use Rtcl\Controllers\PublicAction;
use Rtcl\Controllers\Admin\AdminController;
use Rtcl\Controllers\Query;
use Rtcl\Controllers\SessionHandler;
use Rtcl\Helpers\Functions;
use Rtcl\Models\Cart;
use Rtcl\Models\Factory;
use Rtcl\Models\PaymentGateways;
use Rtcl\Models\RtclEmails;
use Rtcl\Traits\SingletonTrait;
use Rtcl\Widgets\Widget;

/**
 * Class Rtcl
 */
final class Rtcl {

	use SingletonTrait;

	/**
	 * Query instance.
	 * @var Query
	 */
	public $query = null;
	public $post_type = "rtcl_listing";
	public $post_type_cfg = "rtcl_cfg";
	public $post_type_cf = "rtcl_cf";
	public $post_type_payment = "rtcl_payment";
	public $post_type_pricing = "rtcl_pricing";
	public $category = "rtcl_category";
	public $location = "rtcl_location";
	public $nonceId = "__rtcl_wpnonce";
	public $nonceText = "rtcl_nonce_secret";
	private $listing_types_option = "rtcl_listing_types";
	public $api = 'rtcl/v1';
	public $gallery = array();
	public $upload_directory = "classified-listing";


	/**
	 * Factory instance.
	 *
	 * @var Factory $factory
	 */
	public $factory = null;


	/**
	 * @var SessionHandler object
	 */
	public $session = false;


	/**
	 * @var Cart object
	 */
	public $cart = false;


	/**
	 * Cloning is forbidden.
	 * @since 1.0
	 */
	public function __clone() {
		Functions::doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'classified-listing' ), '1.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 1.0
	 */
	public function __wakeup() {
		Functions::doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'classified-listing' ), '1.0' );
	}

	/**
	 * Auto-load in-accessible properties on demand.
	 *
	 * @param mixed $key Key name.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( in_array( $key, array( 'init', 'payment_gateways', 'plugins_loaded' ), true ) ) {
			return $this->$key();
		}
	}


	/**
	 * Rtcl Constructor.
	 */
	public function plugins_loaded() {
		$this->define_constants();
		// Add front end hook
		if ( ! is_admin() ) {
			FrontEndHooks::init();
		}
		// Add Admin hook
		if ( is_admin() ) {
			AdminHooks::init();
		}
		// add hook for both
		Actions::init();

		$this->query = new Query();
		$this->init_hooks();
		do_action( 'rtcl_loaded' );
	}


	private function init_hooks() {
		// Do action
		do_action( 'rtcl_before_init' );
		$this->load_plugin_textdomain();
		$this->load_session();
		$this->factory = new Factory();
		$this->get_cart();
		new AdminController();
		new Ajax();
		new PublicAction();
		new Widget();
		do_action( 'rtcl_init' );
	}

	private function load_session() {
		$this->session = new SessionHandler();
		$this->session->init();
	}


	/**
	 * Load Localisation files.
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 * Locales found in:
	 *      - WP_LANG_DIR/classified-listing/classified-listing-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/classified-listing-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			global $sitepress;
			if ( method_exists( $sitepress, 'switch_lang' ) && isset( $_GET['wpml_lang'] ) && $_GET['wpml_lang'] !== $sitepress->get_default_language() ) {
				$sitepress->switch_lang( $_GET['wpml_lang'], true ); // Alternative	do_action( 'wpml_switch_language', $_GET['wpml_lang'] );
			}
		}
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'classified-listing' );
		unload_textdomain( 'classified-listing' );
		load_textdomain( 'classified-listing', WP_LANG_DIR . '/classified-listing/classified-listing-' . $locale . '.mo' );
		load_plugin_textdomain( 'classified-listing', false, plugin_basename( dirname( RTCL_PLUGIN_FILE ) ) . '/languages' );
	}


	/**
	 * Get gateways class.
	 * @return array
	 */
	public function payment_gateways() {
		return PaymentGateways::instance()->payment_gateways;
	}


	/**
	 * Email Class.
	 *
	 * @return bool|SingletonTrait|RtclEmails
	 */
	public function mailer() {
		return RtclEmails::getInstance();
	}


	private function define_constants() {
		$this->define( 'RTCL_PATH', plugin_dir_path( RTCL_PLUGIN_FILE ) );
		$this->define( 'RTCL_URL', plugins_url( '', RTCL_PLUGIN_FILE ) );
		$this->define( 'RTCL_SLUG', basename( dirname( RTCL_PLUGIN_FILE ) ) );
		$this->define( 'RTCL_SESSION_CACHE_GROUP', 'rtcl_session_id' );

	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string $name Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	public function version() {
		return RTCL_VERSION;
	}


	/**
	 * Get the template path.
	 *
	 * @return string
	 */
	public function get_template_path() {
		return apply_filters( 'rtcl_template_path', 'classified-listing/' );
	}


	public function get_listing_types_option_id() {
		return $this->listing_types_option;
	}

	public function get_assets_uri( $file ) {
		$file = ltrim( $file, '/' );

		return trailingslashit( RTCL_URL . '/assets' ) . $file;
	}



	/**
	 * Get cart object instance for online learning market.
	 *
	 * @return Cart
	 */
	public function get_cart() {
		if ( ! $this->cart ) {
			$cart_class = apply_filters( 'rtcl_cart_class', Cart::class );
			if ( is_object( $cart_class ) ) {
				$this->cart = $cart_class;
			} else {
				if ( class_exists( $cart_class ) ) {
					$this->cart = is_callable( array(
						$cart_class,
						'instance'
					) ) ? call_user_func( array( $cart_class, 'instance' ) ) : new $cart_class();
				}
			}
		}

		return $this->cart;
	}

}

/**
 * @return bool|SingletonTrait|Rtcl
 */
function rtcl() {
	return Rtcl::getInstance();
}

register_activation_hook( RTCL_PLUGIN_FILE, array( Install::class, 'activate' ) );
register_deactivation_hook( RTCL_PLUGIN_FILE, array( Install::class, 'deactivate' ) );

add_action( 'plugins_loaded', array( rtcl(), 'plugins_loaded' ) );