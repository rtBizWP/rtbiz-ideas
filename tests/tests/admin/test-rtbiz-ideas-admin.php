<?php

/**
 * Created by PhpStorm.
 * User: dips
 * Date: 13/8/15
 * Time: 5:00 PM
 */
class Rtbiz_Ideas_AdminTest extends RT_WP_TestCase {
	var $rtideasAdmin;

	/**
	 * Setup Class Object and Parent Test Suite
	 *
	 */
	function setUp() {
		parent::setUp();
		$this->rtideasAdmin = new Rtbiz_Ideas_Admin();
	}

	function test_init_admin(){
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Subscriber_Model' ), 'Class Rtbiz_Ideas_Subscriber_Model does not exist' );
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Votes_Model' ), 'Class Rtbiz_Ideas_Votes_Model does not exist' );

		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Module' ), 'Class Rtbiz_Ideas_Module does not exist' );
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Notification' ), 'Class Rtbiz_Ideas_Notification does not exist' );

		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Votes' ), 'Class Rtbiz_Ideas_Votes does not exist' );
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Woo' ), 'Class Rtbiz_Ideas_Woo does not exist' );

		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Attributes' ), 'Class Rtbiz_Ideas_Attributes does not exist' );
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Settings' ), 'Class Rtbiz_Ideas_Settings does not exist' );

	}

	function test_database_update(){
		$this->assertTrue( class_exists( 'RT_DB_Update' ), 'Class RT_DB_Update does not exist' );
		$updateDB = new RT_DB_Update( trailingslashit( RTBIZ_IDEAS_PATH ) . 'rtbiz-ideas.php', trailingslashit( RTBIZ_IDEAS_PATH . 'admin/schema/' ) );
		$this->assertTrue( method_exists( $updateDB, 'do_upgrade' ), 'do_upgrade does not exist' );
	}

	function test_module_register(){
		$tmp = $this->rtideasAdmin->module_register( array() );

		$this->assertEquals(
			array(
				'label'           => __( 'Idea', RTBIZ_IDEAS_TEXT_DOMAIN ),
				'post_types'      => array( Rtbiz_Ideas_Module::$post_type ),
				'product_support' => array( Rtbiz_Ideas_Module::$post_type ),
				'setting_option_name' => Rtbiz_Ideas_Settings::$ideas_opt,
				'setting_page_url' => admin_url( 'edit.php?post_type=' . Rtbiz_Ideas_Module::$post_type . '&page=' . Rtbiz_Ideas_Settings::$page_slug ),
			), $tmp[ rtbiz_sanitize_module_key( RTBIZ_IDEAS_TEXT_DOMAIN ) ] );
	}
}
