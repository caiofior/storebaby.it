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
    private $columns = array(
        'id',
        'title',
        'description',
        'google product category',
        'product type',
        'link',
        'image link',
        'condition',
        'price',
        'availability',
        'brand'

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
            fwrite($this->googleMerchantHandle, implode("\t","\xEF\xBB\xBF".$this->columns).PHP_EOL);
	}
	
	public function processItemBeforeId($item,$params=null)
	{
            $googleMerchantData = array();
            $googleMerchantData['id']=$item['sku'];
            $googleMerchantData['title']=$item['name'];
            $googleMerchantData['description']=$item['description'];
            $googleMerchantData['google product category']='';
            $googleMerchantData['product type']=  str_replace('/', ' > ', $item['categories']);
            $googleMerchantData['link']='';
            var_dump($item);
                        die();
		return true;
	}
	
}