<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Sharkrevslider extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = 'shark_rev_slider';
	
	public function _construct()
    {
        parent::_construct();
        $this->_init('sharkslideshow/sharkrevslider');
    }

}