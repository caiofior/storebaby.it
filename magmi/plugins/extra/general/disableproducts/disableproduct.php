<?php
class ClearProductUtility extends Magmi_GeneralImportPlugin
{
	public function getPluginInfo()
	{
		return array("name"=>"Disable products",
					 "author"=>"caiofior",
					 "version"=>"0.1");
	}
	
	public function beforeImport()
	{
		$this->exec_stmt('UPDATE catalog_product_entity_int SET value = 2 WHERE attribute_id = (SELECT attribute_id FROM eav_attribute WHERE entity_type_id=(SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code="catalog_product") AND attribute_code="status")');
		$this->exec_stmt('TRUNCATE TABLE `catalog_product_link` ');
		$this->exec_stmt('TRUNCATE TABLE `catalog_product_link_attribute_int` ');
		$this->exec_stmt('DELETE FROM `catalog_product_entity_datetime` WHERE `store_id` <> 0');
                $this->exec_stmt('DELETE FROM `catalog_product_entity_decimal` WHERE `store_id` <> 0');
                $this->exec_stmt('DELETE FROM `catalog_product_entity_gallery` WHERE `store_id` <> 0');
                $this->exec_stmt('DELETE FROM `catalog_product_entity_int` WHERE `store_id` <> 0');
                $this->exec_stmt('DELETE FROM `catalog_product_entity_media_gallery_value` WHERE `store_id` <> 0');
                $this->exec_stmt('DELETE FROM `catalog_product_entity_text` WHERE `store_id` <> 0');
                $this->exec_stmt('DELETE FROM `catalog_product_entity_varchar` WHERE `store_id` <> 0');
		echo "Products disabled";
	}
	
}