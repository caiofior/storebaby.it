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
		//$max_execution_time = max(50,ini_get('max_execution_time')); 
                //ini_set('max_execution_time',1050); 
		//$lock_wait = $this->selectAll('show variables where Variable_name = "innodb_lock_wait_timeout"'); 
		//$lock_wait = max(50,($lock_wait[0]['Value']));
                //$this->exec_stmt('SET GLOBAL innodb_lock_wait_timeout=1000');
                

		$this->exec_stmt('TRUNCATE TABLE `catalog_product_link` ');
		$this->exec_stmt('TRUNCATE TABLE `catalog_product_link_attribute_int` ');
                $tables = array('datetime','decimal','gallery','media_gallery_value','text','varchar','int');
                
                foreach($tables as $table) {
		  $this->log('Updating table '.$table);
		  $this->exec_stmt('DROP TABLE IF EXISTS `catalog_product_entity_'.$table.'_tmp`');
                  $this->exec_stmt('DROP TABLE IF EXISTS `catalog_product_entity_'.$table.'_old`');
                  $this->exec_stmt('CREATE TABLE `catalog_product_entity_'.$table.'_tmp` LIKE `catalog_product_entity_'.$table.'` ');
                  
                  if ($table == 'int') {
                        $attribute_id = $this->selectAll('SELECT attribute_id FROM eav_attribute WHERE
                        entity_type_id=(
                          SELECT entity_type_id FROM eav_entity_type 
                          WHERE entity_type_code="catalog_product"
                        ) AND attribute_code="status"');
                        while (is_array($attribute_id))
                           $attribute_id = array_shift($attribute_id);
                        $record_count = $this->selectAll('SELECT COUNT(*)
                        FROM `catalog_product_entity_'.$table.'` WHERE
			`store_id` = 0 AND 
			attribute_id <> '.$attribute_id);
                        while (is_array($record_count))
                           $record_count = array_shift($record_count);

                        for ($c = 0; $c < $record_count ; $c += 1000) {
   		            $this->exec_stmt('INSERT INTO `catalog_product_entity_'.$table.'_tmp` 
			    SELECT * FROM `catalog_product_entity_'.$table.'` WHERE
			    `store_id` = 0 AND 
			    attribute_id <> '.$attribute_id.'
                            LIMIT '.$c.',1000');
                        }
                        $record_count = $this->selectAll('SELECT COUNT(*)
 			FROM `catalog_product_entity_'.$table.'` WHERE
			`store_id` = 0 AND 
			attribute_id = '.$attribute_id);
                        while (is_array($record_count))
                           $record_count = array_shift($record_count);
                        for ($c = 0; $c < $record_count ; $c += 1000) {
  			    $this->exec_stmt('INSERT INTO `catalog_product_entity_'.$table.'_tmp` 
			    (
			    `value_id`,
			    `entity_type_id`,
			    `attribute_id`,
			    `store_id`,
			    `entity_id`,
			    `value`
			    ) 
			    SELECT
			    `value_id`,
			    `entity_type_id`,
			    `attribute_id`,
			    `store_id`,
			    `entity_id`,
			    2
			    FROM `catalog_product_entity_'.$table.'` WHERE
			    `store_id` = 0 AND 
			    attribute_id = '.$attribute_id.'
                            LIMIT '.$c.',1000');
                        }
                  } else {
                        $record_count = $this->selectAll('SELECT COUNT(*) FROM `catalog_product_entity_'.$table.'` WHERE `store_id` = 0');
                        while (is_array($record_count))
                           $record_count = array_shift($record_count);
                        for ($c = 0; $c < $record_count ; $c += 1000) {
		 	    $this->exec_stmt('INSERT INTO `catalog_product_entity_'.$table.'_tmp` SELECT * FROM `catalog_product_entity_'.$table.'` WHERE `store_id` = 0 LIMIT '.$c.',1000'); 
                        }
		  }
                  $this->exec_stmt('RENAME TABLE `catalog_product_entity_'.$table.'` TO `catalog_product_entity_'.$table.'_old`, `catalog_product_entity_'.$table.'_tmp` TO `catalog_product_entity_'.$table.'`');
                  $this->exec_stmt('DROP TABLE `catalog_product_entity_'.$table.'_old`');
                }
		$this->log('Updating table cataloginventory_stock_item');
                $this->exec_stmt('DROP TABLE IF EXISTS `cataloginventory_stock_item_tmp`');
                $this->exec_stmt('DROP TABLE IF EXISTS `cataloginventory_stock_item_old`');
                $this->exec_stmt('CREATE TABLE `cataloginventory_stock_item_tmp` LIKE `cataloginventory_stock_item` ');
                $record_count = $this->selectAll('SELECT COUNT(*) FROM `cataloginventory_stock_item`');
                while (is_array($record_count))
                     $record_count = array_shift($record_count);
	        for ($c = 0; $c < $record_count ; $c += 1000) {
		    $this->exec_stmt('INSERT INTO `cataloginventory_stock_item_tmp` 
		    (
		    `item_id`,
		    `product_id`,
		    `stock_id`,
		    `qty`,
		    `min_qty`,
		    `use_config_min_qty`,
		    `is_qty_decimal`,
		    `backorders`,
		    `use_config_backorders`,
		    `min_sale_qty`,
		    `use_config_min_sale_qty`,
		    `max_sale_qty`,
		    `use_config_max_sale_qty`,
		    `is_in_stock`,
		    `low_stock_date`,
		    `notify_stock_qty`,
		    `use_config_notify_stock_qty`,
		    `manage_stock`,
		    `use_config_manage_stock`,
		    `stock_status_changed_auto`,
		    `use_config_qty_increments`,
		    `qty_increments`,
		    `use_config_enable_qty_inc`,
		    `enable_qty_increments`,
		    `is_decimal_divided`
		    ) 
		    SELECT
		    `item_id`,
		    `product_id`,
		    `stock_id`,
		     0,
		    `min_qty`,
		    `use_config_min_qty`,
		    `is_qty_decimal`,
		    `backorders`,
		    `use_config_backorders`,
		    `min_sale_qty`,
		    `use_config_min_sale_qty`,
		    `max_sale_qty`,
		    `use_config_max_sale_qty`,
		    `is_in_stock`,
		    `low_stock_date`,
		    `notify_stock_qty`,
		    `use_config_notify_stock_qty`,
		    `manage_stock`,
		    `use_config_manage_stock`,
		    `stock_status_changed_auto`,
		    `use_config_qty_increments`,
		    `qty_increments`,
		    `use_config_enable_qty_inc`,
		    `enable_qty_increments`,
		    `is_decimal_divided`
		    FROM `cataloginventory_stock_item`
	            LIMIT '.$c.',1000');
	        }
                //$this->exec_stmt('UPDATE `cataloginventory_stock_item` SET `qty`=0');
                $this->exec_stmt('RENAME TABLE `cataloginventory_stock_item` TO `cataloginventory_stock_item_old`, `cataloginventory_stock_item_tmp` TO `cataloginventory_stock_item`');
                $this->exec_stmt('DROP TABLE `cataloginventory_stock_item_old`');		
                //$this->exec_stmt('SET GLOBAL innodb_lock_wait_timeout='.$lock_wait);
                //ini_set('max_execution_time',$max_execution_time); 
		$this->log('Products disabled');
	}
	
}