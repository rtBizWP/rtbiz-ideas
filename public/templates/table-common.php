<tr>

	<td>
		<strong>
			<a href="<?php the_permalink(); ?>" rel="bookmark"
			   title="<?php printf( esc_attr__( '%s', RTBIZ_IDEAS_TEXT_DOMAIN ), the_title_attribute( 'echo=0' ) ); ?>"> <?php the_title() ?></a>
		</strong>
	</td>

	<td><?php $author = get_userdata( get_the_author_meta( 'ID' ) );
	if ( function_exists( 'bp_core_get_userlink' ) ) {
		echo bp_core_get_userlink( $author->ID );
	} else { ?>
		<a href="<?php echo get_author_posts_url( $author->ID ); ?>"
		   title="Author of <?php the_title(); ?>"><?php the_author(); ?> </a></td>
	<?php } ?>
	<td class="column-votes">
		<strong id="rtwpIdeaVoteCount-<?php the_ID(); ?>"><?php echo sanitize_text_field( rtbiz_ideas_get_votes_by_idea( get_the_ID() ) ); ?></strong>
	</td>
	<td>
		<div class="rtbiz-idea-action"><?php
			rtbiz_ideas_get_vote_action( true );
			rtbiz_ideas_get_subscribe_action();?>
		</div>
	</td>
</tr>
