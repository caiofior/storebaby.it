<?php
/**
 * Magento Product class
 */
class MagentoProduct {
    /**
     * Reference to product from csv
     * @var ProductFromCsv
     */
    private $productFromCsv;
    /**
     * Product data
     * @var array
     */
    private $data = array();
    /**
     * Headres of the csv
     * @var array
     */
    private static $headers = array(
        'ean13',
        'descrizione',
        'marca',
        'cod.prodotto',
        'reparto',
        'descrizione_reparto',
        'famiglia',
        'descrizione_famiglia',
        'settore',
        'descrizione_settore',
        'fornitore',
        'contropartita',
        'peso',
        'iva',
        'vendita',
        'esistenza',
        'in_ordine',
        'impegnato',
        'riordino',
        'sottoscorta',
        'locazione_mag',
        'foto_articolo',
        'testo'
        
    );
    /**
     * Creates reference to PrductFromCsv
     * @param ProductFromCsv $product_from_csv
     */
    public function __construct(ProductFromCsv $product_from_csv) {
        $this->productFromCsv = $product_from_csv;
    }
    /**
     * Sets product data
     * @param string $key
     * @param string $value
     */
    public function setData($key, $value) {
        $this->data[$key]=$value;
    }
    /**
     * Gets product data
     * @param string $key
     * @return string|null
     */
    public function getData($key) {
        if (key_exists($key, $this->data))
            return $this->data[$key];
    }
    /**
     * Empties product data
     */
    public function emptyData() {
        $this->data=array();
    }
    /**
     * Creates a csv row
     * @return string
     */
    public function getCsvRow() {
        $data = $this->data;
        foreach ($data as $key => $value) {
            if (preg_match('/[^\-0-9 ,\.]/', $value))
                $data[$key]='"'.str_replace (',','.',$value).'"';
        }
        return implode(';',$data);
    }
    
}

