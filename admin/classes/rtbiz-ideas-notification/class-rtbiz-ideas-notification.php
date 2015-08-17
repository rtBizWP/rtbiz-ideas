<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Notification' ) ) {
	/**
	 * Class Rtbiz_Ideas_Notification
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Notification {

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {

			if ( rtbiz_ideas_is_status_change_notification_enable() ) {
				Rtbiz_Ideas::$loader->add_action( 'transition_post_status', $this, 'idea_status_changed_notification', 10, 3 );
			}

			if ( rtbiz_ideas_is_comment_posted_notification_enable() ) {
				Rtbiz_Ideas::$loader->add_action( 'wp_insert_comment', $this, 'idea_comment_posted_notification', 99, 2 );
			}
		}

		/**
		 * Send email notification when idea status changes if email notification is set to true
		 *
		 * @param type $new_status
		 * @param type $old_status
		 * @param type $post
		 *
		 * @since 0.1
		 */
		public function idea_status_changed_notification( $new_status, $old_status, $post ) {
			if ( $new_status != $old_status && get_post_type() == self::$post_type ) {

				update_post_meta( $post->post_ID, '_rt_wpideas_status_changer', get_current_user_id() );

				$user_info = get_userdata( get_current_user_id() );

				$status_changer = $user_info->user_login;

				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';

				$subject = rtbiz_ideas_create_new_idea_title( 'idea_status_change_email_title', $post->ID );

				$author    = $post->post_author;
				$title     = $post->post_title;
				$post_link = get_permalink( $post->ID );

				$author_info = get_userdata( $author );

				global $rtWpIdeasSubscriber;
				$recipients = $rtWpIdeasSubscriber->get_subscriber_email( $post->ID, 'status_change', 'YES' );
				if ( rtbiz_ideas_is_status_change_notification_enable() ) {
					$temp = rtbiz_ideas_get_notification_emails();
					for ( $i = 0; $i < count( $temp ); $i ++ ) {
						array_push( $recipients, $temp[ $i ] );
					}
				}
				$message = '';
				$message .= '<h2>Idea status changed to ' . preg_replace( '/^idea-/', '', $new_status ) . ' for [ <a href="' . $post_link . '"> ' . $title . '</a> ] </h2>';
				$message .= '<h3>[' . preg_replace( '/^idea-/', '', $new_status ) . '] ' . $title . '</h3>';
				$message .= '<label><b>Status updated by: </b><a href="' . get_author_posts_url( $user_info->ID ) . '"> ' . $status_changer . '</a></label><br/>';
				$message .= '<label><b>Author:</b> <a href="' . get_author_posts_url( $author ) . '">' . $author_info->first_name . ' ' . $author_info->last_name . '(' . $author_info->user_login . ')</a></label><br/>';
				if ( get_post_meta( $post->ID, '_rt_wpideas_meta_votes', true ) != 0 ) {
					$votes_count = get_post_meta( $post->ID, '_rt_wpideas_meta_votes', true );
				} else {
					$votes_count = 0;
				}
				$message .= '<label><b>Votes:</b> ' . $votes_count . '</label>';

				$this->send_notifications( $recipients, $subject, $message, $headers );
			}
		}

		/**
		 * Send email notifications when comment is posted on idea if email notification is set to true
		 *
		 * @param $comment_id
		 * @param $comment_object
		 *
		 * @since 0.1
		 */
		public function idea_comment_posted_notification( $comment_id, $comment_object ) {

			if ( $comment_object->comment_approved > 0 && get_post_type() == self::$post_type ) {
				global $rtWpIdeasSubscriber;
				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';
				$rtWpIdeasSubscriber->add_subscriber( $comment_object->comment_post_ID, $comment_object->user_id );

				$comment_content    = $comment_object->comment_content;
				$comment_author_url = get_comment_author_link( $comment_object->comment_ID );
				$idea_id            = $comment_object->comment_post_ID;
				$idea               = get_post( $idea_id );
				$idea_link          = get_permalink( $idea_id );

				$subject = create_new_idea_title( 'idea_comment_add_email_title', $idea_id );

				global $rtWpIdeasSubscriber;
				$recipients = $rtWpIdeasSubscriber->get_subscriber_email( $idea_id, 'comment_post', 'YES' );
				if ( rtbiz_ideas_is_comment_posted_notification_enable() ) {
					$temp = rtbiz_ideas_get_notification_emails();
					for ( $i = 0; $i < count( $temp ); $i ++ ) {
						array_push( $recipients, $temp[ $i ] );
					}
				}

				$comment_content = apply_filters( 'the_content', $comment_content );
				$message  = '';
				$message .= '<h2> New Comment on <a href="' . $idea_link . '">' . $idea->post_title . '</h2>';
				$message .= '<label><b>Commenter:</b> ' . $comment_author_url . '</label><br/>';
				$message .= '<label><b>Content:</b> ' . stripslashes( $comment_content ) . '</label><br/>';
				$message .= '<label><b>Link:</b> <a href="' . get_comment_link( $comment_object ) . '">Go to comment</a></label>';

				$this->send_notifications( $recipients, $subject, $message, $headers );
			}
		}

		/**
		 * Send email notifications when comment is posted on idea if email notification is set to true
		 *
		 * @param $idea_id
		 * @param $idea_object
		 *
		 * @since 0.1
		 */
		public function idea_posted_notification( $idea_id, $idea_object ) {
			if ( rtbiz_ideas_is_new_idea_posted_notification_enable() ) {
				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';

				$subject      = ( rtbiz_ideas_create_new_idea_title( 'idea_new_idea_email_title', $idea_id ) );
				$recipients   = rtbiz_ideas_get_notification_emails();
				$post_content = apply_filters( 'the_content', $idea_object->post_content );
				$currentuser  = wp_get_current_user();

				$message      = '';
				$message .= '<h3>' . $currentuser->display_name . ' posted a new idea</h3>';
				$message .= '<h2>' . stripslashes( $idea_object->post_title ) . '</h2>';
				$message .= '<p>' . stripslashes( $post_content ) . '</p>';
				$this->send_notifications( $recipients, $subject, $message, $headers );
			}
		}

		/**
		 * Send idea plugin notification
		 *
		 * @param $recipients
		 * @param $subject
		 * @param $message
		 * @param $headers
		 */
		public function send_notifications( $recipients, $subject, $message, $headers ) {
			$message .= rtbiz_ideas_get_signature();
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
			wp_mail( $recipients, $subject, $message, $headers );
			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', 'set_html_content_type' );
		}

		/**
		 * set mail html content type
		 * @return string
		 */
		public function set_html_content_type() {
			return 'text/html';
		}
	}
}
