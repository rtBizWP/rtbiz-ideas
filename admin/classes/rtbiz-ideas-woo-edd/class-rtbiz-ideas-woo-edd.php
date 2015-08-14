<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Woo_Edd' ) ) {
	/**
	 * Class Rtbiz_Ideas_Woo
	 *
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Woo_Edd {

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {
			Rtbiz_Ideas::$loader->add_filter( 'woocommerce_product_tabs', $this, 'woo_ideas_tab', 999 );
			Rtbiz_Ideas::$loader->add_action( 'edd_after_download_content', $this, 'woo_edd_ideas_content' );
		}

		/**
		 * @param $tabs
		 *
		 * @return mixed
		 */
		public function woo_ideas_tab( $tabs ) {

			// Adds the new tab
			$tabs['ideas_tab'] = array(
				'title'    => __( 'Ideas', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'priority' => 50,
				'callback' => array( $this, 'woo_edd_ideas_content' ),
			);

			return $tabs;
		}

		/**
		 *
		 */
		public function woo_edd_ideas_content() {
			global $post;
			if ( isset( $post ) ) {
				echo sanitize_html_class( do_shortcode( '[rtbiz_ideas product_id = ' . $post->ID . ' ]' ) );
			}
		}

	}
}
