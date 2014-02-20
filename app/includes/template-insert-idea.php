<?php
wp_register_script('validation', 'http://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', array('jquery'));
wp_enqueue_script('validation');

$postTitleError = '';

if (isset($_POST['submitted']) && isset($_POST['post_nonce_field']) && wp_verify_nonce($_POST['post_nonce_field'], 'post_nonce')) {

	if (trim($_POST['postTitle']) === '') {
		$postTitleError = 'Please enter a title.';
		$hasError = true;
	}
	$post_information = array(
	    'post_title' => wp_strip_all_tags($_POST['postTitle']),
	    'post_content' => $_POST['postContent'],
	    'post_type' => 'idea',
	    'post_status' => 'publish'
	);

	$post_id = wp_insert_post($post_information);

	if ( isset( $post_id ) ) {
		echo '<script>alert("Your idea is published.");window.location.reload();</script>';
		wp_redirect(home_url());
	}
}
?>
<form action="" id="primaryPostForm" method="POST" style="display:none;">
	<h2>Or add new idea here..</h2>
	<fieldset>
		<label for="postTitle"><?php _e('Post Title:', 'framework') ?></label>

		<input type="text" name="postTitle" id="postTitle" class="required" value="<?php if (isset($_POST['postTitle'])) echo $_POST['postTitle']; ?>" />
		<?php if ($postTitleError != '') { ?>
			<span class="error"><?php echo $postTitleError; ?></span>
			<div class="clearfix"></div>
		<?php } ?>
	</fieldset>

	<fieldset>
		<label for="postContent"><?php _e('Post Content:', 'framework') ?></label>

		<textarea name="postContent" id="postContent" rows="8" cols="30" class="required"><?php
			if (isset($_POST['postContent'])) {
				if (function_exists('stripslashes')) {
					echo stripslashes($_POST['postContent']);
				} else {
					echo $_POST['postContent'];
				}
			}
			?></textarea>
	</fieldset>

	<fieldset>
		<input type="hidden" name="submitted" id="submitted" value="true" />
		<?php wp_nonce_field('post_nonce', 'post_nonce_field'); ?>
		<button type="submit"><?php _e('Add Post', 'framework') ?></button>
		<button type="button" id="cancelAdd"><?php _e('Cancel','wpideas'); ?></button>
	</fieldset>

</form>


