<?php
/**
 * User: spock
 * Date: 29/10/14
 * Time: 7:02 PM
 */
if ( ! class_exists( 'RTWPIdeasSubscriberModel' ) ) {
	class RTWPIdeasSubscriberModel extends RT_DB_Model {
		public function __construct() {
			parent::__construct( 'wpideas_subscriber' );

		}


		/**
		 * Insert subscribers
		 *
		 * @param type $data
		 *
		 * @return type
		 */
		function add_subscribers( $data )
		{
			return parent::insert( $data );
		}

		function add_subscriber( $post_id, $user_id ){
			if ( ! $this->check_subscriber_exist( $post_id, $user_id )) {
				$comment_notification =get_user_meta( $user_id,'comment_notification',true );
				$status_change =get_user_meta( $user_id,'status_change_notification',true );
				$status_change = isset($status_change) && $status_change !='NO' ? 'YES' : 'NO';
				$comment_notification = isset($comment_notification) && $comment_notification !='NO' ? 'YES' : 'NO';
				return $this->add_subscribers( array( 'post_id' => $post_id, 'user_id' => $user_id, 'status_change' =>$status_change,'comment_post' =>$comment_notification ) );
			}
			return false;
		}

		function check_subscriber_exist($post_id, $user_id ){
			global $wpdb;
			$result = $wpdb->get_var( $wpdb->prepare( 'SELECT count(*) FROM wp_rt_wpideas_subscriber WHERE post_id = %s and user_id = %s',$post_id,$user_id ) );
			if ( 1 == $result){
				return true;
			}
			return false;
		}

		/**
		 * Update subscribers
		 *
		 * @param type $data
		 * @param type $where
		 *
		 * @return type
		 */
		function update_subscribers( $data, $where )
		{
			return parent::update( $data, $where );
		}

		/**
		 * Delete subscribers
		 *
		 * @param type $where
		 *
		 * @return type
		 */
		function delete_subscribers( $where )
		{
			return parent::delete( $where );
		}

		function delete_subscriber( $post_id, $user_id ){
			$this->delete_subscribers( array( 'post_id' => $post_id, 'user_id' => $user_id, ) );
		}

		function get_subscriber_email( $post_id, $key, $value ){
			global $wpdb;
			$sql = $wpdb->prepare( 'SELECT user_id FROM '.$wpdb->prefix.'rt_wpideas_subscriber WHERE post_id = %s AND '.$key.' = %s',$post_id   , $value );
			$result =$wpdb->get_results( $sql , ARRAY_A );
			$emails = array();
			foreach ( $result as $user_id ){
				$user= get_userdata( $user_id['user_id'] );
				$emails[]= $user->user_email;
			}
			return $emails;
		}

		function get_user_post( $userid ){
			global $wpdb;
			$sql = $wpdb->prepare( 'SELECT post_id FROM wp_rt_wpideas_subscriber WHERE user_id = %s',$userid );
			$result=$wpdb->get_results( $sql , ARRAY_A );
			$key='post_id';
			$output = array_map(function($item) use ($key) {
				return $item[$key];
			}, $result);
			return $output;
		}

		function update_user_from_comment( $userid , $value ){
			$this->update_subscribers( array( 'comment_post' => $value ), array( 'user_id' =>$userid ) );
		}

		function update_user_from_status_change( $userid, $value ){
			$this->update_subscribers( array( 'status_change' => $value ), array( 'user_id' =>$userid ) );
		}

	}
}