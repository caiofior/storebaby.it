<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Tag extends Mage_Core_Model_Abstract {

    protected function _construct() {
        $this->_init('blog/tag');
    }

    public function refreshCount($store = null) {
        //Refreshes tag count
        $postsCount = Mage::getModel('blog/blog')
                ->getCollection();
        if ($store) {
            $postsCount->addStoreFilter($store);
        }
        $postsCount = $postsCount->addTagFilter($this->getTag())
                ->count();
        //var_dump($postsCount);die();


        $this->setTagCount($postsCount)->save();
        return $this;
    }

    public function loadByName($name, $store = null) {
        $coll = Mage::getModel('blog/tag')->getCollection();

        $sel = $coll->getSelect();

        $coll->getSelect()
                ->where('tag=?', $name);
        if (!Mage::app()->isSingleStoreMode() && !is_null($store)) {
            $coll->getSelect()->where('store_id=?', $store);
        }


        foreach ($coll->load() as $item) {
            return $item;
        }

        if (!is_null($store)) {
            $this->setStoreId($store);
        }
        return $this->setTag($name);
    }

}
