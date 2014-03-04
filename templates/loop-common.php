<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> >
	<div class="rtwpIdeaVoteBadge">
		<div class="rtwpIdeaVoteCount">
			<strong
				id="rtwpIdeaVoteCount-<?php the_ID(); ?>"><?php echo sanitize_text_field( get_votes_by_post( get_the_ID() ) ); ?></strong>
			<span>votes</span>
		</div>
		<div class="rtwpIdeaVoteButton">
			<input type="button" id="btnVote-<?php the_ID(); ?>" class="btnVote" data-id="<?php the_ID(); ?>" value="<?php
			if ( is_user_logged_in() ) {
				$is_voted = check_user_voted( get_the_ID() );
				if ( isset( $is_voted ) && $is_voted ) {
					echo 'Vote Down';
				} else {
					if ( isset( $is_voted ) && ! $is_voted ) {
						echo 'Vote Up';
					} else {
						echo 'Vote';
					}
				}
			} else {
				echo 'Vote';
			}
			?>" />
		</div>
	</div>
	<header class="rtwpIdeaHeader">
		<h1 class="rtwpIdeaTitle"><a href="<?php the_permalink(); ?>" rel="bookmark"
									 title="<?php printf( esc_attr__( 'Permanent Link to %s', 'rtCamp' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a>
		</h1>

		<div class="rtwpIdeaDescription">
			<div class="typeset">
				<?php
				if ( is_single() && ! is_product() ) { the_content(); }
				else { the_excerpt(); }
				?>
			</div>
		</div>
		<?php
		$args = array( 'post_parent' => get_the_ID(), 'post_type' => 'attachment', 'posts_per_page' => - 1, 'orderby' => 'menu_order', 'order' => 'ASC', );
		$attachments = get_children( $args );
		if ( ! empty( $attachments ) ) {
			?>
			<ul class="rtwpIdeaAttachments rtwpAttachments"> <?php
				$i = 0;
				foreach ( $attachments as $attachment ) {
					if ( $i >= 3 ) {
						if( ! is_single() ) break;
					}
					$image_attributes = wp_get_attachment_image_src( $attachment -> ID, 'full' );
					?>
					<li class="rtwpAttachment">
						<a class="rtwpAttachmentLink rtwpAttachmentLink-preview"
						   href="<?php echo esc_url( $attachment -> guid ); ?>"
						   title="View <?php echo sanitize_title( $attachment -> post_title ); ?>">
							<figure class="rtwpAttachmentInfo">
								<span class="rtwpAttachmentThumbnail"
									  style="background-image: url('<?php echo esc_url( wp_get_attachment_thumb_url( $attachment -> ID ) ); ?>')">&nbsp;</span>
								<figcaption class="rtwpAttachmentMeta">
									<span
										class="rtwpAttachmentCaption"><?php echo sanitize_title( $attachment -> post_title ); ?></span>
								</figcaption>
							</figure>
						</a>
					</li>
					<?php
					$i ++;
				}
				?></ul><?php
		}
		?>

		<div class="rtwpIdeaMeta">
			<a href="<?php the_permalink(); ?>#comments"
			   title="Comments for <?php the_title(); ?>"><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></a>
			   <?php
			   $categories = get_the_category();
			   $separator = ' ';
			   $output = '';
			   if ( $categories ) {
				   ?><span class="uvStyle-separator">&nbsp;·&nbsp;</span>
				<a href="#" title="Ideas similar to <?php the_title(); ?>"><?php
					foreach ( $categories as $category ) {
						$output .= '<a href="' . get_category_link( $category -> term_id ) . '" title="' . esc_attr( sprintf( __( "View all posts in %s" ), $category -> name ) ) . '">' . $category -> cat_name . '</a>' . $separator;
					}
					echo trim( $output, $separator );
					?>
				</a>
				<?php
			}
			?>
			<span class="rtwpStyle-separator">&nbsp;·&nbsp;</span>
			<a href="#" title="Author of <?php the_title(); ?>"><?php the_author(); ?> →</a>
		</div>
	</header>
</article>
