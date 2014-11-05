<tr>
	<td><a href="<?php the_permalink(); ?>" rel="bookmark"
	       title="<?php printf( esc_attr__( '%s', 'wp-ideas' ), the_title_attribute( 'echo=0' ) ); ?>"> <?php  the_title() ?></a></td>
	<td><?php $author = get_userdata( get_the_author_meta( 'ID' ) );
		if( function_exists( 'bp_core_get_userlink' ) ){
			echo bp_core_get_userlink( $author->ID );
		}else{
		?>
		<a href="<?php echo get_author_posts_url( $author->ID ); ?>" title="Author of <?php the_title(); ?>"><?php the_author(); ?> </a></td>
	<?php } ?>
	<td><div class="">
			<div class="">
				<strong
					id="rtwpIdeaVoteCount-<?php the_ID(); ?>"><?php echo sanitize_text_field( get_votes_by_post( get_the_ID() ) ); ?></strong>
			</div>
		</div></td>
	<td>
		<div class="rtwpIdeaVoteButton">
			<input type="button" id="btnVote-<?php the_ID(); ?>" class="btnVote" data-id="<?php the_ID(); ?>"
			       value="<?php
			       if( get_post_status( get_the_ID() ) != 'idea-new' ){
				       echo ucfirst( get_post_status( get_the_ID() ) );
			       }else{
				       if ( is_user_logged_in() ){
					       $is_voted = check_user_voted( get_the_ID() );
					       if ( isset( $is_voted ) && $is_voted ){
						       echo 'Vote Down';
					       } else {
						       if ( isset( $is_voted ) && ! $is_voted ){
							       echo 'Vote Up';
						       } else {
							       echo 'Vote';
						       }
					       }
				       } else {
					       echo 'Vote';
				       }
			       }

			       ?>" <?php if( get_post_status( get_the_ID() ) != 'idea-new' ){
				echo ' disabled="disabled"';
			} ?> <?php if( get_post_status( get_the_ID() ) == 'idea-accepted' ){
				echo ' style="background-color:GREEN;"';
			}else if( get_post_status( get_the_ID() ) == 'idea-declined' ){
				echo ' style="background-color:RED;"';
			}else if( get_post_status( get_the_ID() ) == 'idea-completed' ){
				echo ' style="background-color:GREEN;"';
			} ?> />

			<?php
			global $rtWpIdeasSubscirber;
			$subcribebuttonflag= $rtWpIdeasSubscirber->check_subscriber_exist(get_the_ID(),get_current_user_id());
			$subcribebuttonvalue= $subcribebuttonflag?'Unsubscribe':'Subscribe';
			$subcribebuttonclass= $subcribebuttonflag?'unsubscribe':'subscribe';
			?>
<!--			<label>  <a id="subscriber---><?php //the_ID(); ?><!--" class="subscribe_email_notification_button button---><?php //echo $subcribebuttonclass; ?><!--" data-id="--><?php //the_ID(); ?><!--" > --><?php //echo $subcribebuttonvalue; ?><!--</a> </label>-->
			<input type='button' id="subscriber-<?php the_ID(); ?>" class="subscribe_email_notification_button button-<?php echo $subcribebuttonclass; ?>" value=<?php echo $subcribebuttonvalue; ?> data-id="<?php the_ID(); ?>" >


		</div>

	</td>

</tr>