<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.1
 *
 * @package    rtbiz-ideas
 * @subpackage rtbiz-ideas/includes
 */

/**
 * The core plugin singleton class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.1
 * @package    rtbiz-ideas
 * @subpackage rtbiz-ideas/includes
 * @author     Dipesh <dipesh.kakadiya@rtcamp.com>
 */
if ( ! class_exists( 'Rtbiz_Ideas' ) ) {
	class Rtbiz_Ideas {

		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.1
		 * @access   protected
		 * @var      Rt_Biz_Ideas_Loader $loader Maintains and registers all hooks for the plugin.
		 */
		public static $loader;

		public static $templateURL;

		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.1
		 */
		public function __construct() {

			global $rtbiz_ideas_plugin_check;

			/*if ( ! $rtbiz_ideas_plugin_check->rtbiz_hd_check_plugin_dependency() ) {
				return false;
			}*/

			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();

			$this->run();
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
		 * - Plugin_Name_i18n. Defines internationalization functionality.
		 * - Plugin_Name_Admin. Defines all hooks for the admin area.
		 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since     1.1
		 * @access    private
		 */
		private function load_dependencies() {

			/**
			 * The class responsible for orchestrating the helping function
			 * core plugin.
			 */

			include_once RTBIZ_IDEAS_PATH . 'admin/helper/rtbiz-ideas-functions.php';

			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'includes/' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'admin/' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'admin/classes' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'admin/classes/models/' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'admin/classes/metabox' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'admin/helper/' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'public/' );
			new RT_WP_Autoload( RTBIZ_IDEAS_PATH . 'public/classes' );

			self::$loader = new Rtbiz_Ideas_Loader();
		}

		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.1
		 * @access   private
		 */
		private function set_locale() {
			$plugin_i18n = new Rtbiz_Ideas_i18n();
			$plugin_i18n->set_domain( RTBIZ_IDEAS_TEXT_DOMAIN );

			// called on plugins_loaded hook
			$plugin_i18n->load_plugin_textdomain();
		}

		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.1
		 * @access   private
		 */
		private function define_admin_hooks() {

			self::$templateURL = apply_filters( 'rtbiz_ideas_template_url', 'rtbiz-ideas' );

			global $rtbiz_ideas_admin;
			$rtbiz_ideas_admin = new RtBiz_Ideas_Admin( );

			$rtbiz_ideas_admin->init_admin();

			self::$loader->add_action( 'admin_init', $rtbiz_ideas_admin, 'database_update' );
			self::$loader->add_filter( 'rtbiz_modules', $rtbiz_ideas_admin, 'module_register' );

			self::$loader->add_action( 'admin_enqueue_scripts', $rtbiz_ideas_admin, 'enqueue_styles' );
			self::$loader->add_action( 'admin_enqueue_scripts', $rtbiz_ideas_admin, 'enqueue_scripts' );
		}

		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.1
		 * @access   private
		 */
		private function define_public_hooks() {

			include_once RTBIZ_IDEAS_PATH . 'public/helper/rtbiz-ideas-functions-public.php';

			$plugin_public = new Rtbiz_Ideas_Public( );

			self::$loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			self::$loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		}

		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			self::$loader->run();
		}

		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.1
		 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}

	}
}
