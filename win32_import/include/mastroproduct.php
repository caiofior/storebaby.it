<?php

/**
 * Mastro product data
 */
class MastroProduct {

    /**
     * Reference to Product from csv
     * @var ProductFromCsv
     */
    private $productFromCsv;

    /**
     * Reference to magento product
     * @var MagentoProduct
     */
    private $magentoProduct;

    /**
     * Mastro Product data
     * @var array
     */
    private $data = array();

    /**
     * Reference to Mastro Image Collection
     * @var MastroImageColl
     */
    private $mastroImageColl;
    /**
     * related products
     * @var array
     */
    private $related = array();
    /**
     * Collumn headers ima Mastro Csv
     * @var array 
     */
    private static $headers = array(
        'EAN13',
        'DESCRIZIONE',
        'MARCA',
        'COD.PRODOTTO',
        'REPARTO',
        'DESCRIZIONE_REPARTO',
        'FAMIGLIA',
        'DESCRIZIONE_FAMIGLIA',
        'SETTORE',
        'DESCRIZIONE_SETTORE',
        'FORNITORE',
        'CONTROPARTITA',
        'PESO',
        'IVA',
        'VENDITA',
        'ESISTENZA',
        'IN_ORDINE',
        'IMPEGNATO',
        'RIORDINO',
        'SOTTOSCORTA',
        'LOCAZIONE_MAG',
        'FOTO_ARTICOLO',
        'TESTO',
        'DATA_MODIFICA',
        'DATA_SCARICO',
        'DATA_CARICO'
    );

     /**
     * 
     * @param ProductFromCsv $product_from_csvInstantiates references to image collection e magento product
     */
    public function __construct(ProductFromCsv $product_from_csv) {
        $this->productFromCsv = $product_from_csv;
        $this->magentoProduct = new MagentoProduct($this);
        $this->mastroImageColl = new MastroImageColl($this);
    }

    /**
     * Imports data from a line of Mastro Csv
     * @param string $string
     */
    public function importFromCsvRow($string) {
        $data = explode('**', $string);
        while (sizeof($data) < sizeof(self::$headers))
            $data[] = '';
        while (sizeof($data) > sizeof(self::$headers))
            unset($data[sizeof($data) - 1]);
        $this->data = array_combine(self::$headers, $data);
        foreach ($this->data as $key => $value)
            $this->data[$key] = trim($value);
    }

    /**
     * Returns reference to  Products from Csv
     * @return ProductFromCsv
     */
    public function getProductFromCsv() {
        return $this->productFromCsv;
    }

