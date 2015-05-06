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
 * Product collection
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Mysql4_Catalog_Product_Collection 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection 
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
     * Add tier price data to loaded items
     *
     * @return Innoexts_StorePricing_Model_Mysql4_Catalog_Product_Collection
     */
    public function addTierPriceData()
    {
        $helper             = $this->getStorePricingHelper();
        $priceHelper        = $helper->getProductPriceHelper();
        if ($this->getFlag('tier_price_added')) {
            return $this;
        }
        $tierPrices = array();
        $productIds = array();
        foreach ($this->getItems() as $item) {
            $productIds[] = $item->getId();
            $tierPrices[$item->getId()] = array();
        }
        if (!count($productIds)) {
            return $this;
        }
        $store              = $helper->getStoreById($this->getStoreId());
        $websiteId          = null;
        $storeId            = null;
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
        $adapter        = $this->getConnection();
        $columns        = array(
            'price_id'      => 'value_id', 
            'website_id'    => 'website_id', 
            'store_id'      => 'store_id', 
            'all_groups'    => 'all_groups', 
            'cust_group'    => 'customer_group_id', 
            'price_qty'     => 'qty', 
            'price'         => 'value', 
            'product_id'    => 'entity_id', 
        );
        $select  = $adapter->select()
            ->from($this->getTable('catalog/product_attribute_tier_price'), $columns)
            ->where('entity_id IN(?)', $productIds)
            ->order(array('entity_id','qty'));
        if ($websiteId == '0') {
            $select->where('website_id = ?', $websiteId);
        } else {
            $select->where('website_id IN(?)', array('0', $websiteId));
        }
        if ($storeId == '0') {
            $select->where('store_id = ?', $storeId);
        } else {
            $select->where('store_id IN(?)', array('0', $storeId));
        }
        foreach ($adapter->fetchAll($select) as $row) {
            $tierPrices[$row['product_id']][] = array(
                'website_id'     => $row['website_id'], 
                'store_id'       => $row['store_id'], 
                'cust_group'     => $row['all_groups'] ? Mage_Customer_Model_Group::CUST_GROUP_ALL : $row['cust_group'], 
                'price_qty'      => $row['price_qty'], 
                'price'          => $row['price'], 
                'website_price'  => $row['price'], 
            );
        }
        foreach ($this->getItems() as $item) {
            $data = $tierPrices[$item->getId()];
            $item->setTierPrices($data);
            $priceHelper->setTierPrice($item);
        }
        $this->setFlag('tier_price_added', true);
        return $this;
    }
}