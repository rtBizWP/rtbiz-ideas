<?php
/**
 * WP Ideas Archive Template
 *
 * @package rtPanel
 *
 * @since   rtPanelChild 2.0
 */
get_header();
?>
    <div id="primary" class="content-area large-8 small-12 columns">
        <div id="content" class="site-content" role="main">

            <header class="page-header">
                <h1 class="page-title"><a href="<?php echo home_url().'/'.RT_WPIDEAS_SLUG ?>"><?php _e( 'Ideas', 'wp-ideas' ); ?></a></h1>
				<div class="searchidea">
                <input type="text" placeholder="Search Ideas Here" id="txtSearchIdea" name="txtSearchIdea"/>
				<?php
					if ( is_user_logged_in() ) {
						$href = '#TB_inline?width=600&height=550&inlineId=wpideas-insert-idea';
					}else{
						$href = wp_login_url( home_url('/').RT_WPIDEAS_SLUG );
					}
				?>
				<a id="btnNewThickbox" href="<?php echo $href; ?>" class="thickbox"> New Idea </a>
				</div>
            </header>
            <label class="success" id="lblIdeaSuccess" style="display:none;">Idea submitted</label>

            <div id="loop-common" class="idea-loop-common">
                <?php if ( have_posts() ) : ?>
                    <?php
                    while ( have_posts() ) : the_post();
                        rtideas_get_template( 'loop-common.php' );
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
                    else :
                        if ( is_user_logged_in() ) {
                                echo 'Looks like we do not have any idea. <br /><br /> Be first one to suggest idea.&nbsp; <a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=wpideas-insert-idea" class="thickbox"> Click Here </a> &nbsp;  to suggest.';

                        } else {
                            echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
                        }

                    endif; ?>
            </div>
        </div>
		<div id="wpideas-insert-idea" style="display:none;">
			<?php
			rtideas_get_template( 'template-insert-idea.php' );
			?>
		</div>
        <!-- #content -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer();
