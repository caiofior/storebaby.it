<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Sorter
{
    public function toOptionArray()
    {
        return array(
            array('value' => Varien_Data_Collection::SORT_ORDER_DESC , 'label' => Mage::helper('adminhtml')->__('Newest first')),
            array('value' => Varien_Data_Collection::SORT_ORDER_ASC, 'label' => Mage::helper('adminhtml')->__('Older first')),
        );
    }
}
