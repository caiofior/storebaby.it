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
 * Product price helper
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Helper_Catalog_Product_Price 
    extends Mage_Core_Helper_Abstract 
{
    /**
     * Tier price attribute
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_tierPriceAttribute;
    /**
     * Price attribute
     * 
     * @var Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected $_priceAttribute;
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
     * Get product helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product
     */
    public function getProductHelper()
    {
        return Mage::helper('storepricing/catalog_product');
    }
    /**
     * Get indexer helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product
     */
    public function getIndexerHelper()
    {
        return Mage::helper('storepricing/catalog_product_price_indexer');
    }
    /**
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getStorePricingHelper()->getVersionHelper();
    }
    /**
     * Check if group price is fixed
     * 
     * @param string $productTypeId
     * 
     * @return bool
     */
    public function isGroupPriceFixed($productTypeId)
    {
        $price = Mage::getSingleton('catalog/product_type')->priceFactory($productTypeId);
        if ($this->getVersionHelper()->isGe1700()) {
            return $price->isGroupPriceFixed();
        } else {
            return $price->isTierPriceFixed();
        }
    }
    /**
     * Get attributes codes
     * 
     * @return array
     */
    protected function getAttributesCodes()
    {
        return array('price', 'special_price', 'special_from_date', 'special_to_date', 'tier_price');
    }
    /**
     * Get scope
     * 
     * @return int
     */
    public function getScope()
    {
        return Mage::helper('catalog')->getPriceScope();
    }
    /**
     * Check if global scope is active
     * 
     * @return bool 
     */
    public function isGlobalScope()
    {
        return ($this->getScope() == 0)  ? true : false;
    }
    /**
     * Check if website scope is active
     * 
     * @return bool
     */
    public function isWebsiteScope()
    {
        return ($this->getScope() == 1)  ? true : false;
    }
    /**
     * Check if store scope is active
     * 
     * @return bool
     */
    public function isStoreScope()
    {
        return ($this->getScope() == 2)  ? true : false;
    }
    /**
     * Get attribute scope
     * 
     * @param int $scope
     * 
     * @return int 
     */
    protected function getAttributeScope($scope)
    {
        $attributeScope = null;
        switch ($scope) {
            case 0: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;
                break;
            case 1: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE;
                break;
            case 2: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE;
                break;
            default: 
                $attributeScope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;
                break;
        }
        return $attributeScope;
    }
    /**
     * Set attribute scope
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param int $scope
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price
     */
    public function setAttributeScope($attribute, $scope = null)
    {
        if (is_null($scope)) {
            $scope = $this->getScope();
        }
        $attribute->setIsGlobal($this->getAttributeScope($scope));
        return $this;
    }
    /**
     * Change scope
     * 
     * @param int $scope
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price
     */
    public function changeScope($scope)
    {
        $productHelper      = $this->getProductHelper();
        $attributeScope     = $this->getAttributeScope($scope);
        $attributesCodes    = $this->getAttributesCodes();
        foreach ($attributesCodes as $attributeCode) {
            $attribute          = $productHelper->getAttribute($attributeCode);
            $attribute->setIsGlobal($attributeScope);
            $attribute->save();
        }
        return $this;
    }
    /**
     * Check if data is ancestor
     * 
     * @param array $data
     * @param mixed $storeId
     * 
     * @return bool
     */
    public function isAncestorData($data, $storeId)
    {
        $helper         = $this->getStorePricingHelper();
        $websiteId      = $helper->getWebsiteIdByStoreId($storeId);
        if (!$this->isGlobalScope() && ($websiteId != 0)) {
            if (
                ($this->isWebsiteScope() && ((int) $data['website_id'] == 0)) || 
                ($this->isStoreScope() && (((int) $data['website_id'] == 0) || ((int) $data['store_id'] == 0)))
            ) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check if data is inactive
     * 
     * @param array $data
     * @param mixed $storeId
     * 
     * @return bool
     */
    public function isInactiveData($data, $storeId)
    {
        if (
            ($this->isGlobalScope() && (($data['website_id'] > 0) || ($data['store_id'] > 0))) || 
            ($this->isWebsiteScope() && ($data['store_id'] > 0))
        ) {
            return true;
        }
        return false;
    }
    /**
     * Get tier price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    public function getTierPriceAttribute()
    {
        if (is_null($this->_tierPriceAttribute)) {
            $attribute = $this->getProductHelper()->getAttribute('tier_price');
            if ($attribute) {
                $this->_tierPriceAttribute = $attribute;
            }
        }
        return $this->_tierPriceAttribute;
    }
    /**
     * Get price attribute
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Attribute 
     */
    public function getPriceAttribute()
    {
        if (is_null($this->_priceAttribute)) {
            $attribute = $this->getProductHelper()->getAttribute('price');
            if ($attribute) {
                $this->_priceAttribute = $attribute;
            }
        }
        return $this->_priceAttribute;
    }
    /**
     * Get price attribute identifier
     * 
     * @return mixed 
     */
    public function getPriceAttributeId()
    {
        return $this->getPriceAttribute()->getId();
    }
    /**
     * Get price attribute table
     * 
     * @return string 
     */
    public function getPriceAttributeTable()
    {
        return $this->getPriceAttribute()->getBackend()->getTable();
    }
    /**
     * Set tier price
     * 
     * @param type $product
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price
     */
    public function setTierPrice($product)
    {
        $attribute = $this->getTierPriceAttribute();
        if (!$attribute) {
            return $this;
        }
        $backend = $attribute->getBackend();
        if (!$backend) {
            return $this;
        }
        $helper             = $this->getStorePricingHelper();
        $store              = $helper->getCurrentStore();
        $isEditMode         = $product->getData('_edit_mode');
        $websiteId          = null;
        $storeId            = null;
        if ($this->isGlobalScope()) {
            $websiteId          = null;
            $storeId            = null;
        } else if ($this->isWebsiteScope() && $store->getId()) {
            $websiteId          = $store->getWebsiteId();
            $storeId            = null;
        } else if ($this->isStoreScope() && $store->getId()) {
            $websiteId          = $store->getWebsiteId();
            $storeId            = $store->getId();
        }
        $typeId             = $product->getTypeId();
        $tierPrices         = $product->getTierPrices();
        if (!empty($tierPrices) && !$isEditMode) {
            $tierPrices     = $backend->preparePriceData2($tierPrices, $typeId, $websiteId, $storeId);
        }
        $product->setFinalPrice(null);
        $product->setData('tier_price', $tierPrices);
        return $this;
    }
    /**
     * Get escaped price
     * 
     * @param float $price
     * 
     * @return float
     */
    public function getEscapedPrice($price)
    {
        if (!is_numeric($price)) {
            return null;
        }
        return number_format($price, 2, null, '');
    }
}