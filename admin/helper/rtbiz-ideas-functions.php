<?php

if ( ! function_exists( 'rtbiz_ideas_is_edit_page' ) ) {

	/**
	 * get ideas setting
	 * @return mixed
	 */
	function rtbiz_ideas_is_edit_page( $new_edit = null ) {
		global $pagenow;
		//make sure we are on the backend
		if ( ! is_admin() ) {
			return false;
		}
		if ( 'edit' == $new_edit ) {
			return in_array( $pagenow, array( 'post.php', ) );
		} elseif ( 'new' == $new_edit ) {
			return in_array( $pagenow, array( 'post-new.php' ) );
		} else {
			return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
		}
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_template' ) ) {
	/**
	 *
	 * Get ideas Templates
	 *
	 * @param $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return void
	 */
	function rtbiz_ideas_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$located = rtbiz_ideas_locate_template( $template_name, $template_path, $default_path );

		do_action( 'rtbiz_ideas_before_template_part', $template_name, $template_path, $located, $args );

		include( $located );

		do_action( 'rtbiz_ideas_after_template_part', $template_name, $template_path, $located, $args );
	}
}

if ( ! function_exists( 'rtbiz_ideas_locate_template' ) ) {

	/**
	 * Loads ideas Templates
	 *
	 * @param $template_name
	 * @param string $template_path
	 * @param string $default_path
	 *
	 * @return mixed|void
	 */
	function rtbiz_ideas_locate_template( $template_name, $template_path = '', $default_path = '' ) {

		global $rtWpIdeas;
		if ( ! $template_path ) {
			$template_path = Rtbiz_Ideas::$templateURL;
		}
		if ( ! $default_path ) {
			$default_path = RTBIZ_IDEAS_PATH_TEMPLATES;
		}

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found
		return apply_filters( 'rtbiz_ideas_locate_template', $template, $template_name, $template_path );
	}
}


if ( ! function_exists( 'rtbiz_ideas_get_redux_settings' ) ) {

	/**
	 * get ideas setting
	 * @return mixed
	 */
	function rtbiz_ideas_get_redux_settings() {
		if ( ! isset( $GLOBALS['redux_idea_settings'] ) ) {
			$GLOBALS['redux_idea_settings'] = get_option( 'redux_idea_settings', array() );
		}

		return $GLOBALS['redux_idea_settings'];
	}
}

if ( ! function_exists( 'rtbiz_ideas_is_editor_enable' ) ) {

	/**
	 * get ideas setting
	 * @return mixed
	 */
	function rtbiz_ideas_is_editor_enable() {
		$settings     = rtbiz_ideas_get_redux_settings();
		if ( isset( $settings['wpideas_editorenabled'] ) && 1 == $settings['wpideas_editorenabled'] ) {
			return true;
		}
		return false;
	}
}

/********************************** Notification setting   **********************************/

if ( ! function_exists( 'rtbiz_ideas_create_new_idea_title' ) ) {

	/**
	 * get subject for notification mail
	 *
	 * @param $key
	 * @param $post_id
	 *
	 * @return null|string
	 */
	function rtbiz_ideas_create_new_idea_title( $key, $post_id ) {
		$redux = rtbiz_ideas_get_redux_settings();
		if ( isset( $redux[ $key ] ) ) {
			return html_entity_decode( rtbiz_ideas_generate_email_title( $post_id, $redux[ $key ] ) );
		}

		return null;
	}
}

