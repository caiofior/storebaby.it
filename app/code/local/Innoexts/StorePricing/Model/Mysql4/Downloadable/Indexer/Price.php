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
 * Downloadable products price indexer resource
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Mysql4_Downloadable_Indexer_Price 
    extends Mage_Downloadable_Model_Mysql4_Indexer_Price 
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
     * Get price indexer helper
     * 
     * @return Innoexts_StorePricing_Helper_Catalog_Product_Price_Indexer
     */
    protected function getProductPriceIndexerHelper()
    {
        return $this->getStorePricingHelper()->getProductPriceIndexerHelper();
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
     * Prepare products default final price in temporary index table
     * 
     * @param int|array $entityIds  the entity ids limitation
     * 
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $write              = $this->_getWriteAdapter();
        $this->_prepareDefaultFinalPriceTable();
        $select             = $indexerHelper->getFinalPriceSelect($write);
        $select->where('e.type_id=?', $this->getTypeId());
        
        $statusCond     = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $this->_addAttributeToSelect($select, 'status', 'e.entity_id', 'cs.store_id', $statusCond, true);
        
        if ($this->getVersionHelper()->isGe1600()) {
            if (Mage::helper('core')->isModuleEnabled('Mage_Tax')) {
                $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
            } else {
                $taxClassId = new Zend_Db_Expr('0');
            }
        } else {
            $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', 'e.entity_id', 'cs.store_id');
        }
        $select->columns(array('tax_class_id' => $taxClassId));
       
        $indexerHelper->addTierPriceJoin($select, 'tp', $this->getTable('catalog/product_index_tier_price'));
        
        $price          = $this->_addAttributeToSelect($select, 'price', 'e.entity_id', 'cs.store_id');

        $specialFrom    = $this->_addAttributeToSelect($select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo      = $this->_addAttributeToSelect($select, 'special_to_date', 'e.entity_id', 'cs.store_id');
        $specialPrice   = $this->_addAttributeToSelect($select, 'special_price', 'e.entity_id', 'cs.store_id');
        
        $finalPrice     = $indexerHelper->getFinalPriceExpr($write, $price, $specialPrice, $specialFrom, $specialTo);

        $select->columns(array(
            'orig_price'    => $price, 
            'price'         => $finalPrice, 
            'min_price'     => $finalPrice, 
            'max_price'     => $finalPrice, 
            'tier_price'    => new Zend_Db_Expr('tp.min_price'), 
            'base_tier'     => new Zend_Db_Expr('tp.min_price'), 
        ));
        if ($this->getVersionHelper()->isGe1700()) {
            $select->columns(array(
                'group_price'      => new Zend_Db_Expr('gp.price'), 
                'base_group_price' => new Zend_Db_Expr('gp.price'), 
            ));
        }
        $select->columns(array(
            'store_id'      => new Zend_Db_Expr('cs.store_id'), 
        ));
        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        $eventData = array(
            'select'            => $select, 
            'entity_field'      => new Zend_Db_Expr('e.entity_id'), 
            'website_field'     => new Zend_Db_Expr('cw.website_id'), 
            'store_field'       => new Zend_Db_Expr('cs.store_id'), 
        );
        Mage::dispatchEvent('prepare_catalog_product_index_select', $eventData);
        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $write->query($query);
        $select = $write->select()->join(array('wd' => $this->_getWebsiteDateTable()), 'i.website_id = wd.website_id', array());
        
        $parameters = array(
            'index_table'       => array('i' => $this->_getDefaultFinalPriceTable()), 
            'select'            => $select, 
            'entity_id'         => 'i.entity_id', 
            'customer_group_id' => 'i.customer_group_id', 
            'website_id'        => 'i.website_id', 
            'store_id'          => 'i.store_id', 
            'update_fields'     => array('price', 'min_price', 'max_price'), 
        );
        if ($this->getVersionHelper()->isGe1600()) {
            $parameters['website_date'] = 'wd.website_date';
        } else {
            $parameters['website_date'] = 'wd.date';
        }
        Mage::dispatchEvent('prepare_catalog_product_price_index_table', $parameters);
        return $this;
    }
    /**
     * Apply custom option minimal and maximal price to temporary final price index table
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _applyCustomOption()
    {
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $write              = $this->_getWriteAdapter();
        $coaTable           = $this->_getCustomOptionAggregateTable();
        $copTable           = $this->_getCustomOptionPriceTable();
        $finalPriceTable    = $this->_getDefaultFinalPriceTable();
        $this->_prepareCustomOptionAggregateTable();
        $this->_prepareCustomOptionPriceTable();
        
        $select             = $indexerHelper->getOptionTypePriceSelect($write, $finalPriceTable);
        $query              = $select->insertFromSelect($coaTable);
        $write->query($query);
        
        $select             = $indexerHelper->getOptionPriceSelect($write, $finalPriceTable);
        $query              = $select->insertFromSelect($coaTable);
        $write->query($query);
        
        $select             = $indexerHelper->getAggregatedOptionPriceSelect($write, $coaTable);
        $query              = $select->insertFromSelect($copTable);
        $write->query($query);
        
        
        $table              = array('i' => $finalPriceTable);
        $select             = $indexerHelper->getOptionFinalPriceSelect($write, $copTable);
        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($coaTable);
            $write->delete($copTable);
        } else {
            if ($this->useIdxTable()) {
                $write->truncate($coaTable);
                $write->truncate($copTable);
            } else {
                $write->delete($coaTable);
                $write->delete($copTable);
            }
        }
        
        return $this;
    }
    /**
     * Calculate and apply Downloadable links price to index
     *
     * @return Mage_Downloadable_Model_Mysql4_Indexer_Price
     */
    protected function _applyDownloadableLink()
    {
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $write              = $this->_getWriteAdapter();
        $table              = $this->_getDownloadableLinkPriceTable();
        $finalPriceTable    = $this->_getDefaultFinalPriceTable();
        $this->_prepareDownloadableLinkPriceTable();
        
        $select             = $indexerHelper->getDownloadableLinkPriceSelect($write, $finalPriceTable);
        $query = $select->insertFromSelect($table);
        $write->query($query);
        
        $select             = $indexerHelper->getDownloadableLinkFinalPriceSelect($write, $table);
        $query = $select->crossUpdateFromSelect(array('i' => $finalPriceTable));
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($table);
        } else {
            if ($this->useIdxTable()) {
                $write->truncate($table);
            } else {
                $write->delete($table);
            }
        }
        
        return $this;
    }
    /**
     * Mode final prices index to primary temporary index table
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _movePriceDataToIndexTable()
    {
        $indexerHelper      = $this->getProductPriceIndexerHelper();
        $columns            = $indexerHelper->getPriceSelectColumns();
        $write              = $this->_getWriteAdapter();
        $table              = $this->_getDefaultFinalPriceTable();
        $select             = $write->select()->from($table, $columns);
        $query              = $select->insertFromSelect($this->getIdxTable());
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($table);
        } else {
            if ($this->useIdxTable()) {
                $write->truncate($table);
            } else {
                $write->delete($table);
            }
        }
        
        return $this;
    }
}