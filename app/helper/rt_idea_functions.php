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
	if (  is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_status_change'] ) && $settings['rt_idea_notification_events']['wpideas_status_change'] == 1 ) {
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

function is_new_idea_posted_notification_enable(){
	$settings     = rt_idea_get_redux_settings();
	if ( is_email_notification_enable() && isset( $settings['rt_idea_notification_events']['wpideas_idea_posted'] ) && $settings['rt_idea_notification_events']['wpideas_idea_posted'] == 1 ) {
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
	return '';
}

function generate_email_title( $post_id, $title ) {
	if ( ! is_null( $title ) ){
		$title = str_replace( '{idea_title}',get_the_title( $post_id ), $title );
		$title = str_replace( '{idea_id}', $post_id, $title );
		return $title;
	}
	return null;
}

function create_new_idea_title( $key, $post_id ){
	$redux = rt_idea_get_redux_settings();
	if ( isset( $redux[ $key ] ) ) {
		return html_entity_decode(generate_email_title( $post_id, $redux[ $key ] ));
	}
	return null;
}
function is_edit_page($new_edit = null){
	global $pagenow;
	//make sure we are on the backend
	if (!is_admin()) return false;
	if($new_edit == "edit")
		return in_array( $pagenow, array( 'post.php',  ) );
	elseif($new_edit == "new") //check for new post page
		return in_array( $pagenow, array( 'post-new.php' ) );
	else //check for either new or edit
		return in_array( $pagenow, array( 'post.php', 'post-new.php' ) );
}