<?php
/**
 * rtbiz-ideas archive tempalate
 */


get_header();
$class = apply_filters( 'rtbiz_idea_post_class', '' );
$content_class = 'site-main';
if ( (string)wp_get_theme() == 'Twenty Twelve' ) {
	$content_class='site-content';
}
$content_id = 'primary';
if ( (string)wp_get_theme() == 'Twenty Ten' ) {
	$content_id='container';
}
$content_class = apply_filters( 'rtbiz_idea_content_class', $content_class );
$content_id = apply_filters( 'rtbiz_idea_content_id', $content_id );
?>

	<div id="<?php echo $content_id; ?>" class="<?php echo $content_class; ?>"> <!-- comment-meta -->
			<article <?php post_class( $class ); ?>>
			<div id="content" class="rtbiz-ideas-archive">

			<header class="rtbiz-ideas-header entry-header">

				<h1 class="rtbiz-ideas-title" >
					<a href="<?php echo home_url( Rtbiz_Ideas_Module::$post_type ); ?>"><?php _e( 'Ideas', RTBIZ_IDEAS_TEXT_DOMAIN ); ?></a>
				</h1><?php

				// Ideas serch form
				rtbiz_ideas_search_form(); ?>

			</header>
			<div class="entry-content">
			<div class="rtbiz-ideas-success" id="lblIdeaSuccess"><?php
				_e( 'Idea submitted', RTBIZ_IDEAS_TEXT_DOMAIN ); ?>
			</div>

			<div id="wpideas-insert-idea" class="rtbiz-idea-form"><?php
				rtbiz_ideas_get_template( 'template-insert-idea.php' );?>
			</div><?php

			// Ideas related navigation tabs
			rtbiz_ideas_nav();

			// Page contnent
			if ( isset( $_REQUEST['tab'] ) && is_user_logged_in() ) {

				if ( 'home' == $_REQUEST['tab'] ) {
					rtbiz_ideas_get_my_ideas( );
				} else if ( 'settings' == $_REQUEST['tab'] ) {
					rtbiz_ideas_get_my_settings( );
				}
			} else { ?>
				<div id="rtbiz-ideas-loop-common" class="rtbiz-ideas-loop-common"><?php
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						rtbiz_ideas_get_template( 'loop-common.php' );
					} ?>
					<div class="rtbiz-ideas-navigation">
						<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Page' ) ?></div>
						<div class="alignright"><?php next_posts_link( 'Next Page &raquo;', '' ) ?></div>
					</div><?php
					if ( is_single() ) {
						comments_template();
					}
				} else { ?>
					<div class="rtbiz-idea-info-text"><?php
						_e( 'Looks like we do not have any idea from you. please suggest idea.', RTBIZ_IDEAS_TEXT_DOMAIN ); ?>
					</div><?php
				} ?>
				</div><?php
			}?>
			</div>
			</div><!-- content -->
			</article>
	</div><!-- primary -->

<?php get_sidebar();
get_footer();
