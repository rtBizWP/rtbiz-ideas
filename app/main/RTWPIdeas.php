<?php
/**
 * Created by PhpStorm.
 * User: faishal
 * Date: 07/02/14
 * Time: 2:20 PM
 */

class RTWPIdeas {
	private $post_type = 'ideas';
	function __construct(){
		$this->init();
	}

	/**
	 * Init all required function like register post-type, etc
	 */
	function init(){
		add_action('init', array( $this, 'register_post_type') );
	}

	/**
	 * This will return wordpress-idea post-type
	 * @return string ideas post type
	 */
	function get_post_type(){
		return $this->post_type;
	}

	/**
	 * This function will register ideas post type in to WordPress
	 */
	function register_post_type(){
		register_post_type( $this->post_type,
			array(

			)
		);
	}
}