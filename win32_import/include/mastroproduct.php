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
    protected $data = array();

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
    public function __construct(ProductFromCsv $productFromCsv) {
        $this->productFromCsv = $productFromCsv;
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
        if (!array_key_exists('DESCRIZIONE', $this->data)) {
             echo 'Missing mastro data'.PHP_EOL;
             return array();
        }
        $key = $this->generateKey($this->data['DESCRIZIONE']);
        $mastroCategory = $this->data['REPARTO'];
        if (
                in_array($mastroCategory, array('15.12','15.13','15.14','15.15','15.16','15.17','15.19')) 
                && preg_match('/fix/i', $this->data['DESCRIZIONE'])    
                )
             $mastroCategory = '15.c1';   


        $categories = $this->productFromCsv->getCategory($mastroCategory);
        if ($categories == false) {
            fwrite($this->productFromCsv->getMagentoExcludedCsvHandler(), $this->data['EAN13'].',"Category not found","'.$mastroCategory.'"'.PHP_EOL);
            return array();
        }
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
        if ($this->data['DESCRIZIONE'] == '') {
           fwrite($this->productFromCsv->getMagentoExcludedCsvHandler(), $this->data['EAN13'].',"Missing description","","'. implode(',',$this->data) .'"'.PHP_EOL);
           return array();
        }
        $this->data['DESCRIZIONE'] = ucfirst(strtolower(stripslashes($this->data['DESCRIZIONE'])));
        if ($this->data['TESTO'] == '')
            $this->data['TESTO']=$this->data['DESCRIZIONE'];
        $this->data['TESTO'] = preg_replace('/^DESCRIZIONE[ (\<br\/\>)]*/i', '', stripslashes($this->data['TESTO']));
        
        $magentoProduct->setData('name', $this->fixCategoryName($mastroCategory));
        if ($this->data['MARCA'] != '') {
            $this->data['MARCA'] = trim(strtolower(stripslashes($this->data['MARCA'])));
            switch ($this->data['MARCA']) {
               case 'jane':
                  $this->data['MARCA'] = 'jane\'';
               break;
               case 'philips':
                  $this->data['MARCA'] = 'philips avent';
               break;
            }
            $magentoProduct->setData('manufacturer', ucfirst($this->data['MARCA']).'::['.str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',$this->data['MARCA']))).']');
            $this->data['MARCA']=ucfirst($this->data['MARCA']);
        }
        
        $magentoProduct->setData('meta_title',
            $magentoProduct->getData('name')
	);
	$magentoProduct->setData('meta_description',
            $this->data['DESCRIZIONE'].' a soli '.
            intval($this->data['VENDITA']).' euro. Vasto assortimento di '.
            strtolower(array_shift($categoriesBranches)).' della '.$this->data['MARCA'].
            ' su storebaby.it. Acquista online oggi stesso!'
	);
        $magentoProduct->setData('description', $this->data['TESTO']);
        $magentoProduct->setData('short_description', $magentoProduct->getData('name'));
        $magentoProduct->setData('meta_keyword', 'articoli infanzia,'.implode(',',  array_slice(array_unique(array_merge($categoriesWords,$nameWords)),0,5)));
        $magentoProduct->setData('url_key', 'articoli_infanzia_'.str_replace(array(' ','/','.'),'_',strtolower( $magentoProduct->getData('name') )));
        $magentoProduct->setData('url_path', 'articoli_infanzia_'.str_replace(array(' ','/','.'),'_',strtolower( $magentoProduct->getData('name') )).'.html');
        $weight = $this->productFromCsv->getWeight($mastroCategory);
        if ($weight ==false) $weight = '0.1';
        $magentoProduct->setData('weight', $weight);
        $iva =  $this->data['IVA'];
        if ($iva == '21') $iva = '22';
        //$magentoProduct->setData('price',$this->data['VENDITA']+1*($iva/100));
        if ($this->data['VENDITA'] == '') {
           fwrite($this->productFromCsv->getMagentoExcludedCsvHandler(), $this->data['EAN13'].',"Missing price",""'.PHP_EOL);
           return array();
        }
        $magentoProduct->setData('price',str_replace(',','.',str_replace('.','',$this->data['VENDITA'])));
        $magentoProduct->setData('tax_class_id', $iva);        
        if(get_class($this) == 'TestMastroProduct') {
            return $magentoProduct;
        }
        $magentoProduct->setData('qty',max(0,$this->data['ESISTENZA']-$this->data['IMPEGNATO']));
        
        preg_match('/\\\[^\\\]+$/', $this->data['FOTO_ARTICOLO'], $fileName);
        
        if($this->data['LOCAZIONE_MAG']=='99' && $magentoProduct->getData('qty')== 0) {
            if (sizeof($fileName) == 1 && $fileName[0] != '') {
               $fileName = str_replace('\\', '', $fileName[0]);
               $image = $this->mastroImageColl->deleteImage($fileName);
            }
            fwrite($this->productFromCsv->getMagentoExcludedCsvHandler(), $this->data['EAN13'].',"Code 99","","'. implode(',',$this->data) .'"'.PHP_EOL);
            return array();
        }
        
        
        if (sizeof($fileName) == 1 && $fileName[0] != '') {
            $fileName = str_replace('\\', '', $fileName[0]);
            $image = $this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND');
            $magentoProduct->setData('image',$image);
            //$image = $this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND_THUMBNAIL');
            $magentoProduct->setData('small_image',$image);
            $magentoProduct->setData('thumbnail',$image);
        }
        if (is_array($fileName)) {
            $fileName = current($fileName);
        }
        if ( $magentoProduct->getData('image') != '') {
            $getModifiedData = $this->mastroImageColl->getModifiedData($this->data);
            
            if ($getModifiedData != '') {
                if ($this->mastroImageColl->getModifiedDescription($this->data,$fileName)) {
                    $magentoProduct->setData('shared_on_social_networks',  '0');
                }
                $magentoProduct->setData('news_from_date',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData));
                $magentoProduct->setData('news_to_date',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData+3600 * 24 * 7 * 7 ));
                $magentoProduct->setData('modify_data',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData));
                $magentoProduct->setData('create_data',  strftime('%Y-%m-%d %H:%M:%S',$this->mastroImageColl->getCreationData($this->data)));
                
                
                foreach (self::$headers as $mastro) {
                    $magentoProduct->setData('MASTRO_'.$mastro, stripslashes($this->data[$mastro]));
                }
                if($key != '') {
                    if (!key_exists($key,$this->related))
                            $this->related[$key] = '';
                    else if ($this->related[$key] != '' )
                            $this->related[$key] .= ',';
                    $this->related[$key] .= $this->data['EAN13'];
                }
                $customPrices = $this->productFromCsv->getCustomPrices($magentoProduct->getData('sku'));
                
                $useCupon = '';
                if (sizeof($customPrices)>0) {
                    foreach($customPrices as $store =>$customPrice ) {
                        if ($useCupon =='') $useCupon = $customPrice['use_cupon'];
                    }
                }
                
                $magentoProduct->setData('use_cupon',$useCupon);
                
                $magentoProductColl = array(
                     0=>$magentoProduct
                );
                if ($this->data['ESISTENZA']-$this->data['IMPEGNATO'] == 0) {
                    $id=sizeof($magentoProductColl);
                    $magentoProductColl[$id]= clone $magentoProduct;
                    $magentoProductColl[$id]->setData('store',  'retail'); 
                    $magentoProductColl[$id]->setData('status', '2'); 					 
                }
                
                if (sizeof($customPrices)>0) {
                   foreach ($magentoProductColl as $magentoProductItem) {
                     foreach($customPrices as $store =>$customPrice ) {
                        if ($store == $magentoProductItem->getData('store')) {                     
                           $magentoProductItem->setData('price',$customPrice['special_price']);
                           $magentoProductItem->setData('show_cupon',$customPrice['use_cupon']);
                           unset ($customPrices[$store]);
                           fwrite($this->productFromCsv->getCustomPricesHandler(),implode(',', array($magentoProductItem->getData('sku'),$store,$customPrice['special_price'],$customPrice['use_cupon'])).PHP_EOL);
                        }
                     }
                  }
                  if (sizeof($customPrices)>0) {
                     foreach($customPrices as $store =>$customPrice ) {
                        $id=sizeof($magentoProductColl);
                        $magentoProductColl[$id]= clone $magentoProduct;
                        $magentoProductColl[$id]->setData('store',  $store); 
                        $magentoProductColl[$id]->setData('price', $customPrice['special_price']);
                        $magentoProductColl[$id]->setData('show_cupon',$customPrice['use_cupon']);
                        fwrite($this->productFromCsv->getCustomPricesHandler(),implode(',', array($magentoProduct->getData('sku'),$store,$customPrice['special_price'],$customPrice['use_cupon'])).PHP_EOL);
                     }
                  }
                }
                
                return $magentoProductColl;
            }
            else {
               fwrite($this->productFromCsv->getMagentoExcludedCsvHandler(), $this->data['EAN13'].',"Nothing modified","","'. implode(',',$this->data) .'"'.PHP_EOL);
               return array();
            };
            
        } else {
            fwrite($this->productFromCsv->getMagentoExcludedCsvHandler(), $this->data['EAN13'].',"Missing image","","'. implode(',',$this->data) .'"'.PHP_EOL);
            return array();
        }
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
         preg_match('/[^ ]*( [^ ]*)?( [^ ]*)?( [^ ]*)?/',strtolower(iconv('UTF-8', 'ASCII//TRANSLIT',$descrizione)),$key);
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
     /**
      * Fixes category name
      * @param sting $mastroCategory
      */
     private function fixCategoryName ($mastroCategory) {
        $description = $this->data['DESCRIZIONE'];
        $secondWord = trim(strtolower($this->data['MARCA']));
        $lastWord = str_replace('/', '', $this->data['COD.PRODOTTO']);
        switch ('#'.$mastroCategory) {
           case '#8.1';
           case '#08.1';
               $firstWord = 'Cancelletto di sicurezza';
           break;
           case '#8.2';    
           case '#08.2';
               $firstWord = 'Barriera letto';
           break;
           case '#9.1';
           case '#09.1';
               $firstWord = 'Succhietto gommotto';
           break;
           case '#9.2';
           case '#09.2';
               $firstWord = 'Biberon';
           break;        
           case '#9.3';
           case '#09.3';
               $firstWord = 'Tettarelle';
           break;        
           case '#9.4';
           case '#09.4';          
               $firstWord = 'Thermos';
           break;        
           case '#9.5';
           case '#09.5';
               $firstWord = 'Accappatoio';
           break;      
           case '#9.6';
           case '#09.6';
               $firstWord = 'Occhiali';
           break;        
           case '#9.7';
           case '#09.7';
               $firstWord = 'Borsa nursery';
           break;
           case '#11.1';
               $firstWord = 'Bagnetto';
           break;
           case '#11.2';
               $firstWord = 'Faciatoio';
           break;
           case '#11.3';
               $firstWord = 'Vasino';
           break;
           case '#12.1';
               $firstWord = 'Radioline neonati';
           break;
           case '#12.2';
               $firstWord = 'Scaldabiberon';
           break;
           case '#12.3';
               $firstWord = 'Sterilizzatore';
           break;
           case '#12.4';
               $firstWord = 'Umidificatore';
           break;
           case '#12.5';
               $firstWord = 'Termometro';
           break;
           case '#12.6';
               $firstWord = 'Bilancia neonato';
           break;
           case '#12.7';
               $firstWord = 'Aereosol';
           break;
           case '#12.8';
               $firstWord = 'Tiralatte';
           break;
           case '#13.1';
               $firstWord = 'Latte neonato';
           break;
           case '#14.1';
               $firstWord = 'Giochi da giardino';
           break;
           case '#14.10';
               $firstWord = 'Bambole e accessori';
           break;
           case '#14.12';
               $firstWord = 'Gioco in legno';
           break;
           case '#14.2';
               $firstWord = 'Giocattolo elettrico';
           break;
           case '#14.3';
               $firstWord = 'Giostrine carillon';
           break;
           case '#14.4';
               $firstWord = 'Tappeto palestrina';
           break;
           case '#14.5';
               $firstWord = 'Gioco neonati';
           break;
           case '#14.6';
               $firstWord = 'Banco mobilità gioco';
           break;
           case '#14.61';
               $firstWord = 'Altalena dondolino';
           break;
           case '#14.7';
               $firstWord = 'Cavalcabile';
           break;
           case '#14.8';
               $firstWord = 'Giocattolo';
           break;
           case '#15.1';
               $firstWord = 'Passeggino trio';
           break;
           case '#15.12';
               $firstWord = 'Navicella auto';
           break;
           case '#15.13';
           case '#15.14';
           case '#15.15';
           case '#15.16';
           case '#15.17';
           case '#15.18';
           case '#15.20';
               $firstWord = 'Seggiolino auto';
           break;
           case '#15.2';
               $firstWord = 'Passeggino duo';
           break;
           case '#15.21';
               $firstWord = 'Seggiolone pappa';
           break;
           case '#15.3';
               $firstWord = 'Passeggino';
           break;
           case '#15.31';
               $firstWord = 'Box';
           break;
           case '#15.4';
               $firstWord = 'Passeggino gemellare';
           break;
           case '#15.41';
               $firstWord = 'Girello';
           break;
           case '#15.5';
               $firstWord = 'Lettini da campeggio - viaggio';
           break;
           case '#15.51';
               $firstWord = 'Sdraietta';
           break;
           case '#15.6';
               $firstWord = 'Marsupio';
           break;
           case '#15.61';
               $firstWord = 'Altalena dondolino';
           break;
           case '#15.7';
               $firstWord = 'Ombrellino passeggino';
           break;
           case '#15.8';
               $firstWord = 'Sacco passeggino';
           break;
           case '#15.c1';
              $firstWord = 'Seggiolino auto isofix';
           break;
           case '#17.3';
              $firstWord = 'Cuscino allattamento';
           break;
           case '#17.4';
              $firstWord = 'Sacco nanna';
           break;
           case '#17.5';
              $firstWord = 'Tappeto';
           break;
           case '#19.1';
              $firstWord = 'Seggiolino bicicletta';
           break;
         }
         if (isset($firstWord)) {
                $description = preg_replace('/\b'.preg_quote(trim($firstWord)).'\b/i', '', $description);
         } else {
                $firstWord='';
         }
         if ($secondWord != '') {
            $description = preg_replace('/\b'.preg_quote(trim($secondWord)).'\b/i', '', $description);
         }
         if ($lastWord != '') {
            $description = preg_replace('/\b'.preg_quote(trim($lastWord)).'\b/i', '', $description);
         }
         $description = $firstWord.' '.ucfirst($secondWord).' '.ucfirst(strtolower($description)).' '.$lastWord;
         $description = trim(preg_replace('/ +/', ' ', $description));
         return $description;
     }
}
