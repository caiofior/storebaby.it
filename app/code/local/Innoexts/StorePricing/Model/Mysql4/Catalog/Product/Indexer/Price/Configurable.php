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
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configurable products price indexer resource
 *
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Model_Mysql4_Catalog_Product_Indexer_Price_Configurable 
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable 
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
    protected function getVersionHelper()
    {
        return $this->getStorePricingHelper()->getVersionHelper();
    }
    /**
     * Prepare products default final price in temporary index table
     *
     * @param int|array $entityIds  the entity ids limitation
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _prepareFinalPriceData($entityIds = null)
    {
        $this->_prepareDefaultFinalPriceTable();
        $write  = $this->_getWriteAdapter();
        $select = $write->select()
            ->from(array('e' => $this->getTable('catalog/product')), array('entity_id'))
            ->join(array('cg' => $this->getTable('customer/customer_group')), '', array('customer_group_id'))
            ->join(array('cw' => $this->getTable('core/website')), '', array('website_id'))
            ->join(array('cwd' => $this->_getWebsiteDateTable()), 'cw.website_id = cwd.website_id', array())
            ->join(array('csg' => $this->getTable('core/store_group')), 'csg.website_id = cw.website_id', array())
            ->join(array('cs' => $this->getTable('core/store')), 'csg.group_id = cs.group_id AND cs.store_id != 0', array())
            ->join(array('pw' => $this->getTable('catalog/product_website')),
                'pw.product_id = e.entity_id AND pw.website_id = cw.website_id', array())
            ->joinLeft(array('tp' => $this->_getTierPriceIndexTable()), '(tp.entity_id = e.entity_id) AND '.
            	'(tp.website_id = cw.website_id) AND (tp.customer_group_id = cg.customer_group_id)', array())
            ->where('e.type_id=?', $this->getTypeId());
        $statusCond = $write->quoteInto('=?', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
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
        $price          = $this->_addAttributeToSelect($select, 'price', 'e.entity_id', 'cs.store_id');
        $specialPrice   = $this->_addAttributeToSelect($select, 'special_price', 'e.entity_id', 'cs.store_id');
        $specialFrom    = $this->_addAttributeToSelect($select, 'special_from_date', 'e.entity_id', 'cs.store_id');
        $specialTo      = $this->_addAttributeToSelect($select, 'special_to_date', 'e.entity_id', 'cs.store_id');
        if ($this->getVersionHelper()->isGe1600()) {
            $currentDate        = $write->getDatePartSql('cwd.website_date');
            $specialFromDate    = $write->getDatePartSql($specialFrom);
            $specialToDate      = $write->getDatePartSql($specialTo);
            $specialFromUse     = $write->getCheckSql("{$specialFromDate} <= {$currentDate}", '1', '0');
            $specialToUse       = $write->getCheckSql("{$specialToDate} >= {$currentDate}", '1', '0');
            $specialFromHas     = $write->getCheckSql("{$specialFrom} IS NULL", '1', "{$specialFromUse}");
            $specialToHas       = $write->getCheckSql("{$specialTo} IS NULL", '1', "{$specialToUse}");
            $finalPrice         = $write->getCheckSql("{$specialFromHas} > 0 AND {$specialToHas} > 0"
                . " AND {$specialPrice} < {$price}", $specialPrice, $price);
        } else {
            $curentDate     = new Zend_Db_Expr('cwd.date');
            $finalPrice     = new Zend_Db_Expr("IF(IF({$specialFrom} IS NULL, 1, "
                . "IF(DATE({$specialFrom}) <= {$curentDate}, 1, 0)) > 0 AND IF({$specialTo} IS NULL, 1, "
                . "IF(DATE({$specialTo}) >= {$curentDate}, 1, 0)) > 0 AND {$specialPrice} < {$price}, "
                . "{$specialPrice}, {$price})");
        }
        $tierPrice          = 'tp.min_price';
        $baseTier           = 'tp.min_price';
        $select->columns(array(
            'orig_price'    => $price, 
            'price'         => $finalPrice, 
            'min_price'     => $finalPrice, 
            'max_price'     => $finalPrice, 
            'tier_price'    => $tierPrice, 
            'base_tier'     => $baseTier, 
            'store_id'      => new Zend_Db_Expr('cs.store_id'), 
        ));
        if (!is_null($entityIds)) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        $eventData = array(
            'select'        => $select,
            'entity_field'  => new Zend_Db_Expr('e.entity_id'),
            'website_field' => new Zend_Db_Expr('cw.website_id'),
            'store_field'   => new Zend_Db_Expr('cs.store_id'), 
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
        $write      = $this->_getWriteAdapter();
        $coaTable   = $this->_getCustomOptionAggregateTable();
        $copTable   = $this->_getCustomOptionPriceTable();
        $this->_prepareCustomOptionAggregateTable();
        $this->_prepareCustomOptionPriceTable();
        $select = $write->select()
            ->from(array('i' => $this->_getDefaultFinalPriceTable()), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('cw' => $this->getTable('core/website')), 'cw.website_id = i.website_id', array())
            ->join(array('cs' => $this->getTable('core/store')), 'cs.store_id = i.store_id', array())
            ->join(array('o' => $this->getTable('catalog/product_option')), 'o.product_id = i.entity_id', array('option_id'))
            ->join(array('ot' => $this->getTable('catalog/product_option_type_value')), 'ot.option_id = o.option_id', array())
            ->join(array('otpd' => $this->getTable('catalog/product_option_type_price')), 
                'otpd.option_type_id = ot.option_type_id AND otpd.store_id = 0',array())
            ->joinLeft(
                array('otps' => $this->getTable('catalog/product_option_type_price')), 
                'otps.option_type_id = otpd.option_type_id AND otpd.store_id = cs.store_id', array())
            ->group(array('i.entity_id', 'i.customer_group_id', 'i.website_id', 'o.option_id', 'i.store_id'));
        $optPriceType   = new Zend_Db_Expr('IF (otps.option_type_price_id > 0, otps.price_type, otpd.price_type)');
        $optPriceValue  = new Zend_Db_Expr('IF (otps.option_type_price_id > 0, otps.price, otpd.price)');
        $minPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $minPriceExpr   = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$minPriceRound})");
        $minPriceMin    = new Zend_Db_Expr("MIN({$minPriceExpr})");
        $minPrice       = new Zend_Db_Expr("IF (MIN(o.is_require) = 1, {$minPriceMin}, 0)");
        $tierPriceRound = new Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr  = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$tierPriceRound})");
        $tierPriceMin   = new Zend_Db_Expr("MIN($tierPriceExpr)");
        $tierPriceValue = new Zend_Db_Expr("IF (MIN(o.is_require) > 0, {$tierPriceMin}, 0)");
        $tierPrice      = new Zend_Db_Expr("IF (MIN(i.base_tier) IS NOT NULL, {$tierPriceValue}, NULL)");
        $maxPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $maxPriceExpr   = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$maxPriceRound})");
        $maxPrice       = new Zend_Db_Expr("IF ((MIN(o.type)='radio' OR MIN(o.type)='drop_down'), MAX({$maxPriceExpr}), SUM({$maxPriceExpr}))");
        $select->columns(array(
            'min_price'   => $minPrice, 
            'max_price'   => $maxPrice, 
            'tier_price'  => $tierPrice, 
            'store_id'    => 'i.store_id', 
        ));
        $query = $select->insertFromSelect($coaTable);
        $write->query($query);

        $select = $write->select()
            ->from(array('i' => $this->_getDefaultFinalPriceTable()), array('entity_id', 'customer_group_id', 'website_id'))
            ->join(array('cw' => $this->getTable('core/website')), 'cw.website_id = i.website_id', array())
            ->join(array('cs' => $this->getTable('core/store')), 'cs.store_id = i.store_id', array())
            ->join(array('o' => $this->getTable('catalog/product_option')), 'o.product_id = i.entity_id', array('option_id'))
            ->join(array('opd' => $this->getTable('catalog/product_option_price')), 'opd.option_id = o.option_id AND opd.store_id = 0', array())
            ->joinLeft(array('ops' => $this->getTable('catalog/product_option_price')), 
                'ops.option_id = opd.option_id AND ops.store_id = cs.store_id', array());
        $optPriceType   = new Zend_Db_Expr('IF (ops.option_price_id > 0, ops.price_type, opd.price_type)');
        $optPriceValue  = new Zend_Db_Expr('IF (ops.option_price_id > 0, ops.price, opd.price)');
        $minPriceRound  = new Zend_Db_Expr("ROUND(i.price * ({$optPriceValue} / 100), 4)");
        $priceExpr      = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$minPriceRound})");
        $minPrice       = new Zend_Db_Expr("IF ({$priceExpr} > 0 AND o.is_require > 1, {$priceExpr}, 0)");
        $maxPrice       = $priceExpr;
        $tierPriceRound = new Zend_Db_Expr("ROUND(i.base_tier * ({$optPriceValue} / 100), 4)");
        $tierPriceExpr  = new Zend_Db_Expr("IF ({$optPriceType} = 'fixed', {$optPriceValue}, {$tierPriceRound})");
        $tierPriceValue = new Zend_Db_Expr("IF ({$tierPriceExpr} > 0 AND o.is_require > 0, {$tierPriceExpr}, 0)");
        $tierPrice      = new Zend_Db_Expr("IF (i.base_tier IS NOT NULL, {$tierPriceValue}, NULL)");    
        $select->columns(array(
            'min_price'   => $minPrice, 
            'max_price'   => $maxPrice, 
            'tier_price'  => $tierPrice, 
            'store_id'    => 'i.store_id', 
        ));
        $query = $select->insertFromSelect($coaTable);
        $write->query($query);
        $select = $write->select()
            ->from(array($coaTable), array('entity_id', 'customer_group_id', 'website_id', 
                'min_price' => 'SUM(min_price)', 'max_price' => 'SUM(max_price)', 'tier_price' => 'SUM(tier_price)', 'store_id'))
            ->group(array('entity_id', 'customer_group_id', 'website_id', 'store_id'));
        $query = $select->insertFromSelect($copTable);
        $write->query($query);
        $table  = array('i' => $this->_getDefaultFinalPriceTable());
        $select = $write->select()
            ->join(array('io' => $copTable), '(i.entity_id = io.entity_id) AND (i.customer_group_id = io.customer_group_id) AND '.
                '(i.website_id = io.website_id) AND (i.store_id = io.store_id)', array());
        $tierPrice = new Zend_Db_Expr('IF(i.tier_price IS NOT NULL, i.tier_price + io.tier_price, NULL)');
        $select->columns(array(
            'min_price'  => new Zend_Db_Expr('i.min_price + io.min_price'),
            'max_price'  => new Zend_Db_Expr('i.max_price + io.max_price'),
            'tier_price' => $tierPrice, 
        ));
        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($coaTable);
            $write->delete($copTable);
        } else {
            if ($this->getVersionHelper()->isGe1610()) {
                if ($this->useIdxTable() && $this->_allowTableChanges) {
                    $write->truncate($coaTable);
                    $write->truncate($copTable);
                } else {
                    $write->delete($coaTable);
                    $write->delete($copTable);
                }
            } else {
                if ($this->useIdxTable()) {
                    $write->truncate($coaTable);
                    $write->truncate($copTable);
                } else {
                    $write->delete($coaTable);
                    $write->delete($copTable);
                }
            }
        }
        
        return $this;
    }
    /**
     * Calculate minimal and maximal prices for configurable product options
     * and apply it to final price
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Configurable
     */
    protected function _applyConfigurableOption()
    {
        $write      = $this->_getWriteAdapter();
        $coaTable   = $this->_getConfigurableOptionAggregateTable();
        $copTable   = $this->_getConfigurableOptionPriceTable();
        $this->_prepareConfigurableOptionAggregateTable();
        $this->_prepareConfigurableOptionPriceTable();
        $select = $write->select()
            ->from(array('i' => $this->_getDefaultFinalPriceTable()), null)
            ->join(array('l' => $this->getTable('catalog/product_super_link')), 'l.parent_id = i.entity_id', array('parent_id', 'product_id'))
            ->columns(array('customer_group_id', 'website_id'), 'i')
            ->join(array('a' => $this->getTable('catalog/product_super_attribute')), 'l.parent_id = a.product_id', array())
            ->join(array('cp' => $this->getValueTable('catalog/product', 'int')),
                'l.product_id = cp.entity_id AND cp.attribute_id = a.attribute_id AND cp.store_id = 0', array())
            ->joinLeft(array('apd' => $this->getTable('catalog/product_super_attribute_pricing')),
                'a.product_super_attribute_id = apd.product_super_attribute_id'
                    . ' AND apd.website_id = 0 AND cp.value = apd.value_index', array())
            ->joinLeft(array('apw' => $this->getTable('catalog/product_super_attribute_pricing')),
                'a.product_super_attribute_id = apw.product_super_attribute_id'
                    . ' AND apw.website_id = i.website_id AND cp.value = apw.value_index', array())
            ->join(array('le' => $this->getTable('catalog/product')),
                'le.entity_id = l.product_id', array())
            ->where('le.required_options = 0')
            ->group(array('l.parent_id', 'i.customer_group_id', 'i.website_id', 'l.product_id', 'i.store_id'));
        $apwPricingValue   = new Zend_Db_Expr('apw.pricing_value');
        $apdPricingValue   = new Zend_Db_Expr('apd.pricing_value');
        $priceExpression   = new Zend_Db_Expr("IF (apw.value_id IS NOT NULL, {$apwPricingValue}, {$apdPricingValue})");
        $percenExpr        = new Zend_Db_Expr('IF (apw.value_id IS NOT NULL, apw.is_percent, apd.is_percent)');
        $roundExpr         = new Zend_Db_Expr("ROUND(i.price * ({$priceExpression} / 100), 4)");
        $roundPriceExpr    = new Zend_Db_Expr("IF ({$percenExpr} = 1, {$roundExpr}, {$priceExpression})");
        $priceColumn       = new Zend_Db_Expr("IF ({$priceExpression} IS NULL, '0', {$roundPriceExpr})");
        $priceColumn       = new Zend_Db_Expr("SUM({$priceColumn})");
        $tierPrice         = new Zend_Db_Expr($priceExpression);
        $tierRoundPriceExp = new Zend_Db_Expr("IF ({$percenExpr} = 1, {$roundExpr}, {$tierPrice})");
        $tierPriceExp      = new Zend_Db_Expr("IF ({$tierPrice} IS NULL, '0', {$tierRoundPriceExp})");
        $tierPriceColumn   = new Zend_Db_Expr("IF (MIN(i.tier_price) IS NOT NULL, SUM({$tierPriceExp}), NULL)");
        $select->columns(array(
            'price'      => $priceColumn,
            'tier_price' => $tierPriceColumn, 
            'store_id'   => 'i.store_id', 
        ));
        $query = $select->insertFromSelect($coaTable);
        $write->query($query);
        $select = $write->select()
            ->from(array($coaTable), array(
                'parent_id', 'customer_group_id', 'website_id', 'MIN(price)', 'MAX(price)', 'MIN(tier_price)', 'store_id'))
            ->group(array('parent_id', 'customer_group_id', 'website_id', 'store_id'));
        $query = $select->insertFromSelect($copTable);
        $write->query($query);
        $table  = array('i' => $this->_getDefaultFinalPriceTable());
        $select = $write->select()
            ->join(array('io' => $copTable), '(i.entity_id = io.entity_id) AND (i.customer_group_id = io.customer_group_id) AND '.
                '(i.website_id = io.website_id) AND (i.store_id = io.store_id)', array());
        $tierPrice = new Zend_Db_Expr('IF(i.tier_price IS NOT NULL, i.tier_price + io.tier_price, NULL)');
        $select->columns(array(
            'min_price'  => new Zend_Db_Expr('i.min_price + io.min_price'),
            'max_price'  => new Zend_Db_Expr('i.max_price + io.max_price'),
            'tier_price' => $tierPrice, 
        ));
        $query = $select->crossUpdateFromSelect($table);
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($coaTable);
            $write->delete($copTable);
        } else {
            if ($this->getVersionHelper()->isGe1610()) {
                if ($this->useIdxTable() && $this->_allowTableChanges) {
                    $write->truncate($coaTable);
                    $write->truncate($copTable);
                } else {
                    $write->delete($coaTable);
                    $write->delete($copTable);
                }
            } else {
                if ($this->useIdxTable()) {
                    $write->truncate($coaTable);
                    $write->truncate($copTable);
                } else {
                    $write->delete($coaTable);
                    $write->delete($copTable);
                }
            }
        }
        
        return $this;
    }
    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    /**
     * Mode Final Prices index to primary temporary index table
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Indexer_Price_Default
     */
    protected function _movePriceDataToIndexTable()
    {
        $columns = array(
            'entity_id'         => 'entity_id', 
            'customer_group_id' => 'customer_group_id', 
            'website_id'        => 'website_id', 
            'tax_class_id'      => 'tax_class_id', 
            'price'             => 'orig_price', 
            'final_price'       => 'price', 
            'min_price'         => 'min_price', 
            'max_price'         => 'max_price', 
            'tier_price'        => 'tier_price', 
            'store_id'			=> 'store_id', 
        );
        $write  = $this->_getWriteAdapter();
        $table  = $this->_getDefaultFinalPriceTable();
        $select = $write->select()->from($table, $columns);
        $query = $select->insertFromSelect($this->getIdxTable());
        $write->query($query);
        
        if ($this->getVersionHelper()->isGe1620()) {
            $write->delete($table);
        } else {
            if ($this->getVersionHelper()->isGe1610()) {
                if ($this->useIdxTable() && $this->_allowTableChanges) {
                    $write->truncate($table);
                } else {
                    $write->delete($table);
                }
            } else {
                if ($this->useIdxTable()) {
                    $write->truncate($table);
                } else {
                    $write->delete($table);
                }
            }
        }
        
        return $this;
    }
}