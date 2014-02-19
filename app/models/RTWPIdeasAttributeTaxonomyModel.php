<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTWikiAttributeTaxonomyModel
 *
 * @author kaklo
 */
if ( !class_exists( 'RtWPIdeasAttributeTaxonomyModel' ) ) {
	class RtWPIdeasAttributeTaxonomyModel extends RTDBModel {
		public function __construct() {
			parent::__construct('wpideas_attribute_taxonomy');
		}

		function attribute_exists( $attribute_name, $post_type = '' ) {
			$attributes = $this->get_all_attributes( $post_type );
			foreach ( $attributes as $attribute ) {
				if ( $attribute_name == $attribute->attribute_name ) {
					return true;
				}
			}
			return false;
		}

		function get_all_attributes( $post_type = '' ) {
                        $args = array();
                        if( !empty ( $post_type ) ){
                            $args['attribute_post_type'] = array(
                                'compare' => '=',
                                'value'   => explode(',', $post_type)
                            );
                        }
			return parent::get( $args );
		}

		function get_attribute( $attribute_id ) {
			$args = array( 'id' => $attribute_id );
			$attribute = parent::get( $args );
			if ( empty( $attribute ) ) {
				return false;
			}
			return $attribute[0];
		}

		function get_attribute_name( $attribute_id ) {
			$attribute = $this->get_attribute( $attribute_id );
			if ( empty( $attribute ) ) {
				return false;
			}
			return $attribute->attribute_name;
		}

                function get_attribute_by_name( $attribute_name, $post_type ) {
                        $args = array();
                        $return = array();
                        if( !empty( $attribute_name ) && !empty ( $post_type ) ){
                            $args['attribute_post_type'] = array(
                                'compare' => '=',
                                'value'   => explode( ',', $post_type )
                            );
                            $args['attribute_name'] = array(
                                'compare' => '=',
                                'value'   => explode( ',', $attribute_name )
                            );
                            $return = parent::get( $args );
                        }
			return $return;
                }
                
		function add_attribute( $data ) {
			return parent::insert( $data );
		}

		function update_attribute( $data, $where ) {
			return parent::update( $data, $where );
		}

		function delete_attribute( $where ) {
			return parent::delete( $where );
		}
	}
}
