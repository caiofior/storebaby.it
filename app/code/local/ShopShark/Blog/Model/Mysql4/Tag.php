<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Mysql4_Tag extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct() {
        $this->_init('blog/tag', 'id');
    }

}
