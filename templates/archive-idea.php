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
                <h1 class="page-title"><a href="<?php echo home_url().'/'.RTBIZ_IDEAS_SLUG ?>"><?php _e( 'Ideas', 'wp-ideas' ); ?></a></h1>
				<div class="searchidea">
                <input type="text" placeholder="Search Ideas Here" id="txtSearchIdea" name="txtSearchIdea"/>
				<?php
					if ( is_user_logged_in() ) {
						$href = '#TB_inline?width=600&height=550&inlineId=wpideas-insert-idea';
					}else{
						$href = wp_login_url( home_url('/').RTBIZ_IDEAS_SLUG );
					}
				?>
				<a id="btnNewThickbox" href="<?php echo $href; ?>" class="thickbox"> New Idea </a>
				</div>
            </header>
	        <?php if (is_user_logged_in()){ ?>
	        <label id="myideas"> <a href="?tab=home"> My ideas </a>| <a href="?tab=settings">My Settings</a></label>
			<?php } ?>
            <label class="success" id="lblIdeaSuccess" style="display:none;">Idea submitted</label>
			<?php if ( isset($_REQUEST['tab']) && is_user_logged_in() ){
				if (    $_REQUEST['tab'] == 'home'){
					global $rtWpIdeasSubscirber;
					$posts_id = $rtWpIdeasSubscirber->get_user_post(get_current_user_id());
					global $wp_query;
					$query = new WP_Query( array('post_type'=>RTBIZ_IDEAS_SLUG ,'post__in' => $posts_id, 'post_status' => 'any') );
					?>
					<div id="loop-common" class="idea-loop-common">
						<?php
						if ( $query->have_posts() ) : ?>
							<?php
							while ( $query->have_posts() ) :$query->the_post();
								rtideas_get_template( 'loop-common.php' );
							endwhile;
							?>
							<div class="navigation">
								<div class="alignleft"><?php previous_posts_link( '&laquo; Previous Page' ) ?></div>
								<div class="alignright"><?php next_posts_link( 'Next Page &raquo;', '' ) ?></div>
							</div>
							<?php
							if ( $query->is_single() ) :
								$query->comments_template();
							endif;
						else :
							if ( is_user_logged_in() ) {
								echo 'Looks like we do not have any idea. <br /><br /> Be first one to suggest idea.&nbsp; <a id="btnOpenThickbox" href="#TB_inline?width=600&height=550&inlineId=wpideas-insert-idea" class="thickbox"> Click Here </a> &nbsp;  to suggest.';

							} else {
								echo '<br/><a id="btnOpenThickbox" href="/wp-login.php">Login to Suggest Idea</a>';
							}

						endif; ?>
					</div> <?PHP

					}
				else if ( $_REQUEST['tab'] =='settings') {
					$comment_notification =get_user_meta( get_current_user_id(),'comment_notification',true );
					$comment_notificationflag='';
					if ( empty($comment_notification) || $comment_notification!='NO' ){
						$comment_notificationflag = 'checked';
					}
					$status_change =get_user_meta( get_current_user_id(),'status_change_notification',true );
					$status_changeflag='';
					if ( empty($status_change) || $status_change != 'NO'){
						$status_changeflag = 'checked';
					}
					?>

					<h3> Email Notification Setting</h3>
					<div>
						<div id='Notificationstatus' class="highlight" style="display: none;">Setting Saved!</div>
					</div>
	        <div>
					<div>
					<input id="status_change_notification" type="checkbox" <?php echo $status_changeflag ?>> <label> Idea Status Change</label>
					</div>
					<div>
					<input id="comment_notification" type="checkbox" <?php echo $comment_notificationflag ?>> <label> Idea Comments notification</label>
					</div>
		        <div>
			        <input id='user_notification_save' type="button" value="Save">
		        </div>
					</div>
					<?php
				}
					?>
			<?PHP
			}
			else{
			?>
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
		            </div>  <?php } ?>
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
