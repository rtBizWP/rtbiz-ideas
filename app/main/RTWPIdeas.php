<?php

/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 07/02/14
 * Time: 2:20 PM
 */
class RTWPIdeas {

	private $post_type = 'idea';

	/**
	 * constructor 
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_wpidea_post_type' ) );
		$this->init_attributes();
		add_action( 'admin_menu', array( $this, 'register_pages' ) );
		$this->register_taxonomies();
	}

	/**
	 * init a globle variable 
	 */
	function init_attributes() {
		global $rtWPIdeasAttributesModel, $rtWPIdeasAttributes, $rtWpideasVotes;
		$rtWPIdeasAttributesModel = new RtWPIdeasAttributeTaxonomyModel();
		$rtWPIdeasAttributes = new RtWPIdeasAttributes();
		$rtWpideasVotes = new RTWPIdeasVotesModel();
	}

	/**
	 * add attributes page link in menu bar
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
	 */
	function register_taxonomies() {
		global $rtWPIdeasAttributesModel, $rtWPIdeasAttributes;
		$attributes = $rtWPIdeasAttributesModel->get_all_attributes( $this->post_type );
		if ( is_array( $attributes ) ) {
			foreach ( $attributes as $attr ) {
				if ( is_object( $attr ) ) {
					$rtWPIdeasAttributes->register_taxonomy( $this->post_type, $attr->id );
				} else {
					$rtWPIdeasAttributes->register_taxonomy( $this->post_type, 0 );
				}
			}
		}
	}

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
			'rewrite' => array( 'slug' => $this->post_type, 'with_front' => true ),
			'has_archive' => true,
			'hierarchical' => false,
			'menu_position' => null,
			'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', 'excerpt', 'comments', 'page-attributes' )
		);

		register_post_type( $this->post_type, $args );
	}

}
