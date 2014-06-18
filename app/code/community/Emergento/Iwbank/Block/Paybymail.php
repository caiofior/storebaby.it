<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
class Emergento_Iwbank_Block_Paybymail extends Mage_Core_Block_Template
{
protected function _construct()
    {

        parent::_construct();

        
    }

    public function _toHtml() {
            return parent::_toHtml();
    }
    public function getCards($mode='json'){
        return Mage::getModel('emergento_iwbank/iwbank')->getAllowedCards($mode);
    }
  }
