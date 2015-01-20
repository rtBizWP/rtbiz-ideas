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

function rt_idea_check_plugin_dependecy() {
	global $rt_idea_plugin_check;
	$rt_idea_plugin_check = array(
		'rtbiz' => array(
			'project_type' => 'all',
			'name' => esc_html__( 'WordPress for Business.', 'rt_biz' ),
			'active' => class_exists( 'Rt_Biz' ),
			'filename' => 'index.php',
		),
	);

	$flag = true;

	if ( ! class_exists( 'Rt_Biz' ) || ! did_action( 'rt_biz_init' ) ) {
		$flag = false;
	}

	if ( ! $flag ) {
		add_action( 'admin_enqueue_scripts', 'rt_idea_plugin_check_enque_js' );
		add_action( 'wp_ajax_rt_idea_activate_plugin',  'rt_idea_activate_plugin_ajax' );
		add_action( 'admin_notices', 'rt_idea_admin_notice_dependency_not_installed'  );
		add_action( 'wp_ajax_rt_idea_install_plugin', 'rt_idea_install_plugin_ajax' );

	}

	return $flag;
}

function rt_idea_plugin_check_enque_js() {
	wp_enqueue_script( 'rt-idea-plugin-check', RTBIZ_IDEAS_URL . 'app/assets/js/rt_idea_plugin_check.js', '', false, true );
	wp_localize_script( 'rt-idea-plugin-check', 'rt_idea_ajax_url', admin_url( 'admin-ajax.php' ) );
}


/**
 * if rtbiz plugin is not installed or activated it gives notification to user to do so.
 *
 * @since 0.1
 */
function rt_idea_admin_notice_dependency_not_installed() {
	if ( ! rt_idea_is_plugin_installed( 'rtbiz' ) ) { ?>
		<div class="error rt-idea-not-installed-error">
			<?php			$nonce = wp_create_nonce( 'rt_idea_install_plugin_rtbiz' ); ?>

			<p><b><?php _e( 'rtBiz Idea:' ) ?></b> <?php _e( 'Click' ) ?> <a href="#"
			                                                                     onclick="install_rt_idea_plugin('rtbiz','rt_idea_install_plugin','<?php echo $nonce ?>')">here</a> <?php _e( 'to install rtBiz.', RT_IDEA_TEXT_DOMAIN ) ?>
			</p>
		</div>
	<?php } else {
		if ( rt_idea_is_plugin_installed( 'rtbiz' ) && ! rt_idea_is_plugin_active( 'rtbiz' ) ) {
			$path  = rt_idea_get_path_for_plugin( 'rtbiz' );
			$nonce = wp_create_nonce( 'rt_idea_activate_plugin_' . $path );
			?>
			<div class="error rt-idea-not-installed-error">
				<p><b><?php _e( 'rtBiz Idea:' ) ?></b> <?php _e( 'Click' ) ?> <a href="#"
				                                                                 onclick="activate_rt_idea_plugin('<?php echo $path ?>','rt_idea_activate_plugin','<?php echo $nonce; ?>')">here</a> <?php _e( 'to activate rtBiz.', RT_IDEA_TEXT_DOMAIN ) ?>
				</p>
			</div>
		<?php }
	}
}

function rt_idea_install_plugin_ajax(){
	if ( empty( $_POST['plugin_slug'] ) ) {
		die( __( 'ERROR: No slug was passed to the AJAX callback.', RT_IDEA_TEXT_DOMAIN ) );
	}
	check_ajax_referer( 'rt_idea_install_plugin_rtbiz');

	if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
		die( __( 'ERROR: You lack permissions to install and/or activate plugins.', RT_IDEA_TEXT_DOMAIN ) );
	}
	rt_idea_install_plugin( $_POST['plugin_slug'] );

	echo 'true';
	die();
}

