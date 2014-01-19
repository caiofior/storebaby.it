<?php
/**
 * Class SampleItemProcessor
 * @author caiofior
 *
 * This class is a sample for item processing   
*/ 
class GoogleMerchant extends Magmi_ItemProcessor
{

    /**
     * Merchant handle
     * @var resource
     */
    private $googleMerchantHandle;
    /**
     *
     * @var type Gets config data
     */
    private $config;
    /**
     * Columns
     * @var array
     */
    private $columns = array(
        'id'=>null,
        'title'=>null,
        'description'=>null,
        'google product category'=>null,
        'product type'=>null,
        'link'=>null,
        'image link'=>null,
        'condition'=>null,
        'price'=>null,
        'availability'=>null,
        'brand'=>null,
        'gtin'=>null

    );
    private $googleMerchantCategories =array();
    /**
     * Returns plugin informations
     * @return array
     */
    public function getPluginInfo()
    {
        return array(
            "name" => "Google merchant center CSV generator",
            "author" => "caiofior",
            "version" => "0.1"
        );
    }
    /**
     * Start up operations
     * @param array $params
     */
	public function initialize($params)
	{
            $this->config = array();
            foreach($this->selectAll(
                    'SELECT `path`,`value` FROM `core_config_data`
                     WHERE `path` = "web/unsecure/base_url"') as $value) {
                $this->config [$value['path']]=$value['value'];
            }
            foreach($this->selectAll(
                    'SELECT 
	(SELECT `value` 
	FROM `catalog_category_entity_varchar` 
	WHERE 
	`catalog_category_entity_varchar`.`attribute_id`= (SELECT `attribute_id` FROM `eav_attribute` WHERE
                            `attribute_code`="name" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_category"
                                )
                            ) AND
	`catalog_category_entity_varchar`.`entity_id`=`catalog_category_entity`.`entity_id`
LIMIT 1
	) as "name",
(SELECT `value` 
	FROM `catalog_category_entity_text` 
	WHERE 
	`catalog_category_entity_text`.`attribute_id`= (SELECT `attribute_id` FROM `eav_attribute` WHERE
                            `attribute_code`="google_merchant_category" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_category"
                                )
                            ) AND
	`catalog_category_entity_text`.`entity_id`=`catalog_category_entity`.`entity_id`
LIMIT 1
	) as "google_merchant"
FROM `catalog_category_entity`
WHERE 
(SELECT `value` 
	FROM `catalog_category_entity_text` 
	WHERE 
	`catalog_category_entity_text`.`attribute_id`= (SELECT `attribute_id` FROM `eav_attribute` WHERE
                            `attribute_code`="google_merchant_category" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_category"
                                )
                            ) AND
	`catalog_category_entity_text`.`entity_id`=`catalog_category_entity`.`entity_id`
LIMIT 1
	) <> ""') as $value) {
                $this->googleMerchantCategories[$value['name']]=$value['google_merchant'];
            }
            $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'googlemerchant.csv';
		if (is_file($file))
                    unlink($file);    
            $this->googleMerchantHandle = fopen($file, 'w');
            $columns = array_keys($this->columns);
            fwrite($this->googleMerchantHandle, "\xEF\xBB\xBF".implode("\t",$columns).PHP_EOL);
	}
	/**
         * Add item to csv
         * @param array $item
         * @param array $params
         * @return boolean
         */
	public function processItemBeforeId(&$item,$params=null)
	{
            $googleMerchantData = $this->columns;
            $googleMerchantData['id']=$item['sku'];
            $googleMerchantData['title']=$item['name'];
            $googleMerchantData['description']=$item['description'];
            $categories = preg_split('/[,\/]/',$item['categories']);
            $categories[]='Default Category';
            $category = '';
            foreach($categories as $categoriesItem) {
                if (key_exists($categoriesItem, $this->googleMerchantCategories) && strlen($this->googleMerchantCategories[$categoriesItem]) > strlen($category))
                        $category= $this->googleMerchantCategories[$categoriesItem];
            }
            $googleMerchantData['google product category']=$category;
            $googleMerchantData['product type']=  str_replace('/', ' > ', $item['categories']);
            $googleMerchantData['link']=$this->config['web/unsecure/base_url'].'index.php/'.$item['url_path'];
            $googleMerchantData['image link']=$this->config['web/unsecure/base_url'].'media/catalog/product/'.preg_replace('/\+\//','',$item['image']);
            $googleMerchantData['condition']='new';
            $googleMerchantData['price']=$item['price'].' EUR';
            $googleMerchantData['availability']='in stock';
            $googleMerchantData['brand']= preg_replace('/::.*/','',$item['manufacturer']);
            $googleMerchantData['gtin']=$item['sku'];
            fwrite($this->googleMerchantHandle, implode("\t",$googleMerchantData).PHP_EOL);
		return true;
	}
        /**
         * After import executed method
         */
        public function afterImport() {
            fclose($this->googleMerchantHandle);
        }
	
}