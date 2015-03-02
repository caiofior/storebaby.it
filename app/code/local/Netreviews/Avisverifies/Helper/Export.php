<?php // Our EXPORT CLASS. 
class Netreviews_Avisverifies_Helper_Export{   
    protected $collection;
    protected $isVersion13;
    protected $API = false;
    
    protected $items;
    protected $count = 0;
    protected $delay;
    protected $idShop;
    protected $resource;
    protected $dataExport;
    protected $arrStoresIds;
    protected $isProductExport = null;
    
    protected $forceParentId;
    protected $mapProductId;

    protected $configuredWebsiteId = false;


    protected $customer = array(
            'order_id'=>0,
            'email'=>'',
            'nom'=>'',
            'prenom'=>'',
            'date'=>'',        
            'delay'=>'',
            'product_id'=>0,
            'category'=>'',
            'product_name'=>'',
            'url'=>'',
            'url_image' => '',
            'id_shop'=>'',
            'amount_order'=>'',
            'status_order'=>'');
    
    public $csvHeader = array(
            'id_order',
            'email',
            'lastname',
            'firstname',
            'date_order',
            'delay',
            'id_product',
            'category',
            'description',
            'product_url',
            'url_image',
            'id_shop',
            'amount_order',
            'id_order_state');

    
        public function convertVersion($version){
            $arr = explode('.', $version);
            $count = count($arr);
            $sum = 0;
            foreach ($arr as $val){
                $val = (int)$val;
                $sum+= $val*pow(1000,$count);
                $count--;
            }
            return $sum;
        }


        public function __construct(){
        $this->resource = Mage::getSingleton('core/resource');
        $this->collection = Mage::getResourceModel('sales/order_collection');
        
        // check for sale order module version
        $version = Mage::getConfig()->getModuleConfig("Mage_Sales")->version;
        $version = $this->convertVersion($version);
        // stable version (known): 1.4.0.15, but going with 1.4.0.0
        $stableVersion = $this->convertVersion('1.4.0.0');
        if ($version < $stableVersion)
            $this->isVersion13 = true;
        else
            $this->isVersion13 = false;
        $this->mainTable = ($this->isVersion13)? "e" : "main_table";
        $this->media_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."catalog/product";
        
    }

    public function exportStruct($isProductExport){
        $this->isProductExport = ($isProductExport == 1 || $isProductExport == 'yes');
    }

    public function createStoresIds($arrStores){
        $magesel = Mage::getModel('core/store')->load(reset($arrStores));
        $delay = $magesel->getConfig(strtolower('AVISVERIFIES/system/DELAY'));
        $this->delay = (isset($delay))? $delay : 0;
        $this->arrStoresIds = $arrStores;
        // Force Parent Id
        $this->forceParentId = ($magesel->getConfig('avisverifies/extra/force_product_parent_id') == '1');
        $this->useSKU = ($magesel->getConfig('avisverifies/extra/useProductSKU') == '1');
        $this->configuredWebsiteId = true ;
        
    }
    
    public function getDataExport(){
        return ($this->dataExport)? $this->dataExport : array();
    }
    
    public function getCSVFile() {
        $data = $this->dataExport;
        if ($this->isVersion13) {
            $content = '';
            foreach ($data as $val) {
               $content .= implode(',', $val)."\r\n"; 
            }
            return $content;
        }
        else {
            $io = new Varien_Io_File();
            $path = Mage::getBaseDir('var') . DS . 'export' . DS;
            $name = md5(microtime());
            $file = $path . DS . $name . '.csv';
            $io->setAllowCreateFolders(true);
            $io->open(array('path' => $path));
            $io->streamOpen($file, 'w+');
            $io->streamLock(true);
            // $this->dataExport[0] == csvHeader
            $io->streamWriteCsv($data[0]);
            unset($data[0]);
            //$delimiter = Mage::getSingleton('core/session')->getExportSeperator();
            foreach ($data as $val) {
                $io->streamWriteCsv($val);
            }
            return array(
                'type'  => 'filename',
                'value' => $file,
                'rm'    => false // can delete file after use
            );
        }
    }


    public function count(){
        $this->count;
    }
    
    protected function queryJoinFixVersion13($resource){
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        // get the customer email eav field.
        $query = "SELECT attribute_id FROM {$resource->getTableName('eav_attribute')} 
            WHERE attribute_code = '%s'";
        $emailID = $readConnection->fetchAll(sprintf($query,'customer_email'));
        $emailID = $emailID[0]['attribute_id'];
        // add join form customer email field.
        $collection = &$this->collection;
        $collection->joinAttribute('billing_firstname', 'order_address/firstname', 'billing_address_id', null, 'left')
            ->joinAttribute('billing_lastname', 'order_address/lastname', 'billing_address_id', null, 'left');
        $collection->getSelect()->joinLeft(
                     array('email'=>$resource->getTableName('sales_order_varchar')),
                     $this->mainTable.'.entity_id = email.entity_id AND email.attribute_id ='.$emailID,
                      array('email.*'));
    }
    
