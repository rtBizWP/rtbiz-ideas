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
				$href= "#Idea-new";
				if (! is_user_logged_in()){
					$href = wp_login_url( home_url('/').RTBIZ_IDEAS_SLUG );
				}
				?>
				<a id="btnNewThickbox" href="<?php echo $href; ?>"> New Idea </a>
				</div>
            </header>
	        <?php if (is_user_logged_in()){ ?>
	        <div id="myideas"> <a href="<?php echo home_url().'/'.RTBIZ_IDEAS_SLUG ?>?tab=home"> My ideas </a>| <a href="<?php echo home_url().'/'.RTBIZ_IDEAS_SLUG ?>?tab=settings">My Settings</a></div>
			<?php } ?>
            <div class="success" id="lblIdeaSuccess" style="display:none;">Idea submitted</div>
	        <div id="wpideas-insert-idea" style="display:none;">
		        <?php
		        rtideas_get_template( 'template-insert-idea.php' );
		        ?>
	        </div>
			<?php if ( isset($_REQUEST['tab']) && is_user_logged_in() ){
				if ( $_REQUEST['tab'] == 'home' ){
					global $wpdb;
					$pagecounts= $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->posts LEFT JOIN ".$wpdb->prefix."rt_wpideas_subscriber ON (".$wpdb->posts.".ID = ".$wpdb->prefix."rt_wpideas_subscriber.post_id) where (".$wpdb->prefix."rt_wpideas_subscriber.user_id = %d OR ".$wpdb->posts.".post_author = %d) AND ".$wpdb->posts.".post_type = '".RTBIZ_IDEAS_SLUG ."' AND ".$wpdb->posts.".post_status <> 'auto-draft'", get_current_user_id(),get_current_user_id()));
					$limit = 20;
					$currentpage = 1;
					if (isset( $_REQUEST['page'])){
						$currentpage =(int)$_REQUEST['page'];
					}
					$offset= ($currentpage - 1)* $limit ;
					$pageposts= $wpdb->get_results($wpdb->prepare("SELECT DISTINCT $wpdb->posts.* FROM $wpdb->posts LEFT JOIN ".$wpdb->prefix."rt_wpideas_subscriber ON (".$wpdb->posts.".ID = ".$wpdb->prefix."rt_wpideas_subscriber.post_id) where (".$wpdb->prefix."rt_wpideas_subscriber.user_id = %d OR ".$wpdb->posts.".post_author = %d) AND ".$wpdb->posts.".post_type = '".RTBIZ_IDEAS_SLUG ."' AND ".$wpdb->posts.".post_status <> 'auto-draft' ORDER BY ".$wpdb->posts.".post_date DESC LIMIT $offset, $limit", get_current_user_id(),get_current_user_id()));
//					var_dump(printf("SELECT DISTINCT $wpdb->posts.* FROM $wpdb->posts LEFT JOIN ".$wpdb->prefix."rt_wpideas_subscriber ON (".$wpdb->posts.".ID = ".$wpdb->prefix."rt_wpideas_subscriber.post_id) where (".$wpdb->prefix."rt_wpideas_subscriber.user_id = %d OR ".$wpdb->posts.".post_author = %d) AND ".$wpdb->posts.".post_type = '".RTBIZ_IDEAS_SLUG ."' AND ".$wpdb->posts.".post_status <> 'auto-draft' ORDER BY ".$wpdb->posts.".post_date DESC LIMIT $offset, $limit", get_current_user_id(),get_current_user_id()));
			?>
					<div id="loop-common" class="idea-loop-common">
						<?php
						if ( $pageposts ) :
							global $post;
							?>
							<table class="myideasTable">
								<th>Title</th>
								<th>Author</th>
								<th>Vote Counts</th>
								<th>Action</th>
							<?php
							foreach ($pageposts as $post):
								setup_postdata($post);
								rtideas_get_template( 'table-common.php' );
							endforeach;
							?>
							</table>
							<div class="navigation">
								<?php
								if( $currentpage > 1 ) { ?>
								<div class="alignleft"><a href="<?php echo home_url().'/'.RTBIZ_IDEAS_SLUG ?>?tab=home&page=<?php echo $currentpage-1 ?> "> '&laquo; Previous Page </a></div>
							<?php }
								if ($currentpage != ceil($pagecounts/$limit) ){
								?>
								<div class="alignright"> <a href="<?php echo home_url().'/'.RTBIZ_IDEAS_SLUG ?>?tab=home&page=<?php echo $currentpage+1 ?> "> Next Page &raquo;</div></div>
									<?php } ?>
							</div>
							<?php
						else :
							$href= "#Idea-new";
							if ( is_user_logged_in() ) {
								echo 'Looks like we do not have any idea. <br /><br /> Be first one to suggest idea.&nbsp; <a id="btnOpenThickbox" href="'.$href.'" > Click Here </a> &nbsp;  to suggest.';

							} else {
								$href = wp_login_url( home_url('/').RTBIZ_IDEAS_SLUG );
								echo '<br/><a id="btnOpenThickbox" href="'.$href .'">Login to Suggest Idea</a>';
							}

						endif; ?>
					</div> <?PHP
					wp_reset_query();
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
<div id="email-notification-setting">
					<h3 id="idea-user-settings"> Email Notification Setting</h3>
					<div>
						<div id='Notificationstatus' class="highlight" style="display: none;">Setting Saved!</div>
					</div>
	        <div>
					<div class="idea-setting-checkbox">
					<input id="status_change_notification" type="checkbox" <?php echo $status_changeflag ?>> <label> Idea Status Change</label>
					</div>
					<div class="idea-setting-checkbox">
					<input id="comment_notification" type="checkbox" <?php echo $comment_notificationflag ?>> <label> Idea Comments notification</label>
					</div>
		        <div>
			        <input id='user_notification_save' type="button" value="Save">
		        </div>
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
			                    $href= "#Idea-new";
			                    if ( is_user_logged_in() ) {
				                    echo 'Looks like we do not have any idea. <br /><br /> Be first one to suggest idea.&nbsp; <a id="btnOpenThickbox" href="'.$href.'" > Click Here </a> &nbsp;  to suggest.';
			                    } else {
				                    $href = wp_login_url( home_url('/').RTBIZ_IDEAS_SLUG );
				                    echo '<br/><a id="btnOpenThickbox" href="'.$href .'">Login to Suggest Idea</a>';
			                    }
		                    endif; ?>
		            </div>  <?php } ?>
		        </div>


        <!-- #content -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer();