if ( ! function_exists( 'rtbiz_ideas_generate_email_title' ) ) {

	/**
	 * generate title from post id & setting setting
	 *
	 * @param $post_id
	 * @param $title
	 *
	 * @return null|string
	 */
	function rtbiz_ideas_generate_email_title( $post_id, $title ) {
		if ( ! is_null( $title ) ) {
			$title = str_replace( '{idea_title}', get_the_title( $post_id ), $title );
			$title = str_replace( '{idea_id}', $post_id, $title );

			return $title;
		}

		return null;
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_notification_emails' ) ) {

	/**
	 * git all globle notification emails
	 * @return null
	 */
	function rtbiz_ideas_get_notification_emails() {
		$settings = rtbiz_ideas_get_redux_settings();
		if ( isset( $settings['wpideas_adminemails'] ) && is_array( $settings['wpideas_adminemails'] ) ) {
			return $settings['wpideas_adminemails'];
		}

		return null;
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_signature' ) ) {

	/**
	 * get signature form settings
	 * @return string
	 */
	function rtbiz_ideas_get_signature() {
		$settings = rtbiz_ideas_get_redux_settings();
		if ( isset( $settings['idea_signature_enable'] ) && 1 == $settings['idea_signature_enable'] ) {
			if ( isset( $settings['idea_signature_text'] ) ) {
				return $settings['idea_signature_text'];
			}
		}

		return '';
	}
}

if ( ! function_exists( 'rtbiz_ideas_is_email_notification_enable' ) ) {

	/**
	 * check email notification enable or not.
	 * @return bool
	 */
	function rtbiz_ideas_is_email_notification_enable() {
		$settings = rtbiz_ideas_get_redux_settings();
		if ( isset( $settings['wpideas_emailenabled'] ) && 1 == $settings['wpideas_emailenabled'] ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'rtbiz_ideas_is_status_change_notification_enable' ) ) {

	/**
	 * Check status_change_notification notification is enble or disable
	 * @return bool
	 */
	function rtbiz_ideas_is_status_change_notification_enable() {
		$settings = rtbiz_ideas_get_redux_settings();
		if ( rtbiz_ideas_is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_status_change'] ) && 1 == $settings['rt_idea_notification_events']['wpideas_status_change'] ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'rtbiz_ideas_is_comment_posted_notification_enable' ) ) {

	/**
	 * Check status_change_notification notification is enble or disable
	 * @return bool
	 */
	function rtbiz_ideas_is_comment_posted_notification_enable() {
		$settings = rtbiz_ideas_get_redux_settings();
		if ( rtbiz_ideas_is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_comment_posted'] ) && 1 == $settings['rt_idea_notification_events']['wpideas_comment_posted'] ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'rtbiz_ideas_is_new_idea_posted_notification_enable' ) ) {

	/**
	 * Check status_change_notification notification is enble or disable
	 * @return bool
	 */
	function rtbiz_ideas_is_new_idea_posted_notification_enable() {
		$settings = rtbiz_ideas_get_redux_settings();
		if ( rtbiz_ideas_is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_idea_posted'] ) && 1 == $settings['rt_idea_notification_events']['wpideas_idea_posted'] ) {
			return true;
		}
		return false;
	}
}

/********************************** Vote   **********************************/

if ( ! function_exists( 'rtbiz_ideas_check_user_voted' ) ) {

	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	function rtbiz_ideas_check_user_voted( $post_id ) {
		global $rtbiz_ideas_votes;
		return $rtbiz_ideas_votes->check_user_voted( $post_id );
	}
}

if ( ! function_exists( 'rtbiz_ideas_add_vote' ) ) {

	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	function rtbiz_ideas_add_vote( $post_id ) {
		global $rtbiz_ideas_votes;
		return $rtbiz_ideas_votes->add_vote( $post_id );
	}
}

if ( ! function_exists( 'rtbiz_ideas_update_vote' ) ) {

	/**
	 * @param $post_id
	 * @param $vote_count
	 *
	 * @return mixed
	 */
	function rtbiz_ideas_update_vote( $post_id, $vote_count ) {
		global $rtbiz_ideas_votes;
		return $rtbiz_ideas_votes->update_vote( $post_id, $vote_count );
	}
}

if ( ! function_exists( 'rtbiz_ideas_delete_vote' ) ) {

	/**
	 * @param $post_id
	 *
	 * @return mixed
	 */
	function rtbiz_ideas_delete_vote( $post_id ) {
		global $rtbiz_ideas_votes;
		return $rtbiz_ideas_votes->delete_vote( $post_id );
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_votes_by_idea' ) ) {

	/**
	 * @param $idea_id
	 *
	 * @return mixed
	 */
	function rtbiz_ideas_get_votes_by_idea( $idea_id ) {
		global $rtbiz_ideas_votes;
		return $rtbiz_ideas_votes->get_votes_by_idea( $idea_id );
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_all_ideas' ) ) {

	/**
	 * @return mixed
	 */
	function rtbiz_ideas_get_all_ideas( ) {
		global $rtbiz_ideas_votes;
		return $rtbiz_ideas_votes->get_all_ideas( );
	}
}
