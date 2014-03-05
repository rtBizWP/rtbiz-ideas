<?php

/**
 * Add data to the wpideas_vote table
 *
 * @global type $post
 * @global type $rtWpideasVotes
 *
 * @param type  $vote_count
 *
 * @return type
 */
function add_vote( $post, $vote_count = 1 ) {
	global $rtWpideasVotes;
	$user = get_current_user_id();
	$data = array( 'post_id' => $post, 'user_id' => $user, 'vote_count' => $vote_count, );

	return $rtWpideasVotes -> add_vote( $data );
}

/**
 * Update vote for the post
 *
 * @global type $post
 * @global type $rtWpideasVotes
 *
 * @param type  $vote_count
 *
 * @return type
 */
function update_vote( $post, $vote_count ) {
	global $rtWpideasVotes;
	$user = get_current_user_id();
	$where = array( 'post_id' => $post, 'user_id' => $user, );
	$data = array( 'vote_count' => $vote_count, );

	return $rtWpideasVotes -> update_vote( $data, $where );
}

/**
 * Removes the vote entry for the user and post
 *
 * @global type $post
 * @global type $rtWpideasVotes
 * @return type
 */
function delete_vote() {
	global $post, $rtWpideasVotes;
	$user = get_current_user_id();
	$where = array( 'post_id' => $post -> ID, 'user_id' => $user, );

	return $rtWpideasVotes -> delete_vote( $where );
}

/**
 * Get votes count for the idea
 *
 * @global type $rtWpideasVotes
 *
 * @param type  $idea
 *
 * @return type
 */
function get_votes_by_idea( $idea ) {
	global $rtWpideasVotes;

	return $rtWpideasVotes -> get_votes_by_idea( $idea );
}

/**
 *
 * @global type $rtWpideasVotes
 *
 * @param type  $post
 *
 * @return type
 */
function get_votes_by_post( $post ) {
	global $rtWpideasVotes;
	$vote_count = $rtWpideasVotes -> get_votes_by_post( $post );
	if ( $vote_count == null ) {
		return 0;
	}

	return $vote_count;
}

/**
 *
 * @global type $rtWpideasVotes
 * @return type
 */
function get_all_ideas() {
	global $rtWpideasVotes;

	return $rtWpideasVotes -> get_all_ideas();
}

/**
 * Check User has voted or not
 *
 * @global type $rtWpideasVotes
 *
 * @param type  $idea
 *
 * @return boolean|null
 */
function check_user_voted( $idea ) {
	global $rtWpideasVotes;
	$user = get_current_user_id();
	$row = $rtWpideasVotes -> check_user_voted( $idea, $user );
	if ( ! empty( $row[ 0 ] ) && $row[ 0 ] -> vote_count == 1 ) {
		return true;
	} else if ( ! empty( $row[ 0 ] ) && $row[ 0 ] -> vote_count == 0 ) {
		return false;
	} else if ( empty( $row[ 0 ] ) ) {
		return null;
	}
}

add_action( 'wp_ajax_vote', 'vote_callback' );
add_action( 'wp_ajax_nopriv_vote', 'vote_callback' );

/**
 * Vote functionality - Up and Down
 */
function vote_callback() {
	$response = array();
	if ( ! is_user_logged_in() ) {
		$response[ 'err' ] = 'Please login to vote.';
	} else {
		$postid = intval( $_POST[ 'postid' ] );
		if ( get_post_status( $postid ) == 'new' ) {
			$is_voted = check_user_voted( $postid );
			if ( isset( $is_voted ) && $is_voted ) {
				update_vote( $postid, 0 );
				$votes = get_post_meta( $postid, '_rt_wpideas_meta_votes', true );
				$votes = $votes - 1;
				update_post_meta( $postid, '_rt_wpideas_meta_votes', $votes );
				$response[ 'btnLabel' ] = 'Vote Up';
			} else if ( isset( $is_voted ) && ! $is_voted ) {
				update_vote( $postid, 1 );
				$votes = get_post_meta( $postid, '_rt_wpideas_meta_votes', true );
				$votes = $votes + 1;
				update_post_meta( $postid, '_rt_wpideas_meta_votes', $votes );
				$response[ 'btnLabel' ] = 'Vote Down';
			} else if ( ! isset( $is_voted ) ) {
				add_vote( $postid );
				$votes = get_post_meta( $postid, '_rt_wpideas_meta_votes', true );
				$votes = $votes + 1;
				update_post_meta( $postid, '_rt_wpideas_meta_votes', $votes );
				$response[ 'btnLabel' ] = 'Vote Down';
			}
			$response[ 'vote' ] = get_votes_by_post( $postid );
		} else {
			$response[ 'err' ] = 'You can not vote on ' . get_post_status( $postid ) . ' idea.';
		}
	}

	echo json_encode( $response );
	die(); // this is required to return a proper result
}
