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

$installer                                = $this;
$connection                               = $installer->getConnection();

$installer->startSetup();

$storeTable                               = $installer->getTable('core/store');

/* Product Tier Price */
$productTierPriceTableName                = 'catalog/product_attribute_tier_price';
$productTierPriceTable                    = $installer->getTable($productTierPriceTableName);

$connection->addColumn($productTierPriceTable, 'store_id', 'smallint(5) unsigned not null default 0 after `website_id`');
$connection->addKey($productTierPriceTable, 'IDX_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STORE_ID', $productTierPriceTable, 'store_id', $storeTable, 'store_id');

if (Mage::helper('storepricing')->getVersionHelper()->isGe1600()) {
    $productTierPriceIndexes = $connection->getIndexList($productTierPriceTable);
    foreach ($productTierPriceIndexes as $index) {
        if ($index['INDEX_TYPE'] == Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE) {
            $connection->dropIndex($productTierPriceTable, $index['KEY_NAME']);
        }
    }
    $connection->addIndex(
        $productTierPriceTable, 
        $installer->getIdxName(
            $productTierPriceTableName, 
            array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'store_id'), 
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'store_id'), 
        Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
    );
} else {
    $connection->addKey($productTierPriceTable, 'UNQ_CATALOG_PRODUCT_TIER_PRICE', array(
        'entity_id', 'all_groups', 'customer_group_id', 'qty', 'website_id', 'store_id', 
    ), 'unique');
}
/* Product Index Tier Price */
$productIndexTierPriceTableName           = 'catalog/product_index_tier_price';
$productIndexTierPriceTable               = $installer->getTable($productIndexTierPriceTableName);

$connection->addColumn($productIndexTierPriceTable, 'store_id', 'smallint(5) unsigned not null default 0 after `website_id`');
$connection->addKey($productIndexTierPriceTable, 'IDX_CATALOG_PRODUCT_INDEX_TIER_PRICE_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOG_PRODUCT_INDEX_TIER_PRICE_STORE_ID', $productIndexTierPriceTable, 'store_id', $storeTable, 'store_id');
$connection->addKey($productIndexTierPriceTable, 'PRIMARY', array('entity_id', 'customer_group_id', 'website_id', 'store_id'), 'primary');

$eavAttributeTable          = $installer->getTable('eav/attribute');
$eavEntityTypeTable         = $installer->getTable('eav/entity_type');

$installer->run("UPDATE `{$eavAttributeTable}` 
    SET `backend_model` = 'catalog/product_attribute_backend_finishdate'
    WHERE (`attribute_code` = 'special_to_date') AND (`entity_type_id` = (
        SELECT `entity_type_id` FROM `{$eavEntityTypeTable}` WHERE `entity_type_code` = 'catalog_product'
    ))");

$installer->endSetup();

