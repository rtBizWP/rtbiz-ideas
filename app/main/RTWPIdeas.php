<?php

/**
 * RTWPIdeas - client class for plugin
 *
 * PHP version 5
 *
 * @category Development
 * @package  RTWPIdeas
 * @author   kaklo <mehul.kaklotar@rtcamp.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://rtcamp.com
 */
if ( ! class_exists( 'RTWPIdeas' ) ) {

	class RTWPIdeas {

		/**
		 *  Constructor
		 */
		public function __construct() {
			// DB Upgrade
			$updateDB = new RT_DB_Update(  RTWPIDEAS_PATH . 'index.php', RTWPIDEAS_PATH . 'app/schema/',false );
			$updateDB->do_upgrade();
			$this -> init_attributes();
			add_action( 'template_redirect', array( $this, 'rtwpideas_template' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'woo_ideas_tab' ), 999,1 );
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
			if ( isset( $wp -> query_vars[ 'post_type' ] ) && $wp -> query_vars[ 'post_type' ] == RT_WPIDEAS_SLUG ) {
                $templatefilename = 'archive-idea.php';
                if ( file_exists( RTWPIDEAS_PATH . 'templates/' . $templatefilename ) ) {
                    $return_template = RTWPIDEAS_PATH . 'templates/' . $templatefilename;
                }
                $this -> do_theme_redirect( $return_template );
			}
		}

		function do_theme_redirect( $url ) {
			global $post, $wp_query;
			add_thickbox();
			include($url);
			die();
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
			if ( isset( $post ) ) {
				echo sanitize_html_class( do_shortcode( '[wpideas product_id = ' . $post -> ID . ' ]' ) );
			}
		}

	}

}	