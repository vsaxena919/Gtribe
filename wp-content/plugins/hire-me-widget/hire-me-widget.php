<?php
/**
 * Hire Me Widget
 *
 * Effortlessly display if your team or you are available for hire using this widget. Useful for freelance developers or designers like me.
 *
 * @link              http://www.nitin247.com/
 * @since             1.0.0
 * @package           Hire_Me_Widget
 *
 * @wordpress-plugin
 * Plugin Name:       Hire Me Widget
 * Plugin URI:        http://www.nitin247.com/wp-plugins/hire-me-widget/
 * Description:       Effortlessly display if your team or you are available for hire using this widget. Useful for freelance developers or designers like me.
 * Version:           1.0.3
 * Author:            Nitin Prakash
 * Author URI:        http://www.nitin247.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       hire-me-widget
 * Domain Path:       /languages
 */

if ( ! function_exists( 'hmw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function hmw_fs() {
        global $hmw_fs;

        if ( ! isset( $hmw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $hmw_fs = fs_dynamic_init( array(
                'id'                  => '4065',
                'slug'                => 'hire-me-widget',
                'type'                => 'plugin',
                'public_key'          => 'pk_a07b1a3c0d4acf4764f5e7d29a3c5',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'plugins.php',
                    'account'        => false,
                ),
            ) );
        }

        return $hmw_fs;
    }

    // Init Freemius.
    hmw_fs();
    // Signal that SDK was initiated.
    do_action( 'hmw_fs_loaded' );
}


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define('HMW_DIRECTORY', plugins_url().'/hire-me-widget/');

/**
 * The code that runs during plugin activation.
 */
function activate_hire_me_widget() {
 // Do Nothing    
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_hire_me_widget() {
        // Do Nothing
}

register_activation_hook( __FILE__, 'activate_hire_me_widget' );
register_deactivation_hook( __FILE__, 'deactivate_hire_me_widget' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'inc/class-hire-me-widget.php';

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */
function run_hire_me_widget() {      

$plugin = new Hire_Me_Widget();
$plugin->run();

}

add_action( 'wp_enqueue_scripts', 'hire_me_widget_styles_enqueue' );

function hire_me_widget_styles_enqueue(){
    wp_enqueue_style( 'hire-me-widget', HMW_DIRECTORY . 'assets/hmw.css' ); 
}

function hire_me_widget_action_links( $links ) {
	$links = array_merge( array(
		'<a href="' . esc_url( 'https://nitin247.com/buy-me-a-coffe' ) . '">' . __( 'Donate', 'hire_me_widget' ) . '</a>'
	), $links );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'hire_me_widget_action_links' );


// Fire the Hire Me Widget
run_hire_me_widget();
