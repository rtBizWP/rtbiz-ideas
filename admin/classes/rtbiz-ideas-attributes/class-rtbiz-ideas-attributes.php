<?php
/**
 * Don't load this file directly!
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rtbiz_Ideas_Attributes' ) ) {
	/**
	 * Class Rtbiz_Ideas_Votes
	 *
	 *
	 * @since 1.1
	 *
	 * @author dipesh
	 */
	class Rtbiz_Ideas_Attributes {


		/**
		 * @var string attributes Page slug
		 *
		 * @since 0.1
		 */
		var $attributes_page_slug = 'rtbiz-ideas-attributes';

		/**
		 * initiate class local Variables
		 *
		 * @since 0.1
		 */
		public function __construct() {

			global $rtbiz_ideas_rt_attributes, $rtbiz_ideas_attributes_model, $rtbiz_ideas_attributes_relationship_model;
			$rtbiz_ideas_rt_attributes                 = new RT_Attributes( RTBIZ_IDEAS_TEXT_DOMAIN );
			$rtbiz_ideas_attributes_model              = new RT_Attributes_Model();
			$rtbiz_ideas_attributes_relationship_model = new RT_Attributes_Relationship_Model();

			Rtbiz_Ideas::$loader->add_action( 'init', $this, 'init_attributes' );
		}

		/**
		 * Add attributes page for rtbiz-HelpDesk
		 *
		 * @since 0.1
		 */
		public function init_attributes() {

			global $rtbiz_ideas_rt_attributes;

			$admin_cap  = rtbiz_get_access_role_cap( RTBIZ_IDEAS_TEXT_DOMAIN, 'admin' );
			$editor_cap = rtbiz_get_access_role_cap( RTBIZ_IDEAS_TEXT_DOMAIN, 'editor' );

			$terms_caps = array(
				'manage_terms' => $editor_cap,
				'edit_terms'   => $editor_cap,
				'delete_terms' => $editor_cap,
				'assign_terms' => $editor_cap,
			);

			$rtbiz_ideas_rt_attributes->add_attributes_page( $this->attributes_page_slug, 'edit.php?post_type=' . Rtbiz_Ideas_Module::$post_type, Rtbiz_Ideas_Module::$post_type, $admin_cap, $terms_caps );
		}

	}
}
