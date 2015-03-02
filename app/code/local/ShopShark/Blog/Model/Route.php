<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Route extends Mage_Core_Model_Config_Data {

    public function toOptionArray() {
        $options = array();
        return $options;
    }

    protected function _beforeSave() {
        $value = $this->getValue();
        if (trim($value) == "") {
            $value = "blog";
        }
        $this->setValue($value);
        return $this;
    }

}
