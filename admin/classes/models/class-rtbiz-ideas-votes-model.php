<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Votes_Model' ) ) {

	/**
	 * Class Rtbiz_Ideas_Subscriber_Model
	 * Modle class for Subscriber
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Votes_Model extends RT_DB_Model {

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {
			parent::__construct( 'wpideas_vote' );
		}

		/**
		 * Get all ideas
		 *
		 * @global type $wpdb
		 * @return type
		 */
		public function get_all_ideas() {
			global $wpdb;
			$querystr = 'SELECT *, COUNT(wvotes.vote_count) AS VOTE_COUNT FROM '.$wpdb->posts.' wposts, '.$wpdb->prefix.'rt_wpideas_vote wvotes WHERE wposts.ID = wvotes.post_id GROUP BY wposts.ID';
			return $wpdb->get_results( $querystr );
		}

		/**
		 * Get votes of idea
		 *
		 * @global type $wpdb
		 *
		 * @param type $idea
		 *
		 * @return type
		 */
		public function get_votes_by_idea( $idea ) {
			global $wpdb;
			$meta_key = $idea;
			return $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM '.$wpdb->prefix.'rt_wpideas_vote WHERE post_id = %s', $meta_key ) );
		}

		/**
		 * Check user has voted or not
		 *
		 * @param type $idea
		 * @param type $user
		 *
		 * @return type
		 */
		public function check_user_voted( $idea, $user ) {
			$columns = array();
			$return  = array();
			if ( ! empty( $idea ) ) {
				$columns['post_id'] = array( 'compare' => '=', 'value' => array( $idea ), );
				$columns['user_id'] = array( 'compare' => '=', 'value' => array( $user ), );
				$return             = parent::get( $columns );
			}

			return $return;
		}

		/**
		 * Search ideas
		 *
		 * @global type $wpdb
		 *
		 * @param type $txtSearch
		 *
		 * @return \WP_Query
		 */
		public function search( $txtSearch ) {
			global $wpdb;
			$row = new WP_Query( 's=' . $txtSearch . '&post_type=idea' );

			return $row;
		}

		/**
		 * Get voters of idea
		 *
		 * @param $idea_id
		 *
		 * @return array
		 */
		public function get_voters_of_idea( $idea_id ) {
			$columns = array();
			$return  = array();
			if ( ! empty( $idea_id ) ) {
				$columns['post_id'] = array( 'compare' => '=', 'value' => array( $idea_id ), );
				$return             = parent::get( $columns );
			}

			return $return;
		}

		/**
		 * Insert vote
		 *
		 * @param type $data
		 *
		 * @return type
		 */
		public function add_vote( $data ) {
			return parent::insert( $data );
		}

		/**
		 * Update vote
		 *
		 * @param type $data
		 * @param type $where
		 *
		 * @return type
		 */
		public function update_vote( $data, $where ) {
			return parent::update( $data, $where );
		}

		/**
		 * Delete vote
		 *
		 * @param type $where
		 *
		 * @return type
		 */
		public function delete_vote( $where ) {
			return parent::delete( $where );
		}

	}

}
