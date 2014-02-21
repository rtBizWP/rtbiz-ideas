/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {

	$('.btnVote').click(function() {
		$(this).attr('disabled', 'disabled');
		var data = {
			action: 'vote',
			postid: $(this).data('id'),
		};

		$.post(rt_wpideas_ajax_url, data, function(response) {

			var json = JSON.parse(response);
			if (json.vote) {
				$('#rtwpIdeaVoteCount-' + data['postid']).html(json.vote);
				$('#btnVote-' + data['postid']).removeAttr('disabled');
				$('#btnVote-' + data['postid']).html(json.btnLabel);
			} else {
				alert(json.err);
				//$('#btnVote-' + data['postid']).hide();
				//$('#btnLogin').show();
			}
		});
	});

	jQuery('#btnLogin').click(function() {
		window.location.href = '/wp-login.php';
	});

	jQuery('#txtNewIdea').focus(function() {
		jQuery('#primaryPostForm').show();
	});
	jQuery('#cancelAdd').click(function() {
		jQuery('#primaryPostForm').hide();
	});

	jQuery('#txtNewIdea').keyup(function() {
		jQuery('#postTitle').val($(this).val());
		
		var data = {
			action: 'search',
			searchtext: $(this).val(),
		};
		jQuery.post(rt_wpideas_ajax_url, data, function(response) {
		});
	});

	jQuery("#primaryPostForm").validate();

});
