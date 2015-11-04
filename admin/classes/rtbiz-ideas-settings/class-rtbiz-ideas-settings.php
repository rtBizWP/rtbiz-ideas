<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 *
 * ReduxFramework Sample Config File
 * For full documentation, please visit: https://docs.reduxframework.com
 * @author udit
 *
 * */
if ( ! class_exists( 'Rtbiz_Ideas_Settings' ) ) {

	class Rtbiz_Ideas_Settings {

		public $args = array();
		public $sections = array();
		public $ReduxFramework;
		static $page_slug = 'rtbiz-ideas-settings';

		static $ideas_opt = 'redux_idea_settings';

		public function __construct() {

			if ( ! class_exists( 'ReduxFramework' ) ) {
				return;
			}
			Rtbiz_Ideas::$loader->add_action( 'p2p_init', $this, 'init_settings', 25 );

		}

		public function init_settings() {
			// Set the default arguments
			$this->set_arguments();

			// Create the sections and fields
			if ( ! empty( $_GET['page'] ) && ! empty( $_GET['post_type'] ) && self::$page_slug === $_GET['page'] && Rtbiz_Ideas_Module::$post_type === $_GET['post_type'] ) {
				$this->set_sections();
			}

			if ( ! isset( $this->args['opt_name'] ) ) { // No errors please
				return;
			}

			// If Redux is running as a plugin, this will remove the demo notice and links
			add_action( 'redux/loaded', array( $this, 'remove_demo' ) );
			// Function to test the compiler hook and demo CSS output.
			// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.
			// add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 3);
			// Change the arguments after they've been declared, but before the panel is created
			// add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
			// Change the default value of a field after it's been set, but before it's been useds
			// add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );
			// Dynamically add a section. Can be also used to modify sections/fields
			// add_filter('redux/options/' . $this->args['opt_name'] . '/sections', array($this, 'dynamic_section'));

			$this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );

			return true;
			//add_action("redux/options/{$this->args[ 'opt_name' ]}/register", array( $this, 'test') );

		}


		/**
		 *
		 * Custom function for filtering the sections array. Good for child themes to override or add to the sections.
		 * Simply include this function in the child themes functions.php file.
		 *
		 * NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
		 * so you must use get_template_directory_uri() if you want to use any of the built in icons
		 * */
		function dynamic_section( $sections ) {
			//$sections = array();
			$sections[] = array(
				'title'  => __( 'Section via hook', 'redux-framework-demo' ),
				'desc'   => __( '<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', 'redux-framework-demo' ),
				'icon'   => 'el-icon-paper-clip',
				// Leave this as a blank section, no options just some intro text set above.
				'fields' => array(),
			);

			return $sections;
		}

		/**
		 *
		 * Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.
		 * */
		function change_arguments( $args ) {
			//$args['dev_mode'] = true;

			return $args;
		}

		/**
		 *
		 * Filter hook for filtering the default value of any given field. Very useful in development mode.
		 * */
		function change_defaults( $defaults ) {
			$defaults['str_replace'] = 'Testing filter hook!';

			return $defaults;
		}

		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function remove_demo() {

			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
				remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::instance(), 'plugin_metalinks' ), null, 2 );

				// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
				remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
			}

		}

		public function set_sections() {

			$author_cap = rtbiz_get_access_role_cap( RTBIZ_IDEAS_TEXT_DOMAIN, 'author' );
			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_IDEAS_TEXT_DOMAIN, 'editor' );
			$admin_cap  = rtbiz_get_access_role_cap( RTBIZ_IDEAS_TEXT_DOMAIN, 'admin' );


			$general_fields   = array(
				array(
					'id'       => 'wpideas_editorenabled',
					'type'     => 'switch',
					'title'    => __( 'Enable WYSIWYG Editor' ),
					'subtitle' => __( 'This will allow WYSIWYG editor on creating of new Idea!' ),
					'default'  => false,
					'on'       => __( 'Enable' ),
					'off'      => __( 'Disable' ),
				),
			);
			$this->sections[] = array(
				'icon'   => 'el-icon-cogs',
				'title'  => __( 'General' ),
				//'permissions' => $admin_cap,
				'fields' => $general_fields,
			);
			$this->sections[] = array(
				'icon'   => 'el-icon-envelope',
				'title'  => __( 'Notification Emails' ),
				'permissions' => $admin_cap,
				'fields' => array(
					array(
						'id'       => 'wpideas_adminemails',
						'title'    => __( 'Email Addresses' ),
						'subtitle' => __( 'Email addresses to be notified on events' ),
						'desc'     => __( 'These email addresses will be notified of the events that occurs in Idea systems. This is a global list. All the subscribers on idea will be notified along with this list.' ),
						'type'     => 'multi_text',
						'validate' => 'email',
						'multi'    => true,
						'show_empty' => false,
						'add_text'   => 'Add Emails',
					),
					array(
						'id'       => 'wpideas_emailenabled',
						'type'     => 'switch',
						'title'    => __( 'Notifications Emails' ),
						'subtitle' => __( 'To enable/disable Notification' ),
						'desc'     => __( 'If you turn on this feature it will send notification emails on below selected notification events.' ),
						'default'  => false,
						'on'       => __( 'Enable' ),
						'off'      => __( 'Disable' ),
					),
					array(
						'id'       => 'section-notification_acl-start',
						'type'     => 'section',
						'indent'   => true, // Indent all options below until the next 'section' option is set.
						'required' => array( 'wpideas_emailenabled', '=', 1 ),
					),
					array(
						'id'       => 'rt_idea_notification_events',
						'title'    => __( 'Notification Events' ),
						'subtitle' => __( 'Events to be notified to users' ),
						'desc'     => __( 'These events will be notified to the Notification Emails whenever they occur.' ),
						'type'     => 'checkbox',
						'options'  => array(
							'wpideas_idea_posted'    => __( 'New idea posted' ),
							'wpideas_comment_posted' => __( 'New comment Added' ),
							'wpideas_status_change'  => __( 'Idea status changed' ),
						),
					),
					array(
						'id'       => 'idea_new_idea_email_title',
						'type'     => 'text',
						'title'    => __( 'Title for new idea' ),
						'subtitle' => __( 'This is title for new idea created' ),
						'desc'     => __( 'You can use {idea_id} and {idea_title}' ),
						'default'  => '[New Idea Created] idea #{idea_id} : {idea_title}',
					),
					array(
						'id'       => 'idea_status_change_email_title',
						'type'     => 'text',
						'title'    => __( 'Title when idea status changed' ),
						'subtitle' => __( 'This is title when idea status changed' ),
						'desc'     => __( 'You can use {idea_id} and {idea_title}' ),
						'default'  => '[Idea Status Changed] idea #{idea_id} : {idea_title}',
					),
					array(
						'id'       => 'idea_comment_add_email_title',
						'type'     => 'text',
						'title'    => __( 'Title when comment added' ),
						'subtitle' => __( 'This is title when comment is added to an Idea' ),
						'desc'     => __( 'You can use {idea_id} and {idea_title}' ),
						'default'  => '[Idea Comment Added] idea #{idea_id} : {idea_title}',
					),
					array(
						'id'       => 'idea_signature_enable',
						'type'     => 'switch',
						'title'    => __( 'Enable email signature ' ),
						'subtitle' => __( 'Enable Append signature to each mail' ),
						'default'  => false,
						'on'       => __( 'Enable' ),
						'off'      => __( 'Disable' ),
					),
					array(
						'id'           => 'idea_signature_text',
						'type'         => 'textarea',
						'title'        => __( 'Email Signature' ),
						'subtitle'     => __( 'Add here Email Signature' ),
						'desc'         => esc_attr( 'You can add email signature here that will be send with every email send with the Idea plugin, Allowed tags are <a> <br> <em> <strong>.' ),
						'validate'     => 'html_custom',
						'default'      => esc_attr( '<br />Sent via rtCamp Idea Plugin<br />' ),
						'required' => array( 'idea_signature_enable', '=', 1 ),
						'allowed_html' => array(
							'a'      => array(
								'href'  => array(),
								'title' => array()
							),
							'br'     => array(),
							'em'     => array(),
							'strong' => array()
						),
					),
					array(
						'id'     => 'section-notification_acl-start',
						'type'   => 'section',
						'indent' => false, // Indent all options below until the next 'section' option is set.
					),
				)
			);

			return true;
		}

		/**
		 *
		 * All the possible arguments for Redux.
		 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
		 * */
		public function set_arguments() {

			//$theme = wp_get_theme(); // For use with some settings. Not necessary.
			$admin_cap = rtbiz_get_access_role_cap( RTBIZ_IDEAS_TEXT_DOMAIN, 'admin' );

			$this->args = array(
				// TYPICAL -> Change these values as you need/desire
				'opt_name'           => self::$ideas_opt,
				// This is where your data is stored in the database and also becomes your global variable name.
				'display_name'       => __( 'Settings' ),
				// Name that appears at the top of your panel
				'display_version'    => RTBIZ_IDEAS_VERSION,
				// Version that appears at the top of your panel
				'menu_type'          => 'submenu',
				//Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
				'allow_sub_menu'     => false,
				// Show the sections below the admin menu item or not
				'menu_title'         => __( 'Settings' ),
				'page_title'         => __( 'Settings' ),
				// You will need to generate a Google API key to use this feature.
				// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
				'google_api_key'     => '',
				// Must be defined to add google fonts to the typography module
				'async_typography'   => true,
				// Use a asynchronous font on the front end or font string
				//'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
				'admin_bar'          => false,
				// Show the panel pages on the admin bar
				'global_variable'    => '',
				// Set a different name for your global variable other than the opt_name
				'dev_mode'           => false,
				// Show the time the page took to load, etc
				'customizer'         => false,
				// Enable basic customizer support
				//'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
				//'disable_save_warn' => true,                    // Disable the save warning when a user changes a field
				// OPTIONAL -> Give you extra features
				'page_priority'      => null,
				// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
				'page_parent'        => 'edit.php?post_type=' . esc_attr( Rtbiz_Ideas_Module::$post_type ),
				// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
				'page_permissions'   => $admin_cap,
				// Permissions needed to access the options panel.
				//'menu_icon' => '', // Specify a custom URL to an icon
				//'last_tab' => '', // Force your panel to always open to a specific tab (by id)
				//'page_icon' => 'icon-themes', // Icon displayed in the admin panel next to your menu_title
				'page_slug'          => self::$page_slug,
				// Page slug used to denote the panel
				'save_defaults'      => true,
				// On load save the defaults to DB before user clicks save or not
				'default_show'       => false,
				// If true, shows the default value next to each field that is not the default value.
				'default_mark'       => '',
				// What to print by the field's title if the value shown is default. Suggested: *
				'show_import_export' => true,
				// Shows the Import/Export panel when not used as a field.
				// CAREFUL -> These options are for advanced use only
				'transient_time'     => 60 * MINUTE_IN_SECONDS,
				'output'             => true,
				// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
				'output_tag'         => true,
				// Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
				// 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
				// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
				'database'           => '',
				// possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
				'system_info'        => false,
				'ajax_save'         => false,
				// REMOVE
				// HINTS
				'hints'              => array(
					'icon'          => 'icon-question-sign',
					'icon_position' => 'right',
					'icon_color'    => 'lightgray',
					'icon_size'     => 'normal',
					'tip_style'     => array(
						'color'   => 'light',
						'shadow'  => true,
						'rounded' => false,
						'style'   => '',
					),
					'tip_position'  => array(
						'my' => 'top left',
						'at' => 'bottom right',
					),
					'tip_effect'    => array(
						'show' => array(
							'effect'   => 'slide',
							'duration' => '500',
							'event'    => 'mouseover',
						),
						'hide' => array(
							'effect'   => 'slide',
							'duration' => '500',
							'event'    => 'click mouseleave',
						),
					),
				),
			);

			return true;
		}

	}

}
