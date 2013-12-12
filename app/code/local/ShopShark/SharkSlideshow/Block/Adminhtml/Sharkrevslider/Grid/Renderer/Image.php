<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Grid_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action {
    public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }

	public function _getValue(Varien_Object $row)
    {
        if ($getter = $this->getColumn()->getGetter()) {
            $val = $row->$getter();
        }
        $val = $row->getData($this->getColumn()->getIndex());
        $val = str_replace("no_selection", "", $val);
	    $out = '';
	    if ( !empty($val) ) {
	        $url = Mage::getBaseUrl('media') . $val;
	        $out = "<img src=". $url ." width='280px' />";
	    }

	    return $out;

    }
}