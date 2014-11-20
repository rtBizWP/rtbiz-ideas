<?php
/**
 * Insert idea
 */
?>
<script>
	jQuery(document).ready(function($) {
		var post_id;
		if(! $('#rt_post_id').val()) {
			jQuery('#product_id').parent().show();
		}

		jQuery('#insertIdeaFormCancel').click(function(){
//			jQuery('#TB_closeWindowButton').click();
			jQuery('#wpideas-insert-idea' ).slideToggle('slow');

		});
		jQuery('#btninsertIdeaFormSubmit').click(function(e) {
			e.preventDefault();
			data = new FormData();
			data.append("action", 'wpideas_insert_new_idea');
			data.append("txtIdeaTitle", $('#txtIdeaTitle').val());
			var editor = null;
			is_tinyMCE_active = false;
			if (typeof(tinyMCE) != "undefined") {
				if (tinyMCE.activeEditor == null || tinyMCE.activeEditor.isHidden() != false) {
					is_tinyMCE_active = true;
				}
			}
			if(is_tinyMCE_active){
				editor= tinyMCE.get('txtIdeaContent');
			}
			var content='';
			if (editor){
				content = editor.getContent();
			} else {
				content = $('#txtIdeaContent').val();
			}
			data.append("txtIdeaContent", content);
			if ($('#product_id').val()){
				post_id=$('#product_id').val();
				data.append("product_id", $('#product_id').val());
			}
			else if($('#rt_post_id').val()){
				post_id=$('#rt_post_id').val();
				data.append("product_id", $('#rt_post_id').val());
			}
//			data.append("product_id", $('#product_id').val());
			data.append("product", $('#product_page').val());
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
							list_ideas_post(post_id);
						}else {
                            search_idea();
                        }
						$('#wpideas-insert-idea' ).slideToggle('slow');
						$('#lblIdeaSuccess').show();
                        $('#lblIdeaSuccess').fadeOut(2000);
						$('#txtIdeaTitleError').hide();
						$('#txtIdeaContentError').hide();
						$('#txtIdeaProductError').hide();
						$('#txtIdeaTitle').val("");
						var editor = null;
						is_tinyMCE_active = false;
						if (typeof(tinyMCE) != "undefined") {
							if (tinyMCE.activeEditor == null || tinyMCE.activeEditor.isHidden() != false) {
								is_tinyMCE_active = true;
							}
						}
						if(is_tinyMCE_active){
							editor= tinyMCE.get('txtIdeaContent');
						}
						if (editor){
							editor.setContent('');
						}else{
							$('#txtIdeaContent').val("");
						}
						$('#file').val("");
					}

					$('#txtIdeaTitle').removeAttr('disabled');
					$('#txtIdeaContent').removeAttr('disabled');
					$('#txtIdeaProduct').removeAttr('disabled');
					$('#file').removeAttr('disabled');
					$('#ideaLoading').hide();
				}
			});
		});
	});
	function list_ideas_post(product_id) {
		var data = {
			action: 'list_ideas_refresh',
			product_id: product_id,
		}
		jQuery.post(rt_wpideas_ajax_url, data, function(res) {
			jQuery('#wpidea-content-wrapper').html(res);
			if (jQuery('#tab-ideas_tab' ).val()) {
				jQuery( "body, html" ).animate( {
					                                scrollTop: jQuery( '#tab-ideas_tab' ).offset().top
				                                }, 600 );
			}
		});
	}
    function search_idea(){
        var data = {
            action: 'wpideas_search',
            searchtext: ''
        };

        jQuery.post(rt_wpideas_ajax_url, data, function (response) {
            jQuery('#loop-common').html(response);
        });
    }

</script>
<form id="insertIdeaForm" method="post" enctype="multipart/form-data" action="">
	<h2>Suggest New Idea</h2>
	<div>
		<label for="txtIdeaTitle"><?php _e( 'Title:', 'wp-ideas' ) ?></label>

		<input type="text" name="txtIdeaTitle" id="txtIdeaTitle" class="required" value="<?php if ( isset( $_POST[ 'txtIdeaTitle' ] ) ) echo $_POST[ 'txtIdeaTitle' ]; ?>" />

		<label class="error" id="txtIdeaTitleError" style="display:none;"></label>

	</div>

	<div>
		<label for="txtIdeaContent"><?php _e( 'Detail:', 'wp-ideas' ) ?></label>

		<?php if ( is_editor_enable() ) {
				if ( isset( $_POST[ 'txtIdeaContent' ] ) ) {
					if ( function_exists( 'stripslashes' ) ) {
						$content = stripslashes( $_POST[ 'txtIdeaContent' ] );
					} else {
						$content = $_POST[ 'txtIdeaContent' ];
					}
				} else {
					$content = '';
				}
				$editor_id = 'txtIdeaContent';
				$settings = array( 'media_buttons' => false, 'editor_class' => 'required');
				
				wp_editor( $content, $editor_id, $settings );
		} else {
		?>
		<textarea name="txtIdeaContent" id="txtIdeaContent" style="height:250px;" class="required"><?php
			if ( isset( $_POST[ 'txtIdeaContent' ] ) ) {
				if ( function_exists( 'stripslashes' ) ) {
					echo stripslashes( $_POST[ 'txtIdeaContent' ] );
				} else {
					echo $_POST[ 'txtIdeaContent' ];
				}
			}
			?></textarea>
		<?php } ?>
		<label class="error" id="txtIdeaContentError" style="display:none;"></label>
	</div>
	<?php
	$terms = get_terms('rt_product');
if (!empty($terms) && is_array($terms)){
		?>
		<div style="display: none">
			<select class="required" id="product_id" name="product_id">
				<option value=""> Select Product </option>
                <?php
				foreach ($terms as $term){
						echo '<option value="' . $term->term_id . '" >' . $term->name . '</option>';
                }
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
			?>" /><input type="hidden" id="product_page" name="product_page" value="product_page" />
			   <?php } ?>
		<input type="hidden" name="submitted" id="submitted" value="true" />
		<?php wp_nonce_field( 'idea_nonce', 'idea_nonce_field' ); ?>
		<input type="button" id="btninsertIdeaFormSubmit" value="<?php _e( 'Submit My Idea', 'wp-ideas' ) ?>" />
		<img src="<?php echo RTBIZ_IDEAS_URL . 'app/assets/img/indicator.gif'; ?>" id="ideaLoading" style="display:none;height: 50px;" />
		<a href="javascript:;" id="insertIdeaFormCancel">Cancel</a>
	</div>
</form>