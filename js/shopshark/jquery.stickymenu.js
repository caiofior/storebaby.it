jQuery(document).ready(function(){
	//Sticky menu
	var menuContainer = jQuery('.nav-container');
	var menuContainerTop = 	menuContainer.offset().top;
	var stickyMenu = function(){
		var scrollTop = jQuery(window).scrollTop();  
		if (scrollTop > menuContainerTop) menuContainer.addClass('sticky');  
		else menuContainer.removeClass('sticky');     
	};  
	  
	stickyMenu();
	  
	jQuery(window).scroll(function() {  
		stickyMenu();  
	});  
});