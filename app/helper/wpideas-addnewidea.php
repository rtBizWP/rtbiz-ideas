<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Attachment handle for the idea
 * 
 * @param type $file_handler
 * @param type $idea_id
 * @param type $setthumb
 */
function insert_attachment( $file_handler, $idea_id, $setthumb = 'false' ) {
	// check to make sure its a successful upload
	if ( $_FILES[ $file_handler ][ 'error' ] !== UPLOAD_ERR_OK )
		__return_false();

	require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
	require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
	require_once(ABSPATH . 'wp-admin' . '/includes/media.php');

	$attach_id = media_handle_upload( $file_handler, $idea_id );
}

add_action( 'wp_ajax_insert_new_idea', 'insert_new_idea' );
add_action( 'wp_ajax_nopriv_insert_new_idea', 'insert_new_idea' );

function insert_new_idea() {
	if ( isset( $_POST[ 'submitted' ] ) && isset( $_POST[ 'idea_nonce_field' ] ) && wp_verify_nonce( $_POST[ 'idea_nonce_field' ], 'idea_nonce' ) ) {

		if ( trim( $_POST[ 'txtIdeaTitle' ] ) === '' ) {
			$ideaTitleError = 'Please enter a title.';
			$hasError = true;
		}
		$idea_information = array(
			'post_title' => wp_strip_all_tags( $_POST[ 'txtIdeaTitle' ] ),
			'post_content' => $_POST[ 'txtIdeaContent' ],
			'post_type' => 'idea',
			'post_status' => 'pending',
		);

		$idea_id = wp_insert_post( $idea_information );

		if ( $_FILES ) {
			foreach ( $_FILES as $file => $array ) {
				$newupload = insert_attachment( $file, $idea_id );
			}
		}

		if ( isset( $idea_id ) ) {
			header("location:".  home_url().'/idea');
		}
	}
}
