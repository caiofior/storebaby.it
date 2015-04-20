<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Menu_Sidebar extends ShopShark_Blog_Block_Abstract
{

    public function getRecent()
    {
        // widget declaration
        if ($this->getBlogWidgetRecentCount()) {
            $size = $this->getBlogWidgetRecentCount();
        } else {
            // standard output
            $size = self::$_helper->getRecentPage();
        }

        if ($size) {
            $collection = clone self::$_collection; 
            $collection->setPageSize($size);

            foreach ($collection as $item) {
                $item->setAddress($this->getBlogUrl($item->getIdentifier()));
            }

            return $collection;
        }

        return false;
    }

    public function getCategories()
    {
        $collection = Mage::getModel('blog/cat')
                ->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder('sort_order', 'asc');
      
        foreach ($collection as $item) {            
            $item->setAddress($this->getBlogUrl(array(self::$_catUriParam, $item->getIdentifier())));
        }
        
        return $collection;
    }
    
    protected function _beforeToHtml() {
        
        return $this;
        
    }
    
    protected function _toHtml()
    {
        if (self::$_helper->getEnabled()) {

            $parent = $this->getParentBlock();

            if (!$parent) {
                return null;
            }

            $showLeft = Mage::getStoreConfig('blog/menu/left');
            $showRight = Mage::getStoreConfig('blog/menu/right');
            
            $isBlogPage = Mage::app()->getRequest()->getModuleName() == ShopShark_Blog_Helper_Data::DEFAULT_ROOT;

            $leftAllowed = ($isBlogPage && ($showLeft == 2)) || ($showLeft == 1);
            $rightAllowed = ($isBlogPage && ($showRight == 2)) || ($showRight == 1);

            if (!$leftAllowed && ($parent->getNameInLayout() == 'left')) {
                return null;
            }
            if (!$rightAllowed && ($parent->getNameInLayout() == 'right')) {
                return null;
            }

            return parent::_toHtml();
        }
    }

}
