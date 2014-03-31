<?php

require_once '../app/Mage.php';
 // if your are not root folder then write the proper path like publichtml/magento/app/Mage.php

Mage::app('default');

$catalogRule = Mage::getModel('catalogrule/rule');
 $catalogRule->applyAll();

Mage::app()->removeCache('catalog_rules_dirty');

?>
