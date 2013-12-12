jQuery(document).ready(function(){
    jQuery("#nav li.parent").hoverIntent({
		interval: 150,
		over: function(){ jQuery(this).children("ul, div").addClass("shown-sub"); },
		timeout: 150,
		out: function(){ jQuery(this).children("ul, div").removeClass("shown-sub"); }
	});
});