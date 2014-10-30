<?php
add_action( 'wp_ajax_wpideas_insert_new_idea', 'wpideas_insert_new_idea' );
add_action( 'wp_ajax_nopriv_wpideas_insert_new_idea', 'wpideas_insert_new_idea' );

/**
 * Attachment handle for the idea
 *
 * @param type $file_handler
 * @param type $idea_id
 * @param type $setthumb
 */
function wpideas_insert_attachment( $file_handler, $idea_id, $setthumb = 'false' ) {
	// check to make sure its a successful upload
	if ( $_FILES[ $file_handler ][ 'error' ] !== UPLOAD_ERR_OK )
		__return_false();

	require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
	require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
	require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );

	$attach_id = media_handle_upload( $file_handler, $idea_id );
	if ( $setthumb )
		update_post_meta( $idea_id, '_thumbnail_id', $attach_id );

	return $attach_id;
}

/**
 * Insert new Idea
 */
function wpideas_insert_new_idea() {
	if ( is_user_logged_in() ) {
		$ideaResponse = array();
		$hasError = false;

		if ( trim( $_POST[ 'txtIdeaTitle' ] ) === '' ) {
			$ideaResponse[ 'title' ] = 'Please enter a title.';
			$hasError = true;
		}
		if ( trim( $_POST[ 'txtIdeaContent' ] ) === '' ) {
			$ideaResponse[ 'content' ] = 'Please enter details.';
			$hasError = true;
		}
		/*if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && $_POST[ 'product_id' ] === '' ) {
			$ideaResponse[ 'product' ] = 'Please select product.';
			$hasError = true;
		}*/

		if ( ! $hasError ) {
			$idea_information = array( 
				'post_title' => wp_strip_all_tags( $_POST[ 'txtIdeaTitle' ] ), 
				'post_content' => $_POST[ 'txtIdeaContent' ], 
				'post_type' => RTBIZ_IDEAS_SLUG,
				'post_status' => 'new', 
			);

			$idea_id = wp_insert_post( $idea_information );

			update_post_meta( $idea_id, '_rt_wpideas_meta_votes', 0 );

			if ( isset( $_POST[ 'product_id' ] ) && $_POST[ 'product_id' ] != '' ) {
				update_post_meta( $idea_id, '_rt_wpideas_product_id', $_POST[ 'product_id' ] );
			}

			if ( $_FILES ) {
				$files = $_FILES[ 'upload' ];
				foreach ( $files[ 'name' ] as $key => $value ) {
					if ( $files[ 'name' ][ $key ] ) {
						$file = array( 'name' => $files[ 'name' ][ $key ], 'type' => $files[ 'type' ][ $key ], 'tmp_name' => $files[ 'tmp_name' ][ $key ], 'error' => $files[ 'error' ][ $key ], 'size' => $files[ 'size' ][ $key ], );
						$_FILES = array( 'upload' => $file, );
						foreach ( $_FILES as $file => $array ) {
							$newupload = wpideas_insert_attachment( $file, $idea_id );
						}
					}
				}
			}

			$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';
			//$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
			//$headers[] = 'Cc: iluvwp@wordpress.org';

			$subject = __( 'New Idea', 'wp-ideas' );

			$recipients = array();

			//			$recipients = explode( ',', trim( get_option( 'wpideas_adminemails' ) ) );
			$recipients = get_notification_emails();

			$message = '';
			$message .= '<h3>' . get_current_user() . ' posted a new idea</h3>';
			$message .= '<h2>' . $_POST[ 'txtIdeaTitle' ] . '</h2>';
			$message .= '<p>' . $_POST[ 'txtIdeaContent' ] . '</p>';

			$rtwpideasAdmin = new RTWPIdeasAdmin();
			$rtwpideasAdmin -> sendNotifications( $recipients, $subject, $message, $headers );

			if ( isset( $_POST[ 'product' ] ) && $_POST[ 'product' ] == 'product_page' ) {
				echo 'product';
			}
		} else {
			echo json_encode( $ideaResponse );
		}

		die();
	}
}

