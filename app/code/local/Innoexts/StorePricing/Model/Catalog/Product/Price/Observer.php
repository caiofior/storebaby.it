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
 * Product price observer
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Catalog_Product_Price_Observer 
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
     * Before product collection load
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return Innoexts_StorePricing_Model_Observer_Catalog
     */
    public function beforeProductCollectionLoad(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            $this->getStorePricingHelper()
                ->getProductPriceIndexerHelper()
                ->addPriceIndexFilter($collection);
        }
        return $this;
    }
    /**
     * After product collection apply limitations
     * 
     * @param Varien_Event_Observer $observer
     * 
     * @return Innoexts_StorePricing_Model_Observer_Catalog
     */
    public function afterProductCollectionApplyLimitations(Varien_Event_Observer $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection) {
            $this->getStorePricingHelper()
                ->getProductPriceIndexerHelper()
                ->addPriceIndexFilter($collection);
        }
        return $this;
    }
}