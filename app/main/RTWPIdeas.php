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
			if ( $wp -> query_vars[ 'post_type' ] == 'idea' ) {
				$templatefilename = 'archive-idea.php';
				if ( file_exists( RTWPIDEAS_PATH . 'templates/' . $templatefilename ) ) {
					$return_template = RTWPIDEAS_PATH . 'templates/' . $templatefilename;
				}
				$this -> do_theme_redirect( $return_template );
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

	}

}	