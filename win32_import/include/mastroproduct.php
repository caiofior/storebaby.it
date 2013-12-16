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
            $magentoProduct->setData('photo',$this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND'));
            $magentoProduct->setData('photo_thumbnail',$this->mastroImageColl->resizeImage($fileName,'CONVERT_COMMAND_THUMBNAIL'));
            
        }
        
        if ( $magentoProduct->getData('photo') != '') {
            $getModifiedData = $this->mastroImageColl->getModifiedData($this->data);
            if ($getModifiedData != '') {
                $magentoProduct->setData('_category_names',$this->productFromCsv->getCategory($this->data['REPARTO']));
                $magentoProduct->setData('stock',max(0,$this->data['ESISTENZA']-$this->data['IMPEGNATO']));
                $magentoProduct->setData('price',$this->data['VENDITA']+1*($this->data['IVA']/100));
                $magentoProduct->setData('is_new',($getModifiedData-time()) < 3600 * 24 * 7 * 7 );
                $magentoProduct->setData('modify_data',  strftime('%Y-%m-%d %H:%M:%S',$getModifiedData));
                $magentoProduct->setData('create_data',  strftime('%Y-%m-%d %H:%M:%S',$this->mastroImageColl->getCreationData($this->data)));
                foreach (self::$headers as $mastro)
                    $magentoProduct->setData('ZZ_'.$mastro, $this->data[$mastro]);
            }
            return $magentoProduct;
        } else
            return false;
    }
    /**
     * Returns product data
     * @return array
     */
    public function getData() {
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
}
