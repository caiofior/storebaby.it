TRUNCATE TABLE `catalog_product_index_price`;
ALTER TABLE `catalog_product_index_price` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_PRICE_STORE_ID;
ALTER TABLE `catalog_product_index_price` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_idx`;
ALTER TABLE `catalog_product_index_price_idx` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_PRICE_IDX_STORE_ID;
ALTER TABLE `catalog_product_index_price_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_tmp`;
ALTER TABLE `catalog_product_index_price_tmp` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_PRICE_TMP_STORE_ID;
ALTER TABLE `catalog_product_index_price_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_final_idx`;
ALTER TABLE `catalog_product_index_price_final_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_final_tmp`;
ALTER TABLE `catalog_product_index_price_final_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_idx`;
ALTER TABLE `catalog_product_index_price_bundle_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_tmp`;
ALTER TABLE `catalog_product_index_price_bundle_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_sel_idx`;
ALTER TABLE `catalog_product_index_price_bundle_sel_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_sel_tmp`;
ALTER TABLE `catalog_product_index_price_bundle_sel_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_opt_idx`;
ALTER TABLE `catalog_product_index_price_bundle_opt_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_bundle_opt_tmp`;
ALTER TABLE `catalog_product_index_price_bundle_opt_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_idx`;
ALTER TABLE `catalog_product_index_price_opt_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_tmp`;
ALTER TABLE `catalog_product_index_price_opt_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_agr_idx`;
ALTER TABLE `catalog_product_index_price_opt_agr_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_opt_agr_tmp`;
ALTER TABLE `catalog_product_index_price_opt_agr_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_downlod_idx`;
ALTER TABLE `catalog_product_index_price_downlod_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_downlod_tmp`;
ALTER TABLE `catalog_product_index_price_downlod_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_idx`;
ALTER TABLE `catalog_product_index_price_cfg_opt_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_tmp`;
ALTER TABLE `catalog_product_index_price_cfg_opt_tmp` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_agr_idx`;
ALTER TABLE `catalog_product_index_price_cfg_opt_agr_idx` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_price_cfg_opt_agr_tmp`;
ALTER TABLE `catalog_product_index_price_cfg_opt_agr_tmp` DROP `store_id`;

DELETE FROM `catalog_product_entity_tier_price` WHERE `store_id` <> 0;
ALTER TABLE `catalog_product_entity_tier_price` DROP FOREIGN KEY FK_CATALOG_PRODUCT_ENTITY_TIER_PRICE_STORE_ID;
ALTER TABLE `catalog_product_entity_tier_price` DROP `store_id`;

TRUNCATE TABLE `catalog_product_index_tier_price`;
ALTER TABLE `catalog_product_index_tier_price` DROP FOREIGN KEY FK_CATALOG_PRODUCT_INDEX_TIER_PRICE_STORE_ID;
ALTER TABLE `catalog_product_index_tier_price` DROP `store_id`;

DROP TABLE IF EXISTS `catalogrule_group_store`;
DROP TABLE IF EXISTS `catalogrule_store`;

-- Uncomment if your version is lower then 1.7.0.0
-- ALTER TABLE `catalogrule` DROP COLUMN `store_ids`;

TRUNCATE TABLE `catalogrule_product`;
ALTER TABLE `catalogrule_product` DROP FOREIGN KEY FK_CATALOGRULE_PRODUCT_STORE_ID;
ALTER TABLE `catalogrule_product` DROP `store_id`;

TRUNCATE TABLE `catalogrule_product_price`;
ALTER TABLE `catalogrule_product_price` DROP FOREIGN KEY FK_CATALOGRULE_PRODUCT_PRICE_STORE_ID;
ALTER TABLE `catalogrule_product_price` DROP `store_id`;

UPDATE `eav_attribute` SET `backend_model` = 'eav/entity_attribute_backend_datetime'
WHERE (`attribute_code` = 'special_to_date') AND (`entity_type_id` = (
    SELECT `entity_type_id` FROM `eav_entity_type` WHERE `entity_type_code` = 'catalog_product'
));

UPDATE `catalog_eav_attribute` SET `is_global` = 1 WHERE `attribute_id` IN (
    SELECT `attribute_id` FROM `eav_attribute` WHERE (`attribute_code` IN (
        'price', 'special_price', 'special_from_date', 'special_to_date', 'tier_price'
    )) AND (`entity_type_id` = (
        SELECT `entity_type_id` FROM `eav_entity_type` WHERE `entity_type_code` = 'catalog_product')
    )
);

UPDATE `core_config_data` SET `value` = '0' WHERE `path`  = 'catalog/price/scope';

DELETE FROM `core_resource` WHERE `code` = 'storepricing_setup';
