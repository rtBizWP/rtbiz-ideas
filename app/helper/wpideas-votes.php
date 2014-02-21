<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Add data to the wpideas_vote table
 * 
 * @global type $post
 * @global type $rtWpideasVotes
 * @param type $vote_count
 * @return type
 */
function add_vote( $post, $vote_count = 1 ) {
	global $rtWpideasVotes;
	$user = get_current_user_id();
	$data = array(
		'post_id' => $post,
		'user_id' => $user,
		'vote_count' => $vote_count,
	);
	return $rtWpideasVotes->add_vote( $data );
}

/**
 * Update vote for the post
 * 
 * @global type $post
 * @global type $rtWpideasVotes
 * @param type $vote_count
 * @return type
 */
function update_vote( $post, $vote_count ) {
	global $rtWpideasVotes;
	$user = get_current_user_id();
	$where = array(
		'post_id' => $post,
		'user_id' => $user,
	);
	$data = array(
		'vote_count' => $vote_count,
	);
	return $rtWpideasVotes -> update_vote( $data , $where );
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
	$where = array(
		'post_id' => $post -> ID,
		'user_id' => $user,
	);
	return $rtWpideasVotes -> delete_vote( $where );
}

/**
 * Get votes count for the idea
 * 
 * @global type $rtWpideasVotes
 * @param type $idea
 * @return type
 */
function get_votes_by_idea( $idea ) {
	global $rtWpideasVotes;
	return $rtWpideasVotes -> get_votes_by_idea( $idea );
}

/**
 * 
 * @global type $rtWpideasVotes
 * @param type $post
 * @return type
 */
function get_votes_by_post( $post ) {
	global $rtWpideasVotes;
	$vote_count = $rtWpideasVotes -> get_votes_by_post( $post );
	if ( $vote_count == null ){
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

function check_user_voted( $idea ){
	global $rtWpideasVotes;
	$user = get_current_user_id();
	$row = $rtWpideasVotes -> check_user_voted( $idea, $user );
	if ( ! empty( $row[0] ) && $row[0]->vote_count == 1 ){
		return true;
	}else if ( ! empty( $row[0] ) && $row[0]->vote_count == 0 ){
		return false;
	} else if ( empty ( $row[0] ) ){
		return null;
	}
}


add_action( 'wp_ajax_vote', 'vote_callback' );
add_action( 'wp_ajax_nopriv_vote', 'vote_callback' );

function vote_callback() {
	$response = array();
	if ( ! is_user_logged_in() ){
		$response['err'] = 'Please login to vote.';
	} else {
		$postid = intval( $_POST['postid'] );
		$is_voted = check_user_voted( $postid );
		if ( isset( $is_voted ) && $is_voted ){
			update_vote( $postid, 0 );
			$response['btnLabel'] = 'Vote Up';
		} else if ( isset( $is_voted ) && ! $is_voted ){
			update_vote( $postid, 1 );
			$response['btnLabel'] = 'Vote Down';
		} else if ( ! isset ( $is_voted ) ){
			add_vote( $postid );
			$response['btnLabel'] = 'Vote Down';
		}
		$response['vote'] = get_votes_by_post( $postid );
	}

	echo json_encode( $response );
	die(); // this is required to return a proper result
}

add_action( 'wp_ajax_search', 'search_callback' );
add_action( 'wp_ajax_nopriv_search', 'search_callback' );

function search_callback() {
	$txtSearch = $_POST['searchtext'];
	global $rtWpideasVotes;
	$response = $rtWpideasVotes->search( $txtSearch );
	//foreach( $response as $row){
	//	$res .=  "'".$row->post_title."',";
	//}
	//$res = substr($res, 0, strlen( $res )-1 );
	echo $response;
	die(); // this is required to return a proper result
}

/**
 * Shortcode for list of idea
 * 
 * @global type $post
 * @param type $atts
 * @return string
 */
function list_all_idea_shortcode( $atts ) {
	global $post;
	$default = array(
		'type' => 'post',
		'post_type' => 'idea',
		//'limit' => 10,
		'status' => 'publish',
	);
	$r = shortcode_atts( $default, $atts );
	extract( $r );

	if ( empty( $post_type ) )
		$post_type = $type;

	$post_type_ob = get_post_type_object( $post_type );
	if ( ! $post_type_ob )
		return '<div class="warning"><p>No such post type <em>' . $post_type . '</em> found.</p></div>';

	$return = '<h3>' . $post_type_ob->name . '</h3>';

	$args = array(
		'post_type' => $post_type,
		'numberposts' => $limit,
		'post_status' => $status,
	);

	$posts = get_posts( $args );
	if ( count( $posts ) ):
		$return .= '<ul>';
		foreach ( $posts as $post ): setup_postdata( $post );
			$return .= '<li>' . get_the_title() . '</li>';
		endforeach;
		wp_reset_postdata();
		$return .= '</ul>';
	else :
		$return .= '<p>No ideas found.</p>';
	endif;

	return $return;
}

add_shortcode( 'ideas', 'list_all_idea_shortcode' );