    protected function queryJoin($resource){
        if ($this->isVersion13)
            $this->queryJoinFixVersion13($resource);
        
       $collection = &$this->collection;
       // join to get the parent id, and not child product id.
       // we use IS NULL to check for parent only id.
       $collection->getSelect()->joinLeft(
                     array('product'=>$resource->getTableName('sales/order_item')),
                     $this->mainTable.'.entity_id = product.order_id AND product.parent_item_id IS NULL ',
                      array('product.*'));
        $collection->getSelect()->joinLeft(
                     array('media'=>"(SELECT * FROM {$resource->getTableName('catalog_product_entity_media_gallery')} GROUP BY entity_id)"),
                     'product.product_id = media.entity_id ',
                      array('media.*'));
        $collection->getSelect()->joinLeft(
                     array('url'=>$resource->getTableName('core/url_rewrite')),
                     'product.product_id = url.product_id AND '.$this->mainTable.'.store_id = url.store_id',
                      array('product.*'));
        $collection->getSelect()->joinLeft(
                     array('viewstore'=>$resource->getTableName('core/store')),
                     $this->mainTable.'.store_id = viewstore.store_id ',
                      array('viewstore.*'));             
    }

    protected function querySelectFixVersion13(){
        $collection = &$this->collection;
        $collection->getSelect()
                ->columns('email.value as email')
                ->columns('_table_billing_firstname.value AS firstname')
                ->columns('_table_billing_lastname.value AS lastname');
    }
    
    protected function querySelectNormal(){
        $collection = &$this->collection;
        $collection->getSelect()
                ->columns($this->mainTable.'.customer_email as email')
                ->columns($this->mainTable.'.customer_firstname AS firstname')
                ->columns($this->mainTable.'.customer_lastname AS lastname')
                ->columns('product.product_type AS product_type')
                ->columns($this->mainTable.'.status');
    }
    
    protected function querySelect(){
        $collection = &$this->collection;
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($this->mainTable.'.entity_id')
                ->columns($this->mainTable.'.increment_id')->columns($this->mainTable.'.grand_total')
                ->columns("DATE_FORMAT({$this->mainTable}.created_at,'%d/%m/%Y %H:%i') as created_at")
                ->columns('product.name')->columns('viewstore.code AS name_shop')->columns('viewstore.store_id AS id_shop')
                ->columns('url.request_path as url')->columns('media.value as url_image');
        if ($this->useSKU) {
            $collection->getSelect()->columns('product.sku as product_id');
        }
        else {
            $collection->getSelect()->columns('product.product_id');
        }
        if ($this->API) {           
            $collection->getSelect()
                ->columns($this->mainTable.'.av_flag')
                ->columns("UNIX_TIMESTAMP({$this->mainTable}.created_at) as timestamp")    
                ->columns($this->mainTable.'.av_horodate_get');
                // fields added after installation  
            }        
        if ($this->isVersion13)
            $this->querySelectFixVersion13();
        else
            $this->querySelectNormal();
    }
    
    protected function _where(){
        $arrStores = implode(',', $this->arrStoresIds);
        $collection = &$this->collection;
        $collection->getSelect()->where("{$this->mainTable}.store_id IN ({$arrStores})");
    }
    
    protected function _whereStatus($status){
        $collection = &$this->collection;
        $collection->addAttributeToFilter('status',array("in"=>$status));
    }
    
    protected function _whereState($status){
        $collection = &$this->collection;
        $collection->addAttributeToFilter('state',array("in"=>$status));
    }
    
    protected function _whereTime($from,$to){
        // using time 
        $collection = &$this->collection;
        $collection->addAttributeToFilter($this->mainTable.'.created_at', array(
            'from' => $from,
            'to' => $to,
            'date' => true, // specifies conversion of comparison values
            ));
    }
    
    protected function _whereDate($from,$to){
        // using date
        $collection = &$this->collection;
        $collection->getSelect()
                ->where("({$this->mainTable}.created_at BETWEEN {$from} AND {$to}) ");
    }
    
    protected function _whereFlag($flag){
        $flag = (int)$flag;
        $collection = &$this->collection;
        $collection->getSelect()->where("{$this->mainTable}.av_flag = {$flag}");
        
    }
    
    protected function queryGroupBy(){
        $collection = &$this->collection;
        $collection->getSelect()->group("{$this->mainTable}.increment_id");
    }
    
