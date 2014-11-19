<?php

/*
  Plugin Name: rtBiz Ideas
  Plugin URI: https://rtcamp.com
  Description: User submitted ideas/feature-request tracking for General Purpose. Also WooCommerce Support added.
  Version: 1.0.4
  Author: rtCamp
  Text Domain: rtbiz-ideas
  Author URI: https://rtcamp.com
 */

/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package rtbiz-ideas
 * @subpackage Main
 */
if ( ! defined( 'RTBIZ_IDEAS_PATH' ) ) {
    /**
     * The server file system path to the plugin directory
     */
    define( 'RTBIZ_IDEAS_PATH', plugin_dir_path( __FILE__ ) );
}


if ( ! defined( 'RT_IDEA_TEXT_DOMAIN' ) ) {
    /**
     * The server file system path to the plugin directory
     */
    define( 'RT_IDEA_TEXT_DOMAIN', 'rtbiz-ideas' );
}


if ( ! defined( 'RTBIZ_IDEAS_URL' ) ) {

    /**
     * The url to the plugin directory
     *
     */
	define( 'RTBIZ_IDEAS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_IDEAS_BASE_NAME' ) ) {

    /**
     * The url to the plugin directory
     *
     */
	define( 'RTBIZ_IDEAS_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'RTBIZ_IDEAS_PATH_ADMIN' ) ) {

    /**
     * The url to the app/admin directory
     *
     */
	define( 'RTBIZ_IDEAS_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'app/admin/' );
}
if ( ! defined( 'RTBIZ_IDEAS_PATH_MAIN' ) ) {

    /**
     * The url to the app/main directory
     *
     */
	define( 'RTBIZ_IDEAS_PATH_MAIN', plugin_dir_path( __FILE__ ) . 'app/main/' );
}
if ( ! defined( 'RTBIZ_IDEAS_PATH_LIB' ) ) {

    /**
     * The url to the app/lib directory
     *
     */
    define( 'RTBIZ_IDEAS_PATH_LIB', plugin_dir_path( __FILE__ ) . 'app/lib/' );
}
if ( ! defined( 'RTBIZ_IDEAS_PATH_HELPER' ) ) {

    /**
     * The url to the app/helper directory
     *
     */
	define( 'RTBIZ_IDEAS_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'app/helper/' );
}
if ( ! defined( 'RTBIZ_IDEAS_PATH_SETTINGS' ) ) {

	/**
	 * The url to the app/helper directory
	 *
	 */
	define( 'RTBIZ_IDEAS_PATH_SETTINGS', plugin_dir_path( __FILE__ ) . 'app/settings/' );
}

if ( ! defined( 'RTBIZ_IDEAS_SLUG' ) ) {

    /**
     * The post type / slug for the plugin - 'idea'
     *
     */
	define( 'RTBIZ_IDEAS_SLUG', 'idea' );
}

if ( ! defined( 'RTBIZ_IDEAS_PATH_TEMPLATES' ) ) {

    /**
     * The url to the templates directory
     *
     */
	define( 'RTBIZ_IDEAS_PATH_TEMPLATES', plugin_dir_path( __FILE__ ) . 'templates/' );
}

if ( ! defined( 'RTBIZ_IDEAS_PATH_VENDOR' ) ) {

    /**
     * The url to the app/helper directory
     *
     */
	define( 'RTBIZ_IDEAS_PATH_VENDOR', plugin_dir_path( __FILE__ ) . 'app/vendor/' );
}

function rtwpideas_enqueue_styles_and_scripts() {
	wp_register_script( 'rtwpideas-custom-script', plugins_url( '/app/assets/js/rtwpideas-custom-script.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'rtwpideas-custom-script' );
	wp_register_style( 'rtwpideas-client-styles', plugins_url( '/app/assets/css/rtwpideas-client-styles.css', __FILE__ ) );
	wp_enqueue_style( 'rtwpideas-client-styles' );
	$ajax_url = admin_url( 'admin-ajax.php' );
	wp_localize_script( 'rtwpideas-custom-script', 'rt_wpideas_ajax_url', $ajax_url );
	wp_enqueue_script( 'jquery-form', array( 'jquery' ), false, true );
	//wp_register_script( 'validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array( 'jquery' ) );
	//wp_enqueue_script( 'validation' );
}

add_action( 'wp_enqueue_scripts', 'rtwpideas_enqueue_styles_and_scripts' );

include_once RTBIZ_IDEAS_PATH_VENDOR . 'taxonomy-metadata.php';

/**
 * Loader function for all the classes
 * @param $class_name
 */
function rt_wordpress_idea_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/admin/' . $class_name . '.php',
		'app/helper/' . $class_name . '.php',
		'app/settings/' . $class_name . '.php',
		'app/main/' . $class_name . '.php',
		'app/lib/rtdbmodel/' . $class_name . '.php',
	);
	foreach ( $rtlibpath as $path ) {
		$path = RTBIZ_IDEAS_PATH . $path;
		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}

/**
 * Register the autoloader function into spl_autoload
 */
spl_autoload_register( 'rt_wordpress_idea_autoloader' );

include_once RTBIZ_IDEAS_PATH_LIB . 'rt-lib.php';


function rtbiz_idea_loader(){
	include_once 'app/helper/wpideas-votes.php';
	include_once 'app/helper/wpideas-common.php';
	include_once 'app/settings/class-redux-framework-idea-config.php';
	include_once 'app/helper/rt_idea_functions.php';
	require_once RTBIZ_IDEAS_PATH_VENDOR . 'redux/ReduxCore/framework.php';

	/**
	 * Instantiate the RTWPIdeas class.
	 */
	global $rtWpIdeas, $rtWpIdeasAttributes, $taxonomy_metadata, $reduxFrameworkIdeaConfig;
	$reduxFrameworkIdeaConfig = new Redux_Framework_Idea_Config();
	$rtWpIdeas = new RTWPIdeas();
	$rtWpIdeasAttributes = new RTWPIdeasAttributes();
	add_action( 'init', 'do_flush_rewrite_rules_idea' ,20 );
}

function do_flush_rewrite_rules_idea(){
	if ( is_admin() && 'true' == get_option( 'rt_idea_call_rewrite' ) ) {
		flush_rewrite_rules();
		delete_option( 'rt_idea_call_rewrite' );
	}
}

function init_call_flush_rewrite_rules_idea(){
	add_option( 'rt_idea_call_rewrite', 'true' );
}
add_action( 'rt_biz_init', 'rtbiz_idea_loader', 1 );
//add_action( 'plugins_loaded', 'rtbiz_idea_loader', 10 );

register_activation_hook( __FILE__, 'init_call_flush_rewrite_rules_idea' );

/*
 * Look Ma! Very few includes! Next File: /app/main/RTWPIdeas.php
 */


add_action('init','check_Rtbiz_install');

function check_rtbiz_install(){
	global $rtbiz_plugins;
	$rtbiz_plugins = array(
		'rtbiz' => array(
			'project_type' => 'all', 'name' => esc_html__( 'WordPress for Business.', 'rt_biz' ), 'active' => class_exists( 'Rt_Biz' ), 'filename' => 'index.php',),
	);
	$flag          = true;
	$used_function = array(
		'rt_biz_get_module_users',
		'rt_biz_get_entity_meta',
		'rt_biz_get_post_for_organization_connection',
		'rt_biz_get_post_for_person_connection',
		'rt_biz_get_organization_post_type',
		'rt_biz_get_person_post_type',
		'rt_biz_search_organization',
		'rt_biz_add_organization',
		'rt_biz_organization_connection_to_string',
		'rt_biz_connect_post_to_organization',
		'rt_biz_clear_post_connections_to_organization',
		'rt_biz_sanitize_module_key',
		'rt_biz_get_access_role_cap',
		'rt_biz_get_person_by_email',
		'rt_biz_add_person',
		'rt_biz_add_entity_meta',
		'rt_biz_person_connection_to_string',
		'rt_biz_connect_post_to_person',
		'rt_biz_get_organization_to_person_connection',
		'rt_biz_search_person',
		'rt_biz_connect_organization_to_person',
		'rt_biz_clear_post_connections_to_person',
		'rt_biz_register_person_connection',
		'rt_biz_register_organization_connection',
		'rt_biz_get_organization_capabilities',
		'rt_biz_get_person_capabilities',
		'rt_biz_get_person_meta_fields',
		'rt_biz_get_organization_meta_fields',
	);

	foreach ( $used_function as $fn ) {
		if ( ! function_exists( $fn ) ) {
			$flag = false;
		}
	}

	if ( ! $flag ) {
		function rtbiz_plugins_enque_js() {
			wp_enqueue_script( 'rtbiz-hd-plugins_idea', RTBIZ_IDEAS_URL . 'app/assets/js/rtbiz_plugin_check.js', '', false, true );
			wp_localize_script( 'rtbiz-hd-plugins_idea', 'rtbiz_ajax_url', admin_url( 'admin-ajax.php' ) );
		}
		add_action( 'admin_enqueue_scripts', 'rtbiz_plugins_enque_js' );
		add_action( 'wp_ajax_rtBiz_idea_active_plugin',  'rt_biz_idea_activate_plugin_ajax' , 10 );
		add_action( 'admin_notices', 'admin_notice_rtbiz_not_installed'  );
	}

	return $flag;
}



/**
 * if rtbiz plugin is not installed or activated it gives notification to user to do so.
 *
 * @since 0.1
 */
function admin_notice_rtbiz_not_installed() {
	?>
	<div class="error rtBiz-not-installed-error">
		<?php
		if ( rt_idea_is_rt_biz_plugin_installed( 'rtbiz' ) && ! rt_idea_is_rt_biz_plugin_active( 'rtbiz' ) ) {
			$path  = rt_idea_get_path_for_rt_biz_plugins( 'rtbiz' );
			$nonce = wp_create_nonce( 'rtBiz_activate_plugin_' . $path );
			?>
			<p><b><?php _e( 'rtBiz Idea:' ) ?></b> <?php _e( 'Click' ) ?> <a href="#"
			                                                                     onclick="activate_rtBiz_plugins('<?php echo $path ?>','rtBiz_idea_active_plugin','<?php echo $nonce; ?>')">here</a> <?php _e( 'to activate rtBiz.', 'rtbiz' ) ?>
			</p>
		<?php } else { ?>
			<p><b><?php _e( 'rtBiz Idea:' ) ?></b> <?php _e( 'rtBiz Core plugin is not found on this site. Please install & activate it in order to use this plugin.', RT_HD_TEXT_DOMAIN ); ?></p>
		<?php } ?>
	</div>
<?php
}

function rt_idea_get_path_for_rt_biz_plugins( $slug ) {
	global $rtbiz_plugins;
	$filename = ( ! empty( $rtbiz_plugins[ $slug ]['filename'] ) ) ? $rtbiz_plugins[ $slug ]['filename'] : $slug . '.php';

	return $slug . '/' . $filename;
}

function rt_idea_is_rt_biz_plugin_active( $slug ) {
	global $rtbiz_plugins;
	if ( empty( $rtbiz_plugins[ $slug ] ) ) {
		return false;
	}

	return $rtbiz_plugins[ $slug ]['active'];
}

function rt_idea_is_rt_biz_plugin_installed( $slug ) {
	global $rtbiz_plugins;
	if ( empty( $rtbiz_plugins[ $slug ] ) ) {
		return false;
	}

	if (rt_idea_is_rt_biz_plugin_active( $slug ) || file_exists( WP_PLUGIN_DIR . '/' .rt_idea_get_path_for_rt_biz_plugins( $slug ) ) ) {
		return true;
	}

	return false;
}

/**
 * ajax call for active plugin
 */
function rt_biz_idea_activate_plugin_ajax() {
	if ( empty( $_POST['path'] ) ) {
		die( __( 'ERROR: No slug was passed to the AJAX callback.', 'rt_biz' ) );
	}
	check_ajax_referer( 'rtBiz_activate_plugin_' . $_POST['path'] );

	if ( ! current_user_can( 'activate_plugins' ) ) {
		die( __( 'ERROR: You lack permissions to activate plugins.', 'rt_biz' ) );
	}

	idea_rt_biz_activate_plugin( $_POST['path'] );

	echo 'true';
	die();
}

/**
 * @param $plugin_path
 * ajax call for active plugin calls this function to active plugin
 */
function idea_rt_biz_activate_plugin( $plugin_path ) {

	$activate_result = activate_plugin( $plugin_path );
	if ( is_wp_error( $activate_result ) ) {
		die( sprintf( __( 'ERROR: Failed to activate plugin: %s', 'rt_biz' ), $activate_result->get_error_message() ) );
	}
}

