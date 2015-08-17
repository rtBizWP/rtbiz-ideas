/*! 
 * rtBiz Helpdesk JavaScript Library 
 * @package rtBiz Helpdesk 
 */jQuery(document).ready(function(a){jQuery.fn.highlight=function(a){function b(a,c){var d=0;if(3==a.nodeType){var e=a.data.toUpperCase().indexOf(c);if(e>=0){var f=document.createElement("span");f.className="highlight";var g=a.splitText(e),h=(g.splitText(c.length),g.cloneNode(!0));f.appendChild(h),g.parentNode.replaceChild(f,g),d=1}}else if(1==a.nodeType&&a.childNodes&&!/(script|style)/i.test(a.tagName))for(var i=0;i<a.childNodes.length;++i)i+=b(a.childNodes[i],c);return d}return this.each(function(){b(this,a.toUpperCase())})};var b={init:function(){b.toggleIdeasForm(),b.voteIdeas(),b.searchIdeas(),b.loadMoreIdeas(),b.subscribeIdeas(),b.saveIdeasEmailNotification(),b.cancelNewIdeas(),b.addNewIdeas()},toggleIdeasForm:function(){a('a[href="#Idea-new"]').click(function(a){a.preventDefault(),jQuery("#wpideas-insert-idea").slideToggle("slow")})},voteIdeas:function(){a(document).on("click",".btnVote",function(b){b.preventDefault(),a(this).attr("disabled","disabled");var c={};c.action="rtbiz_ideas_vote",c.postid=a(this).data("id"),a.ajax({url:ajaxurl,type:"POST",dataType:"json",data:c,success:function(b){b.vote?(a("#rtwpIdeaVoteCount-"+c.postid).html(b.vote),a("#btnVote-"+c.postid).removeAttr("disabled"),a("#btnVote-"+c.postid).is("a")?a("#btnVote-"+c.postid).text(b.btnLabel):a("#btnVote-"+c.postid).attr("value",b.btnLabel)):(alert(b.err),a("#btnVote-"+c.postid).removeAttr("disabled"))},error:function(b,d,e){a("#btnVote-"+c.postid).removeAttr("disabled")}})})},searchIdeas:function(){a("#txtSearchIdea").keyup(function(){var b={};b.action="rtbiz_ideas_search",b.searchtext=a(this).val(),a.ajax({url:ajaxurl,type:"POST",dataType:"text",data:b,success:function(c){a("#rtbiz-ideas-loop-common").html(c),b.searchtext&&(a(".rtbiz-idea-title").highlight(b.searchtext),a(".rtbiz-idea-description").highlight(b.searchtext))},error:function(a,b,c){}})})},loadMoreIdeas:function(){a(document).on("click","#ideaLoadMore",function(b){jQuery("#ideaLoadMore").hide(),jQuery("#ideaLoading").show();var c={};c.action="rtbiz_ideas_load_more",c.offset=a("#rtbiz-ideas-loop-common article").length,c.nonce=jQuery("#ideaLoadMore").attr("data-nonce"),c.post_type=rtbiz_ideas_posttype,c.product_id=a("#idea_product_id").val(),c.postparpage=jQuery("#idea_post_per_page").val(),c.idea_order=jQuery("#idea_order").val(),c.idea_orderby=jQuery("#idea_order_by").val(),c.processData=!1,c.contentType=!1,a.ajax({url:ajaxurl,type:"POST",dataType:"json",data:c,success:function(b){if(b.have_posts){var c=a(b.html.replace(/(\r\n|\n|\r)/gm,""));a(".rtbiz-ideas-loadmore").before(c),jQuery("#ideaLoadMore").show()}else a("#ideaLoadMore").hide();jQuery("#ideaLoading").hide()},error:function(a,b,c){}})})},subscribeIdeas:function(){a(document).on("click",".subscribe_email_notification_button",function(b){b.preventDefault();var c=jQuery(this).attr("id"),d={};d.action="rtbiz_ideas_subscribe_button",d.post_id=a(this).data("id"),a.ajax({url:ajaxurl,type:"POST",dataType:"json",data:d,success:function(a){a.status&&(jQuery("a[id="+c+"]").length?jQuery("#"+c).text(a.btntxt):jQuery("input[id= "+c+"]").length&&(jQuery("#"+c).attr("value",a.btntxt),jQuery("#"+c).toggleClass("button-unsubscribe button-subscribe")))},error:function(a,b,c){}})})},saveIdeasEmailNotification:function(){var b=jQuery("#status_change_notification").is(":checked"),c=jQuery("#comment_notification").is(":checked");jQuery("#user_notification_save").click(function(){var d={},e=jQuery("#status_change_notification").is(":checked"),f=jQuery("#comment_notification").is(":checked");d.action="rtbiz_ideas_subscribe_notification_setting",e!=b&&(d.status_change_notification=e?"YES":"NO"),f!=c&&(d.comment_notification=f?"YES":"NO"),(e!=b||f!=c)&&a.ajax({url:ajaxurl,type:"POST",dataType:"json",data:d,success:function(a){a.status&&a.status&&jQuery("#Notificationstatus").show()},error:function(a,b,c){}})})},cancelNewIdeas:function(){a("#insertIdeaFormCancel").click(function(){a("#wpideas-insert-idea").slideToggle("slow")})},addNewIdeas:function(){a("#btninsertIdeaFormSubmit").click(function(c){c.preventDefault();var d=new FormData;d.append("action","rtbiz_ideas_insert_new_idea"),d.append("txtIdeaTitle",a("#txtIdeaTitle").val());var e="";b.isTinyMCEActive()&&(e=tinyMCE.get("txtIdeaContent"));var f=e?e.getContent():a("#txtIdeaContent").val();d.append("txtIdeaContent",f);var g=a("#product_id").val();g&&d.append("product_id",g),d.append("product",a("#product_page").val());for(var h=document.getElementById("file").files,i=0;i<h.length;i++){var j=h[i];d.append("upload[]",j,j.name)}a.ajax({url:ajaxurl,type:"POST",data:d,processData:!1,contentType:!1,beforeSend:function(b){a("#txtIdeaTitle").attr("disable","disable"),a("#txtIdeaContent").attr("disable","disable"),a("#txtIdeaProduct").attr("disable","disable"),a("#file").attr("disable","disable"),a("#ideaLoading").show()},success:function(c){try{c=JSON.parse(c),c.title?(a("#txtIdeaTitleError").html(c.title),a("#txtIdeaTitleError").show()):a("#txtIdeaTitleError").hide(),c.content?(a("#txtIdeaContentError").html(c.content),a("#txtIdeaContentError").show()):a("#txtIdeaContentError").hide(),c.product?(a("#txtIdeaProductError").html(c.product),a("#txtIdeaProductError").show()):a("#txtIdeaProductError").hide(),a("body, html").animate({scrollTop:jQuery("#wpideas-insert-idea").offset().top-20},600)}catch(d){"product"==c?b.listIdeasPost(g):b.searchIdeaCallback(),a("#wpideas-insert-idea").slideToggle("slow"),a("#txtIdeaTitleError").hide(),a("#txtIdeaContentError").hide(),a("#txtIdeaProductError").hide(),a("#txtIdeaTitle").val(""),e?e.setContent(""):a("#txtIdeaContent").val(""),a("#file").val(""),a("#lblIdeaSuccess").show(),a("#lblIdeaSuccess").fadeOut(5e3),a("body, html").animate({scrollTop:jQuery("#lblIdeaSuccess").offset().top-50},600)}a("#txtIdeaTitle").removeAttr("disabled"),a("#txtIdeaContent").removeAttr("disabled"),a("#txtIdeaProduct").removeAttr("disabled"),a("#file").removeAttr("disabled"),a("#ideaLoading").hide()},error:function(a,b,c){}})})},isTinyMCEActive:function(){return"undefined"!=typeof tinyMCE&&null!==tinyMCE.activeEditor&&tinyMCE.activeEditor.isHidden()===!1?!0:!1},listIdeasPost:function(b){var c={};c.action="rtbiz_ideas_list_refresh",c.product_id=b,a.ajax({url:ajaxurl,type:"POST",dataType:"text",data:c,success:function(b){a("#rtbiz-ideas-loop-common").html(b),a("#tab-ideas_tab").val()&&a("body, html").animate({scrollTop:jQuery("#tab-ideas_tab").offset().top},600)},error:function(a,b,c){}})},searchIdeaCallback:function(){var b={};b.action="rtbiz_ideas_search",b.searchtext="",a.ajax({url:ajaxurl,type:"POST",dataType:"text",data:b,success:function(a){jQuery("#rtbiz-ideas-loop-common").html(a)},error:function(a,b,c){}})}};b.init()});