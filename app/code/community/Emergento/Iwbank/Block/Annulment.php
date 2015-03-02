<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
/* Emergento */
class Emergento_Iwbank_Block_Annulment extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('iwbank/annulment.phtml');
    }


    public function getContinueShoppingUrl()
    {
        return Mage::getUrl('checkout/cart');
    }
}
