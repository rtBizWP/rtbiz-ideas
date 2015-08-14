<?php
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> >

	<div class="rtbiz-idea-vote-Badge">
		<div class="rtbiz-idea-vote-count">
			<span>
				<strong id="rtwpIdeaVoteCount-<?php the_ID(); ?>"><?php
				echo sanitize_text_field( rtbiz_ideas_get_votes_by_idea( get_the_ID() ) ); ?>
				</strong><?php
				_e( 'votes', RTBIZ_IDEAS_TEXT_DOMAIN ); ?>
			</span>
		</div>
		<div class="rtbiz-idea-vote-action"><?php
			rtbiz_ideas_get_vote_action();?>
		</div>
	</div>
	<div class="rtbiz-idea-content">
		<header>
			<h1 class="rtbiz-idea-title">
				<a href="<?php the_permalink(); ?>" rel="bookmark"
                    title="<?php printf( esc_attr__( '%s', 'wp-ideas' ), the_title_attribute( 'echo=0' ) ); ?>">
					<?php the_title(); ?>
				</a>
			</h1>
			<ul class="rtbiz-ideas-meta"><?php
				$taxonomies = array(
					array(
						'slug'  => 'category',
						'label' => 'Category',
					),
					array(
						'slug'  => Rt_Products::$product_slug,
						'label' => 'Product',
					),
				);
				foreach ( $taxonomies as $taxonomy ) {
					$terms = rtbiz_ideas_get_taxonomy_link( $taxonomy['slug'] );
					if ( $terms ) { ?>
						<li>
							<label><?php echo $taxonomy['label']; ?>:</label>
							<span><?php
								echo $terms;?>
							</span>
						</li><?php
					}
				}?>
			</ul>
		</header>
		<div class="rtbiz-idea-description"><?php
		$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		if ( is_single() && ! ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) && is_product() ) ) {
			the_content();
		} else {
			the_excerpt();
		} ?>
		</div><?php
		$args = array( 'post_parent' => get_the_ID(), 'post_type' => 'attachment', 'posts_per_page' => -1, 'orderby' => 'menu_order', 'order' => 'ASC', );
		$attachments = get_children( $args );
		if ( ! empty( $attachments ) ) { ?>
			<ul class="rtbiz-idea-attchements"><?php
			$i = 0;
			foreach ( $attachments as $attachment ) {
				if ( $i >= 3 && ! is_single() ) {
					break;
				}
				$i++;
				$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'full' ); ?>
				<li class="rtbiz-idea-attchement">
					<a class="rtbiz-idea-attchement-link rtbiz-idea-attchement-preview" href="<?php echo esc_url( $attachment->guid ); ?>"
					   title="View <?php echo $attachment->post_title; ?>" >
						<figure class="rtbiz-idea-attchement-info">
							<span class="rtbiz-idea-attchement-thumbnail"
							      style="background-image: url('<?php echo esc_url( wp_get_attachment_thumb_url( $attachment->ID ) ); ?>')">&nbsp;
							</span>
							<figcaption class="rtbiz-idea-attchement-meta">
								<span class="rtbiz-idea-attchement-caption"><?php echo $attachment->post_title; ?></span>
							</figcaption>
						</figure>
					</a>
				</li><?php
			} ?>
			</ul><?php
		} ?>
		<ul class="rtbiz-ideas-meta"><?php
		if ( is_user_logged_in() ) { ?>
			<li><?php edit_post_link( 'Manage Idea', '<span>', ' </span>' ); ?></li>
			<li><?php
				rtbiz_ideas_get_subscribe_action(); ?>
			</li><?php
		} ?>
			<li><?PHP
				rtbiz_ideas_get_comment_link(); ?>
			</li>
			<li><?php
				rtbiz_ideas_get_author_link(); ?>
			</li>
		</ul >
	</div>

</article>
