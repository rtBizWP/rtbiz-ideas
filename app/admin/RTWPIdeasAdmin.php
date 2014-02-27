<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTWPIdeasAdmin
 *
 * @author kaklo
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
			$this -> init_attributes();
			//add_action( 'admin_menu', array( $this, 'register_pages' ) );
			//$this -> register_taxonomies();
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
				'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'comments', 'page-attributes' ),
			);

			register_post_type( RT_WPIDEAS_SLUG, $args );
		}

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

		function wpideas_append_post_status_list() {
			global $pagenow;
			if ( get_post_type() == RT_WPIDEAS_SLUG && ( $pagenow == 'edit.php' || $pagenow == 'post-new.php' || ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] ) == 'edit' ) ) {
				global $post;
				if ( ! isset( $post ) ) {
					return;
				}
				$complete = '';
				$label = get_post_status();
				if ( $post -> post_status == 'new' ) {
					$complete = ' selected=\"selected\"';
					$label = 'New';
				}
				if ( $post -> post_status == 'accepted' ) {
					$complete = ' selected=\"selected\"';
					$label = 'Accepted';
				}
				if ( $post -> post_status == 'declined' ) {
					$complete = ' selected=\"selected\"';
					$label = 'Declined';
				}
				if ( $post -> post_status == 'completed' ) {
					$complete = ' selected=\"selected\"';
					$label = 'Completed';
				}
				echo '
				<script>
				jQuery(document).ready(function($){
					$("select#post_status").append("<option value=\"new\" ' . $complete . '>New</option>");
					$("select#post_status").append("<option value=\"accepted\" ' . $complete . '>Accepted</option>");
					$("select#post_status").append("<option value=\"declined\" ' . $complete . '>Declined</option>");
					$("select#post_status").append("<option value=\"completed\" ' . $complete . '>Completed</option>");
					$(".inline-edit-status select").append("<option value=\"new\" ' . $complete . '>New</option>");
					$(".inline-edit-status select").append("<option value=\"accepted\" ' . $complete . '>Accepted</option>");
					$(".inline-edit-status select").append("<option value=\"declined\" ' . $complete . '>Declined</option>");
					$(".inline-edit-status select").append("<option value=\"completed\" ' . $complete . '>Completed</option>");
					$("#post-status-display").html("' . $label . '");
					$("#publishing-action input").val("Update");
					$(".save-post-status").click(function(){
						$("#publish").hide();
						//$("#publish").val("Update");
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

		function wpideas_ideas_table_head( $defaults ) {
			$defaults[ 'wpideas_votes' ] = _x( 'Votes', 'wp-ideas' );
			return $defaults;
		}

		function wpideas_ideas_table_columns( $column_name, $idea_id ) {
			if ( $column_name == 'wpideas_votes' ) {
				$votes = get_post_meta( $idea_id, '_rt_wpideas_meta_votes', true );
				echo $votes;
			}
		}

	}

}
