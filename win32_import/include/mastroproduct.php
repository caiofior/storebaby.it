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
        'TESTO'
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
        preg_match('/\\\[^\\\]+$/', $this->data['FOTO_ARTICOLO'], $fileName);
        if (sizeof($fileName) == 1 && $fileName[0] != '') {
            $fileName = str_replace('\\', '', $fileName[0]);
            $magentoProduct->setData('image',$this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND'));
            $magentoProduct->setData('small_image',$this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND_THUMBNAIL'));
        }
        
        if ( $magentoProduct->getData('image') != '') {
            $getModifiedData = $this->mastroImageColl->getModifiedData($this->data);
            $key = $this->generateKey($this->data['DESCRIZIONE']);
            
            if ($getModifiedData != '') {
                $magentoProduct->setData('categories',$this->productFromCsv->getCategory($this->data['REPARTO']));
                $magentoProduct->setData('sku',$this->data['EAN13']);
                $magentoProduct->setData('xre_skus',$key);
                $magentoProduct->setData('name', ucfirst(strtolower($this->data['DESCRIZIONE'])));
                $magentoProduct->setData('brand', ucfirst(strtolower($this->data['MARCA'])));
                $magentoProduct->setData('meta_title', 'Articoli infanzia');
                $magentoProduct->setData('meta_description', 'Articoli infanzia '.ucfirst(strtolower($this->data['DESCRIZIONE'])));
                $magentoProduct->setData('url_key', 'articoli_infanzia_'.str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',trim($this->data['DESCRIZIONE'])))));
                $magentoProduct->setData('url_path', 'articoli_infanzia_'.str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',trim($this->data['DESCRIZIONE'])))).'.html');
                $magentoProduct->setData('weight', '0.1');
                $magentoProduct->setData('price',$this->data['VENDITA']+1*($this->data['IVA']/100));
                $magentoProduct->setData('description', preg_replace('/^DESCRIZIONE[ (\<br\>)]*/i', '', $this->data['TESTO']));
                $magentoProduct->setData('short_description', preg_replace('/\..*/','',preg_replace('/^DESCRIZIONE[ (\<br\>)]*/i', '', $this->data['TESTO'])));
                $magentoProduct->setData('meta_keyword', 'Articoli infanzia');
                $magentoProduct->setData('qty',max(0,$this->data['ESISTENZA']-$this->data['IMPEGNATO']));
                if('LOCAZIONE_MAG'=='99' && $magentoProduct->getData('qty')== 0)
                    return false;
                $magentoProduct->setData('news_from_date',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData));
                $magentoProduct->setData('news_to_date',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData+3600 * 24 * 7 * 7 ));
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
            }
            return $magentoProduct;
        } else
            return false;
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
      * Genereate a keu form a produc name
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
     public function getReSkus ($descrizione) {
         $key = $this->generateKey($descrizione);
         if (key_exists($key,$this->related))
            return $this->related[$this->generateKey($descrizione)];
         else return '';
     }
}
