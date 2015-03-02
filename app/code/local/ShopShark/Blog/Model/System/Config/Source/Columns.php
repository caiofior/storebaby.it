<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_System_Config_Source_Columns {

    public function toOptionArray() {
        return array(
            array('value' => 1, 'label' => Mage::helper('adminhtml')->__('Yes, all pages')),
            array('value' => 2, 'label' => Mage::helper('adminhtml')->__('Yes, only blog page')),
            array('value' => 0, 'label' => Mage::helper('adminhtml')->__('No')),
        );
    }

}
