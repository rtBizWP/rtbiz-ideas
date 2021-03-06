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
				'post_status' => 'idea-new',
			);

			$idea_id = wp_insert_post( $idea_information );

//			update_post_meta( $idea_id, '_rt_wpideas_meta_votes', 1 );

			if ( isset( $_POST[ 'product_id' ] ) && $_POST[ 'product_id' ] != '' ) {
                $product_slug = Rt_Products::$product_slug;

//				update_post_meta( $idea_id, '_rt_wpideas_post_id', ','.$_POST[ 'product_id' ].',' );
                wp_set_post_terms($idea_id,$_POST[ 'product_id' ],$product_slug);
				echo 'product';
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
			if (is_new_idea_posted_notification_enable()) {
				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';
				//				$subject = __( 'New Idea', 'wp-ideas' );
				$subject = (create_new_idea_title('idea_new_idea_email_title',$idea_id));
				$recipients = get_notification_emails();
				$message = '';
				$post_content = apply_filters ("the_content", $_POST['txtIdeaContent']);
				$currentuser = wp_get_current_user();
				$message .= '<h3>' . $currentuser ->display_name . ' posted a new idea</h3>';
				$message .= '<h2>' . stripslashes($_POST['txtIdeaTitle']) . '</h2>';
				$message .= '<p>' . stripslashes($post_content) . '</p>';
				$rtwpideasAdmin = new RTWPIdeasAdmin();
				$rtwpideasAdmin->sendNotifications( $recipients, $subject, $message, $headers );
			}
//			if ( isset( $_POST[ 'product' ] ) && $_POST[ 'product' ] == 'product_page' ) {
//				echo 'product';
//			}

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
	$status=  getIdeaStatus();
	$args = array( 's' => $txtSearch, 'post_type' => RTBIZ_IDEAS_SLUG, 'post_status' => $status  );

	$ideas = new WP_Query( $args );
	if ( $ideas -> have_posts() ):
		while ( $ideas -> have_posts() ) : $ideas -> the_post();
			rtideas_get_template( 'loop-common.php' );
		endwhile;
		wp_reset_postdata();
	else :
	if ( is_user_logged_in() ) {
            echo 'Looks like we do not have your idea. <br /><br /> Have you got better one? &nbsp; <a id="btnOpenThickbox" href="#Idea-new" > click here</a>
 &nbsp;  to suggest.';
	} else {
		echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
	}
	endif;
	die(); // this is required to return a proper result
}

function getIdeaStatus(){
	$status = get_post_stati();
	$filter_status= array();
	foreach ($status as $key => $value){
		if (startsWith($value,'idea')){
			array_push( $filter_status , $value );
		}
	}
	return $filter_status;
}

function startsWith($haystack, $needle)
{
	return $needle === "" || strpos($haystack, $needle) === 0;
}

add_action( 'wp_ajax_list_ideas_load_more', 'list_ideas_load_more' ); // when logged in
add_action( 'wp_ajax_nopriv_list_ideas_load_more', 'list_ideas_load_more' ); //when logged out
add_action( 'wp_ajax_list_ideas_refresh', 'list_ideas_refresh' );
add_action( 'wp_ajax_nopriv_list_ideas_refresh', 'list_ideas_refresh' );
add_action( 'wp_ajax_subscribe_notification_setting', 'subscribe_notification_setting');
add_action( 'wp_ajax_subscribe_button', 'subscribe_button');
add_action( 'wp_ajax_nopriv_subscribe_notification_setting', 'subscribe_notification_setting');
add_action( 'wp_ajax_nopriv_subscribe_button', 'subscribe_button');

