<?php
class ShopShark_Ajax_Block_Listmedia extends Mage_Catalog_Block_Product_View_Abstract
{
    public function getGalleryImages()
    {
        $productId = $this->getProduct()->getId();
        $collection = Mage::getModel('catalog/product')->load($productId)->getMediaGalleryImages();
        return $collection;
    }
}