<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Module' ) ) {
	/**
	 * Class Rtbiz_Ideas_Module
	 * Register rtbiz-Ideas CPT [ idea ] & statuses
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Module {

		/**
		 * @var string Stores Post Type
		 *
		 * @since 0.1
		 */
		static $post_type = 'idea';
		/**
		 * @var string used in mail subject title - to detect whether it's a Helpdesk mail or not. So no translation
		 *
		 * @since 0.1
		 */
		static $name = 'Idea';
		/**
		 * @var array Labels for rtbiz-Ideas CPT [ Idea ]
		 *
		 * @since 0.1
		 */
		var $labels = array();
		/**
		 * @var array statuses for rtbiz-Ideas CPT [ Idea ]
		 *
		 * @since 0.1
		 */
		var $statuses = array();

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {
			$this->get_custom_labels();
			$this->get_custom_statuses();
			Rtbiz_Ideas::$loader->add_action( 'init', $this, 'init_ideas' );
			Rtbiz_Ideas::$loader->add_action( 'wp_before_admin_bar_render', $this, 'append_post_status_list', 11 );

			Rtbiz_Ideas::$loader->add_filter( 'manage_' . self::$post_type . '_posts_columns', $this, 'idea_custom_columns_header' );
			Rtbiz_Ideas::$loader->add_action( 'manage_' . self::$post_type . '_posts_custom_column', $this, 'ideas_custom_column_body', 10, 2 );

			Rtbiz_Ideas::$loader->add_action( 'save_post', $this, 'save_idea_post', 13, 2 );

		}

		/**
		 * get rtbiz-Ideas CPT [ Idea ] labels
		 *
		 * @since 0.1
		 *
		 * @return array
		 */
		public function get_custom_labels() {
			$this->labels = array(
				'name'               => __( 'Ideas', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'singular_name'      => __( 'Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'menu_name'          => __( 'Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'all_items'          => __( 'Ideas', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'add_new'            => __( 'Add New Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'add_new_item'       => __( 'Add New Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'new_item'           => __( 'Add New Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'edit_item'          => __( 'Edit Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'view_item'          => __( 'View Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'search_items'       => __( 'Search Ideas', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'parent_item_colon'  => __( 'Parent Ideas', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'not_found'          => __( 'No Ideas found', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'not_found_in_trash' => __( 'No Ideas found in Trash', RTBIZ_IDEAS_TEXT_DOMAIN ),
			);

			return $this->labels;
		}


		/**
		 * get rtbiz-Ideas CPT [ Idea ] statuses
		 *
		 * @since 0.1
		 *
		 * @return array
		 */
		public function get_custom_statuses() {
			$this->statuses = array(
				array(
					'slug'        => 'idea-new',
					'name'        => __( 'New', RTBIZ_IDEAS_TEXT_DOMAIN ),
					'description' => __( 'New Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				),
				array(
					'slug'        => 'idea-accepted',
					'name'        => __( 'Accepted', RTBIZ_IDEAS_TEXT_DOMAIN ),
					'description' => __( 'Idea Accepted', RTBIZ_IDEAS_TEXT_DOMAIN ),
				),
				array(
					'slug'        => 'idea-declined',
					'name'        => __( 'Declined', RTBIZ_IDEAS_TEXT_DOMAIN ),
					'description' => __( 'Idea Declined', RTBIZ_IDEAS_TEXT_DOMAIN ),
				),
				array(
					'slug'        => 'idea-completed',
					'name'        => __( 'Completed', RTBIZ_IDEAS_TEXT_DOMAIN ),
					'description' => __( 'Idea Completed', RTBIZ_IDEAS_TEXT_DOMAIN ),
				),
			);

			return $this->statuses;
		}

		/**
		 * register rtbiz-Ideas CPT [ Idea ]
		 *
		 * @since 0.1
		 */
		public function init_ideas() {
			$menu_position = 35;
			$this->register_custom_post( $menu_position );

			foreach ( $this->statuses as $status ) {
				$this->register_custom_statuses( $status );
			}
		}

		/**
		 * Register CPT ( idea )
		 *
		 * @since 0.1
		 *
		 * @param $menu_position
		 *
		 * @return object|\WP_Error
		 */
		public function register_custom_post( $menu_position ) {

			$logo = apply_filters( 'rthd_helpdesk_logo', RTBIZ_IDEAS_URL . 'admin/img/ideas-16X16.png' );

			$args = array(
				'labels'               => $this->labels,
				'public'               => true,
				'publicly_queryable'   => true,
				'has_archive'          => true,
				'query_var'            => true,
				'rewrite'              => array(
					'slug'       => self::$post_type,
					'with_front' => false,
				),
				'show_ui'              => true, // Show the UI in admin panel
				'show_in_menu'         => true,
				'menu_icon'            => $logo,
				'menu_position'        => $menu_position,
				'supports'             => array(
					'title',
					'editor',
					'author',
					'thumbnail',
					'revisions',
					'excerpt',
					'comments',
					'custom-fields'
				),
				'capability_type'      => self::$post_type,
				'register_meta_box_cb' => array( $this, 'ideas_add_voters_metabox' ),
			);

			return register_post_type( self::$post_type, $args );
		}

		/**
		 * Add voters metabox in idea edit page
		 */
		public function ideas_add_voters_metabox() {
			add_meta_box( 'Voters', __( 'Voters' ), array(
				$this,
				'get_voters_of_idea'
			), self::$post_type, 'side', 'default' );
		}

		/**
		 * Get voters of idea
		 *
		 * @param $post
		 */
		public function get_voters_of_idea( $post ) {
			global $rtbiz_ideas_votes_model;
			$row = $rtbiz_ideas_votes_model->get_voters_of_idea( $post->ID );
			if ( ! empty( $row ) ) {
				for ( $i = 0; $i < sizeof( $row ); $i ++ ) {
					$voter_info = get_userdata( $row[ $i ]->user_id );
					echo '<a href="' . get_edit_user_link( $row[ $i ]->user_id ) . '">' . $voter_info->user_login . '</a><br/>';
				}
			} else {
				echo __( 'No votes yet.', 'wp-ideas' );
			}
		}


		/**
		 * Register Custom statuses for CPT ( idea )
		 *
		 * @since 0.1
		 *
		 * @param $status
		 *
		 * @return array|object|string
		 */
		public function register_custom_statuses( $status ) {

			return register_post_status(
				$status['slug'], array(
					'label'                     => $status['name'],
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( "{$status['name']} <span class='count'>(%s)</span>", "{$status['name']} <span class='count'>(%s)</span>" ),
				)
			);

		}

		/**
		 * Fill custom post status in select box and change the value accordingly
		 */
		public function append_post_status_list() {
			global $pagenow;
			if ( get_post_type() == self::$post_type && ( $pagenow == 'edit.php' || $pagenow == 'post-new.php' || ( isset( $_GET['action'] ) && $_GET['action'] ) == 'edit' ) ) {
				global $post;
				if ( ! isset( $post ) ) {
					return;
				}
				ob_start(); ?>

				<script>
					jQuery(document).ready(function ($) { <?php
                foreach ( $this->statuses as $status ) {
                    $completeCompleted = ( $post -> post_status == $status['slug'] ) ? "selected='selected'" : '' ; ?>
						$("select#post_status").append("<option value='<?php echo $status['slug'] ?>' <?php echo $completeCompleted; ?>><?php echo $status['name'] ?></option>");
						$(".inline-edit-status select").append("<option value='<?php echo $status['slug'] ?>' <?php echo $completeCompleted; ?>><?php echo $status['name'] ?></option>");
						<?php
										   if (! empty( $completeCompleted ) ) { ?>
						$("#post-status-display").html("<?php echo $status['name'] ?>");
						<?php
											}
										} ?>
						$("#publishing-action").html("<span class='spinner'></span><input name='original_publish' type='hidden' id='original_publish' value='Update' /><input type='submit' id='save-publish' class='button button-primary button-large' value='Update' />");
						$(".save-post-status").click(function () {
							$("#publish").hide();
							$("#publishing-action").html("<span class='spinner'></span><input name='original_publish' type='hidden' id='original_publish' value='Update' /><input type='submit' id='save-publish' class='button button-primary button-large' value='Update' />");
						});
						$("#save-publish").click(function () {
							$("#publish").click();
						});
					});
				</script> <?php

				echo ob_get_clean();
			}
		}

		/**
		 * Votes columns in idea list admin
		 *
		 * @param  array $defaults
		 *
		 * @return type
		 */
		public function idea_custom_columns_header( $defaults ) {
			$defaults['wpideas_votes'] = _x( 'Votes', RTBIZ_IDEAS_TEXT_DOMAIN );

			//$defaults[ 'wpideas_posts' ] = _x( 'Idea for post', RTBIZ_IDEAS_TEXT_DOMAIN );
			return $defaults;
		}

		/**
		 * Votes column value with vote count
		 *
		 * @param type $column_name
		 * @param type $idea_id
		 */
		public function ideas_custom_column_body( $column_name, $idea_id ) {
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

		public function save_idea_post( $post_id ) {

			if ( wp_is_post_revision( $post_id ) || self::$post_type != get_post_type( $post_id ) ) {
				return;
			}
			$has_voted = rtbiz_ideas_check_user_voted( $post_id );
			if ( is_null( $has_voted ) || ! $has_voted ) {
				rtbiz_ideas_add_vote( $post_id );
				update_post_meta( $post_id, '_rt_wpideas_meta_votes', 1 );
			}
		}

	}
}
