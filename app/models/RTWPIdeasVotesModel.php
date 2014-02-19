<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTWPIdeasVotesModel
 *
 * @author kaklo
 */
if ( ! class_exists( 'RTWPIdeasVotesModel' ) ) {

	class RTWPIdeasVotesModel extends RTDBModel{
		
		public function __construct() {
			parent::__construct( 'wpideas_vote' );
		}
		
		function get_votes_by_post( $post ){
			global $wpdb;
			$meta_key = $post;
			return $wpdb -> get_var( $wpdb->prepare( 'SELECT SUM(vote_count) FROM '.$wpdb->prefix.'rt_wpideas_vote WHERE post_id = %s', $meta_key ) );
		}
		
		function get_all_ideas() {
			global $wpdb;
			$querystr = 'SELECT *, COUNT(wvotes.vote_count) AS VOTE_COUNT FROM $wpdb->posts wposts, wp_rt_wpideas_vote wvotes'
				. ' WHERE wposts.ID = wvotes.post_id'
				. ' GROUP BY wposts.ID';
			return $wpdb -> get_results( $querystr );
		}
		
		function get_votes_by_idea( $idea ) {
			global $wpdb;
			$meta_key = $idea;
			return $wpdb -> get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ".$wpdb->prefix."rt_wpideas_vote WHERE ID = %s', $meta_key ) );
		}
		
		function check_user_voted( $idea, $user ) {
			$columns = array();
			$return = array();
			if( ! empty( $idea ) ) {
				$columns['post_id'] = array (
					'compare' => '=',
					'value' => array($idea),
				);
				$columns['user_id'] = array (
					'compare' => '=',
					'value' => array($user),
				);
				$return = parent::get( $columns );
			}
			return $return;
		}
		
		function add_vote( $data ) {
			return parent::insert( $data );
		}

		function update_vote( $data, $where ) {
			return parent::update( $data, $where );
		}

		function delete_vote( $where ) {
			return parent::delete( $where );
		}
		
	}

}
