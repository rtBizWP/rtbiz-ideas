<?php
/**
 * Insert idea
 */
?>
<form id="insertIdeaForm" class="rtbiz-ideas-new-idea" method="post" enctype="multipart/form-data" action="">
	<h2 class="rtbiz-idea-title"><?php
		_e( 'Suggest New Idea', RTBIZ_IDEAS_TEXT_DOMAIN ); ?>
	</h2>

	<div class="rtbiz-idea-row">
		<label for="txtIdeaTitle"><?php _e( 'Title:', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></label>
		<input type="text" name="txtIdeaTitle" id="txtIdeaTitle" class="required" value="<?php
		if ( isset( $_POST['txtIdeaTitle'] ) ) {
			echo $_POST['txtIdeaTitle'];
		} ?>" />
		<span class="rtbiz-ideas-error rtbiz-ideas-hide" id="txtIdeaTitleError"></span>
	</div>

	<div class="rtbiz-idea-row">
		<label for="txtIdeaContent"><?php _e( 'Description:', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></label><?php
		$content = '';
		if ( ! empty( $_POST['txtIdeaContent'] ) ) {
			$content = ( function_exists( 'stripslashes' ) ) ? stripslashes( $_POST['txtIdeaContent'] ) : $_POST['txtIdeaContent'];
		}
		if ( rtbiz_ideas_is_editor_enable() ) {
			$editor_id = 'txtIdeaContent';
			$settings = array( 'media_buttons' => false, 'editor_class' => 'required', );
			wp_editor( $content, $editor_id, $settings );
		} else { ?>
			<textarea name="txtIdeaContent" id="txtIdeaContent" class="required"><?php echo $content;?></textarea> <?php
		}?>
		<span class="rtbiz-ideas-error rtbiz-ideas-hide" id="txtIdeaContentError"></span>
	</div><?php

	global $post;
	$product_termid = '';
	if ( ! empty( $post->post_type ) && in_array( $post->post_type , array( 'product' ) ) && is_single() && ! empty( $post->ID ) ) {
		$product_termid = rtbiz_ideas_get_product_taxonomy_id( $post-> ID );
	}
	if ( ! empty( $product_termid ) ) { ?>
		<input type="hidden" id="product_id" name="product_id" value="<?php echo $product_termid;?>" />
		<input type="hidden" id="product_page" name="product_page" value="product_page" /><?php
	} else {
		$terms = get_terms( Rt_Products::$product_slug );
		if ( ! empty( $terms ) ) {?>
			<div class="rtbiz-idea-row">
				<select class="required" id="tax_product_id" name="tax_product_id">
					<option value=""><?php _e( 'Select Product', RTBIZ_IDEAS_TEXT_DOMAIN ) ?></option><?php
					foreach ( $terms as $term ) {
						echo '<option value="' . $term->term_id . '" >' . $term->name . '</option>';
					}?>
				</select>
				<label class="rtbiz-ideas-error rtbiz-ideas-hide" id="txtIdeaProductError"></label>
			</div><?php
		}
	}?>

	<div class="rtbiz-idea-row">
		<input type="file" name="upload[]" id="file" multiple />
	</div>

	<div class="rtbiz-idea-row rtbiz-idea-action">
		<input type="hidden" name="submitted" id="submitted" value="true" />
		<?php wp_nonce_field( 'idea_nonce', 'idea_nonce_field' ); ?>
		<input type="button" id="btninsertIdeaFormSubmit" value="<?php _e( 'Submit My Idea', 'wp-ideas' ) ?>" />
		<img src="<?php echo RTBIZ_IDEAS_URL . 'public/img/indicator.gif'; ?>" id="ideaLoading" style="display:none;height: 50px;" />
		<a class="button" href="javascript:;" id="insertIdeaFormCancel">Cancel</a>
	</div>
</form>
