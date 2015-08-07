<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Common' ) ) {
	/**
	 * Class Rtbiz_Ideas_Common
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Common {


		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {

			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_rtbiz_ideas_insert_new_idea', $this, 'insert_new_idea' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_rtbiz_ideas_insert_new_idea', $this, 'insert_new_idea' );

			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_rtbiz_ideas_search', $this, 'search_callback' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_rtbiz_ideas_search', $this, 'search_callback' );

			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_rtbiz_ideas_load_more', $this, 'ideas_load_more' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_rtbiz_ideas_load_more', $this, 'ideas_load_more' );

			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_list_rtbiz_ideas_refresh', $this, 'list_ideas_refresh' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_list_rtbiz_ideas_refresh', $this, 'list_ideas_refresh' );

			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_rtbiz_ideas_subscribe_notification_setting', $this, 'subscribe_notification_setting' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_rtbiz_ideas_)subscribe_notification_setting', $this, 'subscribe_notification_setting' );

			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_rtbiz_ideas_subscribe_button', $this, 'subscribe_button' );
			Rtbiz_Ideas::$loader->add_action( 'wp_ajax_nopriv_rtbiz_ideas_subscribe_button', $this, 'subscribe_button' );

			add_shortcode( 'rtbiz_ideas', array( $this, 'list_post_ideas' ) );

		}

		/**
		 * Insert new Idea
		 */
		public function insert_new_idea() {
			if ( is_user_logged_in() ) {
				$ideaResponse = array();
				$hasError     = false;

				if ( trim( $_POST['txtIdeaTitle'] ) === '' ) {
					$ideaResponse['title'] = 'Please enter a title.';
					$hasError              = true;
				}
				if ( trim( $_POST['txtIdeaContent'] ) === '' ) {
					$ideaResponse['content'] = 'Please enter details.';
					$hasError                = true;
				}

				if ( ! $hasError ) {
					$idea_information = array(
						'post_title'   => wp_strip_all_tags( $_POST['txtIdeaTitle'] ),
						'post_content' => $_POST['txtIdeaContent'],
						'post_type'    => Rtbiz_Ideas_Module::$post_type,
						'post_status'  => 'idea-new',
					);

					$idea_id = wp_insert_post( $idea_information );


					if ( isset( $_POST['product_id'] ) && '' != $_POST['product_id'] ) {
						$product_slug = Rt_Products::$product_slug;

						wp_set_post_terms( $idea_id, $_POST['product_id'], $product_slug );
						echo 'product';
					}

					if ( $_FILES ) {
						$files = $_FILES['upload'];
						foreach ( $files['name'] as $key => $value ) {
							if ( $files['name'][ $key ] ) {
								$file   = array(
									'name'     => $files['name'][ $key ],
									'type'     => $files['type'][ $key ],
									'tmp_name' => $files['tmp_name'][ $key ],
									'error'    => $files['error'][ $key ],
									'size'     => $files['size'][ $key ],
								);
								$_FILES = array( 'upload' => $file, );
								foreach ( $_FILES as $file => $array ) {
									$newupload = $this->insert_attachment( $file, $idea_id );
								}
							}
						}
					}

					global $rtbiz_ideas_notification;

					$rtbiz_ideas_notification -> idea_posted_notification( $idea_id, get_post( $idea_id ) );

				} else {
					echo json_encode( $ideaResponse );
				}

				die();
			}
		}

		/**
		 * insert_attachment
		 *
		 * @param $file_handler
		 * @param $idea_id
		 * @param string $setthumb
		 *
		 * @return mixed
		 */
		public function insert_attachment( $file_handler, $idea_id, $setthumb = 'false' ) {
			// check to make sure its a successful upload
			if ( UPLOAD_ERR_OK != $_FILES[ $file_handler ]['error'] ) {
				__return_false();
			}

			require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
			require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
			require_once( ABSPATH . 'wp-admin' . '/includes/media.php' );

			$attach_id = media_handle_upload( $file_handler, $idea_id );
			if ( $setthumb ) {
				update_post_meta( $idea_id, '_thumbnail_id', $attach_id );
			}

			return $attach_id;
		}

		/**
		 * search_callback
		 */
		public function search_callback() {
			$txtSearch = $_POST['searchtext'];
			$status    = $this->get_idea_status();
			$args = array(
				's'           => $txtSearch,
				'post_type'   => Rtbiz_Ideas_Module::$post_type,
				'post_status' => $status,
			);

			$ideas = new WP_Query( $args );
			if ( $ideas->have_posts() ) {
				while ( $ideas->have_posts() ) {
					$ideas->the_post();
					rtbiz_ideas_get_template( 'loop-common.php' );
				}
				wp_reset_postdata();
			} else {
				if ( is_user_logged_in() ) {
					echo 'Looks like we do not have your idea. <br /><br /> Have you got better one? &nbsp; <a id="btnOpenThickbox" href="#Idea-new" > click here</a> &nbsp;  to suggest.';
				} else {
					echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
				}
			}
			die(); // this is required to return a proper result
		}

		/**
		 * get_idea_status
		 *
		 * @return array
		 */
		public function get_idea_status(){
			$status        = get_post_stati();
			$filter_status = array();
			foreach ( $status as $key => $value ) {
				if ( $this->starts_with( $value, 'idea' ) ) {
					array_push( $filter_status, $value );
				}
			}

			return $filter_status;
		}

		/**
		 * starts_with
		 *
		 * @param $haystack
		 * @param $needle
		 *
		 * @return bool
		 */
		public function starts_with( $haystack, $needle ) {
			return '' === $needle || strpos( $haystack, $needle ) === 0;
		}

		/**
		 * ideas_load_more
		 */
		function ideas_load_more() {

			if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'load_ideas' ) ) {
				exit( 'No naughty business please' );
			}

			$offset      = isset( $_REQUEST['offset'] ) ? intval( $_REQUEST['offset'] ) : 3;
			$post_type   = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : Rtbiz_Ideas_Module::$post_type;
			$postparpage = isset( $_REQUEST['postparpage'] ) ? $_REQUEST['postparpage'] : 3;
			$order       = isset( $_REQUEST['idea_order'] ) ? $_REQUEST['idea_order'] : 'DESC';
			$orderby     = isset( $_REQUEST['idea_orderby'] ) ? $_REQUEST['idea_orderby'] : 'date';
			$text_slug   = Rt_Products::$product_slug;

			if ( isset( $_REQUEST['product_id'] ) && ! empty( $_REQUEST['product_id'] ) ) {
				$args = array(
					'post_type'      => $post_type,
					'offset'         => $offset,
					'posts_per_page' => $postparpage,
					'tax_query'      => array(
						array(
							'taxonomy' => $text_slug,
							'terms'    => $_POST['product_id'],
						),
					)
				);
			} else {
				$args = array(
					'post_type'      => $post_type,
					'offset'         => $offset,
					'posts_per_page' => $postparpage,
					'order'          => $order,
					'orderby'        => $orderby,
					'post_status'    => 'idea-new',
				);
			}

			$posts_query = new WP_Query( $args );

			if ( $posts_query->have_posts() ) {
				//if we have posts:
				$result['have_posts'] = true; //set result array item "have_posts" to true
				ob_start();
				while ( $posts_query->have_posts() ) {
					$posts_query->the_post();
					rtbiz_ideas_get_template( 'loop-common.php' );
				}
				$result['html'] = ob_get_clean(); // put alloutput data into "html" item
			} else {
				//no posts found
				$result['have_posts'] = false; // return that there is no posts found
			}

			$result = json_encode( $result );
			echo $result;
			die();
		}

		/**
		 * list_ideas_refresh
		 */
		function list_ideas_refresh() {

			$posts_per_page = 3;
			$text_slug      = Rt_Products::$product_slug;

			$args       = array(
				'post_type'      => RTBIZ_IDEAS_SLUG,
				'posts_per_page' => $posts_per_page,
				'tax_query'      => array( array( 'taxonomy' => $text_slug, 'terms' => $_POST['product_id'] ) )
			);
			$args_count = array(
				'post_type' => RTBIZ_IDEAS_SLUG,
				'tax_query' => array( array( 'taxonomy' => $text_slug, 'terms' => $_POST['product_id'] ) )
			);

			$posts       = new WP_Query( $args );
			$posts_count = new WP_Query( $args_count );
			if ( $posts->have_posts() ) {
				?>
				<div id="wpidea-content">
					<?php
					while ( $posts->have_posts() ) : $posts->the_post();
						rtbiz_ideas_get_template( 'loop-common.php' );
					endwhile;
					?>
				</div><?php
				if ( $posts_count->post_count > 3 ) { ?>
					<div class="idea-loadmore">
						<a href="javascript:;" data-nonce="<?php echo esc_attr( wp_create_nonce( 'load_ideas' ) ); ?>"
						   id="ideaLoadMore"
						   class="rtp-readmore button rtp-button-beta-light tiny aligncenter"><?php _e( 'Load More', 'wp-ideas' ); ?></a>
						<img src="<?php echo RTBIZ_IDEAS_URL . 'public/img/indicator.gif'; ?>" id="ideaLoading"
						     class="aligncenter" style="display:none;height: 50px;"/>
						<input type="hidden" value="<?php echo $_POST['product_id']; ?>"
						       id="idea_product_id"/><br/><br/>
					</div><?php
				}
				wp_reset_postdata();
			} else {
				?><p>No idea for this product.</p><?php
			}
			die();
		}

		/**
		 * subscribe_notification_setting
		 */
		public function subscribe_notification_setting(){
			$response           = array();
			$response['status'] = false;
			global $rtbiz_ideas_subscriber_model;

			if ( isset( $_POST['comment_notification'] ) ) {
				if ( 'NO' == $_POST['comment_notification'] ) {
					update_user_meta( get_current_user_id(), 'comment_notification', 'NO' );
					$rtbiz_ideas_subscriber_model->update_user_from_comment( get_current_user_id(), 'NO' );
				} else {
					update_user_meta( get_current_user_id(), 'comment_notification', 'YES' );
					$rtbiz_ideas_subscriber_model->update_user_from_comment( get_current_user_id(), 'YES' );
				}
				$response['status'] = true;
			}
			if ( isset( $_POST['status_change_notification'] ) ) {
				if ( 'NO' == $_POST['status_change_notification'] ) {
					update_user_meta( get_current_user_id(), 'status_change_notification', 'NO' );
					$rtbiz_ideas_subscriber_model->update_user_from_status_change( get_current_user_id(), 'NO' );
				} else {
					update_user_meta( get_current_user_id(), 'status_change_notification', 'YES' );
					$rtbiz_ideas_subscriber_model->update_user_from_status_change( get_current_user_id(), 'YES' );
				}
				$response['status'] = true;
			}
			echo json_encode( $response );
			die();
		}

		/**
		 * subscribe_button
		 */
		public function subscribe_button(){
			$response           = array();
			$response['status'] = false;
			global $rtbiz_ideas_subscriber_model;

			if ( isset( $_POST['post_id'] ) ) {
				$subcribebuttonflag = $rtbiz_ideas_subscriber_model->check_subscriber_exist( $_POST['post_id'], get_current_user_id() );
				if ( $subcribebuttonflag ) {
					$rtbiz_ideas_subscriber_model->delete_subscriber( $_POST['post_id'], get_current_user_id() );
					$response['btntxt'] = 'Subscribe';
				} else {
					$rtbiz_ideas_subscriber_model->add_subscriber( $_POST['post_id'], get_current_user_id() );
					$response['btntxt'] = 'Unsubscribe';
				}
				$response['status'] = true;
			}
			echo json_encode( $response );
			die();
		}

		/**
		 * @param $atts
		 *
		 * @return string
		 */
		public function list_post_ideas( $atts ) {

			$default = array(
				'type'           => 'post',
				'posts_per_page' => 3,
				'order'          => 'DESC',
				'orderby'        => 'date',
				'post_type'      => Rtbiz_Ideas_Module::$post_type,
				'product_id'     => '',
			);
			$r       = shortcode_atts( $default, $atts );
			extract( $r );
			$post_type_ob = get_post_type_object( $post_type );
			if ( ! $post_type_ob ) {
				return '<div class="warning"><p>No such post type <em>' . $post_type . '</em> found.</p></div>';
			}

			if ( isset( $product_id ) && ! empty( $product_id ) ) {
				$text_slug = Rt_Products::$product_slug;
				global $rtbiz_products;
				$product_termid = $rtbiz_products->check_postid_term_exist( $product_id );
				if ( empty( $product_termid ) ) {
					return 'Invalid Product. Please check sync settings.';
				}
				$args       = array(
					'post_type'      => $post_type,
					'posts_per_page' => $posts_per_page,
					'tax_query'      => array( array( 'taxonomy' => $text_slug, 'terms' => $product_termid ) )
				);
				$args_count = array(
					'post_type' => $post_type,
					'tax_query' => array( array( 'taxonomy' => $text_slug, 'terms' => $product_termid ) )
				);
				echo '<br/>';
				if ( isset( $product_termid ) || ! is_null( $product_termid ) ) {
					echo "<input type='hidden' id='rt_product_id' value=" . $product_termid . '>';
				}
			} else {
				$args       = array(
					'post_type'      => $post_type,
					'posts_per_page' => $posts_per_page,
					'order'          => $order,
					'orderby'        => $orderby,
					'post_status'    => 'idea-new',
				);
				$args_count = array(
					'post_type'   => $post_type,
					'order'       => $order,
					'orderby'     => $orderby,
					'post_status' => 'idea-new',
				);

			}
			$posts       = new WP_Query( $args );
			$posts_count = new WP_Query( $args_count ); ?>

			<div id="wpidea-content-wrapper"><?php
			if ( $posts->have_posts() ) { ?>
				<div id="wpidea-content"><?php
				while ( $posts->have_posts() ) {
					$posts->the_post();
					rtbiz_ideas_get_template( 'loop-common.php' );
				} ?>
				</div><?php
				if ( $posts_count->post_count > $posts_per_page ) { ?>
					<div class="idea-loadmore">
						<a href="javascript:;" data-nonce="<?php echo esc_attr( wp_create_nonce( 'load_ideas' ) ); ?>"
						   id="ideaLoadMore"
						   class="rtp-readmore button rtp-button-beta-light tiny aligncenter"><?php _e( 'Load More', 'wp-ideas' ); ?></a>
						<img src="<?php echo RTBIZ_IDEAS_URL . 'public/img/indicator.gif'; ?>" id="ideaLoading"
						     class="aligncenter" style="display:none;height: 50px;"/>
						<?php if ( isset( $termid ) && ! empty( $termid ) ) { ?>
							<input type="hidden" value="<?php echo esc_attr( $termid ); ?>" id="idea_product_id"/>
						<?php } ?>
						<input type="hidden" value="<?php echo esc_attr( $posts_per_page ); ?>"
						       id="idea_post_per_page"/>
						<input type="hidden" value="<?php echo esc_attr( $order ); ?>" id="idea_order"/>
						<input type="hidden" value="<?php echo esc_attr( $orderby ); ?>" id="idea_order_by"/>
					</div><?php
				}
				wp_reset_postdata();
			} else {
				if ( isset( $product_id ) && ! empty( $product_id ) ) {
					?><p>No ideas found for this product.</p><?php
				} else {
					?><p>No ideas found.</p><?php
				}
			}?>
			</div><?php
			if ( is_user_logged_in() ) { ?>
				<br/>
				<div id="wpideas-insert-idea" style="display:none;">
					<?php //		include RTBIZ_IDEAS_PATH . 'templates/template-insert-idea.php';
					rtbiz_ideas_get_template( 'template-insert-idea.php' ) ?>
				</div>
				<a id="btnOpenThickbox" href="#Idea-new"> Suggest Idea </a> <?php
			} else {
				$href = wp_login_url( get_permalink( $product_id ) );
				echo '<br/><a id="btnOpenThickbox" href="' . $href . '">Login to Suggest Idea</a>';
			}
		}

		/**
		 * get_excerpt_by_id
		 *
		 * @param $post_id
		 *
		 * @return string
		 */
		public function get_excerpt_by_id( $post_id ) {
			$the_post       = get_post( $post_id ); //Gets post ID
			$the_excerpt    = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
			$excerpt_length = 50; //Sets excerpt length by word count
			$the_excerpt    = strip_tags( strip_shortcodes( $the_excerpt ) ); //Strips tags and images
			$words          = explode( ' ', $the_excerpt, $excerpt_length + 1 );
			if ( count( $words ) > $excerpt_length ) :
				array_pop( $words );
				array_push( $words, '…' );
				$the_excerpt = implode( ' ', $words );
			endif;
			$the_excerpt = '<p>' . $the_excerpt . '</p>';

			return $the_excerpt;
		}
	}
}
