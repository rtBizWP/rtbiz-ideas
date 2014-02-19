<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div id="primary" class="content-area <?php apply_filters( 'rtwpideas_content_class', 'large-8 small-12 columns' ); ?>">
	<div id="content" class="site-content" role="main">

		<header class="page-header">
			<h1 class="page-title"><?php _e( 'Ideas', 'rtCamp' ); ?></h1>
			<input type="text" placeholder="Enter Idea Here" id="txtNewIdea" name="txtNewIdea" />
		</header>
		
		<?php  if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
		                <article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> >
					<div class="rtwpIdeaVoteBadge">
						<div class="rtwpIdeaVoteCount">
							<strong id="rtwpIdeaVoteCount-<?php the_ID(); ?>"><?php echo sanitize_text_field( get_votes_by_post( get_the_ID() ) ); ?></strong>
							<span> votes</span>    
						</div>
						<div class="rtwpIdeaVoteButton">
							<button id="btnVote-<?php the_ID(); ?>" class="btnVote" data-id="<?php the_ID(); ?>" >
							<?php
							if( is_user_logged_in() ){
								$is_voted = check_user_voted( get_the_ID() );
								if( isset( $is_voted ) && $is_voted ){
									echo 'Vote Down';
								}else if( isset( $is_voted ) && ! $is_voted ){
									echo 'Vote Up';
								}else {
									echo 'Vote';
								}
							} else {
								echo 'Vote';
							}
							?>
							</button>
						</div>
					</div>
					<header class="rtwpIdeaHeader">
						<h1 class="rtwpIdeaTitle"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permanent Link to %s', 'rtCamp' ), the_title_attribute( 'echo=0' ) ); ?>"><?php the_title(); ?></a></h1>
						<div class="rtwpIdeaDescription">
							<div class="typeset"><?php if ( is_single() ) : the_content();
		else : the_excerpt();
		endif; ?>
							</div>
						</div>
						
						<ul class="rtwpIdeaAttachments rtwpAttachments">
							<li class="rtwpAttachment">
								<a class="rtwpAttachmentLink rtwpAttachmentLink-preview" href="/assets/72059504/Screen_Shot_2014-01-25_at_20.20.57.png" rel="Suggestion_5433454" title="View Screen_Shot_2014-01-25_at_20.20.57.png">
									<figure class="rtwpAttachmentInfo">
										<span class="rtwpAttachmentThumbnail" style="background-image: url(https://s3.amazonaws.com/uploads.uservoice.com/assets/072/059/504/thumb/Screen_Shot_2014-01-25_at_20.20.57.png?AWSAccessKeyId=14D6VH0N6B73PJ6VE382&amp;Expires=1392623826&amp;Signature=0xkQRYP0OmJQpKH0rWU8Y4e88Q0%3D)">&nbsp;</span>
										<figcaption class="rtwpAttachmentMeta">
											<span class="rtwpAttachmentCaption">Screen_Shot_2014-01-25_at_20.20.57.png</span>
											<span class="rtwpAttachmentSize">98 KB</span>
										</figcaption>
									</figure>
								</a>
							</li>
							<li class="rtwpAttachment">
								<a class="rtwpAttachmentLink rtwpAttachmentLink-preview" href="/assets/72059504/Screen_Shot_2014-01-25_at_20.20.57.png" rel="Suggestion_5433454" title="View Screen_Shot_2014-01-25_at_20.20.57.png">
									<figure class="rtwpAttachmentInfo">
										<span class="rtwpAttachmentThumbnail" style="background-image: url(https://s3.amazonaws.com/uploads.uservoice.com/assets/072/059/504/thumb/Screen_Shot_2014-01-25_at_20.20.57.png?AWSAccessKeyId=14D6VH0N6B73PJ6VE382&amp;Expires=1392623826&amp;Signature=0xkQRYP0OmJQpKH0rWU8Y4e88Q0%3D)">&nbsp;</span>
										<figcaption class="rtwpAttachmentMeta">
											<span class="rtwpAttachmentCaption">Screen_Shot_2014-01-25_at_20.20.57.png</span>
											<span class="rtwpAttachmentSize">98 KB</span>
										</figcaption>
									</figure>
								</a>
							</li>
							<li class="rtwpAttachment">
								<a class="rtwpAttachmentLink rtwpAttachmentLink-preview" href="/assets/72059504/Screen_Shot_2014-01-25_at_20.20.57.png" rel="Suggestion_5433454" title="View Screen_Shot_2014-01-25_at_20.20.57.png">
									<figure class="rtwpAttachmentInfo">
										<span class="rtwpAttachmentThumbnail" style="background-image: url(https://s3.amazonaws.com/uploads.uservoice.com/assets/072/059/504/thumb/Screen_Shot_2014-01-25_at_20.20.57.png?AWSAccessKeyId=14D6VH0N6B73PJ6VE382&amp;Expires=1392623826&amp;Signature=0xkQRYP0OmJQpKH0rWU8Y4e88Q0%3D)">&nbsp;</span>
										<figcaption class="rtwpAttachmentMeta">
											<span class="rtwpAttachmentCaption">Screen_Shot_2014-01-25_at_20.20.57.png</span>
											<span class="rtwpAttachmentSize">98 KB</span>
										</figcaption>
									</figure>
								</a>
							</li>
						</ul>
						<div class="rtwpIdeaMeta">
							<a href="<?php the_permalink(); ?>#comments" title="Comments for <?php the_title(); ?>"><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></a> 
							<span class="uvStyle-separator">&nbsp;·&nbsp;</span> 
							<a href="#" title="Ideas similar to <?php the_title(); ?>">Category</a>
							<span class="rtwpStyle-separator">&nbsp;·&nbsp;</span>  
							<a href="<?php echo get_the_author_link(); ?>" data-iframe-target="_blank" title="Author of <?php the_title(); ?>"><?php echo get_the_author(); ?> →</a>
						</div>
					</header>
		                </article>
				<?php
		if ( is_single() ) :
			comments_template();
		endif;
			endwhile;
			?>
			<div class="navigation">
				<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Page' ) ?></div>
				<div class="alignright"><?php next_posts_link( 'Next Page &raquo;', '' ) ?></div>
			</div>


		<?php else : ?>
	<?php get_template_part( 'content', 'none' ); ?>
<?php endif; ?>





	</div><!-- #content -->
</div><!-- #primary -->
