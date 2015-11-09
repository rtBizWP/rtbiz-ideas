jQuery(document).ready( function ( $) {

	jQuery.fn.highlight = function (pat) {
		function innerHighlight(node, pat) {
			var skip = 0;
			if (node.nodeType == 3) {
				var pos = node.data.toUpperCase().indexOf(pat);
				if (pos >= 0) {
					var spannode = document.createElement('span');
					spannode.className = 'highlight';
					var middlebit = node.splitText(pos);
					var endbit = middlebit.splitText(pat.length);
					var middleclone = middlebit.cloneNode(true);
					spannode.appendChild(middleclone);
					middlebit.parentNode.replaceChild(spannode, middlebit);
					skip = 1;
				}
			}
			else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
				for (var i = 0; i < node.childNodes.length; ++i) {
					i += innerHighlight(node.childNodes[i], pat);
				}
			}
			return skip;
		}

		return this.each(function () {
			innerHighlight(this, pat.toUpperCase());
		});
	};

	var rtbizIdeasPublic = {
		init: function () {
			rtbizIdeasPublic.toggleIdeasForm();
			rtbizIdeasPublic.voteIdeas();
			rtbizIdeasPublic.searchIdeas();
			rtbizIdeasPublic.loadMoreIdeas();
			rtbizIdeasPublic.subscribeIdeas();
			rtbizIdeasPublic.saveIdeasEmailNotification();
			rtbizIdeasPublic.cancelNewIdeas();
			rtbizIdeasPublic.addNewIdeas();
		},
		toggleIdeasForm: function(){
			$(document ).on('click','a[href="#Idea-new"]',function (e){
				e.preventDefault();
				jQuery('#wpideas-insert-idea' ).slideToggle('slow');
			});
		},
		voteIdeas: function(){
			$(document).on('click', '.btnVote', function ( e ) {
				e.preventDefault();
				$(this).attr('disabled', 'disabled');

				var requestArray = {};
				requestArray.action = 'rtbiz_ideas_vote';
				requestArray.postid = $(this).data('id');
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: requestArray,
					success: function (data) {
						if (data.vote) {
							$( '#rtwpIdeaVoteCount-' + requestArray.postid ).html(data.vote);
							$( '#btnVote-' + requestArray.postid ).removeAttr('disabled');
							if ( $( '#btnVote-' + requestArray.postid).is( 'a' ) ){
								$( '#btnVote-' + requestArray.postid ).text( data.btnLabel );
							}else{
								$( '#btnVote-' + requestArray.postid ).attr('value', data.btnLabel);
							}
						} else {
							alert(data.err);
							$( '#btnVote-' + requestArray.postid ).removeAttr('disabled');
						}
					},
					error: function (xhr, textStatus, errorThrown) {
						$( '#btnVote-' + requestArray.postid ).removeAttr('disabled');
					}
				});
			});
		},
		searchIdeas: function(){
			$('#txtSearchIdea').keyup( function () {
				var requestArray = {};
				requestArray.action = 'rtbiz_ideas_search';
				requestArray.searchtext = $(this).val();
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'text',
					data: requestArray,
					success: function ( data ) {
						$( '#rtbiz-ideas-loop-common' ).html( data );

						// highlight the new term
						if ( requestArray.searchtext ) {
							$('.rtbiz-idea-title').highlight( requestArray.searchtext );
							$('.rtbiz-idea-description').highlight( requestArray.searchtext );
						}
					},
					error: function (xhr, textStatus, errorThrown) {

					}
				});
			});
		},
		loadMoreIdeas: function(){
			$(document).on('click', '#ideaLoadMore', function (e) {

				jQuery('#ideaLoadMore').hide();
				jQuery('#ideaLoading').show();

				var requestArray = {};
				requestArray.action = 'rtbiz_ideas_load_more';
				requestArray.offset = $('#rtbiz-ideas-loop-common article').length;
				requestArray.nonce = jQuery('#ideaLoadMore').attr('data-nonce');
				requestArray.post_type = rtbiz_ideas_posttype;
				requestArray.product_id = $('#idea_product_id').val();
				requestArray.postparpage = jQuery('#idea_post_per_page').val();
				requestArray.idea_order = jQuery('#idea_order').val();
				requestArray.idea_orderby = jQuery('#idea_order_by').val();
				requestArray.processData = false;
				requestArray.contentType = false;
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: requestArray,
					success: function ( data ) {
						if ( data.have_posts ) {
							var $newElems = $( data.html.replace(/(\r\n|\n|\r)/gm, '') );
							$('.rtbiz-ideas-loadmore').before( $newElems );
							jQuery('#ideaLoadMore').show();
						} else {
							$('#ideaLoadMore').hide();
						}
						jQuery('#ideaLoading').hide();
					},
					error: function (xhr, textStatus, errorThrown) {
					}
				});
			});
		},
		subscribeIdeas: function(){
			$(document).on('click', '.subscribe_email_notification_button', function (e) {
				e.preventDefault();
				var ele_id = jQuery(this ).attr('id');
				var requestArray = {};
				requestArray.action = 'rtbiz_ideas_subscribe_button';
				requestArray.post_id = $(this ).data( 'id' );
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: requestArray,
					success: function ( data ) {
						if ( data.status ) {
							if(jQuery('a[id=' + ele_id + ']' ).length){
								jQuery( '#'+ele_id ).text( data.btntxt );
							} else if( jQuery('input[id= ' + ele_id + ']' ).length){
								jQuery( '#'+ele_id ).attr('value',data.btntxt);
								jQuery( '#'+ele_id  ).toggleClass("button-unsubscribe button-subscribe");
							}
						}
					},
					error: function (xhr, textStatus, errorThrown) {

					}
				});
			});
		},
		saveIdeasEmailNotification: function(){

			var statusEmailNotification= jQuery('#status_change_notification').is(':checked');
			var commentEmailNotification= jQuery('#comment_notification').is(':checked');

			jQuery('#user_notification_save' ).click( function () {

				var requestArray = {};

				var newsStatusEmailNotification = jQuery('#status_change_notification').is(':checked');
				var newCommentEmailNotification = jQuery('#comment_notification').is(':checked');

				requestArray.action = 'rtbiz_ideas_subscribe_notification_setting';
				if ( newsStatusEmailNotification != statusEmailNotification ){
					requestArray.status_change_notification =  ( newsStatusEmailNotification ) ? 'YES' : 'NO';
				}
				if( newCommentEmailNotification != commentEmailNotification ){
					requestArray.comment_notification =  ( newCommentEmailNotification ) ? 'YES' : 'NO';
				}

				if ( newsStatusEmailNotification != statusEmailNotification || newCommentEmailNotification != commentEmailNotification ) {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						dataType: 'json',
						data: requestArray,
						success: function ( data ) {
							if ( data.status ) {
								if ( data.status ) {
									jQuery( '#Notificationstatus' ).show();
								}
							}
						},
						error: function (xhr, textStatus, errorThrown) {

						}
					});
				}
			});
		},
		cancelNewIdeas: function(){
			$('#insertIdeaFormCancel').click(function(){
				$('#wpideas-insert-idea' ).slideToggle('slow');
			});
		},
		addNewIdeas: function(){
			$('#btninsertIdeaFormSubmit').click(function(e) {
				e.preventDefault();
				var requestArray = new FormData();

				requestArray.append("action", 'rtbiz_ideas_insert_new_idea');
				requestArray.append( "txtIdeaTitle", $('#txtIdeaTitle').val() );

				var editor = '';
				if ( rtbizIdeasPublic.isTinyMCEActive() ){
					editor= tinyMCE.get('txtIdeaContent');
				}
				var content= ( editor ) ? editor.getContent() : $('#txtIdeaContent').val() ;
				requestArray.append( "txtIdeaContent", content );


				var product_id = $('#product_id').val();
				if ( product_id ){
					requestArray.append( "product_id", product_id );
				}

				var category_id = $('#category_id').val();
				if ( category_id && category_id != -1 ){
					requestArray.append( "category_id", category_id );
				}

				requestArray.append( "product", $('#product_page').val() );

				var files = document.getElementById('file').files;
				for (var i = 0; i < files.length; i++) {
					var file = files[i];
					// Add the file to the request.
					requestArray.append('upload[]', file, file.name);
				}

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: requestArray,
					processData: false,
					contentType: false,
					beforeSend: function(xhr) {
						$('#txtIdeaTitle').attr('disable', 'disable');
						$('#txtIdeaContent').attr('disable', 'disable');
						$('#txtIdeaProduct').attr('disable', 'disable');
						$('#file').attr('disable', 'disable');
						$('#ideaLoading').show();
					},
					success: function ( data ) {
						try {
							data = JSON.parse(data);
							if (data.title) {
								$('#txtIdeaTitleError').html(data.title);
								$('#txtIdeaTitleError').show();

							} else {
								$('#txtIdeaTitleError').hide();
							}
							if (data.content) {
								$('#txtIdeaContentError').html(data.content);
								$('#txtIdeaContentError').show();
							} else {
								$('#txtIdeaContentError').hide();
							}
							if (data.product) {
								$('#txtIdeaProductError').html(data.product);
								$('#txtIdeaProductError').show();
							} else {
								$('#txtIdeaProductError').hide();
							}
							$( "body, html" ).animate( {
								scrollTop: jQuery( '#wpideas-insert-idea' ).offset().top - 20
							}, 600 );
						} catch (e) {
							if ( data == 'product' ) {
								rtbizIdeasPublic.listIdeasPost( product_id);
							} else {
								rtbizIdeasPublic.searchIdeaCallback();
							}

							$('#wpideas-insert-idea' ).slideToggle('slow');

							$('#txtIdeaTitleError').hide();
							$('#txtIdeaContentError').hide();
							$('#txtIdeaProductError').hide();

							$('#txtIdeaTitle').val("");
							if ( editor ){
								editor.setContent('');
							}else{
								$('#txtIdeaContent').val("");
							}
							$('#file').val("");

							$('#product_id option:first-child').attr("selected", "selected");
							$('#category_id option:first-child').attr("selected", "selected");

							$('#lblIdeaSuccess').show();
							$('#lblIdeaSuccess').fadeOut(5000);

							$( "body, html" ).animate( {
								scrollTop: jQuery( '#lblIdeaSuccess' ).offset().top - 50
							}, 600 );
						}

						$('#txtIdeaTitle').removeAttr('disabled');
						$('#txtIdeaContent').removeAttr('disabled');
						$('#txtIdeaProduct').removeAttr('disabled');
						$('#file').removeAttr('disabled');

						$('#ideaLoading').hide();
					},
					error: function (xhr, textStatus, errorThrown) {

					}
				});
			});
		},
		isTinyMCEActive: function(){
			if (typeof(tinyMCE) != "undefined") {
				if (tinyMCE.activeEditor !== null && tinyMCE.activeEditor.isHidden() === false) {
					return true;
				}
			}
			return false;
		},
		listIdeasPost: function( product_id ) {
			var requestArray = {};
			requestArray.action = 'rtbiz_ideas_list_refresh';
			requestArray.product_id = product_id;
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'text',
				data: requestArray,
				success: function ( data ) {
					$('#rtbiz-ideas-loop-common').html(data);
					if ( $('#tab-ideas_tab' ).val() ) {
						$( "body, html" ).animate( {
							scrollTop: jQuery( '#tab-ideas_tab' ).offset().top
						}, 600 );
					}
				},
				error: function (xhr, textStatus, errorThrown) {

				}
			});
		},
		searchIdeaCallback: function(){
			var requestArray = {};
			requestArray.action = 'rtbiz_ideas_search';
			requestArray.searchtext = '';
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'text',
				data: requestArray,
				success: function ( data ) {
					jQuery('#rtbiz-ideas-loop-common').html(data);
				},
				error: function (xhr, textStatus, errorThrown) {

				}
			});
		}
	};

	rtbizIdeasPublic.init();
});

