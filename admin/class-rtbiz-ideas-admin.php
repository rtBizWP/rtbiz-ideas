<?php
/*
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rtbiz-ideas
 * @subpackage Rtbiz-ideas/admin
 * @author     dipesh <dipesh.kakadiya@rtcamp.com>
 */
if ( ! class_exists( 'Rtbiz_Ideas_Admin' ) ) {

	class Rtbiz_Ideas_Admin {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since 1.1
		 */
		public function __construct() {

		}

		public function init_admin() {
			global $rtbiz_ideas_subscriber_model, $rtbiz_ideas_votes_model,
			       $rtbiz_ideas_module, $rtbiz_ideas_notification, $rtbiz_ideas_votes, $rtbiz_ideas_woo, $rtbiz_ideas_attributes, $rtbiz_ideas_settings;

			$rtbiz_ideas_subscriber_model = new Rtbiz_Ideas_Subscriber_Model();
			$rtbiz_ideas_votes_model = new Rtbiz_Ideas_Votes_Model();

			$rtbiz_ideas_module       = new Rtbiz_Ideas_Module();
			$rtbiz_ideas_notification = new Rtbiz_Ideas_Notification();

			$rtbiz_ideas_votes = new Rtbiz_Ideas_Votes();
			$rtbiz_ideas_woo = new Rtbiz_Ideas_Woo();

			$rtbiz_ideas_attributes = new Rtbiz_Ideas_Attributes();
			$rtbiz_ideas_settings = new Rtbiz_Ideas_Settings();

		}

		public function database_update() {
			$updateDB = new RT_DB_Update( trailingslashit( RTBIZ_IDEAS_PATH ) . 'rtbiz-ideas.php', trailingslashit( RTBIZ_IDEAS_PATH . 'admin/schema/' ) );
			$updateDB->do_upgrade();
		}

		public function module_register( $modules ) {

			$modules[ rtbiz_sanitize_module_key( RTBIZ_IDEAS_TEXT_DOMAIN ) ] = array(
				'label'           => __( 'Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'post_types'      => array( Rtbiz_Ideas_Module::$post_type ),
				'product_support' => array( Rtbiz_Ideas_Module::$post_type ),
				'setting_option_name' => Rtbiz_Ideas_Settings::$ideas_opt, // Use for setting page acl to add manage_options capability
				'setting_page_url' => admin_url( 'edit.php?post_type=' . Rtbiz_Ideas_Module::$post_type . '&page=' . Rtbiz_Ideas_Settings::$page_slug ), //
			);

			return $modules;
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since 1.1
		 */
		public function enqueue_styles() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Plugin_Name_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Plugin_Name_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since 1.1
		 */
		public function enqueue_scripts() {

			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Plugin_Name_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Plugin_Name_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */
		}

	}

}
