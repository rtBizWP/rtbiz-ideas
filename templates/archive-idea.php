<?php
/**
 * WP Ideas Archive Template
 *
 * @package rtPanel
 *
 * @since rtPanelChild 2.0
 */
get_header();
?>
<div id="primary" class="content-area large-8 small-12 columns">
	<div id="content" class="site-content" role="main">

		<header class="page-header">
			<h1 class="page-title"><?php _e( 'Ideas', 'wp-ideas' ); ?></h1>
			<input type="text" placeholder="Search Ideas Here" id="txtSearchIdea" name="txtSearchIdea"/>
		</header>
		<label class="success" id="lblIdeaSuccess" style="display:none;">Idea submitted</label>
		<div id="loop-common">
			<?php if ( have_posts() ) : ?>
	<?php
	while ( have_posts() ) : the_post();
		include RTWPIDEAS_PATH . 'templates/loop-common.php';
	endwhile;
	?>
				<div class="navigation">
					<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Page' ) ?></div>
					<div class="alignright"><?php next_posts_link( 'Next Page &raquo;', '' ) ?></div>
				</div>
	<?php
	if ( is_single() ) :
		comments_template();
	endif;
	?>
			<?php endif; ?>
		</div>
	</div>
	<!-- #content -->
</div><!-- #primary -->
<?php
get_sidebar();
get_footer();
