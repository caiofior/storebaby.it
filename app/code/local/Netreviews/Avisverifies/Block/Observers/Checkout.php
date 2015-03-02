<?php
class Netreviews_Avisverifies_Block_Observers_Checkout extends Mage_Core_Block_Template
{
    protected $order;
    public function getOrder() {
       $this->order = Mage::getModel('sales/order')->load(Mage::registry('AV_OrderIds'));
       return $this->order;
    }
}

