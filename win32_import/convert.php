<?php
require (__DIR__.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'autoload.php');
$product_from_csv = new ProductFromCsv(__DIR__.DIRECTORY_SEPARATOR.'config.ini');
$magentoProduct = $product_from_csv->import();
var_dump($magentoProduct);