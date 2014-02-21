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
	return $rtWpideasVotes -> add_vote( $data );
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

function vote_callback() {
	$response = array();
	if ( ! is_user_logged_in() ) {
		$response[ 'err' ] = 'Please login to vote.';
	} else {
		$postid = intval( $_POST[ 'postid' ] );
		$is_voted = check_user_voted( $postid );
		if ( isset( $is_voted ) && $is_voted ) {
			update_vote( $postid, 0 );
			$response[ 'btnLabel' ] = 'Vote Up';
		} else if ( isset( $is_voted ) && ! $is_voted ) {
			update_vote( $postid, 1 );
			$response[ 'btnLabel' ] = 'Vote Down';
		} else if ( ! isset( $is_voted ) ) {
			add_vote( $postid );
			$response[ 'btnLabel' ] = 'Vote Down';
		}
		$response[ 'vote' ] = get_votes_by_post( $postid );
	}

	echo json_encode( $response );
	die(); // this is required to return a proper result
}

add_action( 'wp_ajax_search', 'search_callback' );
add_action( 'wp_ajax_nopriv_search', 'search_callback' );

function search_callback() {
	$txtSearch = $_POST[ 'searchtext' ];
	global $rtWpideasVotes;
	$response = $rtWpideasVotes -> search( $txtSearch );
	$response = $response -> posts;
	if ( $response != null ) {
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
					$.post('<?php echo $ajax_url; ?>', data, function(response) {
						var json = JSON.parse(response);
						if (json.vote) {
							$('#rtwpIdeaVoteCount-' + data['postid']).html(json.vote);
							$('#btnVote-' + data['postid']).removeAttr('disabled');
							$('#btnVote-' + data['postid']).html(json.btnLabel);
						} else {
							alert(json.err);
						}
					});
				});
			});
		</script>
		<?php
		foreach ( $response as $r ) {
			//echo 'post-id' . $r -> ID;
			//echo 'post-author' . $r -> post_author;
			//echo 'post-date' . $r -> post_date;
			//echo 'post-title' . $r -> post_title;
			//echo 'post-content' . $r -> post_content;
			?>
			<article id="post-<?php echo $r -> ID; ?>" class="clearfix" >
				<div class="rtwpIdeaVoteBadge">
					<div class="rtwpIdeaVoteCount">
						<strong
							id="rtwpIdeaVoteCount-<?php echo $r -> ID; ?>"><?php echo sanitize_text_field( get_votes_by_post( $r -> ID ) ); ?></strong>
						<span> votes</span>
					</div>
					<div class="rtwpIdeaVoteButton">
						<button id="btnVote-<?php echo $r -> ID; ?>" class="btnVote" data-id="<?php echo $r -> ID; ?>">
							<?php
							if ( is_user_logged_in() ) {
								$is_voted = check_user_voted( $r -> ID );
								if ( isset( $is_voted ) && $is_voted ) {
									echo 'Vote Down';
								} else {
									if ( isset( $is_voted ) && ! $is_voted ) {
										echo 'Vote Up';
									} else {
										echo 'Vote';
									}
								}
							} else {
								echo 'Vote';
							}
							?>
						</button>
					</div>
				</div>
				<header class="rtwpIdeaHeader">
					<h1 class="rtwpIdeaTitle"><a href="<?php echo get_permalink( $r -> ID ); ?>" rel="bookmark"><?php echo $r -> post_title; ?></a>
					</h1>

					<div class="rtwpIdeaDescription">
						<div class="typeset">
							<?php
							echo $r -> post_content;
							?>
						</div>
					</div>
					<?php
					$args = array( 'post_parent' => $r -> ID, 'post_type' => 'attachment', 'posts_per_page' => - 1, 'orderby' => 'menu_order', 'order' => 'ASC', );
					$attachments = get_children( $args );
					if ( ! empty( $attachments ) ) {
						?>
						<ul class="rtwpIdeaAttachments rtwpAttachments"> <?php
							$i = 0;
							foreach ( $attachments as $attachment ) {
								if ( $i >= 3 ) {
									break;
								}
								$image_attributes = wp_get_attachment_image_src( $attachment -> ID, 'full' );
								?>
								<li class="rtwpAttachment">
									<a class="rtwpAttachmentLink rtwpAttachmentLink-preview"
									   href="<?php echo esc_url( $attachment -> guid ); ?>"
									   title="View <?php echo sanitize_title( $attachment -> post_title ); ?>">
										<figure class="rtwpAttachmentInfo">
											<span class="rtwpAttachmentThumbnail"
											      style="background-image: url('<?php echo esc_url( wp_get_attachment_thumb_url( $attachment -> ID ) ); ?>')">&nbsp;</span>
											<figcaption class="rtwpAttachmentMeta">
												<span
													class="rtwpAttachmentCaption"><?php echo sanitize_title( $attachment -> post_title ); ?></span>
											</figcaption>
										</figure>
									</a>
								</li>
								<?php
								$i ++;
							}
							?></ul><?php
					}
					?>

					<div class="rtwpIdeaMeta">
						<a href="<?php echo $r -> guid; ?>#comments"
						   title="Comments for <?php echo $r -> post_title ?>"><?php echo $r -> comment_count . ' comments'; ?></a>
						<span class="uvStyle-separator">&nbsp;·&nbsp;</span>
						<a href="#" title="Ideas similar to <?php echo $r -> post_title; ?>">Category</a>
						<span class="rtwpStyle-separator">&nbsp;·&nbsp;</span>
						<a href="<?php echo $r -> post_author; ?>" data-iframe-target="_blank"
						   title="Author of <?php echo $r -> post_title ?>"><?php echo $r -> post_author; ?> →</a>
					</div>
				</header>
			</article>
			<?php
		}
	} else {
		?>
		<div id="my-content-id" style="display:none;">
			<?php
			include RTWPIDEAS_PATH . 'templates/template-insert-idea.php';
			?>
		</div>
		<?php
		echo 'There are no ideas matching your search.Please <a href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> click here </a> to suggest one. ';
	}
	//echo json_encode($response);
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

	$return = '<h3>' . $post_type_ob -> name . '</h3>';

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
