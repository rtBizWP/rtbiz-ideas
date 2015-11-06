<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 5/11/15
 * Time: 10:52 AM
 */
if ( !class_exists('rtBiz_Idea_Settings_General ')) :
class rtBiz_Idea_Settings_General extends rtBiz_Settings_Page{

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->id    = 'rtbiz_idea_general';
		$this->label = __( 'General', 'rtbiz_idea' );
		add_filter( 'rtbiz_settings_tabs_array', array( $this, 'add_settings_page' ) );
		add_action( 'rtbiz_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'rtbiz_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = apply_filters( 'rtbiz_idea_general_settings', array(

			array( 'title' => __( 'General Options', RTBIZ_IDEAS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'general_options' ),

			array(
				'title'    => __( 'Enable WYSIWYG Editor', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => __( 'This will allow WYSIWYG editor on creating of new Idea!', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'       => 'wpideas_editorenabled',
				'default'  => 'off',
				'type'     => 'radio',
				'options'  => array(
					'on'       => __( 'On', 'woocommerce' ),
					'off' => __( 'Off', 'woocommerce' ),
				),
				'desc_tip' =>  true,
				'autoload' => false
			),

			array(
				'title'         => __( 'Integration', RTBIZ_IDEAS_TEXT_DOMAIN),
				'desc'          => __( 'WooCommerce', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'            => 'rt_idea_woo_integration',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'desc_tip'      =>  __( 'Ideas will not be shown on frontend of Woo product', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'autoload'      => true
			),

			array(
				'desc'          => __( 'Easy Digital Downloads (EDD)', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'            => 'rt_idea_edd_integration',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'desc_tip'      =>  __( 'Ideas will not be shown on frontend of EDD download page', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'autoload'      => true
			),

			array( 'type' => 'sectionend', 'id' => 'general_options'),

		) );

		return apply_filters( 'rtbiz_get_settings_' . $this->id, $settings );
	}


}
endif;

return new rtBiz_Idea_Settings_General();
