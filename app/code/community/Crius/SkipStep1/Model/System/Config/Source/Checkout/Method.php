<?php
class Crius_SkipStep1_Model_System_Config_Source_Checkout_Method
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'guest', 'label'=>Mage::helper('checkout')->__('Guest')),
            array('value'=>'register', 'label'=>Mage::helper('checkout')->__('Register')),
        );
    }
}
