<?php

if ( ! function_exists( 'rtbiz_ideas_search_form' ) ) {

	/**
	 * get ideas setting
	 * @return mixed
	 */
	function rtbiz_ideas_search_form( ) { ?>
		<from class="rtbiz-ideas-serchfrom">
			<input type="text" placeholder="Search Ideas Here" id="txtSearchIdea" name="txtSearchIdea"/><?php
			if ( ! is_user_logged_in() ) { ?>
				<a id="btnNewThickbox" href="<?php echo wp_login_url( home_url( Rtbiz_Ideas_Module::$post_type ) ); ?>"><?php _e( 'Login to Suggest Idea', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></a><?php
			} else { ?>
				<a id="btnNewThickbox" href="#Idea-new"><?php _e( 'New Idea', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></a> <?php
			}?>
		</from><?php
	}
}

if ( ! function_exists( 'rtbiz_ideas_nav' ) ) {

	/**
	 * get ideas setting
	 * @return mixed
	 */
	function rtbiz_ideas_nav( ) {
		if ( is_user_logged_in() ) { ?>
			<ul class="rtbiz-ideas-nav">
			<li>
				<a href="<?php echo home_url( Rtbiz_Ideas_Module::$post_type ); ?>?tab=home"> My ideas </a>
			</li>
			<li>
				<a href="<?php echo home_url( Rtbiz_Ideas_Module::$post_type ); ?>?tab=settings">My Settings</a>
			</li>
			</ul><?php
		}
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_my_ideas' ) ) {

	/**
	 * get my ideas
	 * @return mixed
	 */
	function rtbiz_ideas_get_my_ideas( ) {
		global $wpdb;
		$user_id = get_current_user_id( );
		$totolIdeas = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts LEFT JOIN {$wpdb->prefix}rt_wpideas_subscriber ON ( {$wpdb->posts}.ID = {$wpdb->prefix}rt_wpideas_subscriber.post_id ) where ( {$wpdb->prefix}rt_wpideas_subscriber.user_id = %d OR $wpdb->posts.post_author = %d ) AND $wpdb->posts.post_type= %s AND $wpdb->posts.post_status <> 'auto-draft'", $user_id, $user_id, Rtbiz_Ideas_Module::$post_type ) );

		$limit = get_option( 'posts_per_page', 3 );
		$currentpage = 1;
		if ( isset( $_REQUEST['page'] ) ) {
			$currentpage = (int) $_REQUEST['page'];
		}
		$offset = ( $currentpage - 1 ) * $limit ;
		$pageposts = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT $wpdb->posts.* FROM $wpdb->posts LEFT JOIN {$wpdb->prefix}rt_wpideas_subscriber ON ( {$wpdb->posts}.ID = {$wpdb->prefix}rt_wpideas_subscriber.post_id ) where ( {$wpdb->prefix}rt_wpideas_subscriber.user_id = %d OR $wpdb->posts.post_author = %d ) AND $wpdb->posts.post_type= %s AND $wpdb->posts.post_status <> 'auto-draft'  ORDER BY {$wpdb->posts}.post_date DESC LIMIT %d, %d", $user_id, $user_id, Rtbiz_Ideas_Module::$post_type, $offset, $limit ) ); ?>

		<div id="loop-common" class="rtbiz-ideas-loop-common"><?php
		if ( $pageposts ) { ?>
			<table class="rtbiz-ideas-table">
				<thead>
					<th><?php _e( 'Title', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
					<th><?php _e( 'Author', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
					<th><?php _e( 'Vote Counts', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
					<th><?php _e( 'Action', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
				</thead>
				<tbody><?php
				global $post;
				foreach ( $pageposts as $post ) {
					setup_postdata( $post );
					rtbiz_ideas_get_template( 'table-common.php' );
				} ?>
				</tbody>
				<tfoot>
					<th><?php _e( 'Title', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
					<th><?php _e( 'Author', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
					<th><?php _e( 'Vote Counts', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
					<th><?php _e( 'Action', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></th>
				</tfoot>
			</table>
			<div class="rtbiz-ideas-navigation"><?php
			if ( $currentpage > 1 ) { ?>
				<div class="alignleft"><a href="<?php echo home_url( Rtbiz_Ideas_Module::$post_type ); ?>?tab=home&page=<?php echo $currentpage - 1; ?> "> &laquo; Previous Page </a></div><?php
			}
			if ( ceil( $totolIdeas / $limit ) != $currentpage ) { ?>
				<div class="alignright"> <a href="<?php echo home_url( Rtbiz_Ideas_Module::$post_type ); ?>?tab=home&page=<?php echo $currentpage + 1; ?> "> Next Page &raquo;</a></div><?php
			} ?>
			</div><?php
		} else { ?>
			<div class="rtbiz-idea-info-text"><?php
			_e( 'Looks like we do not have any idea from you. please suggest idea.', RTBIZ_IDEAS_TEXT_DOMAIN ); ?>
			</div><?php
		} ?>
		</div><?php
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_my_settings' ) ) {

	/**
	 * get my setting for ideas notification
	 * @return mixed
	 */
	function rtbiz_ideas_get_my_settings( ) {
		$user_id = get_current_user_id( );
		$comment_notification = get_user_meta( $user_id,'comment_notification',true );
		$status_notification = get_user_meta( $user_id,'status_change_notification',true );
		if ( empty( $comment_notification ) || 'NO' != $comment_notification ) {
			$comment_notification = 'checked';
		}
		if ( empty( $status_notification ) || 'NO' != $status_notification ) {
			$status_notification = 'checked';
		}?>
		<div class="rtbiz-ideas-settings">
			<h2 class="rtbiz-ideas-title"> Email Notification Setting</h2>
			<form method="post">
				<div class="rtbiz-ideas-success" id="Notificationstatus"><?php
					_e( 'Setting Saved!', RTBIZ_IDEAS_TEXT_DOMAIN ); ?>
				</div>

				<div class="rtbiz-ideas-row">
					<input id="status_change_notification" type="checkbox" <?php echo $status_notification ?>>
					<label for="status_change_notification"> Idea Status Change</label>
				</div>
				<div class="rtbiz-ideas-row">
					<input id="comment_notification" type="checkbox" <?php echo $comment_notification ?>>
					<label for="comment_notification"> Idea Comments notification</label>
				</div>

				<div class="rtbiz-ideas-row">
					<input id='user_notification_save' type="button" value="Save">
				</div>
			</form>
		</div><?php
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_vote_action' ) ) {

	/**
	 * get my setting for ideas notification
	 * @return mixed
	 */
	function rtbiz_ideas_get_vote_action( ) {
		$idea_id = get_the_ID();
		$idea_status = get_post_status( $idea_id );
		if ( 'idea-new' != $idea_status ) {
			$value = ucfirst( preg_replace( '/^idea-/', '', $idea_status ) );?>
			<span class="rtbiz-ideas-status <?php echo $idea_status; ?>" ><?php echo $value; ?></span><?php
		} else {
			$value = __( 'Vote', RTBIZ_IDEAS_TEXT_DOMAIN );
			if ( is_user_logged_in() ) {
				$is_voted = rtbiz_ideas_check_user_voted( $idea_id );
				if ( isset( $is_voted ) && $is_voted ) {
					$value = __( 'Vote Down', RTBIZ_IDEAS_TEXT_DOMAIN );
				} else if ( isset( $is_voted ) && ! $is_voted ) {
					$value = __( 'Vote Up', RTBIZ_IDEAS_TEXT_DOMAIN );
				}
			} ?>
			<input type="button" id="btnVote-<?php echo $idea_id; ?>" class="btnVote"
			       value="<?php echo $value; ?>" data-id="<?php echo $idea_id; ?>" />
			<?php
		}
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_subscribe_action' ) ) {

	/**
	 * get idea subscribe/unsubscribe link
	 * @return mixed
	 */
	function rtbiz_ideas_get_subscribe_action( ) {
		global $rtbiz_ideas_subscriber_model;
		$subscribeflag = $rtbiz_ideas_subscriber_model->check_subscriber_exist( get_the_ID(), get_current_user_id() );
		$subscribevalue = $subscribeflag ? 'Unsubscribe' : 'Subscribe';?>
		<a id="subscriber-<?php the_ID(); ?>" class="subscribe_email_notification_button button-<?php echo strtolower( $subscribevalue ); ?>"
		   data-id="<?php the_ID(); ?>" > <?php
			echo $subscribevalue; ?>
		</a><?php
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_comment_link' ) ) {

	/**
	 * get my setting for ideas notification
	 * @return mixed
	 */
	function rtbiz_ideas_get_comment_link( ) { ?>
		<a href="<?php the_permalink(); ?>#comments"  title="Comments for <?php the_title(); ?>">
							<?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?>
		</a><?PHP
	}
}

if ( ! function_exists( 'rtbiz_ideas_get_author_link' ) ) {

	/**
	 * get my setting for ideas notification
	 * @return mixed
	 */
	function rtbiz_ideas_get_author_link( ) {
		$author = get_userdata( get_the_author_meta( 'ID' ) );
		if ( function_exists( 'bp_core_get_userlink' ) ) {
			echo bp_core_get_userlink( $author->ID );
		} else { ?>
			<a href="<?php echo get_author_posts_url( $author->ID ); ?>" title="Author of <?php the_title(); ?>"><?php the_author(); ?> →</a><?php
		}
	}
}


if ( ! function_exists( 'rtbiz_ideas_get_taxonomy_link' ) ) {

	/**
	 * @param $taxonomy
	 * @param string $s_ele
	 * @param string $e_ele
	 * @param string $separator
	 */
	function rtbiz_ideas_get_taxonomy_link( $taxonomy, $separator = ' '  ) {
		if ( 'category' == $taxonomy ) {
			$terms = get_the_category();
		} else {
			$terms = wp_get_post_terms( get_the_ID(), $taxonomy, array( 'fields' => 'all' ) );
		}
		if ( $terms ) {
			$output = '';
			foreach ( $terms as $term ) {
				$output .= '<a href="' . get_category_link( $term->term_id ) . '" title="' . esc_attr( sprintf( __( 'View all posts in %s' ), $term->name ) ) . '">' . $term->name . '</a>' . $separator;
			}
			ob_start();
			echo trim( $output, $separator );
			return ob_get_clean();
		}
	}
}
