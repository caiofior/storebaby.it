<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_StorePricing
 * @copyright   Copyright (c) 2013 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product helper
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Helper_Catalog_Product 
    extends Mage_Core_Helper_Abstract 
{
    /**
     * Get store pricing helper
     * 
     * @return Innoexts_StorePricing_Helper_Data
     */
    protected function getStorePricingHelper()
    {
        return Mage::helper('storepricing');
    }
    /**
     * Get price helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price
     */
    public function getPriceHelper()
    {
        return Mage::helper('storepricing/catalog_product_price');
    }
    /**
     * Get product attribute by code
     *
     * @param string $code
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getAttribute($code)
    {
        return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $code);
    }
    /**
     * Check if group price is fixed
     * 
     * @param Mage_Catalog_Model_Product $product
     * 
     * @return bool
     */
    public function isGroupPriceFixed($product)
    {
        return $this->getPriceHelper()->isGroupPriceFixed($product->getTypeId());
    }
    /**
     * Get store id by store id
     * 
     * @param int $storeId
     * @return int 
     */
    public function getStoreIdByStoreId($storeId)
    {
        $_storeId       = null;
        $helper         = $this->getStorePricingHelper();
        $priceHelper    = $this->getPriceHelper();
        if ($priceHelper->isStoreScope()) {
            $_storeId       = $storeId;
        } else if ($priceHelper->isWebsiteScope()) {
            $_storeId       = $helper->getDefaultStoreIdByStoreId($storeId);
        } else {
            $_storeId       = 0;
        }
        return $_storeId;
    }
    /**
     * Get store id
     * 
     * @param Mage_Catalog_Model_Product $product
     * @return int
     */
    public function getStoreId($product)
    {
        return $this->getStoreIdByStoreId((int) $product->getStoreId());
    }   
}