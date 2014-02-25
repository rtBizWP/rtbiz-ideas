<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

add_action('wp','insert_new_idea');

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

/**
 * Insert new Idea
 */
function insert_new_idea() {
	if ( is_user_logged_in() ) {
		if ( isset( $_POST[ 'submitted' ] ) && isset( $_POST[ 'idea_nonce_field' ] ) && wp_verify_nonce( $_POST[ 'idea_nonce_field' ], 'idea_nonce' ) ) {

			if ( trim( $_POST[ 'txtIdeaTitle' ] ) === '' ) {
				$ideaTitleError = 'Please enter a title.';
				$hasError = true;
			}
			$idea_information = array(
				'post_title' => wp_strip_all_tags( $_POST[ 'txtIdeaTitle' ] ),
				'post_content' => $_POST[ 'txtIdeaContent' ],
				'post_type' => RT_WPIDEAS_SLUG,
				'post_status' => 'new',
			);

			$idea_id = wp_insert_post( $idea_information );

			if ( isset( $_POST[ 'product_id' ] ) ) {
				update_post_meta( $idea_id, '_rt_wpideas_product_id', $_POST[ 'product_id' ] );
			}

			if ( $_FILES ) {
				foreach ( $_FILES as $file => $array ) {
					$newupload = insert_attachment( $file, $idea_id );
				}
			}

			if ( isset( $idea_id ) ) {
				if ( isset( $_POST[ 'product_id' ] ) ) {
					header( 'location:' . get_permalink( $_POST[ 'product_id' ] ) );
				} else {
					header( 'location:' . home_url() . '/' . RT_WPIDEAS_SLUG );
				}
			}
		}
	}
}

/**
 * Get excerpt of post
 * 
 * @param type $post_id
 * @return string
 */
function get_excerpt_by_id( $post_id ) {
	$the_post = get_post( $post_id ); //Gets post ID
	$the_excerpt = $the_post -> post_content; //Gets post_content to be used as a basis for the excerpt
	$excerpt_length = 50; //Sets excerpt length by word count
	$the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
	$words = explode( ' ', $the_excerpt, $excerpt_length + 1 );
	if ( count( $words ) > $excerpt_length ) :
		array_pop( $words );
		array_push( $words, '…' );
		$the_excerpt = implode( ' ', $words );
	endif;
	$the_excerpt = '<p>' . $the_excerpt . '</p>';
	return $the_excerpt;
}

add_action( 'wp_ajax_wpideas_search', 'wpideas_search_callback' );
add_action( 'wp_ajax_nopriv_wpideas_search', 'wpideas_search_callback' );

/**
 * Search ideas
 * 
 * @global type $rtWpideasVotes
 */
function wpideas_search_callback() {
	$txtSearch = $_POST[ 'searchtext' ];
	$args = array(
		's' => $txtSearch,
		'post_type' => RT_WPIDEAS_SLUG,
	);

	$ideas = new WP_Query( $args );
	if ( $ideas -> have_posts() ):
		$ajax_url = admin_url( 'admin-ajax.php' );
		?>
		<script>
			jQuery(document).ready(function($) {
				$('.btnVote').click(function() {
					$(this).attr('disabled', 'disabled');
					var data = {
						action: 'vote',
						postid: $(this).data('id'),
					};
					$.post('<?php echo esc_url( $ajax_url ); ?>', data, function(response) {
						var json = JSON.parse(response);
						if (json.vote) {
							$('#rtwpIdeaVoteCount-' + data['postid']).html(json.vote);
							$('#btnVote-' + data['postid']).removeAttr('disabled');
							$('#btnVote-' + data['postid']).html(json.btnLabel);
						} else {
							alert(json.err);
							$('#btnVote-' + data['postid']).removeAttr('disabled');
						}
					});
				});
			});
		</script>
		<?php
		while ( $ideas -> have_posts() ) : $ideas -> the_post();
			include RTWPIDEAS_PATH . 'templates/loop-common.php';
		endwhile;
		wp_reset_postdata();
	else :
		?>
		<div id="my-content-id" style="display:none;">
			<?php
			include RTWPIDEAS_PATH . 'templates/template-insert-idea.php';
			?>
		</div>
		<script>jQuery("#TB_overlay").unbind("click", tb_remove);</script>
		<?php
		echo 'There are no ideas matching your search.<br /><br /> <a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> Click Here </a> &nbsp; to suggest one. ';
	endif;
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
		'post_type' => RT_WPIDEAS_SLUG,
	);
	$r = shortcode_atts( $default, $atts );
	extract( $r );

	if ( empty( $post_type ) )
		$post_type = $type;

	$post_type_ob = get_post_type_object( $post_type );
	if ( ! $post_type_ob )
		return '<div class="warning"><p>No such post type <em>' . $post_type . '</em> found.</p></div>';

	$return = '<h3>' . $post_type_ob -> name . '</h3>';

	$args = array(
		'post_type' => $post_type,
	);

	$posts = new WP_Query( $args );
	if ( $posts -> have_posts() ):
		while ( $posts -> have_posts() ) : $posts -> the_post();
			include RTWPIDEAS_PATH . 'templates/loop-common.php';
		endwhile;
		wp_reset_postdata();
	else :
		?><p>No ideas found</p><?php
	endif;
}

add_shortcode( 'ideas', 'list_all_idea_shortcode' );

/**
 * woocommerce product idea tab shortcode
 * 
 * @global type $post
 * @param type $atts
 */
function list_woo_product_ideas( $atts ) {

	global $post;
	$default = array(
		'type' => 'post',
		'post_type' => RT_WPIDEAS_SLUG,
		'product_id' => '',
	);
	$r = shortcode_atts( $default, $atts );
	extract( $r );

	add_thickbox();

	$args = array(
		'post_type' => $post_type,
		'meta_query' => array(
			array(
				'key' => '_rt_wpideas_product_id',
				'value' => $product_id,
			)
		)
	);
	
	echo "<br/>";
	
	$posts = new WP_Query( $args );
	if ( $posts -> have_posts() ):
		while ( $posts -> have_posts() ) : $posts -> the_post();
			include RTWPIDEAS_PATH . 'templates/loop-common.php';
		endwhile;
		wp_reset_postdata();
	else :
		?><p>No idea for this product.</p><?php
	endif;

	if ( is_user_logged_in() ) {
		?>
		<br/>
		<div id = "my-content-id" style = "display:none;">
			<?php
			include RTWPIDEAS_PATH . 'templates/template-insert-idea.php';
			?>
		</div>
		<?php
		echo '<a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> Suggest Idea </a> &nbsp; for this product. <br/><br/>';
	}else{
		echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
	}
}

add_shortcode( 'wpideas', 'list_woo_product_ideas' );
