<?php

require_once __DIR__.'/../app/Mage.php';
 // if your are not root folder then write the proper path like publichtml/magento/app/Mage.php

Mage::app('storebaby');

$catalogRule = Mage::getModel('catalogrule/rule');
 $catalogRule->applyAll();

Mage::app()->removeCache('catalog_rules_dirty');

?>