    protected function query($resource){
        $this->queryJoin($resource);
        $this->querySelect();
        if ($this->isProductExport === false)
            $this->queryGroupBy();
    }

    protected function itemsCSV($from,$to,array $status = array()){
        // GET TABLE NAME
        $resource = $this->resource;
        $readConnection = $resource->getConnection('core_read');
        // Configurable products are saved as parent item and child products
        // with child product having parent_item_id refrence key to their parent id.
        // bcz we are intrested in parent only with and parent_item_id IS NULL.
        $this->query($resource);
        $this->_where();
        if(!empty($status)){
            $this->_whereStatus($status);
        }
        $this->_whereDate($from,$to);

        $query = $this->queryFix();

        $collection = $readConnection->fetchAll($query); 

        $this->items = $collection;
    }
    
    protected function queryFix(){
        $count = 10;
        $collection = &$this->collection;
        $query = $collection->getSelect().""; // object to string.
        $query = str_replace("`(", "(", $query,$count); // JOIN HACK
        $query = str_replace(")`", ")", $query,$count); // JOIN HACK
        return $query;
    }


    protected function itemsAPI(array $config){
        $this->API = true;
        // GET TABLE NAME
        $resource = $this->resource;
        $readConnection = $resource->getConnection('core_read');
        // Configurable products are saved as parent item and child products
        // with child product having parent_item_id refrence key to their parent id.
        // bcz we are intrested in parent only with and parent_item_id IS NULL.
        $query = $this->query($resource);
        $this->_where();
        
        if (isset($config['from']) && isset($config['to']))
            $this->_whereTime($from,$to);
        if (isset($config['flag']))
            $this->_whereFlag(0);
        
        if (isset($config['status']))
            $this->_whereStatus($config['status']);
        
        $query = $this->queryFix();
        $collection = $readConnection->fetchAll($query);   
        $this->items = $collection;
    }
    
