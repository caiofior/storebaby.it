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
 * Process helper
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Helper_Index_Process 
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
     * Get product price process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductPrice()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
    }
    /**
     * Get product flat process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getProductFlat()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_flat');
    }
    /**
     * Get search process 
     * 
     * @return Mage_Index_Model_Process
     */
    protected function getSearch()
    {
        return Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext');
    }
    /**
     * Reindex product price
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function reindexProductPrice()
    {
        $process = $this->getProductPrice();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Reindex product flat
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function reindexProductFlat()
    {
        $process = $this->getProductFlat();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Reindex search
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function reindexSearchFlat()
    {
        $process = $this->getSearch();
        if ($process) {
            $process->reindexAll();
        }
        return $this;
    }
    /**
     * Change product price process status
     * 
     * @param int $status
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function changeProductPriceStatus($status)
    {
        $process = $this->getProductPrice();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
    /**
     * Change product flat process status
     * 
     * @param int $status
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function changeProductFlatStatus($status)
    {
        $process = $this->getProductFlat();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
    /**
     * Change search process status
     * 
     * @param int $status
     * 
     * @return Innoexts_StorePricing_Helper_Index_Process
     */
    public function changeSearchStatus($status)
    {
        $process = $this->getSearch();
        if ($process) {
            $process->changeStatus($status);
        }
        return $this;
    }
}