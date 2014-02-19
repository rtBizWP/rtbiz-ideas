<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function rtwpideas_sanitize_taxonomy_name( $taxonomy ) {
	$taxonomy = strtolower( stripslashes( strip_tags( $taxonomy ) ) );
	$taxonomy = preg_replace( '/&.+?;/', '', $taxonomy ); // Kill entities
	$taxonomy = str_replace( array( '.', '\'', '"' ), '', $taxonomy ); // Kill quotes and full stops.
	$taxonomy = str_replace( array( ' ', '_' ), '-', $taxonomy ); // Replace spaces and underscores.

	return $taxonomy;
}

function rtwpideas_attribute_taxonomy_name( $name ) {
	return  rtwpideas_sanitize_taxonomy_name( $name );
}

function rtwpideas_get_supported_attribute() {
    $attributes = array();
    $rtwpideas_settings = '';
    $rtwpideas_custom = '';
    if( is_multisite() ){
        $rtwpideas_settings = get_site_option( 'rtwpideas_settings', array() );
        $rtwpideas_custom = get_site_option( 'rtwpideas_custom', array() );
    }
    else {
        $rtwpideas_settings = get_option( 'rtwpideas_settings', array() );
        $rtwpideas_custom = get_option( 'rtwpideas_custom', array() );
    }
    if( isset( $rtwpideas_custom[0]['slug'] ) )
        $attributes[] = $rtwpideas_custom[0]['slug'];
    if( isset( $rtwpideas_settings['attribute'] ) )
        $attributes = array_merge ( $attributes, $rtwpideas_settings['attribute'] );
    return $attributes;
}

