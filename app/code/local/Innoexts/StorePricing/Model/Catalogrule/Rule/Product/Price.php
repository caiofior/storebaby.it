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
 * Rule product price
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Catalogrule_Rule_Product_Price 
    extends Mage_CatalogRule_Model_Rule_Product_Price 
{
    /**
     * Apply price rule price to price index table
     * 
     * @param Varien_Db_Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $websiteId
     * @param array $updateFields       the array fields for compare with rule price and update
     * @param string $websiteDate
     * 
     * @return Mage_CatalogRule_Model_Rule_Product_Price
     */
    public function applyPriceRuleToIndexTable(
        Varien_Db_Select $select, $indexTable, $entityId, $customerGroupId, 
        $websiteId, $updateFields, $websiteDate
    ) 
    {
        $this->_getResource()->applyPriceRuleToIndexTable(
            clone $select, $indexTable, $entityId, $customerGroupId, $websiteId, 
            $updateFields, $websiteDate
        );
        return $this;
    }
    /**
     * Apply price rule price to price index table
     *
     * @param Varien_Db_Select $select
     * @param array|string $indexTable
     * @param string $entityId
     * @param string $customerGroupId
     * @param string $websiteId
     * @param string $storeId
     * @param array $updateFields       the array fields for compare with rule price and update
     * @param string $websiteDate
     * 
     * @return Mage_CatalogRule_Model_Rule_Product_Price
     */
    public function applyPriceRuleToIndexTable2(
        Varien_Db_Select $select, $indexTable, $entityId, $customerGroupId, 
        $websiteId, $storeId, $updateFields, $websiteDate
    ) 
    {
        $this->_getResource()->applyPriceRuleToIndexTable2(
            clone $select, $indexTable, $entityId, $customerGroupId, $websiteId, $storeId, 
            $updateFields, $websiteDate
        );
        return $this;
    }
}