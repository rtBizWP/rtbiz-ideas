<?php

/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 07/02/14
 * Time: 2:20 PM
 */
if ( ! class_exists( 'RTWPIdeas' ) ) {

	class RTWPIdeas {

		/**
		 *  Constructor
		 */
		public function __construct() {
			// DB Upgrade
			$updateDB = new RTDBUpdate( false, RTWPIDEAS_PATH . 'index.php', RTWPIDEAS_PATH . 'app/schema/' );
			$updateDB -> do_upgrade();
			$this -> init_attributes();
			add_action( "template_redirect", array( $this, 'rtwpideas_template' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'woo_ideas_tab' ) );
		}

		/**
		 * Init global variables
		 * 
		 * @global RTWPIdeasVotesModel $rtWpideasVotes
		 * @global RTWPIdeasAdmin $rtwpIdeasAdmin
		 */
		function init_attributes() {
			global $rtWpideasVotes, $rtwpIdeasAdmin;
			$rtwpIdeasAdmin = new RTWPIdeasAdmin();
			$rtWpideasVotes = new RTWPIdeasVotesModel();
		}

		/**
		 * Redirect to plugins templates directory
		 * 
		 * @param type $template
		 * @return type
		 */
		function rtwpideas_template( $template ) {
			global $wp;
			//A Specific Custom Post Type
			if ( isset( $wp -> query_vars[ 'post_type' ] ) ) {
				if ( $wp -> query_vars[ 'post_type' ] == 'idea' ) {
					$templatefilename = 'archive-idea.php';
					if ( file_exists( RTWPIDEAS_PATH . 'templates/' . $templatefilename ) ) {
						$return_template = RTWPIDEAS_PATH . 'templates/' . $templatefilename;
					}
					$this -> do_theme_redirect( $return_template );
				}
			}
		}

		function do_theme_redirect( $url ) {
			global $post, $wp_query;
			if ( have_posts() ) {
				add_thickbox();
				include($url);
				die();
			} else {
				$wp_query -> is_404 = true;
			}
		}

		function woo_ideas_tab( $tabs ) {

			// Adds the new tab

			$tabs[ 'ideas_tab' ] = array(
				'title' => __( 'Ideas', 'wp-ideas' ),
				'priority' => 50,
				'callback' => array( $this, 'woo_ideas_tab_content' ),
			);

			return $tabs;
		}

		function woo_ideas_tab_content() {
			global $post;
			echo do_shortcode( '[wpideas product_id = '.$post->ID.' ]' );
		}

	}

}	