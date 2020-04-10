<?php
/**
 * Plugin Name: Universal Voice Search
 * Description: Allows any serach box on the page to be searchable via voice.
 * Version:     1.1.4
 * Author:      speak2web
 * Author URI:  https://speak2web.com/
 * Text Domain: universal-voice-search
 * Domain Path: /languages
 */

/**
 * Copyright (c) 2019 speak2web
 *
 * Universal Voice Search is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * Universal Voice Search is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Universal Voice Search; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'WPINC' ) or die;

include( dirname( __FILE__ ) . '/lib/requirements-check.php' );

$universal_voice_search_requirements_check = new Universal_Voice_Search_Requirements_Check( array(
    'title' => 'Universal Voice Search',
    'php'   => '5.3',
    'wp'    => '2.6',
    'file'  => __FILE__,
));

if ( $universal_voice_search_requirements_check->passes() ) {
    // Get selected language from DB and load local translation library
    $uvs_selected_language = get_option( 'uvs_selected_language', 'english' );
    $uvs_selected_language = empty($uvs_selected_language) ? 'english' : trim($uvs_selected_language);
    $uvs_language_file_name = strtolower($uvs_selected_language) === 'german' ? 'uvs_de_DE' : 'uvs_en_EN';
    include( dirname( __FILE__ ) . '/classes/plugin-languages/'.$uvs_language_file_name.'.php');

     try {
        switch (strtolower($uvs_selected_language)) {
            case 'german':
                define('UVS_LANGUAGE_LIBRARY', uvs_de_DE::UVS_LANGUAGE_LIB);
                break;
            default:
                define('UVS_LANGUAGE_LIBRARY', uvs_en_EN::UVS_LANGUAGE_LIB);
        }
    } catch (\Exception $e) {
        // Do nothing for now
    }

    // Pull in the plugin classes and initialize
    include( dirname( __FILE__ ) . '/lib/wp-stack-plugin.php' );
    include( dirname( __FILE__ ) . '/classes/uvs-admin-notices.php');
    include( dirname( __FILE__ ) . '/classes/plugin.php' );
    include( dirname( __FILE__ ) . '/classes/settings-page.php' );

    Universal_Voice_Search_Plugin::start( __FILE__ );

    // Inline plugin notices
    $path = plugin_basename( __FILE__ );

    // Register action to hook into 'after_plugin_row_' for displaying inline notice when license is missing or invalid
    if (empty(Universal_Voice_Search_Plugin::$uvs_license_key) || empty(Universal_Voice_Search_Plugin::$uvs_api_access_key)) {
        // Go Pro notice
        add_action("after_plugin_row_{$path}", function( $plugin_file, $plugin_data, $status ) {
            echo '<tr class="active">
                <th style="border-left: 4px solid #FFB908; background-color:#FFF8E5;">&nbsp;</th>
                <td colspan="2" style="background-color:#FFF8E5;">
                    <a target="blank" href="https://speak2web.com/plugin/#plan">'.UVS_LANGUAGE_LIBRARY['other']['goProNotice']['goPro'].'</a> '.UVS_LANGUAGE_LIBRARY['other']['goProNotice']['supportMoreBrowsers'].'
                </td>
            </tr>';
        }, 10, 3 );      
    }

    // Hook into plugin activation
    register_activation_hook(__FILE__, function() {
        $vdn_path   = 'voice-dialog-navigation/voice-dialog-navigation.php';
        $vf_path    = 'voice-forms/voice-forms.php';
        $plugin_url = plugin_dir_url(__FILE__ );

        if (is_plugin_active($vf_path) || is_plugin_active($vdn_path)) {
            wp_die(Uvs_Admin_Notices::uvs_denied_activation_notice($plugin_url));
        }

        // Register plugin
        Universal_Voice_Search_Plugin::uvs_register_plugin();
    });
}

unset( $universal_voice_search_requirements_check );
