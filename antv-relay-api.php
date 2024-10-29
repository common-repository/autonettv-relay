<?php
/*
 * Plugin Name:       AutoNetTV Media, Inc. RelayAPI
 * Plugin URI:        https://wordpress.org/plugins/autonettv-relay/
 * Description:       Delivers AutoNet TV blog posts to your wordpress site through the RelayAPI.
 * Version:           3.0.12
 * Requires at least: 5.4
 * Requires PHP:      7.4
 * Author:            AutoNetTv Media, Inc
 * Author URI:        http://www.autonettv.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://wordpress.org/plugins/autonettv-relay/
 * Text Domain:       antv-relay-api
 * Domain Path:       /antv-relay-api
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once(ABSPATH . 'wp-config.php');
require_once(ABSPATH . 'wp-admin/includes/taxonomy.php');

define( 'AUTONETTV_RELAYAPI_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define( 'AUTONETTV_RELAYAPI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AUTONETTV_RELAYAPI_ENDPOINT', 'http://relayapi.autonettv.com/antv/v1/' );
define( 'AUTONETTV_RELAYAPI_PARENT_NAME', 'Car Care' );
define( 'AUTONETTV_RELAYAPI_PARENT_SLUG', 'car-care' );

// Call globals
global $wp_filesystem;

// You have to require following file in the front-end only. In the back-end; its already included
require_once ( ABSPATH . '/wp-admin/includes/file.php' );

// Initiate
WP_Filesystem();

wp_create_category(AUTONETTV_RELAYAPI_PARENT_NAME);

$idObj = get_category_by_slug( AUTONETTV_RELAYAPI_PARENT_SLUG );

if ( $idObj instanceof WP_Term ) {
	define( 'AUTONETTV_RELAYAPI_PARENT_CATEGORY', $idObj->term_id);
}

include( plugin_dir_path( __FILE__ ) . 'includes/autonettv-relay-api-settings-fields.php');
include( plugin_dir_path( __FILE__ ) . 'includes/autonettv-relay-api-category-fields.php');
include( plugin_dir_path( __FILE__ ) . 'includes/autonettv-relay-api-schedule-fields.php');
include( plugin_dir_path( __FILE__ ) . 'includes/autonettv-relay-api-scripts.php');
include( plugin_dir_path( __FILE__ ) . 'includes/autonettv-relay-api-menus.php');
include( plugin_dir_path( __FILE__ ) . 'includes/autonettv-relay-api-footer.php');

function antv_relay_api_javascript(){
	wp_enqueue_script("jquery");
	wp_enqueue_script("antv-relay-video-player", "https://src.api.autonettv.com/js/antv-js-player.js");
}

add_action("wp_enqueue_scripts", "antv_relay_api_javascript");

/** * Completely Remove jQuery From WordPress */
function remove_jquery() {
	if (!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', false);
	}
}
add_action('init', 'remove_jquery');