    /**
     * Creates te associated magento product
     * @return MagentoProduct
     */
    public function createMagentoProduct() {
        $magentoProduct = $this->magentoProduct;
        $magentoProduct->emptyData();
        
        $key = $this->generateKey($this->data['DESCRIZIONE']);
        $mastroCategory = $this->data['REPARTO'];
        if (
                in_array($mastroCategory, array('15.12','15.13','15.14','15.15','15.16','15.17','15.19')) 
                && preg_match('/fix/i', $this->data['DESCRIZIONE'])    
                )
             $mastroCategory = '15.c1';   


        $categories = $this->productFromCsv->getCategory($mastroCategory);
        if ($categories == false)
            return $this->data['EAN13'].',"Category not found","'.$mastroCategory.'"';
        $categoriesBranches = array_unique(preg_split('/[;,\/]/', $categories));
        $rawCategoriesWords = array_unique(preg_split('/[ ;,\/]/', strtolower($categories)));
        $categoriesWords = array();
        foreach($rawCategoriesWords as $rawCategoriesWord) {
            if (strlen($rawCategoriesWord)>3)
                $categoriesWords[] = $rawCategoriesWord;
        }
        $rawNameWords = array_unique(preg_split('/[ ;,\/]/', strtolower($this->data['DESCRIZIONE'])));
        $nameWords = array();
        foreach($rawNameWords as $rawNameWord) {
            if (strlen($rawNameWord)>3)
                $nameWords[] = $rawNameWord;
        }
        $magentoProduct->setData('categories',$categories);
        $magentoProduct->setData('sku',$this->data['EAN13']);
        $magentoProduct->setData('xus_skus',$this->getReSkus($key));
        if ($this->data['DESCRIZIONE'] == '')
            return $this->data['EAN13'].',"Missing description",""';
        $magentoProduct->setData('name', ucfirst(strtolower(stripslashes($this->data['DESCRIZIONE']))));
        if ($this->data['MARCA'] != '')
            $magentoProduct->setData('manufacturer', ucfirst(strtolower(stripslashes($this->data['MARCA']))).'::['.str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',trim(stripslashes($this->data['MARCA']))))).']');
        $magentoProduct->setData('meta_title', 'Articoli infanzia - '.ucfirst(strtolower(stripslashes($this->data['DESCRIZIONE']))));
        $magentoProduct->setData('meta_description', 'Articoli infanzia > '.implode(' > ',$categoriesBranches).' > '.ucfirst(strtolower(stripslashes($this->data['DESCRIZIONE']))));
        $magentoProduct->setData('url_key', 'articoli_infanzia_'.str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',trim(stripslashes($this->data['DESCRIZIONE']))))));
        $magentoProduct->setData('url_path', 'articoli_infanzia_'.str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',trim(stripslashes($this->data['DESCRIZIONE']))))).'.html');
        $weight = $this->productFromCsv->getWeight($mastroCategory);
        if ($weight ==false) $weight = '0.1';
        $magentoProduct->setData('weight', $weight);
        $iva =  $this->data['IVA'];
        if ($iva == '21') $iva = '22';
        //$magentoProduct->setData('price',$this->data['VENDITA']+1*($iva/100));
        if ($this->data['VENDITA'] == '')
            return $this->data['EAN13'].',"Missing price",""';
	$magentoProduct->setData('price',$this->data['VENDITA']);
        $magentoProduct->setData('tax_class_id', $iva);
        $magentoProduct->setData('description', preg_replace('/^DESCRIZIONE[ (\<br\/\>)]*/i', '', stripslashes($this->data['TESTO'])));
        $magentoProduct->setData('short_description', preg_replace('/\..*/','.',preg_replace('/^DESCRIZIONE[ (\<br\/\>)]*/i', '', stripslashes($this->data['TESTO']))));
        $magentoProduct->setData('meta_keyword', 'articoli infanzia,'.implode(',',  array_slice(array_unique(array_merge($categoriesWords,$nameWords)),0,5)));
        $magentoProduct->setData('qty',max(0,$this->data['ESISTENZA']-$this->data['IMPEGNATO']));
        if($this->data['LOCAZIONE_MAG']=='99' && $magentoProduct->getData('qty')== 0)
            return $this->data['EAN13'].',"Code 99",""';
        
        preg_match('/\\\[^\\\]+$/', $this->data['FOTO_ARTICOLO'], $fileName);
        if (sizeof($fileName) == 1 && $fileName[0] != '') {
            $fileName = str_replace('\\', '', $fileName[0]);
            $image = $this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND');
            $magentoProduct->setData('image',$image);
            //$image = $this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND_THUMBNAIL');
            $magentoProduct->setData('small_image',$image);
            $magentoProduct->setData('thumbnail',$image);
        }
        if ( $magentoProduct->getData('image') != '') {
            $getModifiedData = $this->mastroImageColl->getModifiedData($this->data);
            
            
            if ($getModifiedData != '') {
                if ($this->mastroImageColl->getModifiedDescription($this->data,$fileName)) {
                    $magentoProduct->setData('shared_on_social_networks',  '0');
                    $magentoProduct->setData('news_from_date',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData));
                    $magentoProduct->setData('news_to_date',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData+3600 * 24 * 7 * 7 ));
                }
                $magentoProduct->setData('modify_data',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData));
                $magentoProduct->setData('create_data',  strftime('%Y-%m-%d %H:%M:%S',$this->mastroImageColl->getCreationData($this->data)));
                
                
                foreach (self::$headers as $mastro) {
                    $magentoProduct->setData('MASTRO_'.$mastro, $this->data[$mastro]);
                }
                if($key != '') {
                    if (!key_exists($key,$this->related))
                            $this->related[$key] = '';
                    else if ($this->related[$key] != '' )
                            $this->related[$key] .= ',';
                    $this->related[$key] .= $this->data['EAN13'];
                }
                return $magentoProduct;
            }
            else $this->data['EAN13'].',"Nothing modified",""';
            
        } else
            return $this->data['EAN13'].',"Missing image",""';
    }
    /**
     * Returns product data
     * @param string $key
     * @return array|string
     */
     public function getData($key = null) {
        if (!is_null($key) && key_exists($key, $this->data))
                return $this->data[$key];
        else 
            return $this->data;
    }
     /**
     * Sets product data
     * @return array
     */
    public function setData($key,$value) {
        return $this->data[$key]=$value;
    }
    /**
     * Returns headers
     * @return array
     */
    public function getHeaders () {
        return self::$headers;
    }
      /**
      * Genereate a key form a produc name
      * @param string $descrizione
      * @return string
      */
     private function generateKey($descrizione) {
         preg_match('/[^ ]*( [^ ]*)?( [^ ]*)?/',strtolower(iconv('UTF-8', 'ASCII//TRANSLIT',$descrizione)),$key);
         if (sizeof($key)>0)
             return $key[0];
         else return '';
         
     }
     /**
      * Return related sku
      * @return array
      */
     public function getReSkus ($key) {
         if (key_exists($key,$this->related))
            return $this->related[$key];
         else return '';
     }
}
