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
                    else :
                        if ( is_user_logged_in() ) {
                                ?>
                                <div id="my-content-id" style="display:none;">
                                    <?php
                                    include RTWPIDEAS_PATH . 'templates/template-insert-idea.php';
                                    ?>
                                </div>
                                <?php
                                echo 'Looks like we do not have any idea. <br /><br /> Be first one to suggest idea.&nbsp; <a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=my-content-id" class="thickbox"> Click Here </a> &nbsp;  to suggest.';

                        } else {
                            echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
                        }

                    endif; ?>
            </div>
        </div>
		<div id="my-content-id" style="display:none;">
			<?php
			include RTWPIDEAS_PATH . 'templates/template-insert-idea.php';
			?>
		</div>
        <!-- #content -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer();