/**
 * Get excerpt of post
 *
 * @param type $post_id
 *
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
	$args = array( 's' => $txtSearch, 'post_type' => RTBIZ_IDEAS_SLUG, );

	$ideas = new WP_Query( $args );
	if ( $ideas -> have_posts() ):
		while ( $ideas -> have_posts() ) : $ideas -> the_post();
			rtideas_get_template( 'loop-common.php' );
		endwhile;
		wp_reset_postdata();
	else :
	if ( is_user_logged_in() ) {
            echo 'Looks like we do not have your idea. <br /><br /> Have you got better one? &nbsp; <a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=wpideas-insert-idea" class="thickbox"> Click Here </a> &nbsp;  to suggest.';
	} else {
		echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
	}
	endif;
	die(); // this is required to return a proper result
}

/**
 * Shortcode for list of idea
 *
 * @global type $post
 *
 * @param type  $atts
 *
 * @return string
 */
function list_all_idea_shortcode( $atts ) {
	global $post;
	$default = array( 
		'type' => 'post',
		'post_type' => RTBIZ_IDEAS_SLUG,
		'posts_per_page' => get_option( 'posts_per_page' ),
		'order'	=> 'DESC',
		'orderby' => 'date',
		'post_status' => 'new',
		'post__not_in' => ''
	);
	$r = shortcode_atts( $default, $atts );
	extract( $r );

	if ( empty( $post_type ) )
		$post_type = $type;

	$post_type_ob = get_post_type_object( $post_type );
	if ( ! $post_type_ob )
		return '<div class="warning"><p>No such post type <em>' . $post_type . '</em> found.</p></div>';

	$return = '<h3>' . $post_type_ob -> name . '</h3>';
	
	$post__not_in = explode(",", $post__not_in); // explode values

	$args = array( 
		'post_type' => $post_type,
		'posts_per_page' => $posts_per_page,
		'order'	=> $order,
		'orderby' => $orderby,
		'post_status' => $post_status,
		'post__not_in' => $post__not_in
	);

	$posts = new WP_Query( $args );
	if ( $posts -> have_posts() ):
		while ( $posts -> have_posts() ) : $posts -> the_post();
			rtideas_get_template( 'loop-common.php' );
		endwhile;
		wp_reset_postdata();
	else :
		?><p>No ideas found</p><?php
	endif;
}

add_shortcode( 'ideas', 'list_all_idea_shortcode' );


add_action( 'wp_ajax_list_woo_product_ideas_load_more', 'list_woo_product_ideas_load_more' ); // when logged in
add_action( 'wp_ajax_nopriv_list_woo_product_ideas_load_more', 'list_woo_product_ideas_load_more' ); //when logged out
add_action( 'wp_ajax_list_woo_product_ideas_refresh', 'list_woo_product_ideas_refresh' );
add_action( 'wp_ajax_nopriv_list_woo_product_ideas_refresh', 'list_woo_product_ideas_refresh' );
add_action( 'wp_ajax_subscribe_notification_setting', 'subscribe_notification_setting');
add_action( 'wp_ajax_subscribe_button', 'subscribe_button');
add_action( 'wp_ajax_nopriv_subscribe_notification_setting', 'subscribe_notification_setting');
add_action( 'wp_ajax_nopriv_subscribe_button', 'subscribe_button');

function subscribe_button(){
	$response = array();
	$response['status']=false;
	global $rtWpIdeasSubscirber;
	if (isset($_POST['post_id'])){
		$subcribebuttonflag= $rtWpIdeasSubscirber->check_subscriber_exist($_POST['post_id'],get_current_user_id());
		if ($subcribebuttonflag){
			$rtWpIdeasSubscirber->delete_subscriber($_POST['post_id'],get_current_user_id());
			$response['btntxt']='Subscribe';
		}
		else{
			$rtWpIdeasSubscirber->add_subscriber($_POST['post_id'],get_current_user_id());
			$response['btntxt']='Unsubscribe';
		}
		$response['status']=true;
	}
	echo json_encode($response);
	die();
}

