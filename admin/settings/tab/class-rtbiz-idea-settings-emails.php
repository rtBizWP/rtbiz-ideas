<?php

/**
 * Created by PhpStorm.
 * User: spock
 * Date: 5/11/15
 * Time: 2:14 PM
 */

if ( !class_exists('rtBiz_Idea_Settings_Emails ')) :
class rtBiz_Idea_Settings_Emails extends rtBiz_Settings_Page{

	/**
	 * rtBiz_Idea_Settings_Emails constructor.
	 */
	public function __construct() {
		$this->id    = 'rtbiz_idea_email';
		$this->label = __( 'Emails', 'rtbiz_idea' );
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

		$settings = apply_filters( 'rtbiz_idea_email_settings', array(

			array( 'title' => __( 'Email Options', RTBIZ_IDEAS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'email_options' ),

			array(
				'title'    => __( 'Notifications Emails', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => __( 'If you turn on this feature it will send notification emails on selected notification events below.', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'       => 'wpideas_emailenabled',
				'default'  => 'off',
				'type'     => 'radio',
				'options'  => array(
					'on'       => __( 'On', RTBIZ_IDEAS_TEXT_DOMAIN ),
					'off' => __( 'Off', RTBIZ_IDEAS_TEXT_DOMAIN ),
				),
				'desc_tip' =>  true,
				'autoload' => true
			),
			array( 'type' => 'sectionend', 'id' => 'email_options'),

			array( 'title' => __( 'Notification Options', RTBIZ_IDEAS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'notification_options' ),

			array(
				'title'    => __( 'Emails to notify', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => '',
				'id'       => 'wpideas_adminemails',
				'default'  => '',
				'type'     => 'email',
				'custom_attributes' => array(
					'multiple' => 'multiple'
				),
				'css'     => 'width:400px;',
				'autoload' => true
			),

			array(
				'title'         => __( 'Notification events', RTBIZ_IDEAS_TEXT_DOMAIN),
				'desc'          => __( 'New idea posted', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'            => 'rt_idea_notification_events_idea_posted',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'start',
				'desc_tip'      =>  __( 'These events will be notified to the Notification Emails whenever they occur.', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'autoload'      => true
			),

			array(
				'desc'          => __( 'New comment Added', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'            => 'rt_idea_notification_events_comment_posted',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => '',
				'desc_tip'      =>  __( 'Notify via email whenever new comment is posted on idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'autoload'      => true
			),
			array(
				'desc'          => __( 'Idea status changed', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'            => 'rt_idea_notification_events_status_change',
				'default'       => 'no',
				'type'          => 'checkbox',
				'checkboxgroup' => 'end',
				'desc_tip'      =>  __( 'Notify via email whenever status of idea changed.', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'autoload'      => true
			),

			array( 'type' => 'sectionend', 'id' => 'Notification_options'),

			array( 'title' => __( 'Email header', RTBIZ_IDEAS_TEXT_DOMAIN ), 'type' => 'title', 'desc' => '', 'id' => 'email_header_options' ),

			array(
				'title'    => __( 'Title for new idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => __( 'You can use {idea_id} and {idea_title}', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'       => 'idea_new_idea_email_title',
				'type'     => 'text',
				'css'      => 'min-width:400px;',
				'default'  => '[New Idea Created] idea #{idea_id} : {idea_title}',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Title when idea status changed', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => __( 'You can use {idea_id} and {idea_title}', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'       => 'idea_status_change_email_title',
				'type'     => 'text',
				'css'      => 'min-width:400px;',
				'default'  => '[Idea Status Changed] idea #{idea_id} : {idea_title}',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Title when comment added', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => __( 'You can use {idea_id} and {idea_title}', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'       => 'idea_comment_add_email_title',
				'type'     => 'text',
				'css'      => 'min-width:400px;',
				'default'  => '[Idea Comment Added] idea #{idea_id} : {idea_title}',
				'desc_tip' => true,
			),

			array(
				'title'    => __( 'Enable email signature', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => __( 'Enable will append signature to each mail', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'id'       => 'idea_signature_enable',
				'default'  => 'off',
				'type'     => 'radio',
				'options'  => array(
					'on'       => __( 'Enable', RTBIZ_IDEAS_TEXT_DOMAIN ),
					'off' => __( 'Disable', RTBIZ_IDEAS_TEXT_DOMAIN ),
				),
				'desc_tip' =>  true,
				'autoload' => true
			),

			array(
				'title'    => __( 'Email Signature', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'desc'     => 'Add here Email Signature',
				'id'       => 'idea_signature_text',
				'default'  => '<br />Sent via rtCamp Idea Plugin<br />',
				'type'     => 'textarea',
				'css'     => 'width:400px; height: 65px;',
				'autoload' => true
			),

			array( 'type' => 'sectionend', 'id' => 'email_header_options'),
		) );

		return apply_filters( 'rtbiz_get_settings_' . $this->id, $settings );
	}

}
endif;

return new rtBiz_Idea_Settings_Emails();
