<?php
/**
 * Fired during plugin activation
 *
 * @link       https://rtcamp.com/
 * @since      1.1
 *
 * @package    rtbiz-ideas
 * @subpackage rtbiz-ideas/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.1
 * @package    rtbiz-ideas
 * @subpackage rtbiz-ideas/includes
 * @author     Dipesh <dipesh.kakadiya@rtcamp.com>
 */
if ( ! class_exists( 'Rtbiz_Ideas_Activator' ) ) {
	class Rtbiz_Ideas_Activator {

		/**
		 * Short Description. (use period)
		 *
		 * Long Description.
		 *
		 * @since    1.0.0
		 */
		public static function activate() {

			// Add the option to redirect
			update_option( 'rtbiz_ideas_activation_redirect', true, true );

			// Plugin is activated flush rewrite rules for example.com/ticket to work
			update_option( 'rtbiz_ideas_flush_rewrite_rules', true, true );
		}

	}
}

