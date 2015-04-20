/* Add To Cart */
jQuery(function ($) {
	
	function showQuickView(url, id){
		url += '?iframe=true&width=800&height=410';
		$.prettyPhoto.open(url);
	}
	
    function ajaxAddToCart(url, id){
        url = url.replace("checkout/cart","ajax/index");
		url += 'isAjax/1';
        var msgHtml;
        var productImg = $('#item-id-' + id + ' .product-image').html();
		$('body').append('<div id="addedoverlay" style="display:none"></div>');
		$('body').append('<div id="added" style="display:none"><div id="added-internal"><div id="added-content"></div></div></div>');
        var windowOver = $('#addedoverlay');
        var windowBox = $('#added');
		var windowContent = $('#added-content');
        windowOver.show();
		windowBox.show();
		windowContent.css({
        	backgroundImage: "url('"+loaderBckImg+"')"
		});
        try {
        	$.ajax({
            	url : url,
                dataType : 'json',
                success : function(data) {
					if(data.status == 'SUCCESS'){    
						if($('.block-cart')){
							$('.block-cart').replaceWith(data.sidebar);
						}
						if($('.header .cart-header')){
							$('.header .cart-header').replaceWith(data.topcart);
						}	
						msgHtml = '<div style="float:left;">' + productImg + '</div>' + data.message + '<div style="clear:both;"></div><a id="hidewindow" href="javascript:void(0);">' + continueMsg + '</a>&nbsp;<a href="' + cartUrl + '">' + cartMsg + '</a>';
					}else{
						msgHtml = '<p class="error-msg" style="margin-bottom:15px;">' + data.message + '</p><a id="hidewindow" href="javascript:void(0);">' + continueMsg + '</a>&nbsp;<a href="' + cartUrl + '">' + cartMsg + '</a>';
					}            
													
					windowContent.css({
						backgroundImage: 'none'
					});
					
					windowContent.html(msgHtml);					   
												
					windowOver.on('click',function(){
						hidewindow(windowBox,windowOver);                    
					});	       
											 
					$('#hidewindow').click(function(){
						hidewindow(windowBox,windowOver);                    
					});
					
             	}
         	});
        } catch (e) {
        }
	}
			
	$('.ajax-addtocart').click(function (e) {
		e.preventDefault();
        ajaxAddToCart($(this).attr('href'), $(this).attr('data-id'));
    });
	
	$('.btn-quickview').click(function (e) {
		e.preventDefault();
        showQuickView($(this).attr('href'), $(this).attr('data-id'));
    });
	
});

function hidewindow(windowBox,windowOver){
	windowOver.fadeOut(400, function(){ $(this).remove(); });
   	windowBox.fadeOut(400, function(){ $(this).remove(); });	
}

function setAjaxData(data){
	var msgHtml;
	jQuery('body').append('<div id="addedoverlay" style="display:none"></div>');
	jQuery('body').append('<div id="added" style="display:none"><div id="added-internal"><div id="added-content"></div></div></div>');
    var windowOver = jQuery('#addedoverlay');
    var windowBox = jQuery('#added');
	var windowContent = jQuery('#added-content');
    windowOver.show();
	windowBox.show();
		
	if(jQuery('.block-cart')){
		jQuery('.block-cart').replaceWith(data.sidebar);
	}
	if(jQuery('.header .cart-header')){
		jQuery('.header .cart-header').replaceWith(data.topcart);
	}	
		
	msgHtml = data.message + '<div style="clear:both; height: 10px;"></div><a id="hidewindow" href="javascript:void(0);">' + continueMsg + '</a>&nbsp;<a href="' + cartUrl + '">' + cartMsg + '</a>';
		
	windowContent.html(msgHtml);					   
												
	windowOver.on('click',function(){
		hidewindow(windowBox,windowOver);                    
	});	       
											 
	jQuery('#hidewindow').click(function(){
		hidewindow(windowBox,windowOver);                    
	});
}