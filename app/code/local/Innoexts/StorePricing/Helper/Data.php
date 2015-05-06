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
 * Store pricing helper
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Helper_Data 
    extends Mage_Core_Helper_Abstract 
{
    /**
     * Websites
     * 
     * @var array of Mage_Core_Model_Website
     */
    protected $_websites;
    /**
     * Stores
     * 
     * @var array of Mage_Core_Model_Store
     */
    protected $_stores;
    /**
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    public function getVersionHelper()
    {
        return Mage::helper('innocore')->getVersionHelper();
    }
    /**
     * Get database helper
     * 
     * @return Innoexts_InnoCore_Helper_Database
     */
    public function getDatabaseHelper()
    {
        return Mage::helper('innocore')->getDatabaseHelper();
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
     * Get process helper
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function getProcessHelper()
    {
        return Mage::helper('storepricing/index_process');
    }
    /**
     * Get product price helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price
     */
    public function getProductPriceHelper()
    {
        return $this->getProductHelper()->getPriceHelper();
    }
    /**
     * Get product price indexer helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price_Indexer
     */
    public function getProductPriceIndexerHelper()
    {
        return $this->getProductPriceHelper()->getIndexerHelper();
    }
    /**
     * Get table
     * 
     * @param string $entityName
     * 
     * @return string 
     */
    public function getTable($entityName)
    {
        return $this->getDatabaseHelper()->getTable($entityName);
    }
    /**
     * Get request
     * 
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        return Mage::app()->getRequest();
    }
    /**
     * Check if admin store is active
     * 
     * @return boolean
     */
    public function isAdmin()
    {
        return Mage::app()->getStore()->isAdmin();
    }
    /**
     * Check if single store mode is in effect
     * 
     * @return bool 
     */
    public function isSingleStoreMode()
    {
        return Mage::app()->isSingleStoreMode();
    }
    /**
     * Check if create order request is active
     * 
     * @return bool
     */
    public function isCreateOrderRequest()
    {
        if ($this->isAdmin()) {
            $controllerName = Mage::app()->getRequest()->getControllerName();
            if (in_array(strtolower($controllerName), array('sales_order_edit', 'sales_order_create'))) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * Get websites
     * 
     * @return array of Mage_Core_Model_Website
     */
    public function getWebsites()
    {
        if (is_null($this->_websites)) {
            $this->_websites = Mage::app()->getWebsites();
        }
        return $this->_websites;
    }
    /**
     * Get stores
     * 
     * @return array of Mage_Core_Model_Store
     */
    public function getStores()
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores();
        }
        return $this->_stores;
    }
    /**
     * Get website by identifier
     * 
     * @param mixed $websiteId
     * 
     * @return Mage_Core_Model_Website
     */
    public function getWebsiteById($websiteId)
    {
        return Mage::app()->getWebsite($websiteId);
    }
    /**
     * Get website by store identifier
     * 
     * @param mixed $storeId
     * 
     * @return Mage_Core_Model_Website 
     */
    public function getWebsiteByStoreId($storeId)
    {
        return $this->getStoreById($storeId)->getWebsite();
    }
    /**
     * Get store by identifier
     * 
     * @param mixed $storeId
     * 
     * @return Mage_Core_Model_Store
     */
    public function getStoreById($storeId)
    {
        return Mage::app()->getStore($storeId);
    }
    /**
     * Get default store by store identifier
     * 
     * @param mixed $storeId
     * 
     * @return int
     */
    public function getDefaultStoreByStoreId($storeId)
    {
        return $this->getWebsiteByStoreId($storeId)->getDefaultStore();
    }
    /**
     * Get current store
     * 
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        if ($this->isAdmin() && $this->isCreateOrderRequest()) {
            return Mage::getSingleton('adminhtml/session_quote')->getStore();
        } else {
            return Mage::app()->getStore();
        }
    }
    /**
     * Get stores identifiers
     * 
     * @return array
     */
    public function getStoreIds()
    {
        return array_keys($this->getStores());
    }
    /**
     * Get store identifiers by website identifier
     * 
     * @param mixed $websiteId
     * 
     * @return array
     */
    public function getStoreIdsByWebsiteId($websiteId)
    {
        return $this->getWebsiteById($websiteId)->getStoreIds();
    }
    /**
     * Get website identifier by store identifier 
     * 
     * @param mixed $storeId
     * 
     * @return int
     */
    public function getWebsiteIdByStoreId($storeId)
    {
        return $this->getStoreById($storeId)->getWebsiteId();
    }
    /**
     * Get default store identifier by store identifier
     * 
     * @param mixed $storeId
     * 
     * @return int
     */
    public function getDefaultStoreIdByStoreId($storeId)
    {
        return $this->getDefaultStoreByStoreId($storeId)->getId();
    }
    /**
     * Get current store identifier
     * 
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->getCurrentStore()->getId();
    }
}