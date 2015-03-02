<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */


/* Emergento */
class Emergento_Iwbank_Helper_Data extends Mage_Payment_Helper_Data
{
	 const XML_PATH_PAYMENT_METHODS = 'payment';
	 
    public function getPaymentMethods($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, $store);
    }
}
