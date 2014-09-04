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
 * Description of RTWPIdeasAutoProductSynchronization
 *
 * @author kishore
 */
if( !class_exists( 'RTWPIdeasAutoProductSynchronization' ) ){
	class RTWPIdeasAutoProductSynchronization {
		
		public function __construct() {
			$taxonomy_metadata = new Rt_Wp_Ideas_Taxonomy_Metadata\Taxonomy_Metadata();
			$taxonomy_metadata->activate();
			$this->hooks();
			$this->old_product_synchronization_enabled();
		}
		
		/**
		 * hooks function.
		 * Call all hooks :)
		 *
		 * @access public
		 * @return void
		 */
		public function hooks() {
			if ( get_option( 'wpideas_auto_product_synchronizationenabled' ) == 1 ){
				add_action( 'save_post', array( $this, 'insert_products' ) );
				add_action( 'wp_untrash_post', array( $this, 'insert_products' ) );
				// add_action( 'delete_post', array( $this, 'delete_products' ) );
				// add_action( 'trashed_post', array( $this, 'delete_products' ) );
				// add_action( 'delete_product', array( $this, 'delete_products_meta' ) );
			}
		}
		
		/**
		 * old_product_synchronization_enabled function.
		 *
		 * @access public
		 * @return void
		 */
		public function old_product_synchronization_enabled() {
			if ( get_option( 'wpideas_old_product_synchronizationenabled' ) == 1 ){
				$this->bulk_insert_products();
				$this->delete_products();
			}
		}

		/**
		 * insert_products function.
		 *
		 * @access public
		 * @return void
		 */
		public function insert_products( $post_id ) {
		  global $wpdb;
		  $key = '_product_id';
		  $single = 'true';
		  
			
		  // If this is just a revision, don't.
		  if ( wp_is_post_revision( $post_id ) || empty( $_POST['post_type'] ) ){
			return;
		  }
		  
		  // If this isn't a 'product' post, don't update it.
    	  if ( 'product' != $_POST['post_type'] ){
        	return;
    	  }
		  
		  
		  // Rt_Wp_Ideas_Taxonomy_Metadata\get_term_meta($term_id, $key, $single);
		  $taxonomymeta = $wpdb->get_row( "SELECT * FROM $wpdb->taxonomymeta WHERE meta_key ='_product_id' AND meta_value = $post_id " );
		  //print_r($taxonomymeta); die();
		  
		  // If this isn't a 'product' post, don't update it.
    	  if ( ! empty( $taxonomymeta->taxonomy_id ) && is_numeric( $taxonomymeta->taxonomy_id ) ){
        	return;
    	  }
		  
		  $args = array( 'posts_per_page' => -1, 'post_type' => 'product' );
		  $products_array = get_posts( $args ); // Get Woo Commerce post object
		  $product_names = wp_list_pluck( $products_array, 'post_title' ); // Get Woo Commerce post_title
		  $product_ids = wp_list_pluck( $products_array, 'ID' ); // Get Woo Commerce Post ID
		  
		  $taxonomy = "product";
		  $term = sanitize_title( $_POST['post_title'] );
		  
		  if ( $taxonomy == "product" && ! empty( $post_id ) ){
				$post = get_post( $post_id );
				$slug = $post->post_name;
		      	$term = wp_insert_term(
				  $term, // the term 
				  'product', // the taxonomy
				  array(
				    'slug' => $slug
				  )
				);
				if ( is_array( $term ) ){
					$term_id = $term["term_id"];
					Rt_Wp_Ideas_Taxonomy_Metadata\add_term_meta( $term_id, $key, $post_id, true ); // todo: need to fetch product_id
				}
		  }
		  
		}

		/**
		 * bulk_insert_products function.
		 *
		 * @access public
		 * @return void
		 */
		public function bulk_insert_products() {
			 
		  $args = array( 'posts_per_page' => -1, 'post_type' => 'product' );
		  $products_array = get_posts( $args ); // Get Woo Commerce post object
		  $product_names = wp_list_pluck( $products_array, 'post_title' ); // Get Woo Commerce post_title
		  $product_ids = wp_list_pluck( $products_array, 'ID' ); // Get Woo Commerce Post ID
		
		  $taxonomies = array(
		  	'product' => $product_names,
		    'product_id' => $product_ids
		    
		  );
		  
		  $count = 0;
		  $i = 0;
		  $product_array = array();
		  $product_id_array = array();
		  
		  foreach ( $taxonomies as $taxonomy => $terms ) {
			$count++;
			foreach ( $terms as $term ) {
		 		if ( $count == 1 ){
					$product_array[] = $term;
					
				}
				if ( $count == 2 ){
					$product_id_array[] = $term;
				}
		  	}
			if ( $count == 1 ){
				$i = count($product_array);
			}
			
		  }
		  
		  while( $i > 0 ) {
		  	$i--;
			
		  	$term = sanitize_title( $product_array[$i] );
		  
			if ( ! empty( $product_id_array[$i] )){
				$post = get_post( $product_id_array[$i] );
				$slug = $post->post_name;
		      	$term = wp_insert_term(
				  $term, // the term 
				  'product', // the taxonomy
				  array(
				    'slug' => $slug
				  )
				);
				if (is_array($term)){
					$term_id = $term["term_id"];
					Rt_Wp_Ideas_Taxonomy_Metadata\add_term_meta($term_id, "_product_id", $product_id_array[$i], true); // todo: need to fetch product_id
				}
			}
			
		  }
		  
		}
		
		/**
		 * delete_products function.
		 *
		 * @access public
		 * @return void
		 */
		public function delete_products() {
			$args = array( 'posts_per_page' => -1, 'post_type' => 'product' ); // get all woo commerce product
			$products_array = get_posts( $args );
			$product_names = wp_list_pluck( $products_array, 'post_name' );
			
			$product_taxonomies = get_terms( 'product', 'hide_empty=0' ); // Get all the product list from product taxonomy under Ideas
			$product_taxonomy_names = wp_list_pluck( $product_taxonomies, 'slug' );
			
			$product_taxonomies_to_delete = array_diff($product_taxonomy_names, $product_names); // Do a array diff
			
			foreach ( $product_taxonomies_to_delete as $product_taxonomy_to_delete ) {
				$product_taxonomies_obj = get_term_by('slug', $product_taxonomy_to_delete, 'product');
				wp_delete_term( $product_taxonomies_obj->term_id, 'product' ); // Now Delete those products which are not present in woo-commerce product section.
				Rt_Wp_Ideas_Taxonomy_Metadata\delete_term_meta($product_taxonomies_obj->term_id, '_product_id');
			}
		}
		
		/**
		 * delete_products_meta function.
		 *
		 * @access public
		 * @return void
		 */
		public function delete_products_meta( $term_id ) {
			Rt_Wp_Ideas_Taxonomy_Metadata\delete_term_meta( $term_id, '_product_id' );
		}
		
		
				
	}
}