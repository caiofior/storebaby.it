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

$installer = $this;
$connection = $installer->getConnection();

$catalogEavAttributeTable = $installer->getTable('catalog/eav_attribute');
$eavAttributeTable = $installer->getTable('eav/attribute');
$eavEntityTypeTable = $installer->getTable('eav/entity_type');
$storeTable = $installer->getTable('core/store');

$installer->startSetup();

$installer->run("UPDATE `{$catalogEavAttributeTable}` SET `is_global` = 0 WHERE `attribute_id` = (
    SELECT `attribute_id` FROM `{$eavAttributeTable}` WHERE (`attribute_code` = 'price') AND (`entity_type_id` = (
        SELECT `entity_type_id` FROM `{$eavEntityTypeTable}` WHERE `entity_type_code` = 'catalog_product')
    )
);");

/**
 * Price index
 */
$productIndexPriceTable = $installer->getTable('catalog/product_index_price');
$connection->addColumn($productIndexPriceTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceTable, 'IDX_CATALOG_PRODUCT_INDEX_PRICE_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_INDEX_PRICE_STORE_ID', $productIndexPriceTable, 'store_id', $storeTable, 'store_id');
$connection->addKey($productIndexPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');

$productIndexPriceIdxTable = $installer->getTable('catalog/product_price_indexer_idx');
$connection->addColumn($productIndexPriceIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceIdxTable, 'IDX_CATALOG_PRODUCT_INDEX_PRICE_IDX_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_INDEX_PRICE_IDX_STORE_ID', $productIndexPriceIdxTable, 'store_id', $storeTable, 'store_id');
$connection->addKey($productIndexPriceIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');

$productIndexPriceTmpTable = $installer->getTable('catalog/product_price_indexer_tmp');
$connection->addColumn($productIndexPriceTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceTmpTable, 'IDX_CATALOG_PRODUCT_INDEX_PRICE_TMP_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_INDEX_PRICE_TMP_STORE_ID', $productIndexPriceTmpTable, 'store_id', $storeTable, 'store_id');
$connection->addKey($productIndexPriceTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');
/**
 * Final price index
 */
$productIndexPriceFinalIdxTable = $installer->getTable('catalog/product_price_indexer_final_idx');
$connection->addColumn($productIndexPriceFinalIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceFinalIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');

$productIndexPriceFinalTmpTable = $installer->getTable('catalog/product_price_indexer_final_tmp');
$connection->addColumn($productIndexPriceFinalTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceFinalTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');
/**
 * Bundle price index
 */
$productIndexPriceBundleIdxTable = $installer->getTable('bundle/price_indexer_idx');
$connection->addColumn($productIndexPriceBundleIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceBundleIdxTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');

$productIndexPriceBundleTmpTable = $installer->getTable('bundle/price_indexer_tmp');
$connection->addColumn($productIndexPriceBundleTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceBundleTmpTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');

$productIndexPriceBundleSelectionIdxTable = $installer->getTable('bundle/selection_indexer_idx');
$connection->addColumn($productIndexPriceBundleSelectionIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceBundleSelectionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'store_id'
), 'primary');

$productIndexPriceBundleSelectionTmpTable = $installer->getTable('bundle/selection_indexer_tmp');
$connection->addColumn($productIndexPriceBundleSelectionTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceBundleSelectionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'selection_id', 'store_id'
), 'primary');

$productIndexPriceBundleOptionIdxTable = $installer->getTable('bundle/option_indexer_idx');
$connection->addColumn($productIndexPriceBundleOptionIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceBundleOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'store_id'
), 'primary');

$productIndexPriceBundleOptionTmpTable = $installer->getTable('bundle/option_indexer_tmp');
$connection->addColumn($productIndexPriceBundleOptionTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceBundleOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'store_id'
), 'primary');
/**
 * Option price index
 */
$productIndexPriceOptionAggregateIdxTable = $installer->getTable('catalog/product_price_indexer_option_aggregate_idx');
$connection->addColumn($productIndexPriceOptionAggregateIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceOptionAggregateIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'store_id'
), 'primary');

$productIndexPriceOptionAggregateTmpTable = $installer->getTable('catalog/product_price_indexer_option_aggregate_tmp');
$connection->addColumn($productIndexPriceOptionAggregateTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceOptionAggregateTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'option_id', 'store_id'
), 'primary');

$productIndexPriceOptionIdxTable = $installer->getTable('catalog/product_price_indexer_option_idx');
$connection->addColumn($productIndexPriceOptionIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');

$productIndexPriceOptionTmpTable = $installer->getTable('catalog/product_price_indexer_option_tmp');
$connection->addColumn($productIndexPriceOptionTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');
/**
 * Downloadable price index
 */
$productIndexPriceDownloadableIdxTable = $installer->getTable('downloadable/product_price_indexer_idx');
$connection->addColumn($productIndexPriceDownloadableIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceDownloadableIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');

$productIndexPriceDownloadableTmpTable = $installer->getTable('downloadable/product_price_indexer_tmp');
$connection->addColumn($productIndexPriceDownloadableTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceDownloadableTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');
/**
 * Configurable option price index
 */
$productIndexPriceCfgOptionAggregateIdxTable = $installer->getTable('catalog/product_price_indexer_cfg_option_aggregate_idx');
$connection->addColumn($productIndexPriceCfgOptionAggregateIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceCfgOptionAggregateIdxTable, 'PRIMARY', array(
    'parent_id', 'child_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');

$productIndexPriceCfgOptionAggregateTmpTable = $installer->getTable('catalog/product_price_indexer_cfg_option_aggregate_tmp');
$connection->addColumn($productIndexPriceCfgOptionAggregateTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceCfgOptionAggregateTmpTable, 'PRIMARY', array(
    'parent_id', 'child_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');

$productIndexPriceCfgOptionIdxTable = $installer->getTable('catalog/product_price_indexer_cfg_option_idx');
$connection->addColumn($productIndexPriceCfgOptionIdxTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceCfgOptionIdxTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');

$productIndexPriceCfgOptionTmpTable = $installer->getTable('catalog/product_price_indexer_cfg_option_tmp');
$connection->addColumn($productIndexPriceCfgOptionTmpTable, 'store_id', 'smallint(5) unsigned not null default 0');
$connection->addKey($productIndexPriceCfgOptionTmpTable, 'PRIMARY', array(
    'entity_id', 'customer_group_id', 'website_id', 'store_id'
), 'primary');

$installer->endSetup();
