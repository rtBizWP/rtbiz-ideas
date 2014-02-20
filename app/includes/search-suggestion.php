<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.9.0/build/fonts/fonts-min.css" />
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.9.0/build/autocomplete/assets/skins/sam/autocomplete.css" />
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/animation/animation-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/datasource/datasource-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.9.0/build/autocomplete/autocomplete-min.js"></script>
<script type="text/javascript">
	YAHOO.example.Data = {
		arraySuggestions: [
			<?php echo $str; ?>
		]
	};
	YAHOO.example.BasicLocal = function() {
		// Use a LocalDataSource
		var oDS = new YAHOO.util.LocalDataSource(YAHOO.example.Data.arraySuggestions);
		// Optional to define fields for single-dimensional array
		oDS.responseSchema = {fields: ["idea"]};
		// Instantiate the AutoComplete
		var oAC = new YAHOO.widget.AutoComplete("txtNewIdea", "SuggestionContainer", oDS);
		oAC.prehighlightClassName = "yui-ac-prehighlight";
		oAC.useShadow = true;
		return {
			oDS: oDS,
			oAC: oAC
		};
	}();


</script>