function subscribe_notification_setting(){
	$response = array();
	$response['status']=false;
	global $rtWpIdeasSubscirber;
	if ( isset($_POST['comment_notification'] ) ) {
		if ( $_POST['comment_notification'] == 'NO' ) {
			update_user_meta( get_current_user_id(), 'comment_notification', 'NO' );
			$rtWpIdeasSubscirber->update_user_from_comment( get_current_user_id(),'NO' );
		} else {
			update_user_meta( get_current_user_id(), 'comment_notification', 'YES' );
			$rtWpIdeasSubscirber->update_user_from_comment( get_current_user_id(),'YES' );
		}
		$response['status']=true;
	}
	if(isset($_POST['status_change_notification']) ) {
		if ( $_POST['status_change_notification'] == 'NO') {
			update_user_meta( get_current_user_id(), 'status_change_notification', 'NO' );
			$rtWpIdeasSubscirber->update_user_from_status_change( get_current_user_id(), 'NO');
		} else {
			update_user_meta( get_current_user_id(), 'status_change_notification', 'YES' );
			$rtWpIdeasSubscirber->update_user_from_status_change( get_current_user_id(), 'YES');
		}
		$response['status']=true;
	}
	echo json_encode($response);
	die();
}

/**
 * woocommerce product idea tab shortcode
 *
 * @global type $post
 *
 * @param type  $atts
 */
function list_woo_product_ideas( $atts ) {

	global $post;
	$default = array( 'type' => 'post', 'post_type' => RTBIZ_IDEAS_SLUG, 'product_id' => '', );
	$r = shortcode_atts( $default, $atts );
	extract( $r );

	$posts_per_page = 3;

	add_thickbox();

	$args = array( 'post_type' => $post_type, 'posts_per_page' => $posts_per_page, 'meta_query' => array( array( 'key' => '_rt_wpideas_product_id', 'value' => $product_id, ) ) );
	$args_count = array( 'post_type' => $post_type, 'meta_query' => array( array( 'key' => '_rt_wpideas_product_id', 'value' => $product_id, ) ) );
	echo '<br/>';

	$posts = new WP_Query( $args );
	$posts_count = new WP_Query( $args_count );
	echo '<div id="wpidea-content-wrapper">';
	if ( $posts -> have_posts() ):
        ?>
        <div id="wpidea-content">
        <?php
		while ( $posts -> have_posts() ) : $posts -> the_post();
			rtideas_get_template( 'loop-common.php' );
		endwhile;
        ?>
        </div>
        <?php if($posts_count->post_count > 3 ){ ?>
		<div class="idea-loadmore">
			<a href="javascript:;" data-nonce="<?php echo esc_attr( wp_create_nonce( 'load_ideas' ) ); ?>" id="ideaLoadMore" class="rtp-readmore button rtp-button-beta-light tiny aligncenter"><?php _e( 'Load More', 'wp-ideas' ); ?></a>
			<img src="<?php echo RTBIZ_IDEAS_URL . 'app/assets/img/indicator.gif'; ?>" id="ideaLoading" class="aligncenter" style="display:none;height: 50px;" />
			<input type="hidden" value="<?php echo esc_attr( $product_id ); ?>" id="idea_product_id"/><br/><br/>
		</div>
        <?php
		}
		wp_reset_postdata();
	else :
		?><p>No idea for this product.</p><?php
	endif;
	echo '</div>';
	?>

    <?php
	if ( is_user_logged_in() ) {
		?>
		<br/>
		<div id="my-content-id" style="display:none;">
		<?php
		include RTBIZ_IDEAS_PATH . 'templates/template-insert-idea.php';
		?>
		</div>
			<?php
			echo '<a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> Suggest Idea for this product</a>';
	} else {
		echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
	}
}

add_shortcode( 'wpideas', 'list_woo_product_ideas' );

