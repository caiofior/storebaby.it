// Universal transparent-PNG enabler for MSIE/Win 5.5+
// http://dsandler.org
// From original code: http://www.youngpup.net/?request=/snippets/sleight.xml
// and background-image code: http://www.allinthehead.com/retro/69
// also:
//  * use sizingMethod=crop to avoid scaling PNGs (who would do such a thing?)
//  * only do this once, to make it compatible with CSS rollovers

if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent) {
	window.attachEvent("onload", enableAlphaImages);
}

function enableAlphaImages(){
	var rslt = navigator.appVersion.match(/MSIE (\d+\.\d+)/, '');
	var itsAllGood = (rslt != null && Number(rslt[1]) >= 5.5);
	if (itsAllGood) {
		for (var i=0; i<document.all.length; i++){
			var obj = document.all[i];
			var bg = obj.currentStyle.backgroundImage;
			var img = document.images[i];
			if (bg && bg.match(/\.png/i) != null) {
				var img = bg.substring(5,bg.length-2);
				var offset = obj.style["background-position"];
				obj.style.filter =
				"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+img+"', sizingMethod='crop')";
				obj.style.backgroundImage = "url('"+BLANK_IMG+"')";
				obj.style["background-position"] = offset; // reapply
			} else if (img && img.src.match(/\.png$/i) != null) {
				var src = img.src;
				img.style.width = img.width + "px";
				img.style.height = img.height + "px";
				img.style.filter =
				"progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+src+"', sizingMethod='crop')"
				img.src = BLANK_IMG;
			}
		}
	}
}
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

ieHover = function() {
    var items, iframe;
    items = $$('#nav ul', '#nav div', '.truncated_full_value .item-options', '.tool-tip');
    $$('#checkout-step-payment', '.tool-tip').each(function(el) {
        el.show();
        el.setStyle({'visibility':'hidden'})
    })
    for (var j=0; j<items.length; j++) {
        iframe = document.createElement('IFRAME');
        iframe.src = BLANK_URL;
        iframe.scrolling = 'no';
        iframe.frameBorder = 0;
        iframe.className = 'hover-fix';
        iframe.style.width = items[j].offsetWidth+"px";
        iframe.style.height = items[j].offsetHeight+"px";
        items[j].insertBefore(iframe, items[j].firstChild);
    }
    $$('.tool-tip', '#checkout-step-payment').each(function(el) {
        el.hide();
        el.setStyle({'visibility':'visible'})
    })
}
Event.observe(window, 'load', ieHover);

