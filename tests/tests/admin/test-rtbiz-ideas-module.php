<?php

/**
 * Created by PhpStorm.
 * User: dips
 * Date: 13/8/15
 * Time: 5:20 PM
 */
class Rtbiz_Ideas_ModuleTest extends RT_WP_TestCase {
	var $rtideasModule;

	/**
	 * Setup Class Object and Parent Test Suite
	 *
	 */
	function setUp() {
		parent::setUp();
		$this->rtideasModule = new Rtbiz_Ideas_Module();
		$rtideasAdmin = new Rtbiz_Ideas_Admin();
		$rtideasAdmin->database_update();
		wp_set_current_user( 1 );
	}

	function test_construct(){
		$this->assertTrue( method_exists( $this->rtideasModule, 'get_custom_labels' ), 'get_custom_labels does not exist' );
		$this->assertTrue( method_exists( $this->rtideasModule, 'get_custom_statuses' ), 'get_custom_statuses does not exist' );

		$this->assertTrue( method_exists( $this->rtideasModule, 'init_ideas' ), 'init_ideas does not exist' );
		$this->assertTrue( method_exists( $this->rtideasModule, 'append_post_status_list' ), 'append_post_status_list does not exist' );

		$this->assertTrue( method_exists( $this->rtideasModule, 'idea_custom_columns_header' ), 'idea_custom_columns_header does not exist' );
		$this->assertTrue( method_exists( $this->rtideasModule, 'ideas_custom_column_body' ), 'ideas_custom_column_body does not exist' );

		$this->assertTrue( method_exists( $this->rtideasModule, 'save_idea_post' ), 'save_idea_post does not exist' );

	}

	function  test_class_local_variable() {
		$this->assertEquals( 'idea', Rtbiz_Ideas_Module::$post_type );
		$this->assertEquals( 'Idea', Rtbiz_Ideas_Module::$name );
	}

	function test_get_custom_labels(){
		$this->assertTrue( is_array( $this->rtideasModule->labels ) );
		$this->assertEquals( $this->rtideasModule->get_custom_labels(), $this->rtideasModule->labels );
	}

	function test_get_custom_statuses(){
		$this->assertTrue( is_array( $this->rtideasModule->statuses ) );
		$this->assertEquals( $this->rtideasModule->get_custom_statuses(), $this->rtideasModule->statuses );
		$this->assertEquals( count( $this->rtideasModule->get_custom_statuses() ), 6 );
	}

	function test_init_ideas(){
		$this->assertTrue( method_exists( $this->rtideasModule, 'register_custom_post' ), 'register_custom_post does not exist' );
		$this->assertTrue( method_exists( $this->rtideasModule, 'register_custom_statuses' ), 'register_custom_statuses does not exist' );
	}

	function test_register_custom_post(){
		$this->assertTrue( method_exists( $this->rtideasModule, 'ideas_add_voters_metabox' ), 'ideas_add_voters_metabox does not exist' );
		$this->assertTrue( post_type_exists( Rtbiz_Ideas_Module::$post_type ) );
	}

	function test_ideas_add_voters_metabox(){
		$this->assertTrue( method_exists( $this->rtideasModule, 'get_voters_of_idea' ), 'get_voters_of_idea does not exist' );
	}

	function test_get_voters_of_idea(){
		$post = $this->factory->post->create_and_get( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		ob_start();
		$this->rtideasModule->get_voters_of_idea( $post );
		$temp_res = ob_get_clean();
		$this->assertEquals( $temp_res, '<a href="http://example.org/wp-admin/profile.php">admin</a><br/>' );

		$post = $this->factory->post->create_and_get( array( 'post_author' => 1, 'post_type' => 'post' ) );
		ob_start();
		$this->rtideasModule->get_voters_of_idea( $post );
		$temp_res = ob_get_clean();
		$this->assertEquals( $temp_res, 'No votes yet.' );
	}

	function test_register_custom_statuses(){
		$status = array(
			'slug'        => 'Demo',
			'name'        => __( 'Demo', RTBIZ_IDEAS_TEXT_DOMAIN ),
			'description' => __( 'Ticket is Demo.', RTBIZ_IDEAS_TEXT_DOMAIN ),
		);
		$this->assertTrue( is_object( $this->rtideasModule->register_custom_statuses( $status ) ) );
	}

	function test_idea_custom_columns_header(){
		$columns = $this->rtideasModule->idea_custom_columns_header( array() );
		$this->assertEquals( $columns, array( 'wpideas_votes' => _x( 'Votes', RTBIZ_IDEAS_TEXT_DOMAIN ) ) );
	}

	function test_ideas_custom_column_body(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		ob_start();
		$this->rtideasModule->ideas_custom_column_body( 'wpideas_votes', $post_id );
		$temp_res = ob_get_clean();
		$this->assertEquals( $temp_res, 1 );

		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'post' ) );
		ob_start();
		$this->rtideasModule->ideas_custom_column_body( 'wpideas_votes', $post_id );
		$temp_res = ob_get_clean();
		$this->assertEquals( $temp_res, '' );
	}
}
