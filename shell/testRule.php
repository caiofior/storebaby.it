<?php
require_once '../app/Mage.php';
umask(0);
Mage::app();
Mage::setIsDeveloperMode(true);

//$rule = Mage::getModel('catalogRule/rule')->load(5);

//var_dump($rule->getData());
//var_dump($rule->getMatchingProductIds());

$product = Mage::getModel('catalog/product')->load(1);
var_dump(get_class($product->getPriceModel()));
var_dump($product->getSpecialPrice());