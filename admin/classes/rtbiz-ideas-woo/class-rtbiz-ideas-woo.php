<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Woo' ) ) {
	/**
	 * Class Rtbiz_Ideas_Woo
	 *
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Woo {

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {
			Rtbiz_Ideas::$loader->add_filter( 'woocommerce_product_tabs', $this, 'woo_ideas_tab', 999 );
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
				'callback' => array( $this, 'woo_ideas_tab_content' ),
			);

			return $tabs;
		}

		/**
		 *
		 */
		public function woo_ideas_tab_content() {
			global $post;
			if ( isset( $post ) ) {
				echo sanitize_html_class( do_shortcode( '[rtbiz_ideas product_id = ' . $post->ID . ' ]' ) );
			}
		}

	}
}
