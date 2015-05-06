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

$helper                                     = Mage::helper('storepricing');
$versionHelper                              = $helper->getVersionHelper();
$databaseHelper                             = $helper->getDatabaseHelper();

$installer->startSetup();

$productTable                               = $installer->getTable('catalog/product');
$storeTable                                 = $installer->getTable('core/store');
$customerGroupTable                         = $installer->getTable('customer/customer_group');

$catalogRuleTable                           = $installer->getTable('catalogrule/rule');

$catalogRuleGroupStoreTable                 = $installer->getTable('catalogrule/rule_group_store');

if ($versionHelper->isGe1700()) {
    $catalogRuleStoreTable                      = $installer->getTable('catalogrule/store');
}
$catalogRuleProductTableName                = 'catalogrule/rule_product';
$catalogRuleProductTable                    = $installer->getTable($catalogRuleProductTableName);
$catalogRuleProductPriceTableName           = 'catalogrule/rule_product_price';
$catalogRuleProductPriceTable               = $installer->getTable($catalogRuleProductPriceTableName);

/* Catalog Rule */
if (!$versionHelper->isGe1700()) {
    $connection->addColumn($catalogRuleTable, 'store_ids', 'text null default null after `website_ids`');
}
    
/**
 * Catalog Rule Group Store
 */
$installer->run("
CREATE TABLE `{$catalogRuleGroupStoreTable}` (
  `rule_id` int(10) unsigned not null, 
  `customer_group_id` smallint(5) unsigned not null, 
  `store_id` smallint(5) unsigned not null, 
  PRIMARY KEY  (`rule_id`, `customer_group_id`, `store_id`), 
  KEY `IDX_CATALOGRULE_GROUP_STORE_RULE_ID` (`rule_id`), 
  KEY `IDX_CATALOGRULE_GROUP_STORE_CUSTOMER_GROUP_ID` (`customer_group_id`), 
  KEY `IDX_CATALOGRULE_GROUP_STORE_STORE_ID` (`store_id`), 
  CONSTRAINT `FK_CATALOGRULE_GROUP_STORE_RULE_ID` 
    FOREIGN KEY (`rule_id`) REFERENCES {$catalogRuleTable} (`rule_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOGRULE_GROUP_STORE_CUSTOMER_GROUP_ID` 
    FOREIGN KEY (`customer_group_id`) REFERENCES {$customerGroupTable} (`customer_group_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  CONSTRAINT `FK_CATALOGRULE_GROUP_STORE_STORE_ID` 
    FOREIGN KEY (`store_id`) REFERENCES {$storeTable} (`store_id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

/**
 * Catalog Rule Store
 */
if ($versionHelper->isGe1700()) {
    $installer->run("
    CREATE TABLE `{$catalogRuleStoreTable}` (
      `rule_id` int(10) unsigned not null, 
      `store_id` smallint(5) unsigned not null, 
      PRIMARY KEY  (`rule_id`, `store_id`), 
      KEY `IDX_CATALOGRULE_STORE_RULE_ID` (`rule_id`), 
      KEY `IDX_CATALOGRULE_STORE_STORE_ID` (`store_id`), 
      CONSTRAINT `FK_CATALOGRULE_STORE_RULE_ID` 
        FOREIGN KEY (`rule_id`) REFERENCES {$catalogRuleTable} (`rule_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE, 
      CONSTRAINT `FK_CATALOGRULE_STORE_STORE_ID` 
        FOREIGN KEY (`store_id`) REFERENCES {$storeTable} (`store_id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
}

/**
 * Catalog Rule Product
 */
$connection->addColumn($catalogRuleProductTable, 'store_id', 'smallint(5) unsigned not null default 0 after `website_id`');
$connection->addKey($catalogRuleProductTable, 'IDX_CATALOGRULE_PRODUCT_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOGRULE_PRODUCT_STORE_ID', $catalogRuleProductTable, 'store_id', $storeTable, 'store_id');

$databaseHelper->replaceUniqueKey(
    $installer, $catalogRuleProductTableName, 'sort_order', array(
        'rule_id', 'from_time', 'to_time', 'website_id', 'store_id', 'customer_group_id', 'product_id', 'sort_order'
    )
);

/**
 * Catalog Rule Product Price
 */
$connection->addColumn($catalogRuleProductPriceTable, 'store_id', 'smallint(5) unsigned not null default 0 after `website_id`');
$connection->addKey($catalogRuleProductPriceTable, 'IDX_CATALOGRULE_PRODUCT_PRICE_STORE_ID', array('store_id'), 'index');
$connection->addConstraint('FK_CATALOGRULE_PRODUCT_PRICE_STORE_ID', $catalogRuleProductPriceTable, 'store_id', $storeTable, 'store_id');

$databaseHelper->replaceUniqueKey(
    $installer, $catalogRuleProductPriceTableName, 'rule_date', array(
        'rule_date', 'website_id', 'store_id', 'customer_group_id', 'product_id'
    )
);

$installer->endSetup();

