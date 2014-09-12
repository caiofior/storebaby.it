<?php

require_once  __DIR__.'/../app/Mage.php';
 // if your are not root folder then write the proper path like publichtml/magento/app/Mage.php

Mage::app('storebaby');
Mage::getModel( 'turpentine/varnish_admin' )->applyConfig();
Mage::getModel( 'turpentine/varnish_admin' )->flushAll();
foreach( Mage::helper( 'turpentine/varnish' )->getSockets() as $socket ) {
	var_dump($socket->status());
}

?>
