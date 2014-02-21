<?php
wp_register_script( 'validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', array( 'jquery' ) );
wp_enqueue_script( 'validation' );

$ideaTitleError = '';

?>
<form action="" id="insertIdeaForm" method="POST" enctype="multipart/form-data">
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

		<textarea name="txtIdeaContent" id="postContent" rows="30" cols="30" class="required"><?php
			if ( isset( $_POST[ 'txtIdeaContent' ] ) ) {
				if ( function_exists( 'stripslashes' ) ) {
					echo stripslashes( $_POST[ 'txtIdeaContent' ] );
				} else {
					echo $_POST[ 'txtIdeaContent' ];
				}
			}
			?></textarea>
	</div>

	<div>
		<input type="file" name="file1" id="file1" >
	</div>

	<div>
		<input type="hidden" name="submitted" id="submitted" value="true" />
		<input type="hidden" name="action" value="insert_new_idea" />
		<?php wp_nonce_field( 'idea_nonce', 'idea_nonce_field' ); ?>
		<button type="submit"><?php _e( 'Submit My Idea', 'wp-ideas' ) ?></button>
	</div>

</form>