function subscribe_button(){
	$response = array();
	$response['status']=false;
	global $rtWpIdeasSubscriber;
	if (isset($_POST['post_id'])){
		$subcribebuttonflag= $rtWpIdeasSubscriber->check_subscriber_exist($_POST['post_id'],get_current_user_id());
		if ($subcribebuttonflag){
			$rtWpIdeasSubscriber->delete_subscriber($_POST['post_id'],get_current_user_id());
			$response['btntxt']='Subscribe';
		}
		else{
			$rtWpIdeasSubscriber->add_subscriber($_POST['post_id'],get_current_user_id());
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
	global $rtWpIdeasSubscriber;
	if ( isset($_POST['comment_notification'] ) ) {
		if ( $_POST['comment_notification'] == 'NO' ) {
			update_user_meta( get_current_user_id(), 'comment_notification', 'NO' );
			$rtWpIdeasSubscriber->update_user_from_comment( get_current_user_id(),'NO' );
		} else {
			update_user_meta( get_current_user_id(), 'comment_notification', 'YES' );
			$rtWpIdeasSubscriber->update_user_from_comment( get_current_user_id(),'YES' );
		}
		$response['status']=true;
	}
	if(isset($_POST['status_change_notification']) ) {
		if ( $_POST['status_change_notification'] == 'NO') {
			update_user_meta( get_current_user_id(), 'status_change_notification', 'NO' );
			$rtWpIdeasSubscriber->update_user_from_status_change( get_current_user_id(), 'NO');
		} else {
			update_user_meta( get_current_user_id(), 'status_change_notification', 'YES' );
			$rtWpIdeasSubscriber->update_user_from_status_change( get_current_user_id(), 'YES');
		}
		$response['status']=true;
	}
	echo json_encode($response);
	die();
}

/**
 * Post id display short code
 *
 * @global type $post
 *
 * @param type  $atts
 */
function list_post_ideas( $atts ) {

	global $post;
	$default = array( 'type' => 'post', 'posts_per_page' => 3,'order'	=> 'DESC', 'orderby' => 'date', 'post_type' => RTBIZ_IDEAS_SLUG, 'product_id' => '', );
	$r = shortcode_atts( $default, $atts );
	extract( $r );
    $post_type_ob = get_post_type_object( $post_type );
    if ( ! $post_type_ob )
        return '<div class="warning"><p>No such post type <em>' . $post_type . '</em> found.</p></div>';

    if( isset( $product_id ) && !empty( $product_id ) ) {
        $text_slug = Rt_Products::$product_slug;
        global $rtbiz_products;
        $termid = $rtbiz_products->check_postid_term_exist($product_id);
        if (empty($termid)) {
            return "Invalid Product. Please check sync settings.";
        }
        $args = array('post_type' => $post_type, 'posts_per_page' => $posts_per_page, 'tax_query' => array(array('taxonomy' => $text_slug, 'terms' => $termid)));
        $args_count = array('post_type' => $post_type, 'tax_query' => array(array('taxonomy' => $text_slug, 'terms' => $termid)));
        echo '<br/>';
        if (isset($termid) || !is_null($termid)) {
            echo "<input type='hidden' id='rt_product_id' value=" . $termid . ">";
        }
    }
    else{
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'order'	=> $order,
            'orderby' => $orderby,
            'post_status' => 'idea-new',
        );
        $args_count= array(
            'post_type' => $post_type,
            'order'	=> $order,
            'orderby' => $orderby,
            'post_status' => 'idea-new',
        );

    }
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
        <?php
//        if( isset( $product_id ) && !empty( $product_id ) ) {
            if ($posts_count->post_count > $posts_per_page) {
                ?>
                <div class="idea-loadmore">
                    <a href="javascript:;" data-nonce="<?php echo esc_attr(wp_create_nonce('load_ideas')); ?>"
                       id="ideaLoadMore"
                       class="rtp-readmore button rtp-button-beta-light tiny aligncenter"><?php _e('Load More', 'wp-ideas'); ?></a>
                    <img src="<?php echo RTBIZ_IDEAS_URL . 'app/assets/img/indicator.gif'; ?>" id="ideaLoading"
                         class="aligncenter" style="display:none;height: 50px;"/>
                    <?php if (isset($termid) && !empty($termid)){ ?>
                    <input type="hidden" value="<?php echo esc_attr($termid); ?>" id="idea_product_id"/>
                    <?php } ?>
                    <input type="hidden" value="<?php echo esc_attr($posts_per_page); ?>" id="idea_post_per_page"/>
                    <input type="hidden" value="<?php echo esc_attr($order); ?>" id="idea_order"/>
                    <input type="hidden" value="<?php echo esc_attr($orderby); ?>" id="idea_order_by"/>
                </div>
            <?php
//            }
        }
		wp_reset_postdata();
	else :
        if( isset( $product_id ) && !empty( $product_id ) ) {
            ?><p>No ideas found for this product.</p><?php
        }
        else{
            ?><p>No ideas found.</p><?php
        }
	endif;
	echo '</div>';
	?>

    <?php
	if ( is_user_logged_in() ) {
		?>
		<br/>
		<div id="wpideas-insert-idea" style="display:none;">
		<?php
//		include RTBIZ_IDEAS_PATH . 'templates/template-insert-idea.php';
		rtideas_get_template( 'template-insert-idea.php' )
		?>
		</div>
			<?php
//			echo '<a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> Suggest Idea for this post</a>';
?>
		<a id="btnOpenThickbox" href="#Idea-new" > Suggest Idea for this post</a>

	<?php
	} else {
		$href = wp_login_url( get_permalink($product_id) );
		echo '<br/><a id="btnOpenThickbox" href="'.$href.'">Login to Suggest Idea</a>';
	}
}

add_shortcode( 'wpideas', 'list_post_ideas' );

function list_ideas_refresh() {

	$posts_per_page = 3;
    $text_slug = Rt_Products::$product_slug;
    //    $termid=   check_postid_term_exist($_POST[ 'product_id' ]);

	$args = array(
        'post_type' => RTBIZ_IDEAS_SLUG,
        'posts_per_page' => $posts_per_page,
        'tax_query' => array( array( 'taxonomy' => $text_slug, 'terms' => $_POST[ 'product_id' ] )  )
       /* 'meta_query' => array(
            array(
                'key' => '_rt_wpideas_post_id',
                'value' => ','.$_POST[ 'product_id' ].',',
                'compare'=>'LIKE',
            )
        )*/
    );
	$args_count = array(
		'post_type' => RTBIZ_IDEAS_SLUG,
        'tax_query' => array( array( 'taxonomy' => $text_slug, 'terms' => $_POST[ 'product_id' ] )  )
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

function list_ideas_load_more() {

	if ( ! wp_verify_nonce( $_REQUEST[ 'nonce' ], 'load_ideas' ) ) {
		exit( 'No naughty business please' );
	}

	$offset = isset( $_REQUEST[ 'offset' ] ) ? intval( $_REQUEST[ 'offset' ] ) : 3;
	$post_type = isset( $_REQUEST[ 'post_type' ] ) ? $_REQUEST[ 'post_type' ] : 'idea';
	$product_id = isset( $_REQUEST[ 'product_id' ] ) ? $_REQUEST[ 'product_id' ] : 0;
	$postparpage = isset( $_REQUEST[ 'postparpage' ] ) ? $_REQUEST[ 'postparpage' ] : 3;
    $order=   isset( $_REQUEST[ 'idea_order' ] ) ? $_REQUEST[ 'idea_order' ] : 'DESC';
    $orderby=   isset( $_REQUEST[ 'idea_orderby' ] ) ? $_REQUEST[ 'idea_orderby' ] : 'date';
    $text_slug = Rt_Products::$product_slug;
//    $termid=   check_postid_term_exist($_POST[ 'product_id' ]);
    if(isset($_REQUEST[ 'product_id' ]) && !empty($_REQUEST[ 'product_id' ])) {
        $args = array('post_type' => $post_type, 'offset' => $offset, 'posts_per_page' => $postparpage, 'tax_query' => array(array('taxonomy' => $text_slug, 'terms' => $_POST['product_id'])));
    }
    else{
        $args = array('post_type' => $post_type, 'offset' => $offset, 'posts_per_page' => $postparpage,  'order'	=> $order,  'orderby' => $orderby, 'post_status' => 'idea-new',);
    }

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
