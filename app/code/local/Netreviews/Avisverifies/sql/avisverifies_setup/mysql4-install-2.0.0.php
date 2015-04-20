<?php // updagrade script Name : upgrade-2.0.0-2.5.0.php ---> upgrade-{old version}-{newest version}.php 
$installer = $this;
$installer->startSetup();
$helper = Mage::helper('avisverifies/Install');
$helper->addFields();
$helper->createTables();
$installer->endSetup();