    protected function createExport() {
        $doubleProductResult = array();
        $storeUrlResult = array();
        foreach ($this->items as $item) {
            if ($this->isProductExport && isset($doubleProductResult[$item['increment_id']][$item['product_id']])) {
                continue;
            }
            else {
                $doubleProductResult[$item['increment_id']][$item['product_id']] = 1;
            }
            if (isset($storeUrlResult[$item['id_shop']])) {
                $url = $storeUrlResult[$item['id_shop']];
            }
            else {
                $url = Mage::app()->getStore($item['id_shop'])->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);;
                $storeUrlResult[$item['id_shop']] = $url;
            }
            
            $item['url_image'] = empty($item['url_image'])? 'NULL' : $this->media_url.$item['url_image'];
            $item['url'] = empty($item['url'])? 'NULL' : $url.$item['url'];
            $this->customer['order_id'] = $item['increment_id'];
            $this->customer['email'] = $item['email'];
            $this->customer['amount_order'] = $item['grand_total'];
            $this->customer['nom'] = utf8_decode($item['lastname']);
            $this->customer['prenom'] = utf8_decode($item['firstname']);  
            $this->customer['date'] = $item['created_at'];				
            $this->customer['delay'] = $this->delay;			
            $this->customer['id_shop'] = $item['name_shop'];
            $this->customer['product_id'] = ($this->isProductExport)? $item['product_id'] : "";
            $this->customer['product_name'] = ($this->isProductExport)? utf8_decode($item['name']) : "";
            $this->customer['product_name'] = str_replace(",", " - ", $this->customer['product_name']);
            $this->customer["url"] = ($this->isProductExport)? $item['url'] : "";
            $this->customer['url_image'] = ($this->isProductExport)? $item['url_image'] : "";
            $this->customer['status_order'] = (isset($item['status']))? $item['status'] : "";
//            $this->customer["product_type"] = (isset($item['product_type']))? $item['product_type'] : "";  # debug for configurable products
            if($this->API){           
                 $this->customer['date_av_getted_order'] = $item['av_horodate_get'];
                 $this->customer['is_flag'] = $item['av_flag'];
                 $this->customer['timestamp'] = $item['timestamp'];
                 
                 $this->customer['entity_id'] = $item['entity_id'];
                // fields added after installation  
            }
            // Force Product Parent id.
            if ($this->customer['product_id'] != "" && $this->forceParentId)
                $this->mapProductId[$this->customer['product_id']] = 0; // for now 0;
            
            $this->dataExport[] = $this->customer;
            $this->count++;
      }
      if ($this->forceParentId)
          $this->forceParentProductId();
    }
    
    protected function forceParentProductId() {
        // Get product parent id
        $resource = $this->resource;
        $readConnection = $resource->getConnection('core_read');
        $sku_table = $resource->getTableName('catalog/product');
        
        try{
            // super link
            $table = $resource->getTableName('catalog/product_super_link');
            $query = " SELECT main.parent_id,main.product_id,sku_parent.sku as sku_parent,sku_enfant.sku as sku_enfant "
                    . " FROM $table main LEFT JOIN $sku_table sku_parent ON main.parent_id = sku_parent.entity_id"
                    . " LEFT JOIN $sku_table sku_enfant ON main.product_id = sku_enfant.entity_id";

            $collection = $readConnection->fetchAll($query);

            // enfant parent
            foreach ($collection as $val) {
                if (isset($this->mapProductId[$val['product_id']])) {
                    $this->mapProductId[$val['product_id']] = (int) $val['parent_id'];
                }
                elseif (isset($this->mapProductId[$val['sku_enfant']])) {
                    $this->mapProductId[$val['sku_enfant']] =  $val['sku_parent'];
                }
                
            }
        } 
        catch (Exception $ex) {
        // do nothing    
        }
        
        try{
            // product link
            $table = $resource->getTableName('catalog/product_link');
            $query = " SELECT main.linked_product_id,main.product_id,sku_parent.sku as sku_parent,sku_enfant.sku as sku_enfant "
                    . " FROM $table main LEFT JOIN $sku_table sku_parent ON main.product_id = sku_parent.entity_id"
                    . " LEFT JOIN $sku_table sku_enfant ON main.product_id = sku_enfant.entity_id";
            $collection = $readConnection->fetchAll($query);
            
            foreach ($collection as $val) {
                if (isset($this->mapProductId[$val['linked_product_id']])) {
                    $this->mapProductId[$val['linked_product_id']] = (int) $val['product_id'];
                }
                elseif (isset($this->mapProductId[$val['sku_enfant']])) {
                    $this->mapProductId[$val['sku_enfant']] = $val['sku_parent'];
                }
            }
        } 
        catch (Exception $ex) {
        // do nothing    
        }
        
        try{
            // product link
            $table = $resource->getTableName('catalog/product_relation');
            $query = " SELECT main.parent_id,main.child_id,sku_parent.sku as sku_parent,sku_enfant.sku as sku_enfant "
                    . " FROM $table main LEFT JOIN $sku_table sku_parent ON main.parent_id = sku_parent.entity_id"
                    . " LEFT JOIN $sku_table sku_enfant ON main.child_id = sku_enfant.entity_id";
            $collection = $readConnection->fetchAll($query);
            
            foreach ($collection as $val) {
                if (isset($this->mapProductId[$val['child_id']])) {
                    $this->mapProductId[$val['child_id']] = (int) $val['parent_id'];
                }
                elseif (isset($this->mapProductId[$val['sku_enfant']])) {
                    $this->mapProductId[$val['sku_enfant']] = $val['sku_parent'];
                }
            }
        } 
        catch (Exception $ex) {
        // do nothing    
        }
        
        // continue algo
        $tmp = $this->dataExport;
        unset($tmp[0]); // 0 contain header info
        // now loop over the array and change id to parent id.
        if (is_array($tmp)) {
            foreach ($tmp as $index=>$val) {
               $id = $val['product_id']; 
               if($id == '') continue;
               // else we change the id.
               if (!empty($this->mapProductId[$val['product_id']])) {
                   $this->dataExport[$index]['product_id'] = $this->mapProductId[$val['product_id']];
               }
            }
        }
    }
    
    protected function throwExecption(){
        if ($this->isProductExport === null) {
            Mage::throwException('Please specify "exportStruct()" before creating items. ');
        }
        
        if ($this->configuredWebsiteId !== true) {
            Mage::throwException('Please specify "createStoresIds()" before creating items. ');
        }
    }


    public function createExportCSV($from,$to){
        // test if object is correctly configured.
        $this->throwExecption();
        // continue
        $this->itemsCSV($from, $to);
        $this->dataExport[0] = $this->csvHeader;
        $this->createExport();
    }
    
    public function createExportAPI(array $config){
       // test if object is correctly configured.
        $this->throwExecption();
        // continue
        $this->itemsAPI($config);
        $this->createExport();
    }
    
    public function updateFlag(array $ids){
        if (empty($ids))
            return "";
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();
        $fields = array(
            'av_flag' => 1,
            'av_horodate_get' => time(),
        );
        foreach($ids as $val)
            $tmp[] = '?';
        $where = $connection->quoteInto("entity_id IN (".implode(',', $tmp).")",$ids);
        $connection->update($this->resource->getTableName('sales/order'), $fields, $where);
        $connection->commit();
    }
}