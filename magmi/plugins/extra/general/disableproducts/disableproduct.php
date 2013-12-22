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
		$this->exec_stmt('UPDATE catalog_product_entity_int SET value = 0 WHERE attribute_id = (SELECT attribute_id FROM eav_attribute WHERE entity_type_id=(SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code="catalog_product") AND attribute_code="status")');
		echo "Products disabled";
	}
	
}