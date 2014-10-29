<?PHP

// Setting ApI
function rt_idea_get_redux_settings() {
	if ( ! isset( $GLOBALS['redux_idea_settings'] ) ) {
		$GLOBALS['redux_idea_settings'] = get_option( 'redux_idea_settings', array() );
	}
	return $GLOBALS['redux_idea_settings'];
}

function is_email_notification_enable(){
	$settings     = rt_idea_get_redux_settings();
	if (  isset( $settings['wpideas_emailenabled'] ) && $settings['wpideas_emailenabled'] == 1 ) {
		return true;
	}
	return false;
}

function get_notification_emails(){
	$settings     = rt_idea_get_redux_settings();
	if (  isset( $settings['wpideas_adminemails'] ) && is_array( $settings['wpideas_adminemails'] )){
		return $settings['wpideas_adminemails'];
	}
	return null;
}

function is_status_change_notification_enable(){
	$settings     = rt_idea_get_redux_settings();
	if (  is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_status_changes'] ) && $settings['rt_idea_notification_events']['wpideas_status_changes'] == 1 ) {
		return true;
	}
	return false;
}

function is_comment_posted_notification_enable(){
	$settings     = rt_idea_get_redux_settings();
	if ( is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_comment_posted'] ) && $settings['rt_idea_notification_events']['wpideas_comment_posted'] == 1 ) {
		return true;
	}
	return false;
}

function is_editor_enable(){
	$settings     = rt_idea_get_redux_settings();
	if ( isset( $settings['wpideas_editorenabled'] ) && $settings['wpideas_editorenabled'] == 1 ) {
		return true;
	}
	return false;
}

function get_signature(){
	$settings     = rt_idea_get_redux_settings();
	if ( isset( $settings['idea_signature_enable'] ) && $settings['idea_signature_enable'] == 1 ){
		if ( isset( $settings['idea_signature_text'] ) ) {
			return $settings['idea_signature_text'];
		}
	}
}