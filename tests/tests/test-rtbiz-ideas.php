<?php

/**
 * Created by PhpStorm.
 * User: dips
 * Date: 13/8/15
 * Time: 3:28 PM
 */
class Rtbiz_IdeasTest extends RT_WP_TestCase {
	var $rtbizideas;

	/**
	 * Setup Class Object and Parent Test Suite
	 *
	 */
	function setUp() {
		parent::setUp();
		$this->rtbizideas = new Rtbiz_Ideas();
	}

	/**
	 * Dependecy check
	 */
	function test_check_rtbiz_dependecy() {
		global $rtbiz_ideas_plugin_check;
		$this->assertTrue( is_a( $rtbiz_ideas_plugin_check, 'Rtbiz_Ideas_Plugin_Check' ), '$rtbiz_ideas_plugin_check is not init.' );
		$this->assertTrue( method_exists( $rtbiz_ideas_plugin_check, 'rtbiz_ideas_check_plugin_dependency' ), 'rtbiz_ideas_check_plugin_dependency not exist' );
		$this->assertTrue( $rtbiz_ideas_plugin_check->rtbiz_ideas_check_plugin_dependency(), 'Dependency not include properly' );
	}

	function test_Rtbiz_Ideas(){
		$this->assertTrue( method_exists( $this->rtbizideas, 'load_dependencies' ), 'load_dependencies does not exist' );
		$this->assertTrue( method_exists( $this->rtbizideas, 'set_locale' ), 'set_locale does not exist' );
		$this->assertTrue( method_exists( $this->rtbizideas, 'define_admin_hooks' ), 'define_admin_hooks does not exist' );
		$this->assertTrue( method_exists( $this->rtbizideas, 'define_public_hooks' ), 'define_public_hooks not exist' );
		$this->assertTrue( method_exists( $this->rtbizideas, 'run' ), 'run not exist' );
	}

	function test_load_dependencies(){
		$this->assertTrue( class_exists( 'RT_WP_Autoload' ), 'Class RT_WP_Autoload does not exist' );
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Loader' ), 'Class Rtbiz_Ideas_Loader does not exist' );
		$this->assertTrue( method_exists( $this->rtbizideas, 'get_loader' ), 'get_loader does not exist' );
		$this->assertTrue( is_a( $this->rtbizideas->get_loader(), 'Rtbiz_Ideas_Loader' ), 'Rtbiz_Ideas::$loader is not init.' );
	}

	function test_set_locale(){
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_i18n' ), 'Class Rtbiz_Ideas_i18n does not exist' );
		$plugin_i18n = new Rtbiz_Ideas_i18n();
		$this->assertTrue( method_exists( $plugin_i18n, 'set_domain' ), 'set_domain does not exist' );
		$this->assertTrue( method_exists( $plugin_i18n, 'load_plugin_textdomain' ), 'load_plugin_textdomain does not exist' );
	}

	function test_define_admin_hooks(){
		$this->assertEquals( Rtbiz_Ideas::$templateURL, 'rtbiz-ideas', 'Rtbiz_Ideas::$templateURL is not init.' );
		$this->assertTrue( class_exists( 'RtBiz_Ideas_Admin' ), 'Class RtBiz_Ideas_Admin does not exist' );
		$rtbiz_ideas_admin = new RtBiz_Ideas_Admin( );
		$this->assertTrue( method_exists( $rtbiz_ideas_admin, 'init_admin' ), 'init_admin exist' );
		$this->assertTrue( method_exists( $rtbiz_ideas_admin, 'database_update' ), 'database_update does not exist' );
		$this->assertTrue( method_exists( $rtbiz_ideas_admin, 'module_register' ), 'module_register does not exist' );
		$this->assertTrue( method_exists( $rtbiz_ideas_admin, 'enqueue_styles' ), 'enqueue_styles does not exist' );
		$this->assertTrue( method_exists( $rtbiz_ideas_admin, 'enqueue_scripts' ), 'enqueue_scripts does not exist' );
	}

	function test_define_public_hooks(){
		$this->assertTrue( class_exists( 'Rtbiz_Ideas_Public' ), 'Class Rtbiz_Ideas_Public does not exist' );
		$rtbiz_ideas_public = new Rtbiz_Ideas_Public( );
		$this->assertTrue( method_exists( $rtbiz_ideas_public, 'enqueue_styles' ), 'enqueue_styles does not exist' );
		$this->assertTrue( method_exists( $rtbiz_ideas_public, 'enqueue_scripts' ), 'enqueue_scripts does not exist' );
	}

	function test_run(){
		$this->assertTrue( method_exists( $this->rtbizideas->get_loader(), 'run' ), 'run does not exist' );
	}

}
