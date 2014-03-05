<?php

/*
  Plugin Name: WordPress Ideas
  Plugin URI: http://git.rtcamp.com/crm/wordpress-ideas
  Description: User submitted ideas/feature-request tracking like http://www.uservoice.com/
  Version: 1.0
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
 * @package rtWpIdeas
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

    /**
     * The url to the app/admin directory
     *
     */
	define( 'RT_WPIDEAS_PATH_ADMIN', plugin_dir_path( __FILE__ ) . 'app/admin/' );
}
if ( ! defined( 'RT_WPIDEAS_PATH_MAIN' ) ) {

    /**
     * The url to the app/main directory
     *
     */
	define( 'RT_WPIDEAS_PATH_MAIN', plugin_dir_path( __FILE__ ) . 'app/main/' );
}
if ( ! defined( 'RT_WPIDEAS_PATH_LIB' ) ) {

    /**
     * The url to the app/lib directory
     *
     */
    define( 'RT_WPIDEAS_PATH_LIB', plugin_dir_path( __FILE__ ) . 'app/lib/' );
}
if ( ! defined( 'RT_WPIDEAS_PATH_HELPER' ) ) {

    /**
     * The url to the app/helper directory
     *
     */
	define( 'RT_WPIDEAS_PATH_HELPER', plugin_dir_path( __FILE__ ) . 'app/helper/' );
}
if ( ! defined( 'RT_WPIDEAS_SLUG' ) ) {

    /**
     * The post type / slug for the plugin - 'idea'
     *
     */
	define( 'RT_WPIDEAS_SLUG', 'idea' );
}

function rtwpideas_enqueue_styles_and_scripts() {
	wp_register_script( 'rtwpideas-custom-script', plugins_url( '/app/assets/js/rtwpideas-custom-script.js', __FILE__ ), array( 'jquery' ) );
	wp_enqueue_script( 'rtwpideas-custom-script' );
	wp_register_style( 'rtwpideas-client-styles', plugins_url( '/app/assets/css/rtwpideas-client-styles.css', __FILE__ ) );
	wp_enqueue_style( 'rtwpideas-client-styles' );
	$ajax_url = admin_url( 'admin-ajax.php' );
	wp_localize_script( 'rtwpideas-custom-script', 'rt_wpideas_ajax_url', $ajax_url );
	//wp_register_script( 'validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js', array( 'jquery' ) );
	//wp_enqueue_script( 'validation' );
}

add_action( 'wp_enqueue_scripts', 'rtwpideas_enqueue_styles_and_scripts' );

/**
 * Loader function for all the classes
 * @param $class_name
 */
function rt_wordpress_idea_autoloader( $class_name ) {
	$rtlibpath = array(
		'app/admin/' . $class_name . '.php',
		'app/helper/' . $class_name . '.php',
		'app/main/' . $class_name . '.php',
		'app/lib/rtdbmodel/' . $class_name . '.php',
	);
	foreach ( $rtlibpath as $path ) {
		$path = RTWPIDEAS_PATH . $path;
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

include_once 'app/helper/wpideas-votes.php';
include_once 'app/helper/wpideas-common.php';
/**
 * Instantiate the RTWPIdeas class.
 */
global $rtWpIdeas;
$rtWpIdeas = new RTWPIdeas();
/*
 * Look Ma! Very few includes! Next File: /app/main/RTWPIdeas.php
 */