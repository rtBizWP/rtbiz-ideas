<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 07/02/14
 * Time: 2:20 PM
 */

class RTWPIdeas {
	private $post_type = 'idea';

	function __construct(){
		$this->init();
	}

	/**
	 * Init all required function like register post-type, etc
	 */
	function init(){
		add_action('init', array( $this, 'register_post_type') ,0 );
	}

	/**
	 * This will return wordpress-idea post-type
	 * @return string ideas post type
	 */
	function get_post_type ( ) {
		return $this->post_type;
	}

	/**
	 * This function will register ideas post type in to WordPress
	 */
	function register_post_type(){
		$labels = array(
			'name'               => 'Ideas',
			'singular_name'      => 'Idea',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Idea',
			'edit_item'          => 'Edit Idea',
			'new_item'           => 'New Idea',
			'all_items'          => 'All Ideas',
			'view_item'          => 'View Ideas',
			'search_items'       => 'Search Ideas',
			'not_found'          => 'No Idea found',
			'not_found_in_trash' => 'No Idea found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Ideas'
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => $this->post_type, 'with_front' => true),
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'author', 'excerpt', 'comments' )
		);

		register_post_type( $this->post_type, $args );
	}

}