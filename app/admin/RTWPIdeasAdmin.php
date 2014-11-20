<?php
/**
 * RTWPIdeasAdmin - admin class for plugin
 *
 * PHP version 5
 *
 * @category Development
 * @package  RTWPIdeas
 * @author   kaklo <mehul.kaklotar@rtcamp.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://rtcamp.com
 */
if ( ! class_exists( 'RTWPIdeasAdmin' ) ) {
	class RTWPIdeasAdmin {

		/**
		 * constructor
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_wpidea_post_type' ) );
			add_action( 'init', array( $this, 'wpideas_custom_post_status' ) );
			add_action( 'wp_before_admin_bar_render', array( $this, 'wpideas_append_post_status_list' ), 11 );
			add_filter( 'manage_idea_posts_columns', array( $this, 'wpideas_ideas_table_head' ) );
			add_action( 'manage_idea_posts_custom_column', array( $this, 'wpideas_ideas_table_columns' ), 10, 2 );
//			if ( get_option( 'wpideas_emailenabled' ) == 'true' && get_option( 'wpideas_status_changes' ) == '1' ){
//				add_action( 'transition_post_status', array( $this, 'wpideas_idea_status_changed' ), 10, 3 );
//			}
			if ( is_status_change_notification_enable() ){
				add_action( 'transition_post_status', array( $this, 'wpideas_idea_status_changed' ), 10, 3 );
			}
//			if ( get_option( 'wpideas_emailenabled' ) == 'true' && get_option( 'wpideas_comment_posted' ) == '1' ) {
//				add_action( 'wp_insert_comment', array( $this, 'wpideas_idea_comment_posted' ), 99, 2 );
//			}
//			if ( is_comment_posted_notification_enable() ) {
				add_action( 'wp_insert_comment', array( $this, 'wpideas_idea_comment_posted' ), 99, 2 );
//			}
			add_action( 'save_post', array( $this, 'wpideas_save_post' ), 13, 2 );
//			add_rewrite_rule('^idea/([^/]*)$','idea?tab=$matches[1]','top');

			$this -> init_attributes();

//			add_rewrite_rule(
//				'^idea/([^/]*)$',
//				'idea.php?tab=$matches[1]',
//				'top'
//			);
		}

		function wpideas_save_post( $post_id ) {
			if ( wp_is_post_revision( $post_id ) || RTBIZ_IDEAS_SLUG != get_post_type( $post_id ) ) {
				return;
			}
			$has_voted= check_user_voted( $post_id );
			if ( is_null( $has_voted ) || ! $has_voted ) {
				add_vote( $post_id );
				update_post_meta( $post_id, '_rt_wpideas_meta_votes', 1 );
			}
		}

		/**
		 * Init global variables
		 */
		function init_attributes() {

		}

		/**
		 * Register custom post type
		 */
		function register_wpidea_post_type() {
			$settings     = rt_idea_get_redux_settings();
			$labels = array(
				'name' => __( 'Ideas', 'rtCamp' ),
				'singular_name' => __( 'Idea', 'rtCamp' ),
				'add_new' => __( 'Add New', 'rtCamp' ),
				'add_new_item' => __( 'Add New Idea', 'rtCamp' ),
				'edit_item' => __( 'Edit Idea', 'rtCamp' ),
				'new_item' => __( 'New Idea', 'rtCamp' ),
				'all_items' => __( 'All Ideas', 'rtCamp' ),
				'view_item' => __( 'View Ideas', 'rtCamp' ),
				'search_items' => __( 'Search Ideas', 'rtCamp' ),
				'not_found' => __( 'No Idea found', 'rtCamp' ),
				'not_found_in_trash' => __( 'No Idea found in Trash', 'rtCamp' ),
				'parent_item_colon' => __( '', 'rtCamp' ),
				'menu_name' => __( isset( $settings['rt_idea_menu_label'] ) ? $settings['rt_idea_menu_label'] : 'Idea', 'rtCamp' ),
			);
			$menu_icon =  isset( $settings['rt_idea_logo_url']['url'] ) && ! empty( $settings['rt_idea_logo_url']['url'] )  ? $settings['rt_idea_logo_url']['url'] : RTBIZ_IDEAS_URL. 'app/assets/img/rt-16X16.png';
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => RTBIZ_IDEAS_SLUG,
					'with_front' => false,
				),
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => 35,
				'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'comments', 'page-attributes', 'custom-fields' ),
				'register_meta_box_cb' => array( $this, 'wpideas_add_voters_metabox' ),
				'menu_icon' => $menu_icon,
			);

			register_post_type( RTBIZ_IDEAS_SLUG, $args );
		}

		/**
		 * register custom post status
		 */
		function wpideas_custom_post_status() {
			register_post_status( 'idea-new', array(
				'label' => _x( 'New', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>' ),
			) );
			register_post_status( 'idea-accepted', array(
				'label' => _x( 'Accepted', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'Accepted <span class="count">(%s)</span>', 'Accepted <span class="count">(%s)</span>' ),
			) );
			register_post_status( 'idea-declined', array(
				'label' => _x( 'Declined', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'Declined <span class="count">(%s)</span>', 'Declined <span class="count">(%s)</span>' ),
			) );
			register_post_status( 'idea-completed', array(
				'label' => _x( 'Completed', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>' ),
			) );
		}

		/**
		 * Fill the post status select box and change the value accordingly
		 * 
		 * @global type $pagenow
		 * @global type $post
		 * @return type
		 */
		function wpideas_append_post_status_list() {
			global $pagenow;
			if ( get_post_type() == RTBIZ_IDEAS_SLUG && ( $pagenow == 'edit.php' || $pagenow == 'post-new.php' || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] ) == 'edit' ) ) {
				global $post;
				if ( ! isset( $post ) ) {
					return;
				}
				$completeNew = '';
				$completeAccepted = '';
				$completeDeclined = '';
				$completeCompleted = '';
				$label = get_post_status();
				if ( $post -> post_status == 'idea-new' ) {
					$completeNew = ' selected=\"selected\"';
					$label = 'New';
				}
				if ( $post -> post_status == 'idea-accepted' ) {
					$completeAccepted = ' selected=\"selected\"';
					$label = 'Accepted';
				}
				if ( $post -> post_status == 'idea-declined' ) {
					$completeDeclined = ' selected=\"selected\"';
					$label = 'Declined';
				}
				if ( $post -> post_status == 'idea-completed' ) {
					$completeCompleted = ' selected=\"selected\"';
					$label = 'Completed';
				}
				echo '
				<script>
				jQuery(document).ready(function($){
					$("select#post_status").append("<option value=\"idea-new\" ' . $completeNew . '>New</option>");
					$("select#post_status").append("<option value=\"idea-accepted\" ' . $completeAccepted . '>Accepted</option>");
					$("select#post_status").append("<option value=\"idea-declined\" ' . $completeDeclined . '>Declined</option>");
					$("select#post_status").append("<option value=\"idea-completed\" ' . $completeCompleted . '>Completed</option>");
					$(".inline-edit-status select").append("<option value=\"idea-new\" ' . $completeNew . '>New</option>");
					$(".inline-edit-status select").append("<option value=\"idea-accepted\" ' . $completeAccepted . '>Accepted</option>");
					$(".inline-edit-status select").append("<option value=\"idea-declined\" ' . $completeDeclined . '>Declined</option>");
					$(".inline-edit-status select").append("<option value=\"idea-completed\" ' . $completeCompleted . '>Completed</option>");
					$("#post-status-display").html("' . $label . '");
					$("#publishing-action").html("<span class=\"spinner\"><\/span><input name=\"original_publish\" type=\"hidden\" id=\"original_publish\" value=\"Update\"><input type=\"submit\" id=\"save-publish\" class=\"button button-primary button-large\" value=\"Update\" ><\/input>");
					$(".save-post-status").click(function(){
						$("#publish").hide();
						$("#publishing-action").html("<span class=\"spinner\"><\/span><input name=\"original_publish\" type=\"hidden\" id=\"original_publish\" value=\"Update\"><input type=\"submit\" id=\"save-publish\" class=\"button button-primary button-large\" value=\"Update\" ><\/input>");
					});
					$("#save-publish").click(function(){
						$("#publish").click();
					});
				});
				</script>
				';
			}
		}

		/**
		 * Votes columns in idea list admin
		 * 
		 * @param array $defaults
		 * @return type
		 */
		function wpideas_ideas_table_head( $defaults ) {
			$defaults[ 'wpideas_votes' ] = _x( 'Votes', 'wp-ideas' );
//			$defaults[ 'wpideas_posts' ] = _x( 'Idea for post', 'wp-ideas' );
			return $defaults;
		}

		/**
		 * Votes column value with vote count
		 * 
		 * @param type $column_name
		 * @param type $idea_id
		 */
		function wpideas_ideas_table_columns( $column_name, $idea_id ) {
			if ( $column_name == 'wpideas_votes' ) {
				$votes = get_post_meta( $idea_id, '_rt_wpideas_meta_votes', true );
				echo $votes;
			}
			/*if ( $column_name == 'wpideas_posts'){
				$postid= get_post_meta( $idea_id, '_rt_wpideas_post_id', true );
				if ( is_null($postid) || "" == $postid ){
					echo "-";
				}
				else{
                    $ids= explode(',',substr($postid, 1, -1));
                    foreach ($ids as $id) {
                        echo "<a href='" . get_edit_post_link($id) . "'>#$id: </a> " . get_the_title($id)." <br/>   ";
                    }
				}
			}*/
		}

		/**
		 * Send email notification when idea status changes if email notification is set to true
		 * 
		 * @param type $new_status
		 * @param type $old_status
		 * @param type $post
		 */
		function wpideas_idea_status_changed( $new_status, $old_status, $post ) {
			if ( $new_status != $old_status && get_post_type() == RTBIZ_IDEAS_SLUG ) {

				update_post_meta( $post -> post_ID, '_rt_wpideas_status_changer', get_current_user_id() );

				$user_info = get_userdata( get_current_user_id() );

				$status_changer = $user_info->user_login;

				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';

//				$subject = '[WP Ideas] Idea Status Change';
				$subject = create_new_idea_title('idea_status_change_email_title', $post -> ID);

				$author = $post -> post_author;
				$title = $post -> post_title;
				$post_link = get_permalink( $post->ID );

				$author_info = get_userdata( $author );

				global $rtWpIdeasSubscriber;
				$recipients =$rtWpIdeasSubscriber->get_subscriber_email($post->ID ,'status_change','YES');
				//				$recipients = array();
				//				array_push( $recipients, get_the_author_meta( 'user_email', $author ) );
				//				$temp = explode( ',', trim( get_option( 'wpideas_adminemails' ) ) );
				if (is_status_change_notification_enable()) {
					$temp = get_notification_emails();
					for ( $i = 0; $i < count( $temp ); $i ++ ) {
						array_push( $recipients, $temp[ $i ] );
					}
				}
				$message = '';
				$message .= '<h2>Idea status changed to '.preg_replace('/^idea-/', '', $new_status).' for [ <a href="'.$post_link.'"> ' . $title . '</a> ] </h2>';
				$message .= '<h3>[' . preg_replace('/^idea-/', '', $new_status) . '] ' . $title . '</h3>';
				$message .= '<label><b>Status updated by: </b><a href="' . get_author_posts_url( $user_info->ID ) . '"> ' . $status_changer . '</a></label><br/>';
				$message .= '<label><b>Author:</b> <a href="' . get_author_posts_url( $author ) . '">' . $author_info->first_name .' '. $author_info->last_name .'('. $author_info->user_login .')</a></label><br/>';
				if( get_post_meta( $post -> ID, '_rt_wpideas_meta_votes', true ) != 0 ){
					$votes_count = get_post_meta( $post -> ID, '_rt_wpideas_meta_votes', true );
				}else{
					$votes_count = 0;
				}
				$message .= '<label><b>Votes:</b> ' . $votes_count . '</label>';

				$this -> sendNotifications( $recipients, $subject, $message, $headers );
			}
		}

		/**
		 * Send email notifications when comment is posted on idea if email notification is set to true
		 * 
		 * @param type $comment_id
		 * @param type $comment_object
		 */
		function wpideas_idea_comment_posted( $comment_id, $comment_object ) {

			if ( $comment_object -> comment_approved > 0 && get_post_type() == RTBIZ_IDEAS_SLUG ) {
				global $rtWpIdeasSubscriber;
				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';
				//$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
				//$headers[] = 'Cc: iluvwp@wordpress.org';
				$rtWpIdeasSubscriber->add_subscriber($comment_object->comment_post_ID, $comment_object->user_id);

				$comment_content = $comment_object->comment_content;
				$comment_author = $comment_object->comment_author;
                $comment_author_url = get_comment_author_link( $comment_object->comment_ID );
				$idea_id = $comment_object->comment_post_ID;
				$idea = get_post( $idea_id );
				$idea_link = get_permalink( $idea_id );

				//				$subject = '[WP Ideas] Comment On ' . $idea -> post_title;
				$subject = create_new_idea_title('idea_comment_add_email_title', $idea_id);

				$author = $idea -> post_author;
//				array_push( $recipients, get_the_author_meta( 'user_email', $author ) );
				global $rtWpIdeasSubscriber;
				$recipients =$rtWpIdeasSubscriber->get_subscriber_email($idea_id,'comment_post','YES');
//				$temp = explode( ',', trim( get_option( 'wpideas_adminemails' ) ) );
				if (is_comment_posted_notification_enable()){
					$temp = get_notification_emails();
					for ( $i = 0; $i < count( $temp ); $i ++  ) {
						array_push( $recipients, $temp[ $i ] );
					}
				}
/*				function on_all_status_transitions( $new_status, $old_status, $post ) {
					if ( $new_status != $old_status ) {
						// A function to perform actions any time any post changes status.
					}
				}
				add_action(  'transition_post_status',  'on_all_status_transitions', 10, 3 );*/
				$comment_content = apply_filters ("the_content", $comment_content);
				$message  = '';
				$message .= '<h2> New Comment on <a href="'. $idea_link .'">' . $idea -> post_title . '</h2>';
				$message .= '<label><b>Commenter:</b> '. $comment_author_url .'</label><br/>';
				$message .= '<label><b>Content:</b> '.stripslashes($comment_content).'</label><br/>';
                $message .= '<label><b>Link:</b> <a href="'. get_comment_link( $comment_object ). '">Go to comment</a></label>';

				$this -> sendNotifications( $recipients, $subject, $message, $headers );
			}
		}

		/**
		 * Send email
		 * 
		 * @param type $recipients
		 * @param type $subject
		 * @param type $message
		 * @param type $headers
		 */
		function sendNotifications( $recipients, $subject, $message, $headers ) {
			$message.=get_signature();
			$multiple_to_recipients = $recipients;
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
			wp_mail( $multiple_to_recipients, $subject, $message, $headers );
			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
		}

		function set_html_content_type() {
			return 'text/html';
		}

		/**
		 * Add voters metabox in idea edit page
		 */
		function wpideas_add_voters_metabox(){
			add_meta_box( 'Voters', __( 'Voters' ), array( $this, 'wpideas_get_voters_of_idea' ), RTBIZ_IDEAS_SLUG, 'side', 'default' );
		}

		/**
		 * Get voters of idea
		 *
		 * @param $post
		 */
		function wpideas_get_voters_of_idea( $post ){
			global $rtWpideasVotes;
			$row = $rtWpideasVotes->get_voters_of_idea( $post->ID );
			if( ! empty( $row ) ){
				for( $i = 0; $i < sizeof( $row ); $i++ ){
					$voter_info = get_userdata( $row[$i]->user_id );
					echo '<a href="'. get_edit_user_link( $row[$i]->user_id ) .'">'. $voter_info->user_login .'</a><br/>';
				}
			}else{
				echo __('No votes yet.', 'wp-ideas' );
			}
		}

	}

}
