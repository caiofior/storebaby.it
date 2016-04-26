<?php
require_once '../app/Mage.php';
umask(0);
Mage::app();
//Mage::setIsDeveloperMode(true);

$rule = Mage::getModel('catalogRule/rule')->load(8);

var_dump($rule->getData());
var_dump($rule->getMatchingProductIds());
