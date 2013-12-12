<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Mysql4_Related_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        $this->_init('blog/related');
    }

    public function toOptionArray() {
        return $this->_toOptionArray('post_id', 'product_id');
    }

    public function addPostFilter($postId) {
        $this->getSelect()
                ->where('post_id = ?', $postId);
        return $this;
    }

}
