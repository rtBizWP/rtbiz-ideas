<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Plugin_Check' ) ) {

	/**
	 * Class Rtbiz_Ideas_Plugin_Check
	 * Check Dependency
	 * Main class that initialize the rt-helpdesk Classes.
	 * Load Css/Js for front end
	 *
	 * @since  1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Plugin_Check {

		private $plugins_dependency = array();

		public function __construct( $plugins_dependency ) {
			$this->plugins_dependency = $plugins_dependency;
		}

		public function rtbiz_ideas_check_plugin_dependency() {

			$flag = true;

			if ( ! class_exists( 'Rtbiz' ) || ! did_action( 'rtbiz_init' ) ) {
				$flag = false;
			}

			if ( ! $flag ) {
				add_action( 'admin_init', array( $this, 'rtbiz_ideas_install_dependency' ) );
			}
			return $flag;
		}

		/**
		 * install dependency
		 */
		function rtbiz_ideas_install_dependency() {
			$biz_installed = $this->rtbiz_ideas_is_plugin_installed( 'rtbiz' );
			$isRtbizActionDone = false;
			$string = '';

			if ( ! $biz_installed ) {
				$this->rtbiz_ideas_install_plugin( 'rtbiz' );
				$isRtbizActionDone = true;
				$string = 'installed and activated <strong>rtBiz</strong> plugin.';
			}

			$rtbiz_active = $this->rtbiz_ideas_is_plugin_active( 'rtbiz' );
			if ( ! $rtbiz_active  ) {
				$rtbizpath = $this->rtbiz_ideas_get_path_for_plugin( 'rtbiz' );
				$this->rtbiz_ideas_activate_plugin( $rtbizpath );
				$isPtopActionDone = true;
				$string = 'activated <strong>rtBiz</strong> plugin.';
			}

			if ( ! empty( $string ) ) {
				$string = 'rtBiz Idea has also  ' . $string;
				update_option( 'rtbiz_ideas_dependency_installed', $string );
			}
		}

		function rtbiz_ideas_is_plugin_installed( $slug ) {
			if ( empty( $this->plugins_dependency[ $slug ] ) ) {
				return false;
			}

			if ( $this->rtbiz_ideas_is_plugin_active( $slug ) || file_exists( WP_PLUGIN_DIR . '/' . $this->rtbiz_ideas_get_path_for_plugin( $slug ) ) ) {
				return true;
			}

			return false;
		}

		public function rtbiz_ideas_is_plugin_active( $slug ) {
			if ( empty( $this->plugins_dependency[ $slug ] ) ) {
				return false;
			}

			return $this->plugins_dependency[ $slug ]['active'];
		}


		/**
		 * @param $plugin_path
		 * ajax call for active plugin calls this function to active plugin
		 */
		public function rtbiz_ideas_activate_plugin( $plugin_path ) {

			$activate_result = activate_plugin( $plugin_path );
			if ( is_wp_error( $activate_result ) ) {
				die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RTBIZ_IDEAS_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
			}
		}

		public function rtbiz_ideas_get_path_for_plugin( $slug ) {
			$filename = ( ! empty( $this->plugins_dependency[ $slug ]['filename'] ) ) ? $this->plugins_dependency[ $slug ]['filename'] : $slug . '.php';

			return $slug . '/' . $filename;
		}

		function rtbiz_ideas_install_plugin( $plugin_slug ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

			$api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

			if ( is_wp_error( $api ) ) {
				die( sprintf( __( 'ERROR: Error fetching plugin information: %s', RTBIZ_IDEAS_TEXT_DOMAIN ), $api->get_error_message() ) );
			}

			if ( ! class_exists( 'Plugin_Upgrader' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			}

			if ( ! class_exists( 'Rtbiz_HD_Plugin_Upgrader_Skin' ) ) {
				require_once( RTBIZ_IDEAS_PATH . 'admin/classes/rtbiz-ideas-plugin-upgrader-skin/class-rtbiz-ideas-plugin-upgrader-skin.php' );
			}

			$upgrader = new Plugin_Upgrader( new Rtbiz_Idea_Plugin_Upgrader_Skin( array(
				'nonce' => 'install-plugin_' . $plugin_slug,
				'plugin' => $plugin_slug,
				'api' => $api,
			) ) );

			$install_result = $upgrader->install( $api->download_link );

			if ( ! $install_result || is_wp_error( $install_result ) ) {
				// $install_result can be false if the file system isn't writable.
				$error_message = __( 'Please ensure the file system is writable', RTBIZ_IDEAS_TEXT_DOMAIN );

				if ( is_wp_error( $install_result ) ) {
					$error_message = $install_result->get_error_message();
				}

				die( sprintf( __( 'ERROR: Failed to install plugin: %s', RTBIZ_IDEAS_TEXT_DOMAIN ), $error_message ) );
			}

			$activate_result = activate_plugin( $this->rtbiz_ideas_get_path_for_plugin( $plugin_slug ) );
			if ( is_wp_error( $activate_result ) ) {
				die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RTBIZ_IDEAS_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
			}
		}

	}
}
