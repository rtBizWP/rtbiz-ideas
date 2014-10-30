<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//use Zend\Mail\Storage\Imap as ImapStorage;

/**
 *
 * ReduxFramework Sample Config File
 * For full documentation, please visit: https://docs.reduxframework.com
 * @author utkarsh
 *
 * */
if ( ! class_exists( 'Redux_Framework_Idea_Config' ) ) {

	class Redux_Framework_Idea_Config {

		public $args = array();
		public $sections = array();
		public $ReduxFramework;
		static $page_slug = 'rtidea-settings';

		public function __construct() {

			if ( ! class_exists( 'ReduxFramework' ) ) {
				return;
			}

			add_action( 'plugins_loaded', array( $this, 'init_settings' ), 15 );
		}

		public function init_settings() {
			// Set the default arguments
			$this->set_arguments();

			// Set a few help tabs so you can see how it's done
			//			$this->set_helptabs();

			// Create the sections and fields
			$this->set_sections();

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
				'fields' => array()
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
				remove_filter( 'plugin_row_meta', array(
					ReduxFrameworkPlugin::instance(),
					'plugin_metalinks',
				), null, 2 );

				// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
				remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
			}

		}

		public function set_sections() {
			// ACTUAL DECLARATION OF SECTIONS
			$general_fields = array(
				array(
					'id'          => 'rt_idea_menu_label',
					'type'        => 'text',
					'title'       => __( 'Menu Label' ),
					'subtitle'    => __( 'Menu Label Identity for the Plugin.' ),
					'desc'        => __( 'This label will be used for the Menu Item label for Idea' ),
					'default'     => __( 'Idea' ),
					'placeholder' => __( 'rtIdea, Idea, etc.' ),
				),
				array(
					'id'       => 'rt_idea_logo_url',
					'type'     => 'media',
					'url'      => true,
					'title'    => __( 'Logo' ),
					'desc'     => __( 'This logo will be used for all the Menu, Submenu, Post Types Menu Icons in Idea.' ),
					'subtitle' => __( 'Upload any logo using the WordPress native uploader, preferrably with the size of 16x16.' ),
					'default'  => array(
						'url' => RTBIZ_IDEAS_URL . 'app/assets/img/rt-16X16.png',
					),
				),
				array(
					'id'       => 'wpideas_emailenabled',
					'type'     => 'switch',
					'title'    => __( 'Notifications Emails' ),
					'subtitle' => __( 'This feature' ),
					'default'  => false,
					'on'       => __( 'Enable' ),
					'off'      => __( 'Disable' ),
				),
				array(
					'id'       => 'wpideas_adminemails',
					'title'    => __( 'Notification Emails' ),
					'subtitle' => __( 'Email addresses to be notified on events' ),
					'desc'     => __( 'These email addresses will be notified of the events that occurs in Idea systems. This is a global list. All the subscribers on idea will be notified along with this list.' ),
					'type'     => 'multi_text',
					'validate' => 'email',
					'multi'    => true,
				),
				array(
					'id'       => 'rt_idea_notification_events',
					'title'    => __( 'Notification Events' ),
					'subtitle' => __( 'Events to be notified to users' ),
					'desc'     => __( 'These events will be notified to the Notification Emails whenever they occur.' ),
					'type'     => 'checkbox',
					'options' => array(
//						'wpideas_status_changes'      => __( 'Receive status changes, admin responses.' ),
//						'wpideas_comment_posted'       => __( 'Receive comment updates.' ),
						'wpideas_idea_posted'       => __( 'New idea is posted' ),
						'wpideas_comment_posted'       => __( 'New comment Added' ),
						'wpideas_status_change'       => __( 'Idea status changed' ),
					),
				),
				array(
					'id'       => 'wpideas_editorenabled',
					'type'     => 'switch',
					'title'    => __( 'Enable WYSIWYG Editor' ),
					'subtitle' => __( 'This will allow WYSIWYG editor on creating of new Idea!' ),
					'default'  => false,
					'on'       => __( 'Enable' ),
					'off'      => __( 'Disable' ),
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
					'id'=>'idea_signature_text',
					'type' => 'textarea',
					'title' => __('Email Signature'),
					'subtitle' => __('Add here Email Signature'),
					'desc' => esc_attr("You can add email signature here that will be send with every email send with the Idea plugin, Allowed tags are <a> <br> <em> <strong>."),
					'validate' => 'html_custom',
					'default' => esc_attr('<br />Send via rtCamp Idea Plugin<br />'),
					'allowed_html' => array(
						'a' => array(
							'href' => array(),
							'title' => array()
						),
						'br' => array(),
						'em' => array(),
						'strong' => array()
					),
				),
			);
			$this->sections[] = array(
				'icon'        => 'el-icon-cogs',
				'title'       => __( 'General' ),
//				'permissions' => $admin_cap,
				'fields'      => $general_fields,
			);
			return true;
		}
		/*
		public function set_helptabs() {

			// Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
			$this->args['help_tabs'][] = array(
				'id'      => 'redux-help-tab-1',
				'title'   => __( 'Theme Information 1', 'redux-framework-demo' ),
				'content' => __( '<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo' )
			);

			$this->args['help_tabs'][] = array(
				'id'      => 'redux-help-tab-2',
				'title'   => __( 'Theme Information 2', 'redux-framework-demo' ),
				'content' => __( '<p>This is the tab content, HTML is allowed.</p>', 'redux-framework-demo' )
			);

			// Set the help sidebar
			$this->args['help_sidebar'] = __( '<p>This is the sidebar content, HTML is allowed.</p>', 'redux-framework-demo' );

			return true;
		}*/

		/**
		 *
		 * All the possible arguments for Redux.
		 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
		 * */
		public function set_arguments() {

			//$theme = wp_get_theme(); // For use with some settings. Not necessary.
			$this->args = array(
				// TYPICAL -> Change these values as you need/desire
				'opt_name'           => 'redux_idea_settings',
				// This is where your data is stored in the database and also becomes your global variable name.
				'display_name'       => __( 'Settings' ),
				// Name that appears at the top of your panel
				'display_version'    => '1.0',
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
				'customizer'         => true,
				// Enable basic customizer support
				//'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
				//'disable_save_warn' => true,                    // Disable the save warning when a user changes a field
				// OPTIONAL -> Give you extra features
				'page_priority'      => null,
				// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
//				'page_parent'        => 'edit.php?post_type=' . esc_attr( RTBIZ_IDEAS_SLUG ),
				'page_parent'        => 'edit.php?post_type=idea',
				// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
//				'page_permissions'   => $author_cap,
				// Permissions needed to access the options panel.
				//'menu_icon' => '', // Specify a custom URL to an icon
				//'last_tab' => '', // Force your panel to always open to a specific tab (by id)
				//'page_icon' => 'icon-themes', // Icon displayed in the admin panel next to your menu_title
				'page_slug'          => self::$page_slug,
				// Page slug used to denote the panel
				'save_defaults'      => true,
				// On load save the defaults to DB before user clicks save or not
				'default_show'       => true,
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
				)
			);

			// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
			$this->args['share_icons'][] = array(
				'url'   => 'https://github.com/ReduxFramework/ReduxFramework',
				'title' => 'Visit us on GitHub',
				'icon'  => 'el-icon-github',
				//'img'   => '', // You can use icon OR img. IMG needs to be a full URL.
			);
			$this->args['share_icons'][] = array(
				'url'   => 'https://www.facebook.com/pages/Redux-Framework/243141545850368',
				'title' => 'Like us on Facebook',
				'icon'  => 'el-icon-facebook',
			);
			$this->args['share_icons'][] = array(
				'url'   => 'http://twitter.com/reduxframework',
				'title' => 'Follow us on Twitter',
				'icon'  => 'el-icon-twitter',
			);
			$this->args['share_icons'][] = array(
				'url'   => 'http://www.linkedin.com/company/redux-framework',
				'title' => 'Find us on LinkedIn',
				'icon'  => 'el-icon-linkedin',
			);

			// Panel Intro text -> before the form
			//			if ( ! isset( $this->args['global_variable'] ) || false !== $this->args['global_variable'] ) {
			//				if ( ! empty( $this->args['global_variable'] ) ) {
			//					$v = $this->args['global_variable'];
			//				} else {
			//					$v = str_replace( '-', '_', $this->args['opt_name'] );
			//				}
			//				$this->args['intro_text'] = sprintf( __( '<p>Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', 'redux-framework-demo' ), $v );
			//			} else {
			//				$this->args['intro_text'] = __( '<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'redux-framework-demo' );
			//			}

			// Add content after the form.
			$this->args['footer_text'] = __( '<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'redux-framework-demo' );

			return true;
		}

	}

}