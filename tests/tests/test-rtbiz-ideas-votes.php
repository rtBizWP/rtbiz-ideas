<?php

/**
 * Created by PhpStorm.
 * User: dips
 * Date: 14/8/15
 * Time: 4:15 PM
 */
class Rtbiz_Ideas_VotesTest extends RT_WP_TestCase {
	var $rtideasVote;

	/**
	 * Setup Class Object and Parent Test Suite
	 *
	 */
	function setUp() {
		parent::setUp();
		$this->rtideasVote = new Rtbiz_Ideas_Votes();
		$rtideasAdmin = new Rtbiz_Ideas_Admin();
		$rtideasAdmin->database_update();
		wp_set_current_user( 1 );
	}

	function test_check_user_voted(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		$this->assertTrue( $this->rtideasVote->check_user_voted( $post_id ) );

		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'post' ) );
		$this->assertNull( $this->rtideasVote->check_user_voted( $post_id ) );
	}

	function test_add_vote(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );
		$vote_id = $this->rtideasVote->add_vote( $post_id );
		$this->assertTrue( is_numeric( $vote_id ) );
		$this->assertTrue( $this->rtideasVote->check_user_voted( $post_id ) );
	}

	function test_update_vote(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );
		$vote_id = $this->rtideasVote->update_vote( $post_id, 0 );
		$this->assertTrue( is_numeric( $vote_id ) );
		$this->assertEmpty( $this->rtideasVote->check_user_voted( $post_id ) );
	}

	function test_delete_vote(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		$vote_id = $this->rtideasVote->delete_vote( $post_id );
		$this->assertTrue( is_numeric( $vote_id ) );
		$this->assertNull( $this->rtideasVote->check_user_voted( $post_id ) );
	}

	function test_get_votes_by_idea(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		$this->assertTrue( $this->rtideasVote->check_user_voted( $post_id ) );
		$this->assertEquals( $this->rtideasVote->get_votes_by_idea( $post_id ), 1 );
	}

	function test_get_all_ideas(){
		$post_id = $this->factory->post->create( array( 'post_author' => 1, 'post_type' => 'idea' ) );
		$posts = $this->rtideasVote->get_all_ideas( );
		$this->assertTrue( is_array( $posts ) );
		$this->assertContainsOnly( 'object', $posts );
		$this->assertObjectHasAttribute( 'ID', $posts[0] );
	}
}
