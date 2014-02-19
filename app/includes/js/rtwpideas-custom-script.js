/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {

	$('.btnVote').click(function(){
		$(this).attr('disabled','disabled');
		var data = {
			action: 'vote',
			postid: $(this).data('id'),
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$.post(rt_wpideas_ajax_url, data, function(response) {
			
			var json = JSON.parse(response);
			if(json.vote){
				$('#rtwpIdeaVoteCount-'+data['postid']).html(json.vote);
				$('#btnVote-'+data['postid']).removeAttr('disabled');
				if( $('#btnVote-'+data['postid']).html() == 'Vote Down' ){
					$('#btnVote-'+data['postid']).html('Vote Up');
				}else{
					$('#btnVote-'+data['postid']).html('Vote Down');
				}
			}else{
				alert(json.err);
			}
		});
	});
	
});

