/**
 * Created by spock on 17/10/14.
 */


function activate_rt_idea_plugin( path, action, rt_idea_nonce ) {
	jQuery('.rt-idea-not-installed-error').removeClass('error');
	jQuery('.rt-idea-not-installed-error').addClass('updated');
	jQuery('.rt-idea-not-installed-error p').html('<b>rtBiz Idea  :</b> ' + path + ' will be activated. Please wait. <div class="spinner"> </div>');
	jQuery("div.spinner").show();
	var param = {
		action: action,
		path: path,
		_ajax_nonce: rt_idea_nonce
	};
	jQuery.post( rt_idea_ajax_url, param,function(data){
		data = data.trim();
        console.log(data);
		if(data == "true") {
			jQuery('.rt-idea-not-installed-error p').html('<b>rtBiz Idea  :</b> ' + path + ' activated.');
			location.reload();
		} else {
			jQuery('.rt-idea-not-installed-error p').html('<b>rtBiz Idea  :</b> There is some problem. Please try again.');
		}
	});
}
function install_rt_idea_plugin( plugin_slug,action,rtm_nonce ) {
	jQuery('.rt-idea-not-installed-error').removeClass('error');
	jQuery('.rt-idea-not-installed-error').addClass('updated');
	jQuery('.rt-idea-not-installed-error p').html('<b>rtBiz Idea  :</b> ' + plugin_slug + ' will be installed and activated. Please wait...<div class="spinner"> </div>');
	jQuery('div.spinner').show();
	var param = {
		action: action,
		plugin_slug: plugin_slug,
		_ajax_nonce: rtm_nonce
	};
	jQuery.post( rt_idea_ajax_url, param,function( data ){
		data = data.trim();
		if(data === 'true') {
			jQuery('.rt-idea-not-installed-error p').html('<b>rtBiz Idea  :</b> ' + plugin_slug + ' installed and activated successfully.');
			location.reload();
		} else {
			jQuery('.rt-idea-not-installed-error p').html('<b>rtBiz Idea  :</b> There is some problem. Please try again.');
		}
	});
}
