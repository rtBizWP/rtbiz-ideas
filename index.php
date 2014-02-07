<?php
/*
  Plugin Name: WordPress Ideas
  Plugin URI: http://git.rtcamp.com/crm/wordpress-ideas
  Description: User submitted ideas/feature-request tracking like http://www.uservoice.com/
  Version: 0.1-beta
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
	 *  The server file system path to the plugin directory
	 *
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

/**
 * Auto Loader Function
 *
 * Autoloads classes on instantiation. Used by spl_autoload_register.
 *
 * @param string $class_name The name of the class to autoload
 */
function rt_wp_ideas_autoloader( $class_name ) {
	$rt_wp_ideas_lib_path = array(
		'app/services/' . $class_name . '.php',
	);
	foreach ( $rt_wp_ideas_lib_path as $path ) {
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
spl_autoload_register( 'rt_wp_ideas_autoloader' );

/**
 * Instantiate the rtWordPressIdeas class.
 */

global $rtWpIdeas;
$rtWpIdeas = new RTWPIdeas();

/*
 * Look Ma! Very few includes! Next File: /app/main/RTWPIdeas.php
 */