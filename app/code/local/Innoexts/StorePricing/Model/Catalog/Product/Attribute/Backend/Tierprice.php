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
 * Product tier price backend attribute
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Catalog_Product_Attribute_Backend_Tierprice 
    extends Mage_Catalog_Model_Product_Attribute_Backend_Tierprice 
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
     * Get product helper
     * 
     * @return Innoexts_StorePricing_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    protected function getProductHelper()
    {
        return $this->getStorePricingHelper()->getProductHelper();
    }
    /**
     * Get product price helper
     * 
     * @return Innoexts_StorePricing_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    protected function getProductPriceHelper()
    {
        return $this->getStorePricingHelper()->getProductPriceHelper();
    }
    /**
     * Set attribute instance
     * 
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * 
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Price
     */
    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        $this->setScope($attribute);
        return $this;
    }
    /**
     * Redefine attribute scope
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * 
     * @return Mage_Catalog_Model_Product_Attribute_Backend_Price
     */
    public function setScope($attribute)
    {
        $priceHelper = $this->getStorePricingHelper()->getProductPriceHelper();
        $priceHelper->setAttributeScope($attribute);
        return $this;
    }
    /**
     * Validate data
     * 
     * @param array $data
     * @param int $storeId
     * @param bool $filterEmpty
     * @param bool $filterInactive
     * @param bool $filterAncestors
     * 
     * @return bool
     */
    protected function validateData($data, $storeId, $filterEmpty = true, $filterInactive = true, $filterAncestors = true)
    {
        $priceHelper        = $this->getProductPriceHelper();
        if ($filterEmpty) {
            if (empty($data['price_qty']) || !isset($data['cust_group']) || !empty($data['delete'])) {
                return false;
            }
        }
        if ($filterInactive) {
            if ($priceHelper->isInactiveData($data, $storeId)) {
                return false;
            }
        }
        if ($filterAncestors) {
            if ($priceHelper->isAncestorData($data, $storeId)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Get data key
     * 
     * @param array $data
     * @param bool $allWebsites
     * @return string 
     */
    protected function getDataKey($data, $allWebsites = false)
    {
        return join('-', array(
            (($allWebsites) ? 0 : $data['website_id']), 
            $data['store_id'], 
            $data['cust_group'], 
            $data['price_qty'] * 1
        ));
    }
    /**
     * Get short data key
     * 
     * @param array $data
     * @return string 
     */
    protected function getShortDataKey($data)
    {
        return join('-', array(
            $data['cust_group'], 
            $data['price_qty'] * 1
        ));
    }
    /**
     * Validate tier price data
     * 
     * @param Mage_Catalog_Model_Product $object
     * @throws Mage_Core_Exception
     * 
     * @return bool
     */
    public function validate($object)
    {
        $helper             = $this->getStorePricingHelper();
        $productHelper      = $this->getProductHelper();
        $priceHelper        = $this->getProductPriceHelper();
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $tiers              = $object->getData($attributeName);
        if (empty($tiers)) { 
            return true; 
        }
        $duplicateMessage = $helper->__('Duplicate website tier price store, customer group and quantity.');
        $duplicates = array();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) { 
                continue; 
            }
            $compare = $this->getDataKey($tier);
            if (isset($duplicates[$compare])) {
                Mage::throwException($duplicateMessage);
            }
            $duplicates[$compare] = true;
        }
        if (($priceHelper->isStoreScope() || $priceHelper->isWebsiteScope()) && $object->getStoreId()) {
            $storeId            = $object->getStoreId();
            $origTierPrices     = $object->getOrigData($attributeName);
            foreach ($origTierPrices as $tier) {
                if ($priceHelper->isAncestorData($tier, $storeId)) {
                    $compare        = $this->getDataKey($tier);
                    $duplicates[$compare] = true;
                }
            }
        }
        $baseCurrency = Mage::app()->getBaseCurrencyCode();
        $rates = $this->_getWebsiteRates();
        foreach ($tiers as $tier) {
            if (!empty($tier['delete'])) {
                continue;
            }
            if ($tier['website_id'] == 0) {
                continue;
            }
            $websiteCurrency = $rates[$tier['website_id']]['code'];
            $compare = $this->getDataKey($tier);
            $globalCompare = $this->getDataKey($tier, true);
            if ($baseCurrency == $websiteCurrency && isset($duplicates[$globalCompare])) {
                Mage::throwException($duplicateMessage);
            }
        }
        return true;
    }
    /**
     * Sort price data
     *
     * @param array $a
     * @param array $b
     * 
     * @return int
     */
    protected function _sortPriceData($a, $b)
    {
        if ($a['website_id'] != $b['website_id']) {
            return $a['website_id'] < $b['website_id'] ? 1 : -1;
        }
        if ($a['store_id'] != $b['store_id']) {
            return $a['store_id'] < $b['store_id'] ? 1 : -1;
        }
        return 0;
    }
    /**
     * Sort price data by quantity
     *
     * @param array $a
     * @param array $b
     * 
     * @return int
     */
    protected function _sortPriceDataByQty($a, $b)
    {
        if ($a['price_qty'] != $b['price_qty']) {
            return $a['price_qty'] < $b['price_qty'] ? -1 : 1;
        }
        return 0;
    }
    /**
     * Prepare tier prices data for website
     *
     * @param array $priceData
     * @param string $productTypeId
     * @param int $websiteId
     * @param int $storeId
     * 
     * @return array
     */
    public function preparePriceData2(array $priceData, $productTypeId, $websiteId, $storeId)
    {
        $priceHelper            = $this->getProductPriceHelper();
        $isGroupPriceFixed      = $priceHelper->isGroupPriceFixed($productTypeId);
        $data                   = array();
        $rates                  = $this->_getWebsiteRates();
        usort($priceData, array($this, '_sortPriceData'));
        foreach ($priceData as $v) {
            $key = $this->getShortDataKey($v);
            if (
                !isset($data[$key]) && (
                    ( $v['website_id'] == $websiteId && $v['store_id'] == $storeId ) || 
                    ( $v['website_id'] == $websiteId && $v['store_id'] == 0 ) || 
                    ( $v['website_id'] == 0 )
                )
            ) {
                $data[$key] = $v;
                $data[$key]['website_id'] = $websiteId;
                $data[$key]['store_id'] = $storeId;
                
                if ($v['website_id'] == 0) {
                    if ($isGroupPriceFixed) {
                        $data[$key]['price'] = $v['price'] * $rates[$websiteId]['rate'];
                        $data[$key]['website_price'] = $v['price'] * $rates[$websiteId]['rate'];
                    }
                }
            }
        }
        usort($data, array($this, '_sortPriceDataByQty'));
        return $data;
    }
    /**
     * After load
     * 
     * @param Mage_Catalog_Model_Product $object
     * 
     * @return Innoexts_StorePricing_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    public function afterLoad($object)
    {
        $helper             = $this->getStorePricingHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $resource           = $this->_getResource();
        $websiteId          = null;
        $storeId            = null;
        $store              = $helper->getStoreById($object->getStoreId());
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $isEditMode         = $object->getData('_edit_mode');
        if ($priceHelper->isGlobalScope()) {
            $websiteId          = null;
            $storeId            = null;
        } else if ($priceHelper->isWebsiteScope() && $store->getId()) {
            $websiteId          = $helper->getWebsiteIdByStoreId($store->getId());
            $storeId            = null;
        } else if ($priceHelper->isStoreScope() && $store->getId()) {
            $websiteId          = $helper->getWebsiteIdByStoreId($store->getId());
            $storeId            = $store->getId();
        }
        $data = $resource->loadPriceData2($object->getId(), $websiteId, $storeId);
        foreach ($data as $k => $v) {
            $data[$k]['website_price'] = $v['price'];
            if ($v['all_groups']) {
                $data[$k]['cust_group'] = Mage_Customer_Model_Group::CUST_GROUP_ALL;
            }
        }
        $object->setTierPrices($data);
        $priceHelper->setTierPrice($object);
        $object->setOrigData($attributeName, $object->getData($attributeName));
        $valueChangedKey = $attributeName.'_changed';
        $object->setOrigData($valueChangedKey, 0);
        $object->setData($valueChangedKey, 0);
        return $this;
    }
    /**
     * After save
     *
     * @param Mage_Catalog_Model_Product $object
     * 
     * @return Innoexts_StorePricing_Model_Catalog_Product_Attribute_Backend_Tierprice
     */
    public function afterSave($object)
    {
        $helper             = $this->getStorePricingHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        $resource           = $this->_getResource();
        $objectId           = $object->getId();
        $storeId            = $object->getStoreId();
        $websiteId          = $helper->getWebsiteIdByStoreId($storeId);
        $attribute          = $this->getAttribute();
        $attributeName      = $attribute->getName();
        $tierPrices         = $object->getData($attributeName);
        if (empty($tierPrices)) {
            if ($priceHelper->isGlobalScope() || $websiteId == 0) {
                $resource->deletePriceData2($objectId);
            } else if ($priceHelper->isWebsiteScope()) {
                $resource->deletePriceData2($objectId, $websiteId);
            } else if ($priceHelper->isStoreScope()) {
                $resource->deletePriceData2($objectId, $websiteId, $storeId);
            }
            return $this;
        }
        $old                = array();
        $new                = array();
        $origTierPrices     = $object->getOrigData($attributeName);
        if (!is_array($origTierPrices)) { 
            $origTierPrices = array(); 
        }
        foreach ($origTierPrices as $data) {
            if (!$this->validateData($data, $storeId, false, false, true)) {
                continue;
            }
            $key = $this->getDataKey($data);
            $old[$key] = $data;
        }
        foreach ($tierPrices as $data) {
            if (!$this->validateData($data, $storeId, true, true, true)) {
                continue;
            }
            $key = $this->getDataKey($data);
            $useForAllGroups = $data['cust_group'] == Mage_Customer_Model_Group::CUST_GROUP_ALL;
            $customerGroupId = !$useForAllGroups ? $data['cust_group'] : 0;
            $new[$key] = array(
                'website_id'        => $data['website_id'], 
                'store_id'          => $data['store_id'], 
                'all_groups'        => $useForAllGroups ? 1 : 0,
                'customer_group_id' => $customerGroupId,
                'qty'               => $data['price_qty'],
                'value'             => $data['price']
            );
        }
        $delete         = array_diff_key($old, $new);
        $insert         = array_diff_key($new, $old);
        $update         = array_intersect_key($new, $old);
        $isChanged      = false;
        $productId      = $objectId;
        if (!empty($delete)) {
            foreach ($delete as $data) {
                $resource->deletePriceData2($productId, null, null, $data['price_id']);
                $isChanged = true;
            }
        }
        if (!empty($insert)) {
            foreach ($insert as $data) {
                $price = new Varien_Object($data);
                $price->setEntityId($productId);
                $resource->savePriceData($price);
                $isChanged = true;
            }
        }
        if (!empty($update)) {
            foreach ($update as $k => $v) {
                if ($old[$k]['price'] != $v['value']) {
                    $price = new Varien_Object(array('value_id' => $old[$k]['price_id'], 'value' => $v['value']));
                    $resource->savePriceData($price);
                    $isChanged = true;
                }
            }
        }
        if ($isChanged) {
            $valueChangedKey = $attributeName.'_changed';
            $object->setData($valueChangedKey, 1);
        }
        return $this;
    }
}