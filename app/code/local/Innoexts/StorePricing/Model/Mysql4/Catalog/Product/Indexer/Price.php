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
 * Price indexer resource
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Mysql4_Catalog_Product_Indexer_Price 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price 
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
    protected function getProductPriceHelper()
    {
        return $this->getStorePricingHelper()
            ->getProductPriceHelper();
    }
    /**
     * Get price indexer helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price_Indexer
     */
    protected function getProductPriceIndexerHelper()
    {
        return $this->getStorePricingHelper()
            ->getProductPriceIndexerHelper();
    }
    /**
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    protected function getVersionHelper()
    {
        return $this->getStorePricingHelper()->getVersionHelper();
    }
    /**
     * Prepare tier price index table
     *
     * @param int|array $entityIds the entity ids limitation
     * 
     * @return Innoexts_StorePricing_Model_Mysql4_Catalog_Product_Indexer_Price
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $write              = $this->_getWriteAdapter();
        $table              = $this->_getTierPriceIndexTable();
        $write->delete($table);
        $price              = new Zend_Db_Expr("IF (tp.website_id=0, ROUND(tp.value * cwd.rate, 4), tp.value)");
        $columns = array(
            'entity_id'             => new Zend_Db_Expr('tp.entity_id'), 
            'customer_group_id'     => new Zend_Db_Expr('cg.customer_group_id'), 
            'website_id'            => new Zend_Db_Expr('cw.website_id'), 
            'store_id'              => new Zend_Db_Expr('cs.store_id'), 
            'min_price'             => new Zend_Db_Expr("MIN({$price})"), 
        );
        $group = array('tp.entity_id', 'cg.customer_group_id', 'cw.website_id', 'cs.store_id');
        $select = $write->select()
            ->from(array('tp' => $this->getValueTable('catalog/product', 'tier_price')), array())
            ->join(
                array('cg' => $this->getTable('customer/customer_group')),
                'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)', array())
            ->join(
                array('cw' => $this->getTable('core/website')),
                'tp.website_id = 0 OR tp.website_id = cw.website_id', array())
            ->join(
                array('cwd' => $this->_getWebsiteDateTable()),
                'cw.website_id = cwd.website_id', array())
            ->join(
                array('csg' => $this->getTable('core/store_group')), 
                'csg.website_id = cw.website_id', array())
            ->join(
                array('cs' => $this->getTable('core/store')), 
                '(csg.group_id = cs.group_id) AND ((tp.store_id = 0) OR (tp.store_id = cs.store_id))', array())
            ->where('(cw.website_id != 0) AND (cs.store_id != 0)')
            ->columns($columns)
            ->group($group);
        if (!empty($entityIds)) {
            $select->where('tp.entity_id IN(?)', $entityIds);
        }
        $query = $select->insertFromSelect($table);
        $write->query($query);
        return $this;
    }
}