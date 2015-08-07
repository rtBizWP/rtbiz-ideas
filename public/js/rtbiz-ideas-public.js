(function( $ ) {
	'use strict';
	$(document).ready( function () {

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
			},
			toggleIdeasForm: function(){
				$('a[href="#Idea-new"]' ).click(function (){
					jQuery('#wpideas-insert-idea' ).slideToggle('slow');
				});
			},
			voteIdeas: function(){
				$('.btnVote').on('click', function () {
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
								$( '#btnVote-' + requestArray.postid ).attr('value', data.btnLabel);
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
							$( '#loop-common' ).html( data );

							// highlight the new term
							if ( requestArray.searchtext ) {
								$('.rtwpIdeaTitle').highlight( requestArray.searchtext );
								$('.rtwpIdeaDescription').highlight( requestArray.searchtext );
							}
						},
						error: function (xhr, textStatus, errorThrown) {

						}
					});
				});
			},
			loadMoreIdeas: function(){
				$('#ideaLoadMore').on('click', function (e) {

					jQuery('#ideaLoadMore').hide();
					jQuery('#ideaLoading').show();

					var requestArray = {};
					requestArray.action = 'rtbiz_ideas_load_more';
					requestArray.offset = $('#wpidea-content article').length;
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
								$('#wpidea-content').append( $newElems );
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
				$('.subscribe_email_notification_button').on('click', function (e) {

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
			}
		};

		rtbizIdeasPublic.init();
	});
})( jQuery );
