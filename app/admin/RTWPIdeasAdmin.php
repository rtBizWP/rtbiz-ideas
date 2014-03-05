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
			add_action( 'admin_menu', array( $this, 'wpideas_settings_menu' ) );
			add_action( 'admin_init', array( $this, 'register_ideas_settings' ) );
			if ( get_option( 'wpideas_emailenabled' ) == 'true' && get_option( 'wpideas_status_changes' ) == '1' ) {
				add_action( 'transition_post_status', array( $this, 'wpideas_idea_status_changed' ), 10, 3 );
			}
			if ( get_option( 'wpideas_emailenabled' ) == 'true' && get_option( 'wpideas_comment_posted' ) == '1' ) {
				add_action( 'wp_insert_comment', array( $this, 'wpideas_idea_comment_posted' ), 99, 2 );
			}
			$this -> init_attributes();
		}

		/**
		 * Init global variables
		 */
		function init_attributes() {
			
		}

		/**
		 * add attributes page link in menu bar
		 * 
		 * @global type $rtWPIdeasAttributes
		 */
		function register_pages() {
			global $rtWPIdeasAttributes;
			$attributes = rtwpideas_get_supported_attribute();
			if ( is_array( $attributes ) && ! empty( $attributes ) ) {
				foreach ( $attributes as $attribute ) {
					if ( $attribute !== 'post' ) {
						add_submenu_page( 'edit.php?post_type=' . $attribute, __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute . '-attributes', array( $rtWPIdeasAttributes, 'attributes_page' ) );
					} else {
						add_submenu_page( 'edit.php', __( 'Attributes' ), __( 'Attributes' ), 'administrator', $attribute . '-attributes', array( $rtWPIdeasAttributes, 'attributes_page' ) );
					}
				}
			}
		}

		/**
		 * create a taxonomies
		 * 
		 * @global type $rtWPIdeasAttributesModel
		 * @global type $rtWPIdeasAttributes
		 */
		function register_taxonomies() {
			global $rtWPIdeasAttributesModel, $rtWPIdeasAttributes;
			$attributes = $rtWPIdeasAttributesModel -> get_all_attributes( $this -> post_type );
			if ( is_array( $attributes ) ) {
				foreach ( $attributes as $attr ) {
					if ( is_object( $attr ) ) {
						$rtWPIdeasAttributes -> register_taxonomy( $this -> post_type, $attr -> id );
					} else {
						$rtWPIdeasAttributes -> register_taxonomy( $this -> post_type, 0 );
					}
				}
			}
		}

		/**
		 * Register custom post type
		 */
		function register_wpidea_post_type() {
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
				'menu_name' => __( 'Ideas', 'rtCamp' ),
				'menu_icon' => RTWPIDEAS_URL . '/app/assets/img/16x16-green.png',
			);

			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array(
					'slug' => RT_WPIDEAS_SLUG,
					'with_front' => true,
				),
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'comments', 'page-attributes', 'custom-fields' ),
			);

			register_post_type( RT_WPIDEAS_SLUG, $args );
		}

		/**
		 * register custom post status
		 */
		function wpideas_custom_post_status() {
			register_post_status( 'new', array(
				'label' => _x( 'New', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'New <span class="count">(%s)</span>', 'New <span class="count">(%s)</span>' ),
			) );
			register_post_status( 'accepted', array(
				'label' => _x( 'Accepted', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'Accepted <span class="count">(%s)</span>', 'Accepted <span class="count">(%s)</span>' ),
			) );
			register_post_status( 'declined', array(
				'label' => _x( 'Declined', 'post' ),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop( 'Declined <span class="count">(%s)</span>', 'Declined <span class="count">(%s)</span>' ),
			) );
			register_post_status( 'completed', array(
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
			if ( get_post_type() == RT_WPIDEAS_SLUG && ( $pagenow == 'edit.php' || $pagenow == 'post-new.php' || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] ) == 'edit' ) ) {
				global $post;
				if ( ! isset( $post ) ) {
					return;
				}
				$completeNew = '';
				$completeAccepted = '';
				$completeDeclined = '';
				$completeCompleted = '';
				$label = get_post_status();
				if ( $post -> post_status == 'new' ) {
					$completeNew = ' selected=\"selected\"';
					$label = 'New';
				}
				if ( $post -> post_status == 'accepted' ) {
					$completeAccepted = ' selected=\"selected\"';
					$label = 'Accepted';
				}
				if ( $post -> post_status == 'declined' ) {
					$completeDeclined = ' selected=\"selected\"';
					$label = 'Declined';
				}
				if ( $post -> post_status == 'completed' ) {
					$completeCompleted = ' selected=\"selected\"';
					$label = 'Completed';
				}
				echo '
				<script>
				jQuery(document).ready(function($){
					$("select#post_status").append("<option value=\"new\" ' . $completeNew . '>New</option>");
					$("select#post_status").append("<option value=\"accepted\" ' . $completeAccepted . '>Accepted</option>");
					$("select#post_status").append("<option value=\"declined\" ' . $completeDeclined . '>Declined</option>");
					$("select#post_status").append("<option value=\"completed\" ' . $completeCompleted . '>Completed</option>");
					$(".inline-edit-status select").append("<option value=\"new\" ' . $completeNew . '>New</option>");
					$(".inline-edit-status select").append("<option value=\"accepted\" ' . $completeAccepted . '>Accepted</option>");
					$(".inline-edit-status select").append("<option value=\"declined\" ' . $completeDeclined . '>Declined</option>");
					$(".inline-edit-status select").append("<option value=\"completed\" ' . $completeCompleted . '>Completed</option>");
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
		}

		/**
		 * Send email notification when idea status changes if email notification is set to true
		 * 
		 * @param type $new_status
		 * @param type $old_status
		 * @param type $post
		 */
		function wpideas_idea_status_changed( $new_status, $old_status, $post ) {
			if ( $new_status != $old_status ) {
				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';
				//$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
				//$headers[] = 'Cc: iluvwp@wordpress.org';

				$subject = '[WP Ideas] Idea Status Change';

				$author = $post -> post_author;
				$title = $post -> post_title;

				$recipients = array();
				array_push( $recipients, get_the_author_meta( 'user_email', $author ) );
				$temp = explode( ',', trim( get_option( 'wpideas_adminemails' ) ) );
				for ( $i = 0; $i < count( $temp ); $i ++  ) {
					array_push( $recipients, $temp[ $i ] );
				}
				$message .= '<h2>Idea status changed to '.$new_status.' for [ ' . $title . ' ]</h2>';
				$message .= '<h3>[' . $new_status . '] ' . $title . '</h3>';
				$message .= '<label>Author: <a href="' . get_author_posts_url( $author ) . '">' . get_author_name( $author ) . '</a></label><br/>';
				$message .= '<label>Votes: ' . get_post_meta( $post -> ID, '_rt_wpideas_meta_votes', true ) . '</label>';

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
			if ( $comment_object -> comment_approved > 0 ) {
				$headers[] = 'From: WP Ideas <wpideas@rtcamp.net>';
				//$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
				//$headers[] = 'Cc: iluvwp@wordpress.org';

				$comment_content = $comment_object -> comment_content;
				$comment_author = $comment_object -> comment_author;
				$idea_id = $comment_object -> comment_post_ID;
				$idea = get_post( $idea_id );

				$subject = '[WP Ideas] Comment On ' . $idea -> post_title;

				$author = $idea -> post_author;
				$recipients = array();
				array_push( $recipients, get_the_author_meta( 'user_email', $author ) );
				$temp = explode( ',', trim( get_option( 'wpideas_adminemails' ) ) );
				for ( $i = 0; $i < count( $temp ); $i ++  ) {
					array_push( $recipients, $temp[ $i ] );
				}

				$message .= '<h2> New Comment on ' . $idea -> post_title . '</h2>';
				$message .= '<label>Commentator: '.$comment_author.'</label><br/>';
				$message .= '<label>Comment: '.$comment_content.'</label>';

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
		 * register the options settings
		 */
		function register_ideas_settings() {
			register_setting( 'ideas-settings-group', 'wpideas_emailenabled', '' );
			register_setting( 'ideas-settings-group', 'wpideas_adminemails' );
			register_setting( 'ideas-settings-group', 'wpideas_status_changes', '' );
			register_setting( 'ideas-settings-group', 'wpideas_comment_posted', '' );
		}

		/**
		 * add setings sub menu
		 */
		function wpideas_settings_menu() {
			add_submenu_page( 'edit.php?post_type=idea', 'Ideas Settings', 'Settings', 'edit_posts', basename( __FILE__ ), array( $this, 'wpideas_settings' ) );
		}

		/**
		 * settings callback function
		 */
		function wpideas_settings() {
			?><h2>Ideas Settings</h2>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'ideas-settings-group' );
				do_settings_sections( 'ideas-settings-group' );
				?>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">Email Notifications</th>
							<td>
								<fieldset><legend class="screen-reader-text"><span>Email Notifications</span></legend>
									<?php
									$wpideas_emailenabled = get_option( 'wpideas_emailenabled' );
									if ( empty( $wpideas_emailenabled ) ) {
										$wpideas_emailenabled = 'false';
									}
									?>
									<label><input type="radio" id="wpideas_emailenabled1" name="wpideas_emailenabled" value="true" <?php
										if ( $wpideas_emailenabled == 'true' ) {
											echo 'checked="checked"';
										}
										?>> <span>Enable</span></label><br>
									<label><input type="radio" id="wpideas_emailenabled2" name="wpideas_emailenabled" value="false" <?php
										if ( $wpideas_emailenabled == 'false' ) {
											echo 'checked="checked"';
										}
										?>> <span>Disable</span></label><br>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="adminemails">Email Addresses</label></th>
							<td>
								<input name="wpideas_adminemails" type="text" id="wpideas_adminemails" value="<?php echo get_option( 'wpideas_adminemails' ); ?>" class="regular-text" />
								<p class="description">Admin &AMP; other emails separated by comma (,)</p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">Notifications</th>
							<td> <fieldset><legend class="screen-reader-text"><span>Notifications</span></legend><label>
										<input name="wpideas_status_changes" type="checkbox" id="wpideas_status_changes" value="1" <?php checked( '1', get_option( 'wpideas_status_changes' ) ); ?> >
										Status Changes
										<input name="wpideas_comment_posted" type="checkbox" id="wpideas_comment_posted" value="1" <?php checked( '1', get_option( 'wpideas_comment_posted' ) ); ?> >
										Comment Posted</label>
								</fieldset></td>
						</tr>
					</tbody></table>
				<?php submit_button(); ?>
			</form>
			<?php
		}

	}

}
