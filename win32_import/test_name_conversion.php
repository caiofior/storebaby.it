<?php
require (__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'autoload.php');
class TestProductFromCsv extends ProductFromCsv {
    public function __construct($sconfig_file) {
        parent::__construct($sconfig_file);
        $this->categories['15.1']='Test';
        $this->categories['15.2']='Test';
    }
}
class TestMastroProduct extends MastroProduct {
    public function __construct(ProductFromCsv $product_from_csv) {
        if (!array_key_exists('argv',$GLOBALS) || !array_key_exists(1, $GLOBALS['argv'])) {
            throw new Exception('Missing description to parse');
        }
        if (!array_key_exists('argv',$GLOBALS) || !array_key_exists(2, $GLOBALS['argv'])) {
            throw new Exception('Missing brand to parse');
        }
        if (!array_key_exists('argv',$GLOBALS) || !array_key_exists(3, $GLOBALS['argv'])) {
            throw new Exception('Missing procuct code');
        }
        if (!array_key_exists('argv',$GLOBALS) || !array_key_exists(4, $GLOBALS['argv'])) {
            throw new Exception('Missing procuct description');
        }
        parent::__construct($product_from_csv);
        $this->data['DESCRIZIONE']=$GLOBALS['argv'][1];
        $this->data['MARCA']=$GLOBALS['argv'][2];
        $this->data['COD.PRODOTTO']=$GLOBALS['argv'][3];
        $this->data['VENDITA']='10';
        $this->data['IVA']='22';
        $this->data['ESISTENZA']='1';
        $this->data['IMPEGNATO']='0';
        $this->data['FOTO_ARTICOLO']='';
        $this->data['EAN13']='8003670365679';
        $this->data['LOCAZIONE_MAG']='1';
        $this->data['REPARTO']='15.2';
        $this->data['TESTO']=$GLOBALS['argv'][4];
    }
}
$product_from_csv = new TestProductFromCsv(__DIR__.DIRECTORY_SEPARATOR.'config.ini');
try{
$mastroProduct = new TestMastroProduct($product_from_csv);
} catch (\Exception $e) {
    echo $e->getMessage().PHP_EOL;
    exit;
}
if (is_string($mastroProduct->createMagentoProduct())) {
    echo $mastroProduct->createMagentoProduct();
    exit;
}
echo PHP_EOL.'Name'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('name');
echo PHP_EOL.'Description'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('description');
echo PHP_EOL.'Short Description'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('short_description');
echo PHP_EOL.'Meta title'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('meta_title');
echo PHP_EOL.'Meta description'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('meta_description');
echo PHP_EOL.'URL key'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('url_key');
echo PHP_EOL.'URL path'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('url_path');
echo PHP_EOL.'Manufacturer'.PHP_EOL;
echo $mastroProduct->createMagentoProduct()->getData('manufacturer');
echo PHP_EOL.PHP_EOL;