function list_woo_product_ideas_refresh() {

	$posts_per_page = 3;

	$args = array(
        'post_type' => RTBIZ_IDEAS_SLUG,
        'posts_per_page' => $posts_per_page,
        'meta_query' => array(
            array(
                'key' => '_rt_wpideas_product_id',
                'value' => $_POST[ 'product_id' ],
            )
        )
    );
	$args_count = array(
		'post_type' => RTBIZ_IDEAS_SLUG,
		'meta_query' => array(
			array(
				'key' => '_rt_wpideas_product_id',
				'value' => $_POST[ 'product_id' ],
			)
		)
	);

	$posts = new WP_Query( $args );
	$posts_count = new WP_Query( $args_count );
	if ( $posts -> have_posts() ):
		?>
		<div id="wpidea-content">
			<?php
			while ( $posts -> have_posts() ) : $posts -> the_post();
				rtideas_get_template( 'loop-common.php' );
			endwhile;
			?>
		</div>
		<?php if($posts_count->post_count > 3 ){ ?>
		<div class="idea-loadmore">
			<a href="javascript:;" data-nonce="<?php echo esc_attr( wp_create_nonce( 'load_ideas' ) ); ?>" id="ideaLoadMore" class="rtp-readmore button rtp-button-beta-light tiny aligncenter"><?php _e( 'Load More', 'wp-ideas' ); ?></a>
			<img src="<?php echo RTBIZ_IDEAS_URL . 'app/assets/img/indicator.gif'; ?>" id="ideaLoading" class="aligncenter" style="display:none;height: 50px;" />
			<input type="hidden" value="<?php echo $_POST[ 'product_id' ]; ?>" id="idea_product_id"/><br/><br/>
		</div>
	<?php
	}
		wp_reset_postdata();
	else :
		?><p>No idea for this product.</p><?php
	endif;
	die();
}

function list_woo_product_ideas_load_more() {

	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'load_ideas' ) ) {
		exit( 'No naughty business please' );
	}

	$offset = isset( $_REQUEST[ 'offset' ] ) ? intval( $_REQUEST[ 'offset' ] ) : 3;
	$post_type = isset( $_REQUEST[ 'post_type' ] ) ? $_REQUEST[ 'post_type' ] : 'idea';
	$product_id = isset( $_REQUEST[ 'product_id' ] ) ? $_REQUEST[ 'product_id' ] : 0;

	$args = array( 'post_type' => $post_type, 'offset' => $offset, 'posts_per_page' => 3, 'meta_query' => array( array( 'key' => '_rt_wpideas_product_id', 'value' => $product_id, ) ) );

	$posts_query = new WP_Query( $args );

	if ( $posts_query -> have_posts() ) {
		//if we have posts:
		$result[ 'have_posts' ] = true; //set result array item "have_posts" to true
		ob_start();
		while ( $posts_query -> have_posts() ) : $posts_query -> the_post();
			rtideas_get_template( 'loop-common.php' );
		endwhile;
		$result[ 'html' ] = ob_get_clean(); // put alloutput data into "html" item
	} else {
		//no posts found
		$result[ 'have_posts' ] = false; // return that there is no posts found
	}

	$result = json_encode( $result );
	echo $result;
	die();
}


/**
 *
 * Get rtideas Templates
 *
 * @param $template_name
 * @param array $args
 * @param string $template_path
 * @param string $default_path
 * @return void
 */
function rtideas_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( $args && is_array( $args ) )
		extract( $args );

	$located = rtideas_locate_template( $template_name, $template_path, $default_path );

	do_action( 'rtideas_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'rtideas_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Loads rtideas Templates
 *
 * @param $template_name
 * @param string $template_path
 * @param string $default_path
 * @return mixed|void
 */
function rtideas_locate_template( $template_name, $template_path = '', $default_path = '' ) {

	global $rtWpIdeas;
	if ( ! $template_path ) {
		$template_path = $rtWpIdeas->templateURL;
	}
	if ( ! $default_path ) {
		$default_path = RTBIZ_IDEAS_PATH_TEMPLATES;
	}

	// Look within passed path within the theme - this is priority
	$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters( 'rtideas_locate_template', $template, $template_name, $template_path );
}