function rt_idea_install_plugin( $plugin_slug ){
	include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	$api = plugins_api( 'plugin_information', array( 'slug' => $plugin_slug, 'fields' => array( 'sections' => false ) ) );

	if ( is_wp_error( $api ) ) {
		die( sprintf( __( 'ERROR: Error fetching plugin information: %s', RT_IDEA_TEXT_DOMAIN ), $api->get_error_message() ) );
	}

	if ( ! class_exists( 'Plugin_Upgrader' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
	}

	if ( ! class_exists( 'Rt_Idea_Plugin_Upgrader_Skin' ) ) {
		require_once( RTBIZ_IDEAS_PATH . 'app/admin/class-rt-idea-plugin-upgrader-skin.php' );
	}

	$upgrader = new Plugin_Upgrader( new Rt_Idea_Plugin_Upgrader_Skin( array(
		                                                                 'nonce'  => 'install-plugin_' . $plugin_slug,
		                                                                 'plugin' => $plugin_slug,
		                                                                 'api'    => $api,
	                                                                 ) ) );

	$install_result = $upgrader->install( $api->download_link );

	if ( ! $install_result || is_wp_error( $install_result ) ) {
		// $install_result can be false if the file system isn't writeable.
		$error_message = __( 'Please ensure the file system is writeable', RT_IDEA_TEXT_DOMAIN );

		if ( is_wp_error( $install_result ) ) {
			$error_message = $install_result->get_error_message();
		}

		die( sprintf( __( 'ERROR: Failed to install plugin: %s', RT_IDEA_TEXT_DOMAIN ), $error_message ) );
	}

	$activate_result = activate_plugin( rt_idea_get_path_for_plugin( $plugin_slug ) );
	if ( is_wp_error( $activate_result ) ) {
		die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RT_IDEA_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
	}
}


function rt_idea_get_path_for_plugin( $slug ) {
	global $rt_idea_plugin_check;
	$filename = ( ! empty( $rt_idea_plugin_check[ $slug ]['filename'] ) ) ? $rt_idea_plugin_check[ $slug ]['filename'] : $slug . '.php';

	return $slug . '/' . $filename;
}

function rt_idea_is_plugin_active( $slug ) {
	global $rt_idea_plugin_check;
	if ( empty( $rt_idea_plugin_check[ $slug ] ) ) {
		return false;
	}

	return $rt_idea_plugin_check[ $slug ]['active'];
}

function rt_idea_is_plugin_installed( $slug ) {
	global $rt_idea_plugin_check;
	if ( empty( $rt_idea_plugin_check[ $slug ] ) ) {
		return false;
	}

	if (rt_idea_is_plugin_active( $slug ) || file_exists( WP_PLUGIN_DIR . '/' . rt_idea_get_path_for_plugin( $slug ) ) ) {
		return true;
	}

	return false;
}

/**
 * ajax call for active plugin
 */
function rt_idea_activate_plugin_ajax() {
	if ( empty( $_POST['path'] ) ) {
		die( __( 'ERROR: No slug was passed to the AJAX callback.', RT_IDEA_TEXT_DOMAIN ) );
	}
	check_ajax_referer( 'rt_idea_activate_plugin_' . $_POST['path'] );

	if ( ! current_user_can( 'activate_plugins' ) ) {
		die( __( 'ERROR: You lack permissions to activate plugins.', RT_IDEA_TEXT_DOMAIN ) );
	}

	rt_idea_activate_plugin( $_POST['path'] );

	echo 'true';
	die();
}

/**
 * @param $plugin_path
 * ajax call for active plugin calls this function to active plugin
 */
function rt_idea_activate_plugin( $plugin_path ) {

	$activate_result = activate_plugin( $plugin_path );
	if ( is_wp_error( $activate_result ) ) {
		die( sprintf( __( 'ERROR: Failed to activate plugin: %s', RT_IDEA_TEXT_DOMAIN ), $activate_result->get_error_message() ) );
	}
}

