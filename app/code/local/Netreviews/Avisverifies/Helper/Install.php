<?php
class Netreviews_Avisverifies_Helper_Install {
    
    public  function checkOrder($orderId){
        try {
            return $this->_checkOrder($orderId);
        } catch (Exception $exc) {
            // do nothing
            return array();
        }
    }
    
    protected  function _checkOrder($orderId) {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $orderId = (is_null($orderId))? NULL : (int) stripcslashes(urlencode($orderId));
        $table3 = $resource->getTableName('sales/order');
        $checkOrders = array();
        if ($orderId) {
            $table4 = $resource->getTableName('sales/order_item');
            $query = " SELECT 
                        `t3`.`increment_id`,
                        `t4`.`item_id`, 
                        `t4`.`order_id`, 
                        `t4`.`parent_item_id`, 
                        `t4`.`quote_item_id`, 
                        `t3`.`store_id`, 
                        `t4`.`created_at`, 
                        `t4`.`updated_at`, 
                        `t4`.`product_id`, 
                        `t4`.`product_type`, 
                        `t4`.`is_virtual`, 
                        `t4`.`sku`, 
                        `t4`.`name` "
                    . " FROM $table4 t4  LEFT JOIN $table3 t3 ON t3.entity_id = t4.order_id "
                    . " WHERE t3.increment_id = :orderId ";

            $bind = array('orderId' => $orderId);
            return $read->query($query, $bind)->fetchAll();
        }
    }
    
    public  function flagAll(){
        try {
            $this->_flagAll();
        } catch (Exception $exc) {
            // do nothing
        }
    }
    
    protected  function _flagAll(){
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $order = $resource->getTableName('sales/order');
        $write->query("UPDATE `{$order}` SET `av_flag` = 1;");
    }
    
    public  function addUpdateFields(){
        try {
            $this->_addUpdateFields();
        } catch (Exception $exc) {
            // do nothing
        }
    }
    
    protected  function _addUpdateFields(){
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $average = $resource->getTableName('avisverifies_products_average');
        $reviews = $resource->getTableName('avisverifies_products_reviews');
        
        $write->query("DROP TABLE `{$average}`");
        $write->query("DROP TABLE `{$reviews}`");
        sleep(1);
        $this->_createTables();
    }
    
    public  function addFields(){
        try {
            $this->_addFields();
        } catch (Exception $exc) {
            // do nothing
        }
    }
    
    protected  function _addFields(){
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        $read = $resource->getConnection('core_read');
        $order = $resource->getTableName('sales/order');
        
        // Flag 
        $avFlag = $read->query('SELECT COUNT(*) as flag
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = "'.$order.'"
                                    AND table_schema = DATABASE()
                                    AND column_name = "av_flag"')->fetchAll();
        $avFlag = $avFlag[0]['flag'];
        if ($avFlag == 0) {
             $write->query("ALTER TABLE `{$order}` add  av_flag tinyint default 0;");
             $write->query("UPDATE `{$order}` SET `av_flag` = 1;");   
        }
        else {
            $write->query("UPDATE `{$order}` SET `av_flag` = 1;");
        }
        // horodate
        $horodate = $read->query('SELECT COUNT(*) as horodate
                                    FROM INFORMATION_SCHEMA.COLUMNS
                                    WHERE table_name = "'.$order.'"
                                    AND table_schema = DATABASE()
                                    AND column_name = "av_horodate_get"')->fetchAll();
        $horodate = $horodate[0]['horodate'];
        if ($horodate == 0) {
             $write->query("ALTER TABLE `{$order}` add  av_horodate_get varchar(32);"); 
        }
        
    }
    
    public  function createTables(){
        try {
            $this->_createTables();
        } catch (Exception $exc) {
            // do nothing
        }
    }
    
    public  function _createTables(){
        $resource = Mage::getSingleton('core/resource');
        $write = $resource->getConnection('core_write');
        // Install Tables, RUN Query.
        $write->query("
        CREATE TABLE IF NOT EXISTS `{$resource->getTableName('avisverifies_products_reviews')}` (
                          `id_product_av` varchar(36) NOT NULL,
                          `ref_product` varchar(255) NOT NULL,
                          `rate` varchar(5) NOT NULL,
                          `review` text NOT NULL,
                          `customer_name` varchar(30) NOT NULL,
                          `horodate`  varchar(32) NOT NULL,
                          `discussion` text,
                          `lang` varchar(5),
                          `website_id` smallint(5) not null default 0,
                          PRIMARY KEY (`id_product_av`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        $write->query("
        CREATE TABLE IF NOT EXISTS `{$resource->getTableName('avisverifies_products_average')}` (
                          `id_product_av` varchar(36) NOT NULL,
                          `ref_product` varchar(255) NOT NULL,
                          `rate` varchar(5) NOT NULL,
                          `nb_reviews` int(10) NOT NULL,
                          `horodate_update`  varchar(32) NOT NULL,
                          `id_lang` varchar(5) DEFAULT NULL,
                          `website_id` smallint(5) not null default 0,
                          PRIMARY KEY (`ref_product`, `website_id`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");
        
    }
    
}

