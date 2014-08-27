<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTWPIdeasAttributes
 *
 * @author kishore
 */
if( ! class_exists( 'RTWPIdeasAttributes' ) ){
	class RTWPIdeasAttributes {

		var $attributes_page_slug = 'wp-ideas-attributes';

		public function __construct() {
			add_action( 'init', array( $this, 'init_attributes' ) );
		}

		function init_attributes() {
			global $wp_ideas_rt_attributes,$wp_ideas_attributes_model, $wp_ideas_attributes_relationship_model;
			$wp_ideas_rt_attributes = new RT_Attributes( 'wp-ideas' );

			$admin_cap = 'administrator';
			$post_type = 'idea';
			
			$terms_caps = array();

			$wp_ideas_rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php?post_type='.$post_type, $post_type, $admin_cap, $terms_caps, $render_type = false, $storage_type = true, $orderby = true );
			$wp_ideas_attributes_model = new RT_Attributes_Model();
			$wp_ideas_attributes_relationship_model = new RT_Attributes_Relationship_Model();
			
			register_taxonomy(
				'product',
				$post_type,
				array(
					'label' => __( 'Product' ),
					'rewrite' => array( 'slug' => 'product' ),
					'hierarchical' => true,
				)
			);
			
			$auto_product_synchronization = new RTWPIdeasAutoProductSynchronization();
		}
		
	}
}
