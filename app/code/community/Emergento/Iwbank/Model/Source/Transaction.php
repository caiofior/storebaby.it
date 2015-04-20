<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */

/* Emergento */
class Emergento_Iwbank_Model_Source_Transaction
{
    public function toOptionArray()
    {
        $options =  array();
        
        foreach (Mage::getSingleton('iwbank/config')->getTransactionModes() as $code => $name) {
            $options[] = array(
                   'value' => $code,
                   'label' => $name
            );
        }
        return $options;
    }
}

