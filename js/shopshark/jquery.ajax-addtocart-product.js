/* Add To Cart From Product Page */
jQuery(function ($) {
	
	function hidewindow(windowBox,windowOver){
        windowOver.fadeOut(400, function(){ $(this).remove(); });
    	windowBox.fadeOut(400, function(){ $(this).remove(); });	
    }
		
	$('#product_addtocart_form').ajaxForm({
            url: $('#product_addtocart_form').attr('action').replace("checkout/cart","ajax/index"),
            data: {'isAjax':1},
            dataType: 'json',
            beforeSubmit: function(){    
                if(productAddToCartForm.validator.validate()){
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
                }else{
                    return false;
                }
            },
            error: function(data){
	            windowBox.css({
       			      backgroundImage: 'none'
                }).html(' + errorMsg + ');
                windowOver.one('click',function(){
		            hidewindow(windowBox,windowOver);
                });	       
                                 
                $('#hidewindow').click(function(){
		            hidewindow(windowBox,windowOver);
                });
            },
            success : function(data,statusText,xhr){
				
				var productImg = $('#zoom').html();
				var windowOver = $('#addedoverlay');
				var windowBox = $('#added');
				var windowContent = $('#added-content');
				
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
});