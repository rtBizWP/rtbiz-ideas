/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function ($) {

	var ideastatus= jQuery('#status_change_notification').is(':checked');
	var commentstatus= jQuery('#comment_notification').is(':checked');

	jQuery('a[href="#Idea-new"]' ).click(function (){
		jQuery('#wpideas-insert-idea' ).slideToggle('slow');
	});
	jQuery('.btnVote').live('click', function () {
        $(this).attr('disabled', 'disabled');
        var data = {
            action: 'vote',
            postid: $(this).data('id'),
        };

        $.post(rt_wpideas_ajax_url, data, function (response) {
            var json = JSON.parse(response);
            if (json.vote) {
                $('#rtwpIdeaVoteCount-' + data['postid']).html(json.vote);
                $('#btnVote-' + data['postid']).removeAttr('disabled');
                $('#btnVote-' + data['postid']).attr('value', json.btnLabel);
            } else {
                alert(json.err);
                $('#btnVote-' + data['postid']).removeAttr('disabled');
            }
        });
    });

    jQuery('#txtSearchIdea').keyup(function () {
        if (jQuery.trim($(this).val()) != '') {
            var data = {
                action: 'wpideas_search',
                searchtext: $(this).val(),
            };

            $.post(rt_wpideas_ajax_url, data, function (response) {
                $('#loop-common').html(response);
                // pull in the new value
                var searchTerm = data['searchtext'];
                // remove any old highlighted terms
                $('.rtwpIdeaTitle').removeHighlight();
                $('.rtwpIdeaDescription').removeHighlight();
                // disable highlighting if empty
                if (searchTerm) {
                    // highlight the new term
                    $('.rtwpIdeaTitle').highlight(searchTerm);
                    $('.rtwpIdeaDescription').highlight(searchTerm);
                }
            });
        }

    });

    jQuery('#ideaLoadMore').live('click', function (e) {
        jQuery('#ideaLoadMore').hide();
        jQuery('#ideaLoading').show();
        var post_type = 'idea'; // this is optional and can be set from anywhere, stored in mockup etc...
        var offset = $('#wpidea-content article').length;
        var product_id = $('#idea_product_id').val();
        var nonce = jQuery('#ideaLoadMore').attr('data-nonce');
        var data = {
            action: "list_ideas_load_more",
            offset: offset,
            nonce: nonce,
            post_type: post_type,
            product_id: product_id,
            processData: false,
            contentType: false,
        }
        jQuery.ajax({
            type: "post",
            context: this,
            dataType: "html",
            url: rt_wpideas_ajax_url,
            data: data,
            success: function (response) {
                jQuery('#ideaLoadMore').show();
                jQuery('#ideaLoading').hide();
                response = JSON.parse(response);
                if (response.have_posts) {//if have posts:
                    var $newElems = $(response['html'].replace(/(\r\n|\n|\r)/gm, ''));
                    $('#wpidea-content').append($newElems);
                } else {
                    $('#ideaLoadMore').hide();
                }
            }
        });
    });


	jQuery('.subscribe_email_notification_button' ).click( function (){
		var request = {};
		var id = jQuery(this ).attr('id');
		request['action']='subscribe_button';
		request['post_id']=jQuery(this ).data().id;
		console.log(request);
		jQuery.ajax( {
			             type: "post", //context: this,
			             dataType: "json",
			             url: rt_wpideas_ajax_url,
			             data: request,
			             success: function ( response ) {
				             if ( response.status ) {
					             if(jQuery('a[id='+id+']' ).length){
						             jQuery( '#'+id ).text(response.btntxt);
					             }
					             else if(jQuery('input[id= '+id+'] ').length){
						             jQuery( '#'+id ).attr('value',response.btntxt);
						             jQuery( '#'+id  ).toggleClass("button-unsubscribe button-subscribe");
					             }
				             }
			             }
		             } );
	});

	jQuery('#user_notification_save' ).click( function () {
		var request = {};
		var newstatus = jQuery('#status_change_notification').is(':checked');
		var comment = jQuery('#comment_notification').is(':checked');
		request['action']='subscribe_notification_setting';
		if ( newstatus != ideastatus ){
			if( newstatus ){
				request['status_change_notification']='YES'
			}
			else{
				request['status_change_notification']='NO'
			}

		}
		if( comment != commentstatus ){
			if(comment){
				request['comment_notification']= 'YES'
			}
			else{
				request['comment_notification']= 'NO'
			}
		}
		if (newstatus != ideastatus || comment != commentstatus ) {
			jQuery.ajax( {
				             type: "post", //context: this,
				             dataType: "json", url: rt_wpideas_ajax_url,
				             data: request,
				             success: function ( response ) {
									console.log( response );
									if ( response.status ) {
										jQuery( '#Notificationstatus' ).show();
									}
								}
			             } );
		}
	});


});

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

jQuery.fn.removeHighlight = function () {
    function newNormalize(node) {
        for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
            var child = children[i];
            if (child.nodeType == 1) {
                newNormalize(child);
                continue;
            }
            if (child.nodeType != 3) {
                continue;
            }
            var next = child.nextSibling;
            if (next == null || next.nodeType != 3) {
                continue;
            }
            var combined_text = child.nodeValue + next.nodeValue;
            new_node = node.ownerDocument.createTextNode(combined_text);
            node.insertBefore(new_node, child);
            node.removeChild(child);
            node.removeChild(next);
            i--;
            nodeCount--;
        }
    }

    return this.find("span.highlight").each(function () {
        var thisParent = this.parentNode;
        thisParent.replaceChild(this.firstChild, this);
        newNormalize(thisParent);
    }).end();
};
