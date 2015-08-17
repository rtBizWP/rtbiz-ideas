<?php

/**
 * Created by PhpStorm.
 * User: dips
 * Date: 17/8/15
 * Time: 12:20 PM
 */
class Rtbiz_Ideas_CommonTest extends WP_Ajax_UnitTestCase {
	var $rtideasCommon;

	/**
	 * Setup Class Object and Parent Test Suite
	 *
	 */
	function setUp() {
		parent::setUp();
		$this->rtideasCommon = new Rtbiz_Ideas_Common();
		$rtideasAdmin = new Rtbiz_Ideas_Admin();
		$rtideasAdmin->database_update();
		wp_set_current_user( 1 );
	}

	/**
	 * @group ajax
	 */
	function test_insert_new_idea(){

		$this->_setRole( 'administrator' );

		$term_id = $this->factory->term->create( array( 'taxonomy' => 'rt-product' ) );

		$_POST['txtIdeaTitle'] = 'Demo Idea';
		$_POST['txtIdeaContent'] = 'Demo Idea content';
		$_POST['product_id'] = $term_id;
		$_POST['product'] = 'product_page';
		try {
			$this->_handleAjax( 'rtbiz_ideas_insert_new_idea' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$this->assertEquals( 'product', $this->_last_response );
	}

	/**
	 * @group ajax
	 */
	function test_insert_new_idea_empty_product(){

		$this->_setRole( 'administrator' );

		$_POST['txtIdeaTitle'] = 'Demo Idea';
		$_POST['txtIdeaContent'] = 'Demo Idea content';
		try {
			$this->_handleAjax( 'rtbiz_ideas_insert_new_idea' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$this->assertEquals( 'new-idea', $this->_last_response );
	}

	/**
	 * @group ajax
	 */
	function test_insert_new_idea_title_error(){

		$this->_setRole( 'administrator' );

		$term_id = $this->factory->term->create( array( 'taxonomy' => 'rt-product' ) );

		$_POST['txtIdeaTitle'] = '';
		$_POST['txtIdeaContent'] = 'Demo Idea';
		$_POST['product_id'] = $term_id;
		$_POST['product'] = 'product_page';
		try {
			$this->_handleAjax( 'rtbiz_ideas_insert_new_idea' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$response = json_decode( $this->_last_response );
		$this->assertObjectHasAttribute( 'title', $response );
		$this->assertEquals( 'Please enter a title.', $response->title );
	}

	/**
	 * @group ajax
	 */
	function test_insert_new_idea_content_error(){

		$this->_setRole( 'administrator' );

		$term_id = $this->factory->term->create( array( 'taxonomy' => 'rt-product' ) );

		$_POST['txtIdeaTitle'] = 'Demo Idea';
		$_POST['txtIdeaContent'] = '';
		$_POST['product_id'] = $term_id;
		$_POST['product'] = 'product_page';
		try {
			$this->_handleAjax( 'rtbiz_ideas_insert_new_idea' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$response = json_decode( $this->_last_response );
		$this->assertObjectHasAttribute( 'content', $response );
		$this->assertEquals( 'Please enter details.', $response->content );
	}

	/**
	 * @group ajax
	 */
	function test_insert_search_callback(){

		$this->_setRole( 'administrator' );

		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_title' => 'New Demo Idea', 'post_status' => 'idea-new', 'post_type' => 'idea' ) );

		$_POST['searchtext'] = 'Demo';
		try {
			$this->_handleAjax( 'rtbiz_ideas_search' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$this->assertContains( $_POST['searchtext'], $this->_last_response );

	}

	/**
	 * @group ajax
	 */
	function test_insert_search_callback_not_found(){

		$this->_setRole( 'administrator' );

		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_title' => 'New Demo Idea', 'post_status' => 'idea-new', 'post_type' => 'idea' ) );

		$_POST['searchtext'] = 'Demo123';
		try {
			$this->_handleAjax( 'rtbiz_ideas_search' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$this->assertEquals( 'Looks like we do not have your idea. <br /><br /> Have you got better one? &nbsp; <a id="btnOpenThickbox" href="#Idea-new" > click here</a> &nbsp;  to suggest.', $this->_last_response );

	}

	/**
	 * @group ajax
	 */
	function test_subscribe_notification_setting(){

		$this->_setRole( 'administrator' );

		$_POST['status_change_notification'] = 'YES';
		$_POST['comment_notification'] = 'NO';
		try {
			$this->_handleAjax( 'rtbiz_ideas_subscribe_notification_setting' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$response = json_decode( $this->_last_response );
		$this->assertObjectHasAttribute( 'status', $response );
		$this->assertTrue( $response->status );
		$this->assertEquals( 'NO', get_user_meta( get_current_user_id(), 'comment_notification', true ) );
		$this->assertEquals( 'YES', get_user_meta( get_current_user_id(), 'status_change_notification', true ) );
	}

	/**
	 * @group ajax
	 */
	function test_subscribe_button(){

		$this->_setRole( 'administrator' );

		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_title' => 'New Demo Idea', 'post_status' => 'idea-new', 'post_type' => 'idea' ) );

		$_POST['post_id'] = $post_id;
		try {
			$this->_handleAjax( 'rtbiz_ideas_subscribe_button' );
		} catch ( WPAjaxDieContinueException $e ) {
		}
		$this->assertTrue( isset( $e ) );
		$this->assertEquals( '', $e->getMessage() );

		$response = json_decode( $this->_last_response );
		$this->assertObjectHasAttribute( 'status', $response );
		$this->assertTrue( $response->status );
		$this->assertObjectHasAttribute( 'btntxt', $response );
		$this->assertEquals( 'Subscribe', $response->btntxt );
	}
}
