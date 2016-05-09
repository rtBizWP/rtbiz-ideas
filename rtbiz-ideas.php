<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rtcamp.com/
 * @since             1.1
 * @package           rtbiz-ideas
 *
 * @wordpress-plugin
 * Plugin Name:       rtBiz Ideas
 * Plugin URI:        https://rtcamp.com/
 * Description:       A WordPress based ideas/feature-request tracking for General Purpose, Also WooCommerce/EDD Support added.
 * Version:           1.1.1
 * Author:            rtCamp
 * Author URI:        https://rtcamp.com/
 * License:           GPL-2.0+
 * License URI:       https://rtcamp.com/
 * Text Domain:       rtbiz-ideas
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! defined( 'RTBIZ_IDEAS_VERSION' ) ) {
	define( 'RTBIZ_IDEAS_VERSION', '1.1.1' );
}

if ( ! defined( 'RTBIZ_IDEAS_TEXT_DOMAIN' ) ) {
	define( 'RTBIZ_IDEAS_TEXT_DOMAIN', 'rtbiz-ideas' );
}

if ( ! defined( 'RTBIZ_IDEAS_PLUGIN_FILE' ) ) {
	define( 'RTBIZ_IDEAS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'RTBIZ_IDEAS_PATH' ) ) {
	define( 'RTBIZ_IDEAS_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_IDEAS_URL' ) ) {
	define( 'RTBIZ_IDEAS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_IDEAS_BASE_NAME' ) ) {
	define( 'RTBIZ_IDEAS_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_IDEAS_PATH_TEMPLATES' ) ) {
	define( 'RTBIZ_IDEAS_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'public/templates/' );
}

/*if ( ! defined( 'EDD_RT_IDEAS_STORE_URL' ) ) {

	define( 'EDD_RT_IDEAS_STORE_URL', 'https://rtcamp.com/' );
}

if ( ! defined( 'EDD_RT_IDEAS_ITEM_NAME' ) ) {

	define( 'EDD_RT_IDEAS_ITEM_NAME', 'rtBiz Ideas' );
}*/

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rt-biz-helpdesk-activator.php
 */
function activate_rtbiz_ideas() {

	require_once RTBIZ_IDEAS_PATH . 'includes/class-rtbiz-ideas-activator.php';
	Rtbiz_Ideas_Activator::activate();

}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_rtbiz_ideas() {

	require_once RTBIZ_IDEAS_PATH . 'includes/class-rtbiz-ideas-deactivator.php';
	Rtbiz_Ideas_deactivator::deactivate();

}

register_activation_hook( RTBIZ_IDEAS_PLUGIN_FILE, 'activate_rtbiz_ideas' );
register_activation_hook( RTBIZ_IDEAS_PLUGIN_FILE, 'deactivate_rtbiz_ideas' );

require_once RTBIZ_IDEAS_PATH . 'includes/class-rtbiz-ideas-plugin-check.php';

global $rtbiz_ideas_plugin_check;

$plugins_dependency = array(
	'rtbiz' => array(
		'project_type' => 'all',
		'name'         => esc_html__( 'rtBiz', RTBIZ_IDEAS_TEXT_DOMAIN ),
		'desc' => esc_html__( 'WordPress for Business.', RTBIZ_IDEAS_TEXT_DOMAIN ),
		'active' => class_exists( 'Rt_Biz' ),
		'filename' => 'rtbiz.php',
	),
);

$rtbiz_ideas_plugin_check = new Rtbiz_Ideas_Plugin_Check( $plugins_dependency );

//add_action( 'init', array( $rtbiz_ideas_plugin_check, 'rtbiz_ideas_check_plugin_dependency' ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rtbiz_ideas() {

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require_once RTBIZ_IDEAS_PATH. 'includes/class-rtbiz-ideas.php';

	global $rtbiz_ideas;

	$rtbiz_ideas = new Rtbiz_Ideas();

}
add_action( 'rtbiz_init', 'run_rtbiz_ideas', 1 );
