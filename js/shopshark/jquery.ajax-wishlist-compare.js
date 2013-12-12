/* Add To Cart And Add To Compare */
jQuery(function ($) {

    function showMessage(message)
    {
		$('body').append('<div class="ajax-message"></div>');
        $('.ajax-message').fadeIn(400);
        $('.ajax-message').html(message).append('<div id="ajax-close"></div>');
        $('#ajax-close').click(function () {
            $('.ajax-message').fadeOut(400);
        });
        $('.ajax-message').slideDown('400', function () {
            setTimeout(function () {
                $('.ajax-message').slideUp('400', function () {
                    $(this).fadeOut(400, function(){ $(this).detach(); })
                });
            }, 7000)
        });
    }

    function ajaxCompare(url, id)
    {
        url = url.replace("catalog/product_compare/add", "ajax/index/compare");
        url += 'isAjax/1/';
        $('#ajax_loading' + id).css('display', 'block');
        $.ajax({
            url:url,
            dataType:'jsonp',
            success:function (data) {
                $('#ajax_loading' + id).css('display', 'none');
                showMessage(data.message);
                if (data.status != 'ERROR' ) {
                    $('.block-compare').replaceWith(data.sidebar);
                    $('.compare-top-container').replaceWith(data.top_block);
                }
            }
        });
    }

    function ajaxWishlist(url, id) {
        url = url.replace("wishlist/index/add", "ajax/index/wishlist");
        url += 'isAjax/1/';
        $('#ajax_loading' + id).css('display', 'block');
        $.ajax({
            url:url,
            dataType:'jsonp',
            success:function (data) {
                $('#ajax_loading' + id).css('display', 'none');
                showMessage(data.message);
                if (data.status != 'ERROR') {
                    if ($('.block-wishlist').length) {
                        $('.block-wishlist').replaceWith(data.sidebar);
                    } else {
                        $('.header-container .links').replaceWith(data.toplink);
                    }
                }
            }
        });
    }
	
	$('.link-wishlist').click(function (e) {
		e.preventDefault();
        ajaxWishlist($(this).attr('href'), $(this).attr('data-id'));
    });
	
    $('.link-compare').click(function (e) {
		e.preventDefault();
        ajaxCompare($(this).attr('href'), $(this).attr('data-id'));
    });

});