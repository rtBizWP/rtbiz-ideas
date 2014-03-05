<?php
/**
 * Insert idea
 */
wp_enqueue_script( 'jquery-form', array( 'jquery' ), false, true );
?>
<script>
	jQuery(document).ready(function($) {

		jQuery('#btninsertIdeaFormSubmit').click(function(e) {
			e.preventDefault();
			data = new FormData();
			data.append("action", 'wpideas_insert_new_idea');
			data.append("txtIdeaTitle", $('#txtIdeaTitle').val());
			data.append("txtIdeaContent", $('#txtIdeaContent').val());
			data.append("product_id", $('#product_id').val());
			data.append("product", $('#product').val());
			// Get the selected files from the input.
			var files = document.getElementById('file').files;
			// Loop through each of the selected files.
			for (var i = 0; i < files.length; i++) {
				var file = files[i];

				// Add the file to the request.
				data.append('upload[]', file, file.name);
			}
			//data.append("upload", $('#file').get(0).files[0]);
			$.ajax({
				url: rt_wpideas_ajax_url,
				type: 'POST',
				data: data,
				processData: false,
				contentType: false,
				beforeSend: function(xhr) {
					$('#txtIdeaTitle').attr('disable', 'disable');
					$('#txtIdeaContent').attr('disable', 'disable');
					$('#txtIdeaProduct').attr('disable', 'disable');
					$('#file').attr('disable', 'disable');
					$('#ideaLoading').show();
				},
				success: function(res) {
					try {
						var json = JSON.parse(res);

						if (json.title) {
							$('#txtIdeaTitleError').html(json.title);
							$('#txtIdeaTitleError').show();
						} else {
							$('#txtIdeaTitleError').hide();
						}
						if (json.content) {
							$('#txtIdeaContentError').html(json.content);
							$('#txtIdeaContentError').show();
						} else {
							$('#txtIdeaContentError').hide();
						}
						if (json.product) {
							$('#txtIdeaProductError').html(json.product);
							$('#txtIdeaProductError').show();
						} else {
							$('#txtIdeaProductError').hide();
						}
					}
					catch (e)
					{
						tb_remove();
						if (res === 'product') {
							woo_list_ideas_product($('#product_id').val());
						}
						$('#txtSearchIdea').val('');
						$('#txtSearchIdea').keyup();
						$('#lblIdeaSuccess').show();
					}
					jQuery('article:nth-child(1)').addClass('sticky');
					setTimeout(function() {
						jQuery('article:nth-child(1)').removeClass('sticky');
						$('#lblIdeaSuccess').hide();
					}, 5000);
					$('#txtIdeaTitle').removeAttr('disabled');
					$('#txtIdeaContent').removeAttr('disabled');
					$('#txtIdeaProduct').removeAttr('disabled');
					$('#file').removeAttr('disabled');
					$('#ideaLoading').hide();
				},
			});
		});
	});
	function woo_list_ideas_product(product_id) {
		var data = {
			action: 'list_woo_product_ideas_refresh',
			product_id: product_id,
		}
		jQuery.post(rt_wpideas_ajax_url, data, function(res) {
			jQuery('#wpidea-content').html(res);
			jQuery("body, html").animate({
				scrollTop: jQuery('#tab-ideas_tab').offset().top
			}, 600);
		});
	}

</script>
<form id="insertIdeaForm" method="post" enctype="multipart/form-data" action="">
	<h2>Suggest New Idea</h2>
	<div>
		<label for="txtIdeaTitle"><?php _e( 'Title:', 'wp-ideas' ) ?></label>

		<input type="text" name="txtIdeaTitle" id="txtIdeaTitle" class="required" value="<?php if ( isset( esc_attr( $_POST[ 'txtIdeaTitle' ] ) ) ) echo esc_attr( $_POST[ 'txtIdeaTitle' ] ); ?>" />

		<label class="error" id="txtIdeaTitleError" style="display:none;"></label>

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
		<label class="error" id="txtIdeaContentError" style="display:none;"></label>
	</div>
	<?php
	if ( get_post_type() != 'product' ) {
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
			<label class="error" id="txtIdeaProductError" style="display:none;"></label>
		</div>

		<?php
	}
	?>

	<div>
		<input type="file" name="upload[]" id="file" multiple />
	</div>

	<div>
		<?php if ( get_post_type() == 'product' && is_single() ) { ?>
			<input type="hidden" id="product_id" name="product_id" value="<?php
			global $post;
			echo $post -> ID;
			?>" /><input type="hidden" id="product" name="product" value="product" />
			   <?php } ?>
		<input type="hidden" name="submitted" id="submitted" value="true" />
		<?php wp_nonce_field( 'idea_nonce', 'idea_nonce_field' ); ?>
		<input type="button" id="btninsertIdeaFormSubmit" value="<?php _e( 'Submit My Idea', 'wp-ideas' ) ?>" />
		<img src="<?php echo RTWPIDEAS_URL . 'app/assets/img/indicator.gif'; ?>" id="ideaLoading" style="display:none;" />
		<a href="javascript:tb_remove();" id="insertIdeaFormCancel">Cancel</a>
	</div>
</form>