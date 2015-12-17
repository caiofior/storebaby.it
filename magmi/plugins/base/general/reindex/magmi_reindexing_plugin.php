<?php
class Magmi_ReindexingPlugin extends Magmi_GeneralImportPlugin
{
	protected $_reindex;
	protected $_indexlist="catalog_product_attribute,catalog_product_price,catalog_product_flat,catalog_category_flat,catalog_category_product,cataloginventory_stock,catalog_url,catalogsearch_fulltext";
	protected $_mdh;
	
	public function getPluginInfo()
	{
		return array("name"=>"Magmi Magento Reindexer",
					 "author"=>"Dweeves",
					 "version"=>"1.0.2",
					 "url"=>$this->pluginDocUrl("Magmi_Magento_Reindexer"));
	}
	
	public function afterImport()
	{
		$this->fixFlat();
		$this->log("running indexer","info");
		$this->updateIndexes();
		return true;
	}
	
	public function OptimEav()
	{
		$tables=array("catalog_product_entity_varchar",
					   "catalog_product_entity_int",
					   "catalog_product_entity_text",
					   "catalog_product_entity_decimal",
					   "catalog_product_entity_datetime",
					   "catalog_product_entity_media_gallery",
					   "catalog_product_entity_tier_price");
		
		$cpe=$this->tablename('catalog_product_entity');
		$this->log("Optmizing EAV Tables...","info");
		foreach($tables as $t)
		{
			$this->log("Optmizing $t....","info");
			$sql="DELETE ta.* FROM ".$this->tablename($t)." as ta
			LEFT JOIN $cpe as cpe on cpe.entity_id=ta.entity_id 
			WHERE ta.store_id=0 AND cpe.entity_id IS NULL";
			$this->delete($sql);
			$this->log("$t optimized","info");
		}	
	}
	
	public function fixFlat()
	{
		$this->log("Cleaning flat tables before reindex...","info");
		$stmt=$this->exec_stmt("SHOW TABLES LIKE '".$this->tablename('catalog_product_flat')."%'",NULL,false);
		while($row=$stmt->fetch(PDO::FETCH_NUM))
		{
			$tname=$row[0];
			//removing records in flat tables that are no more linked to entries in catalog_product_entity table
			//for some reasons, this seem to happen
			$sql="DELETE cpf.* FROM $tname as cpf
			LEFT JOIN ".$this->tablename('catalog_product_entity')." as cpe ON cpe.entity_id=cpf.entity_id 
			WHERE cpe.entity_id IS NULL";
			$this->delete($sql);
		}
		if (intval(rand(0,7)) == 1) {	        
		        $this->exec_stmt("TRUNCATE catalogsearch_fulltext");
			$this->exec_stmt("TRUNCATE catalogsearch_query");
			$this->exec_stmt("TRUNCATE core_cache");
			$this->exec_stmt("TRUNCATE core_cache_tag");
			$this->exec_stmt("TRUNCATE core_session");
			$this->exec_stmt("TRUNCATE log_customer");
			$this->exec_stmt("TRUNCATE log_visitor");
			$this->exec_stmt("TRUNCATE log_visitor_info");
			$this->exec_stmt("TRUNCATE log_url");
			$this->exec_stmt("TRUNCATE log_url_info");
			$this->exec_stmt("TRUNCATE log_quote");
			$this->exec_stmt("TRUNCATE log_summary");
			$this->exec_stmt("TRUNCATE log_summary_type");
			$this->exec_stmt("TRUNCATE report_viewed_product_index");
			$this->exec_stmt("TRUNCATE report_compared_product_index");
			$this->exec_stmt("TRUNCATE report_event");
			$this->exec_stmt("TRUNCATE catalog_compare_item");
			$this->exec_stmt("TRUNCATE catalog_product_flat_1");
			$this->exec_stmt("TRUNCATE catalog_product_flat_2");
			$this->exec_stmt("TRUNCATE catalog_product_flat_3");
			$this->exec_stmt("TRUNCATE dataflow_profile");
		}
	}
	public function getPluginParamNames()
	{
		return array("REINDEX:indexes","REINDEX:phpcli");
	}
	
	public function getIndexList()
	{
		return $this->_indexlist;
	}
	
	public function updateIndexes()
	{
		//make sure we are not in session
		if(session_id()!=="")
		{
			session_write_close();
		}

                exec ('rm -Rf '.__DIR__.'/../../../../../var/locks ');

		$tstart=microtime(true);
		$this->log("Reindexing ....","info");
			
	
                exec('pkill -9 indexer.php');
                $basePath = realpath(__DIR__.str_repeat(DIRECTORY_SEPARATOR.'..',5));
                $commands = array(
                     "sh -c \"sleep 600; /usr/local/bin/php $basePath/shell/pricerule.php\"",
                     "sh -c \"sleep 800; /usr/local/bin/php $basePath/shell/indexer.php --reindexall\" 2>&1 > $basePath/reindex.log",
                     "sh -c \"sleep 1800; /usr/local/bin/php $basePath/shell/turpentine.php\"",
                     "find $basePath/media/catalog/product/cache/ -type f -mtime +30 -delete",
                     "find $basePath/var/report/ -type f  -mtime +2 -delete"
                );
                foreach ($commands as $command) {
                    pclose(popen("$command 2>&1 > /dev/null &","r"));
                }
		$out = shell_exec('ps ax | grep php');
		$this->log($out,"info");
		$tend=microtime(true);
		$this->log("done in ".round($tend-$tstart,2). " secs","info");
		
                
                $out = exec ('rm -Rf '.__DIR__.'/../../../../../var/cache ');
	}
			
	
	public function isRunnable()
	{
		return array(true,"");
	}
	
	public function initialize($params)
	{
		$magdir=Magmi_Config::getInstance()->getMagentoDir();
		$this->_mdh=MagentoDirHandlerFactory::getInstance()->getHandler($magdir);
			
	}
}