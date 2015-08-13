<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Rtbiz-ideas
 * @subpackage Rtbiz-ideas/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rtbiz-ideas
 * @subpackage Rtbiz-ideas/public
 * @author     Your Name <email@example.com>
 */

if ( ! class_exists( 'Rtbiz_Ideas_Public' ) ) {
	class Rtbiz_Ideas_Public {

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since 1.1
		 */
		public function __construct() {
			global $rtbiz_ideas_common;

			Rtbiz_Ideas::$loader->add_filter( 'template_include', $this, 'ideas_template' );
			Rtbiz_Ideas::$loader->add_filter( 'init', $this, 'flush_rewrite_rules', 15 );
			$rtbiz_ideas_common = new Rtbiz_Ideas_Common();
		}

		/**
		 * Redirect to plugins templates directory
		 *
		 * @param type $template
		 * @return type
		 */
		public function ideas_template( $template ) {
			global $wp;
			//A Specific Custom Post Type
			if ( isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] == Rtbiz_Ideas_Module::$post_type ) {
				add_thickbox();
				$template = rtbiz_ideas_get_template( 'archive-idea.php' );
			}

			return $template;
		}

		function flush_rewrite_rules() {
			if ( is_admin() && true == get_option( 'rtbiz_ideas_flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
				delete_option( 'rtbiz_ideas_flush_rewrite_rules' );
			}
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
			wp_enqueue_style( RTBIZ_IDEAS_TEXT_DOMAIN . 'common-css', RTBIZ_IDEAS_URL . 'public/css/rtbiz-ideas-public.css', array(), RTBIZ_IDEAS_VERSION, 'all' );
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


			wp_enqueue_script( 'jquery-form', array( 'jquery' ) );
			wp_enqueue_script( RTBIZ_IDEAS_TEXT_DOMAIN, RTBIZ_IDEAS_URL . 'public/js/rtbiz-ideas-public-min.js', array( 'jquery' ), RTBIZ_IDEAS_VERSION, false );

			$ajax_url = admin_url( 'admin-ajax.php' );
			wp_localize_script( RTBIZ_IDEAS_TEXT_DOMAIN, 'ajaxurl', $ajax_url );
			wp_localize_script( RTBIZ_IDEAS_TEXT_DOMAIN, 'rtbiz_ideas_posttype', Rtbiz_Ideas_Module::$post_type );

		}

	}
}
