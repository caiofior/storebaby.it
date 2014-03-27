<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Dateformat {

    protected $_options;
    const FORMAT_TYPE_FULL = 'full';
    const FORMAT_TYPE_LONG = 'long';
    const FORMAT_TYPE_MEDIUM= 'medium';
    const FORMAT_TYPE_SHORT = 'short';

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_FULL,
                'label' => Mage::helper('blog')->__('Full')
            );
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_LONG,
                'label' => Mage::helper('blog')->__('Long')
            );
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_MEDIUM,
                'label' => Mage::helper('blog')->__('Medium')
            );
            $this->_options[] = array(
                'value' => self::FORMAT_TYPE_SHORT,
                'label' => Mage::helper('blog')->__('Short')
            );
        }
        return $this->_options;
    }

}
