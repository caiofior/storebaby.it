<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
 class Emergento_Iwbank_Model_Iwbank_Observer 
{
    public function __construct()
    {
    }
    /**
     * Applies the special price percentage discount
     * @param   Varien_Event_Observer $observer
     * @return  Xyz_Catalog_Model_Price_Observer
     */
    public function send_transaction_email($observer)
    {
        $order = $observer->getOrder();
        
        if(Mage::app()->getStore()->isAdmin() && ($order->getPayment()->getMethodInstance()->getCode() == 'iwbank_cc')){
            Mage::getModel('emergento_iwbank/iwbank')-> setPayment($order->getIncrementId())->sendTransactionEmail();
        }
    }

}
