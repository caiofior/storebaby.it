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
 * Catalog rule observer
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Catalogrule_Observer 
    extends Mage_CatalogRule_Model_Observer 
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
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getStorePricingHelper()->getVersionHelper();
    }
    /**
     * Apply all catalog price rules for specific product
     *
     * @param   Varien_Event_Observer $observer
     * 
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function applyAllRulesOnProduct($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product || $product->getIsMassupdate()) {
            return $this;
        }
        $productWebsiteIds = $product->getWebsiteIds();
        $rules = Mage::getModel('catalogrule/rule')->getCollection()->addFieldToFilter('is_active', 1);
        foreach ($rules as $rule) {
            if ($this->getVersionHelper()->isGe1700()) {
                $websiteIds = array_intersect($productWebsiteIds, $rule->getWebsiteIds());
                $storeIds = $rule->getStoreIds();
            } else {
                if (!is_array($rule->getWebsiteIds())) {
                    $ruleWebsiteIds = (array)explode(',', $rule->getWebsiteIds());
                } else {
                    $ruleWebsiteIds = $rule->getWebsiteIds();
                }
                $websiteIds = array_intersect($productWebsiteIds, $ruleWebsiteIds);
                if (!is_array($rule->getStoreIds())) {
                    $storeIds = (array) explode(',', $rule->getStoreIds());
                } else {
                    $storeIds = $rule->getStoreIds();
                }
            }
            $rule->applyToProduct2($product, $websiteIds, $storeIds);
        }
        return $this;
    }
    /**
     * Apply catalog price rules to product on frontend
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processFrontFinalPrice($observer)
    {
        $helper             = $this->getStorePricingHelper();
        $event              = $observer->getEvent();
        $product            = $event->getProduct();
        $pId                = $product->getId();
        $storeId            = $product->getStoreId();
        if ($event->hasDate()) {
            $date = $event->getDate();
        } else {
            $date = Mage::app()->getLocale()->storeTimeStamp($storeId);
        }
        if ($event->hasWebsiteId()) {
            $wId = $event->getWebsiteId();
        } else {
            $wId = Mage::app()->getStore($storeId)->getWebsiteId();
        }
        if ($event->hasStoreId()) {
            $sId = $event->getStoreId();
        } else {
            $sId = Mage::app()->getStore($storeId)->getId();
        }
        if ($event->hasCustomerGroupId()) {
            $gId = $event->getCustomerGroupId();
        } elseif ($product->hasCustomerGroupId()) {
            $gId = $product->getCustomerGroupId();
        } else {
            $gId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        $key = implode('|', array($date, $wId, $sId, $gId, $pId));
        if (!isset($this->_rulePrices[$key])) {
            $rulePrice = Mage::getResourceModel('catalogrule/rule')->getRulePrice2($date, $wId, $sId, $gId, $pId);
            $this->_rulePrices[$key] = $rulePrice;
        }
        if ($this->_rulePrices[$key]!==false) {
            $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
            $product->setFinalPrice($finalPrice);
        }
        return $this;
    }
    /**
     * Apply catalog price rules to product in admin
     *
     * @param   Varien_Event_Observer $observer
     *
     * @return  Mage_CatalogRule_Model_Observer
     */
    public function processAdminFinalPrice($observer)
    {
        $helper     = $this->getStorePricingHelper();
        $product    = $observer->getEvent()->getProduct();
        $storeId    = $product->getStoreId();
        $date       = Mage::app()->getLocale()->storeDate($storeId);
        $key        = false;
        if ($ruleData = Mage::registry('rule_data')) {
            $wId    = $ruleData->getWebsiteId();
            $sId    = $ruleData->getStoreId();
            $gId    = $ruleData->getCustomerGroupId();
            $pId    = $product->getId();
            $key    = implode('|', array($date, $wId, $sId, $gId, $pId));
        } elseif (
            !is_null($product->getWebsiteId()) && !is_null($product->getStoreId()) && 
            !is_null($product->getCustomerGroupId())
        ) {
            $wId    = $product->getWebsiteId();
            $sId    = $product->getStoreId();
            $gId    = $product->getCustomerGroupId();
            $pId    = $product->getId();
            $key    = implode('|', array($date, $wId, $sId, $gId, $pId));
        }
        if ($key) {
            if (!isset($this->_rulePrices[$key])) {
                $rulePrice = Mage::getResourceModel('catalogrule/rule')
                    ->getRulePrice2($date, $wId, $sId, $gId, $pId);
                $this->_rulePrices[$key] = $rulePrice;
            }
            if ($this->_rulePrices[$key] !== false) {
                $finalPrice = min($product->getData('final_price'), $this->_rulePrices[$key]);
                $product->setFinalPrice($finalPrice);
            }
        }
        return $this;
    }
    /**
     * Calculate minimal final price with catalog rule price
     *
     * @param Varien_Event_Observer $observer
     * 
     * @return Mage_CatalogRule_Model_Observer
     */
    public function prepareCatalogProductPriceIndexTable(Varien_Event_Observer $observer)
    {
        $event              = $observer->getEvent();
        $select             = $event->getSelect();
        $indexTable         = $event->getIndexTable();
        $entityId           = $event->getEntityId();
        $customerGroupId    = $event->getCustomerGroupId();
        $websiteId          = $event->getWebsiteId();
        $storeId            = $event->getStoreId();
        $websiteDate        = $event->getWebsiteDate();
        $updateFields       = $event->getUpdateFields();
        if ($entityId && $customerGroupId && $websiteId && $storeId && $websiteDate) {
            Mage::getSingleton('catalogrule/rule_product_price')->applyPriceRuleToIndexTable2(
                $select, $indexTable, $entityId, $customerGroupId, $websiteId, $storeId, $updateFields, $websiteDate
            );
        }
        return $this;
    }
    /**
     * Prepare catalog product collection prices
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return Innoexts_StorePricing_Model_Catalogrule_Observer 
     */
    public function prepareCatalogProductCollectionPrices(Varien_Event_Observer $observer)
    {
        $helper         = $this->getStorePricingHelper();
        $event          = $observer->getEvent();
        $collection     = $event->getCollection();
        $store          = Mage::app()->getStore($event->getStoreId());
        $websiteId      = $store->getWebsiteId();
        $storeId        = $store->getId();
        if ($event->hasCustomerGroupId()) {
            $groupId    = $event->getCustomerGroupId();
        } else {
            $session    = Mage::getSingleton('customer/session');
            if ($session->isLoggedIn()) {
                $groupId    = Mage::getSingleton('customer/session')->getCustomerGroupId();
            } else {
                $groupId    = Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;
            }
        }
        if ($event->hasDate()) {
            $date = $event->getDate();
        } else {
            $date = Mage::app()->getLocale()->storeTimeStamp($store);
        }
        $productIds = array();
        foreach ($collection as $product) {
            $key = implode('|', array($date, $websiteId, $storeId, $groupId, $product->getId()));
            if (!isset($this->_rulePrices[$key])) {
                $productIds[] = $product->getId();
            }
        }
        if ($productIds) {
            $rulePrices = Mage::getResourceModel('catalogrule/rule')->getRulePrices2(
                $date, $websiteId, $storeId, $groupId, $productIds
            );
            foreach ($productIds as $productId) {
                $key = implode('|', array($date, $websiteId, $storeId, $groupId, $productId));
                $this->_rulePrices[$key] = isset($rulePrices[$productId]) ? $rulePrices[$productId] : false;
            }
        }
        return $this;
    }
}