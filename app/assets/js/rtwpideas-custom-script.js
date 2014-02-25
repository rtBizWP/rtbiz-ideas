/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {

	jQuery('.btnVote').click(function() {
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
				$('#btnVote-' + data['postid']).attr('value', json.btnLabel);
			} else {
				alert(json.err);
				$('#btnVote-' + data['postid']).removeAttr('disabled');
			}
		});
	});

	jQuery('#txtSearchIdea').keyup(function() {
		var data = {
			action: 'wpideas_search',
			searchtext: $(this).val(),
		};

		$.post(rt_wpideas_ajax_url, data, function(response) {
			$('#res').html(response);
			// pull in the new value
			var searchTerm = data['searchtext'];
			// remove any old highlighted terms
			$('#res').removeHighlight();
			// disable highlighting if empty
			if (searchTerm) {
				// highlight the new term
				$('#res').highlight(searchTerm);
			}
		});

	});

});

jQuery.fn.highlight = function(pat) {
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
	return this.each(function() {
		innerHighlight(this, pat.toUpperCase());
	});
};

jQuery.fn.removeHighlight = function() {
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

	return this.find("span.highlight").each(function() {
		var thisParent = this.parentNode;
		thisParent.replaceChild(this.firstChild, this);
		newNormalize(thisParent);
	}).end();
};
