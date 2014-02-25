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

add_action( 'wp_ajax_search', 'search_callback' );
add_action( 'wp_ajax_nopriv_search', 'search_callback' );

/**
 * Search ideas
 * 
 * @global type $rtWpideasVotes
 */
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
		foreach ( $response as $r ) {
			//echo 'post-id' . $r -> ID;
			//echo 'post-author' . $r -> post_author;
			//echo 'post-date' . $r -> post_date;
			//echo 'post-title' . $r -> post_title;
			//echo 'post-content' . $r -> post_content;
			?>
			<article id="post-<?php echo sanitize_title( $r -> ID ); ?>" class="clearfix" >
				<div class="rtwpIdeaVoteBadge">
					<div class="rtwpIdeaVoteCount">
						<strong
							id="rtwpIdeaVoteCount-<?php echo sanitize_title( $r -> ID ); ?>"><?php echo sanitize_title( get_votes_by_post( $r -> ID ) ); ?></strong>
						<span> votes</span>
					</div>
					<div class="rtwpIdeaVoteButton">
						<input type="button" id="btnVote-<?php echo sanitize_title( $r -> ID ); ?>" class="btnVote" data-id="<?php echo sanitize_title( $r -> ID ); ?>" value="<?php
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
						?>" />

					</div>
				</div>
				<header class="rtwpIdeaHeader">
					<h1 class="rtwpIdeaTitle"><a href="<?php echo get_permalink( $r -> ID ); ?>" rel="bookmark"><?php echo $r -> post_title; ?></a>
					</h1>

					<div class="rtwpIdeaDescription">
						<div class="typeset">
							<?php
							echo get_excerpt_by_id( $r -> ID );
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
						<a href="<?php echo esc_url( $r -> guid ); ?>#comments"
						   title="Comments for <?php echo sanitize_title( $r -> post_title ); ?>"><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></a>
						   <?php
						   $categories = get_the_category( $r -> ID );
						   $separator = ' ';
						   $output = '';
						   if ( $categories ) {
							   ?><span class="uvStyle-separator">&nbsp;·&nbsp;</span>
							<a href="#" title="Ideas similar to <?php echo sanitize_text_field( $r -> post_title ); ?>"><?php
								foreach ( $categories as $category ) {
									$output .= '<a href="' . get_category_link( $category -> term_id ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category -> name ) ) . '">' . $category -> cat_name . '</a>' . $separator;
								}
								echo trim( $output, $separator );
								?>
							</a>
							<?php
						}
						?>
						<span class="rtwpStyle-separator">&nbsp;·&nbsp;</span>
						<a href="#" title="Author of <?php echo $r -> post_title; ?>"><?php echo get_author_name( $r -> post_author ); ?> →</a>
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
		<script>jQuery("#TB_overlay").unbind("click", tb_remove);</script>
		<?php
		echo 'There are no ideas matching your search.<br /><br /> <a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> Click Here </a> &nbsp; to suggest one. ';
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
			common_loop();
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
			common_loop();
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


/**
 * Common loop
 */
function common_loop(){
	?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> >
				<div class="rtwpIdeaVoteBadge">
					<div class="rtwpIdeaVoteCount">
						<strong
							id="rtwpIdeaVoteCount-<?php the_ID(); ?>"><?php echo sanitize_text_field( get_votes_by_post( get_the_ID() ) ); ?></strong>
						<span> votes</span>
					</div>
					<div class="rtwpIdeaVoteButton">
						<input type="button" id="btnVote-<?php the_ID(); ?>" class="btnVote" data-id="<?php the_ID(); ?>" value="<?php
						if ( is_user_logged_in() ) {
							$is_voted = check_user_voted( get_the_ID() );
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
						?>" />
					</div>
				</div>
				<header class="rtwpIdeaHeader">
					<h1 class="rtwpIdeaTitle"><a href="<?php the_permalink(); ?>" rel="bookmark"
												 title="<?php printf( esc_attr__( 'Permanent Link to %s', 'rtCamp' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a>
					</h1>

					<div class="rtwpIdeaDescription">
						<div class="typeset">
							<?php the_excerpt();
							?>
						</div>
					</div>
					<?php
					$args = array( 'post_parent' => get_the_ID(), 'post_type' => 'attachment', 'posts_per_page' => - 1, 'orderby' => 'menu_order', 'order' => 'ASC', );
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
						<a href="<?php the_permalink(); ?>#comments"
						   title="Comments for <?php the_title(); ?>"><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></a>
						   <?php
						   $categories = get_the_category();
						   $separator = ' ';
						   $output = '';
						   if ( $categories ) {
							   ?><span class="uvStyle-separator">&nbsp;·&nbsp;</span>
							<a href="#" title="Ideas similar to <?php the_title(); ?>"><?php
								foreach ( $categories as $category ) {
									$output .= '<a href="' . get_category_link( $category -> term_id ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category -> name ) ) . '">' . $category -> cat_name . '</a>' . $separator;
								}
								echo trim( $output, $separator );
								?>
							</a>
							<?php
						}
						?>
						<span class="rtwpStyle-separator">&nbsp;·&nbsp;</span>
						<a href="#" title="Author of <?php the_title(); ?>"><?php the_author(); ?> →</a>
					</div>
				</header>
			</article>
			<?php
}
