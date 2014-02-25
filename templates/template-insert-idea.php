<?php
$ideaTitleError = '';
$ajax_url = admin_url( 'admin-ajax.php' );
?>
<script>
	jQuery("#insertIdeaForm").validate();
</script>
<form action="<?php echo esc_url( $ajax_url ); ?>" id="insertIdeaForm" method="POST" enctype="multipart/form-data">
	<h2>Suggest New Idea</h2>
	<div>
		<label for="txtIdeaTitle"><?php _e( 'Title:', 'wp-ideas' ) ?></label>

		<input type="text" name="txtIdeaTitle" id="txtIdeaTitle" class="required" value="<?php if ( isset( $_POST[ 'txtIdeaTitle' ] ) ) echo $_POST[ 'txtIdeaTitle' ]; ?>" />
		<?php if ( $ideaTitleError != '' ) { ?>
			<span class="error"><?php echo $ideaTitleError; ?></span>
			<div class="clearfix"></div>
		<?php } ?>
	</div>

	<div>
		<label for="txtIdeaContent"><?php _e( 'Detail:', 'wp-ideas' ) ?></label>

		<textarea name="txtIdeaContent" id="txtIdeaContent" style="height:250px;" class="required"><?php
			if ( isset( $_POST[ 'txtIdeaContent' ] ) ) {
				if ( function_exists( 'stripslashes' ) ) {
					echo stripslashes( $_POST[ 'txtIdeaContent' ] );
				} else {
					echo $_POST[ 'txtIdeaContent' ];
				}
			}
			?></textarea>
	</div>
	<?php
	if ( is_post_type_archive( RT_WPIDEAS_SLUG ) ) {
		?>
		<div>
			<select class="required" id="product_id" name="product_id">
				<option value=""> Select Product </option>
				<?php
				$args = array(
					'post_type' => 'product',
					'posts_per_page' => -1,
				);

				query_posts( $args );
				while ( have_posts() ) : the_post();
					echo '<option value="' . get_the_ID() . '">' . get_the_title() . '</option>';
				endwhile;
				?>
			</select> 
		</div>
		<?php
	}
	?>

	<div>
		<input type="file" name="files" id="file" multiple />
	</div>

	<div>
		<?php
		if ( function_exists( is_product() ) ) :
			?> <input type="hidden" name="product_id" value="<?php
			global $post;
			echo $post -> ID;
			?>" /> <?php
			   endif;
			   ?>
		<input type="hidden" name="submitted" id="submitted" value="true" />
		<input type="hidden" name="action" value="insert_new_idea" />
		<?php wp_nonce_field( 'idea_nonce', 'idea_nonce_field' ); ?>
		<input type="submit" id="btninsertIdeaFormSubmit" value="<?php _e( 'Submit My Idea', 'wp-ideas' ) ?>" />
		<a href="javascript:tb_remove();" id="insertIdeaFormCancel">Cancel</a>
	</div>

</form>


