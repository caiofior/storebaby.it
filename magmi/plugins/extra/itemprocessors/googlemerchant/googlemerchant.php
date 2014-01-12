<?php
/**
 * Class SampleItemProcessor
 * @author caiofior
 *
 * This class is a sample for item processing   
*/ 
class GoogleMerchant extends Magmi_ItemProcessor
{

    private $googleMerchantHandle;
    private $config;
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
        'brand'=>null

    );
    public function getPluginInfo()
    {
        return array(
            "name" => "Google merchant center CSV generator",
            "author" => "caiofior",
            "version" => "0.1"
        );
    }
	public function initialize($params)
	{
            $this->config = array();
            foreach($this->selectAll(
                    'SELECT `path`,`value` FROM `core_config_data`
                     WHERE `path` = "web/unsecure/base_url"') as $value) {
                $this->config [$value['path']]=$value['value'];
            }
            $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'var'.DIRECTORY_SEPARATOR;
            if (!is_dir($file))
                mkdir($file);
            
            $file .= 'export'.DIRECTORY_SEPARATOR;
		if (!is_dir($file))
                mkdir($file);
                
            $file .= 'googlemerchant.csv';
		if (is_file($file))
                    unlink($file);    
            $this->googleMerchantHandle = fopen($file, 'w');
            fwrite($this->googleMerchantHandle, implode("\t","\xEF\xBB\xBF".array_keys($this->columns)).PHP_EOL);
	}
	
	public function processItemBeforeId($item,$params=null)
	{
            $googleMerchantData = $this->columns;
            $googleMerchantData['id']=$item['sku'];
            $googleMerchantData['title']=$item['name'];
            $googleMerchantData['description']=$item['description'];
            $googleMerchantData['google product category']='';
            $googleMerchantData['product type']=  str_replace('/', ' > ', $item['categories']);
            $googleMerchantData['link']=$this->config['web/unsecure/base_url'].$item['url_path'];
            $googleMerchantData['image link']=$this->config['web/unsecure/base_url'].'/media/catalog/product/'.$item['image'];
		return true;
	}
	
}