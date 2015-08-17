<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Votes' ) ) {
	/**
	 * Class Rtbiz_Ideas_Votes
	 *
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Votes {

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_rtbiz_ideas_vote', $this, 'idea_vote_callback' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_rtbiz_ideas_vote', $this, 'idea_vote_callback' );
		}

		/**
		 * Vote functionality - Up and Down
		 */
		public function idea_vote_callback() {
			$response = array();

			if ( ! is_user_logged_in() ) {
				$response['err'] = 'Please login to vote.';
			} else {

				$postid = intval( $_POST['postid'] );
				if ( get_post_status( $postid ) == 'idea-new' ) {
					$is_voted = $this->check_user_voted( $postid );
					if ( isset( $is_voted ) && $is_voted ) {
						rtbiz_ideas_update_vote( $postid, 0 );
						$votes = get_post_meta( $postid, '_rt_wpideas_meta_votes', true );
						$votes = $votes - 1;
						update_post_meta( $postid, '_rt_wpideas_meta_votes', $votes );
						$response['btnLabel'] = 'Vote Up';
					} else if ( isset( $is_voted ) && ! $is_voted ) {
						rtbiz_ideas_update_vote( $postid, 1 );
						$votes = get_post_meta( $postid, '_rt_wpideas_meta_votes', true );
						$votes = $votes + 1;
						update_post_meta( $postid, '_rt_wpideas_meta_votes', $votes );
						$response['btnLabel'] = 'Vote Down';
					} else if ( ! isset( $is_voted ) ) {
						rtbiz_ideas_add_vote( $postid );
						$votes = get_post_meta( $postid, '_rt_wpideas_meta_votes', true );
						$votes = $votes + 1;
						update_post_meta( $postid, '_rt_wpideas_meta_votes', $votes );
						$response['btnLabel'] = 'Vote Down';
					}
					$response['vote'] = rtbiz_ideas_get_votes_by_idea( $postid );
				} else {
					$response['err'] = 'You can not vote on ' . get_post_status( $postid ) . ' idea.';
				}
			}
			echo json_encode( $response );
			wp_die( ); // this is required to return a proper result
		}

		/**
		 * @param $idea
		 *
		 * @return bool|null
		 */
		public function check_user_voted( $idea ) {
			global $rtbiz_ideas_votes_model;
			$user = get_current_user_id();
			$row  = $rtbiz_ideas_votes_model->check_user_voted( $idea, $user );
			if ( ! empty( $row[0] ) && $row[0]->vote_count == 1 ) {
				return true;
			} else if ( ! empty( $row[0] ) && $row[0]->vote_count == 0 ) {
				return false;
			} else if ( empty( $row[0] ) ) {
				return null;
			}
		}


		/**
		 * Add data to the wpideas_vote table\
		 *
		 * @param $post
		 * @param int $vote_count
		 *
		 * @return mixed
		 */
		public function add_vote( $post_id, $vote_count = 1 ) {
			global $rtbiz_ideas_subscriber_model, $rtbiz_ideas_votes_model;

			$user = get_current_user_id();
			$rtbiz_ideas_subscriber_model->add_subscriber( $post_id, $user );
			$data = array( 'post_id' => $post_id, 'user_id' => $user, 'vote_count' => $vote_count, );
			return $rtbiz_ideas_votes_model->add_vote( $data );
		}

		/**
		 * Update vote for the post
		 *
		 * @param $post
		 * @param $vote_count
		 *
		 * @return mixed
		 */
		public function update_vote( $post_id, $vote_count ) {
			global $rtbiz_ideas_subscriber_model, $rtbiz_ideas_votes_model;

			$user  = get_current_user_id();
			$rtbiz_ideas_subscriber_model->add_subscriber( $post_id, $user );

			$where = array( 'post_id' => $post_id, 'user_id' => $user, );
			$data  = array( 'vote_count' => $vote_count, );
			return $rtbiz_ideas_votes_model->update_vote( $data, $where );
		}

		/**
		 * Removes the vote entry for the user and post
		 *
		 * @return mixed
		 */
		public function delete_vote( $post_id ) {
			global $rtbiz_ideas_subscriber_model, $rtbiz_ideas_votes_model;

			$user = get_current_user_id();
			$rtbiz_ideas_subscriber_model->delete_subscriber( $post_id, $user );

			$where = array( 'post_id' => $post_id, 'user_id' => $user, );
			return $rtbiz_ideas_votes_model->delete_vote( $where );
		}

		/**
		 * Removes the vote entry for the user and post
		 *
		 * @return mixed
		 */
		public function get_votes_by_idea( $idea_id ) {
			global $rtbiz_ideas_votes_model;
			$vote_count = $rtbiz_ideas_votes_model -> get_votes_by_idea( $idea_id );
			if ( null == $vote_count ) {
				return 0;
			}
			return $vote_count;
		}

		/**
		 * @return mixed
		 */
		function get_all_ideas() {
			global $rtbiz_ideas_votes_model;
			return $rtbiz_ideas_votes_model -> get_all_ideas();
		}


	}
}
