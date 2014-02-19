<?php

/*
  Plugin Name: WordPress Ideas
  Plugin URI: http://git.rtcamp.com/crm/wordpress-ideas
  Description: User submitted ideas/feature-request tracking like http://www.uservoice.com/
  Version: 1.1
  Author: rtCamp
  Text Domain: wp-ideas
  Author URI: http://rtcamp.com/?utm_source=dashboard&utm_medium=plugin&utm_campaign=wp-ideas
 */

/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 07/02/14
 * Time: 2:13 PM
 */
/**
 * Main file, contains the plugin metadata and activation processes
 *
 * @package rtMedia
 * @subpackage Main
 */
if ( ! defined( 'RTWPIDEAS_PATH' ) ) {
	/**
	 * The server file system path to the plugin directory
	 */
	define( 'RTWPIDEAS_PATH', plugin_dir_path( __FILE__ ) ); 
}


if ( ! defined( 'RTWPIDEAS_URL' ) ) {

	/**
	 * The url to the plugin directory
	 *
	 */
	define( 'RTWPIDEAS_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'RTWPIDEAS_BASE_NAME' ) ) {

	/**
	 * The url to the plugin directory
	 *
	 */
	define( 'RTWPIDEAS_BASE_NAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'RT_WPIDEAS_PATH_ADMIN' ) ) {
	define( 'RT_WPIDEAS_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'app/main/' );
}
if ( ! defined( 'RT_WPIDEAS_PATH_LIB' ) ) {
	define( 'RT_WPIDEAS_PATH_LIB', plugin_dir_path( __FILE__ ) . 'app/lib/' );
}
if ( ! defined( 'RT_WPIDEAS_PATH_MODELS' ) ) {
	define( 'RT_WPIDEAS_PATH_MODELS', plugin_dir_path( __FILE__ ) . 'app/models/' );
}
if ( ! defined( 'RT_WPIDEAS_PATH_HELPER' ) ) {
	define( 'RT_WPIDEAS_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'app/helper/' );
}
if ( ! defined( 'RT_WPIDEAS_SLUG' ) ) {
	define( 'RT_WPIDEAS_SLUG', 'idea' );
}

function rtwpideas_enqueue_styles_and_scripts() {
	wp_register_script( 'rtwpideas-custom-script', plugins_url( '/app/includes/js/rtwpideas-custom-script.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'rtwpideas-custom-script' );
	wp_register_style( 'rtwpideas-client-styles', plugins_url( '/app/includes/css/rtwpideas-client-styles.css', __FILE__ ) );
	wp_enqueue_style( 'rtwpideas-client-styles' );
	$ajax_url = admin_url('admin-ajax.php');
	wp_localize_script('rtwpideas-custom-script', 'rt_wpideas_ajax_url', $ajax_url);
}
add_action( 'wp_enqueue_scripts', 'rtwpideas_enqueue_styles_and_scripts' );

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rtwpideas_include_class_file( $dir ) {
	if ( $dh = opendir( $dir ) ) {
		while ( $file = readdir( $dh ) ) {
			//Loop
			if ( $file !== '.' && $file !== '..' && $file[0] !== '.' ) {
				if ( is_dir( $dir . $file ) ) {
					rtwpideas_include_class_file( $dir . $file . '/' );
				} else {
					include_once $dir . $file;
				}
			}
		}
		closedir( $dh );
		return 0;
	}
}

function rtwpideas_include() {
	$rtWooCLIncludePaths = array(
		RT_WPIDEAS_PATH_LIB,
		RT_WPIDEAS_PATH_MODELS,
		RT_WPIDEAS_PATH_HELPER,
		RT_WPIDEAS_PATH_ADMIN,
	);
	foreach ( $rtWooCLIncludePaths as $path ) {
		rtwpideas_include_class_file( $path );
	}
}

/**
 * Register the autoloader function into spl_autoload
 */
//spl_autoload_register('rtwpideas_include');

/**
 * Instantiate the rtWordPressIdeas class.
 */
function rtwpideas_init() {
	rtwpideas_include();

	// DB Upgrade
	$updateDB = new RTDBUpdate( false, RTWPIDEAS_PATH . 'index.php', RTWPIDEAS_PATH . 'app/schema/' );
	$updateDB->do_upgrade();

	global $rtWpIdeas;
	$rtWpIdeas = new RTWPIdeas();

	
}

add_action( 'init', 'rtwpideas_init', 0 );

function rtwpideas_template( $template ) {
	global $wp;
	global $wp_query;
	if ( $wp->query_vars['post_type'] == RT_WPIDEAS_SLUG ) {
		// Let's look for the your_custom_post_type_template.php template
		// file in the current theme
		if ( have_posts() ) {
			include( RTWPIDEAS_PATH . 'app/includes/plugin_template/archive-idea.php' );
			die();
		} else {
			$wp_query->is_404 = true;
		}
	}
	return $template;
}

add_filter( 'template_include', 'rtwpideas_template', 99 );

/*
 * Look Ma! Very few includes! Next File: /app/main/RTWPIdeas.php
 */
