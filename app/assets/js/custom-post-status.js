/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {
	$("select#post_status").append("<option value=\"new\" ' . $complete . '>New</option>");
	$("select#post_status").append("<option value=\"accepted\" ' . $complete . '>Accepted</option>");
	$("select#post_status").append("<option value=\"declined\" ' . $complete . '>Declined</option>");
	$("select#post_status").append("<option value=\"completed\" ' . $complete . '>Completed</option>");
	$(".inline-edit-status select").append("<option value=\"new\" ' . $complete . '>New</option>");
	$(".inline-edit-status select").append("<option value=\"accepted\" ' . $complete . '>Accepted</option>");
	$(".inline-edit-status select").append("<option value=\"declined\" ' . $complete . '>Declined</option>");
	$(".inline-edit-status select").append("<option value=\"completed\" ' . $complete . '>Completed</option>");
	$("#post-status-display").html("' . $label . '");
	$("#publishing-action input").val("Update");
	$(".save-post-status").click(function() {
		$("#publish").hide();
		//$("#publish").val("Update");
		$("#publishing-action").html("<span class=\"spinner\"><\/span><input name=\"original_publish\" type=\"hidden\" id=\"original_publish\" value=\"Update\"><input type=\"submit\" id=\"save-publish\" class=\"button button-primary button-large\" value=\"Update\" ><\/input>");
	});
	$("#save-publish").click(function() {
		$("#publish").click();
	});
});


