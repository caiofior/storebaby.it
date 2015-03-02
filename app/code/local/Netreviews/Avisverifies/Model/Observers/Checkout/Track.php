<?php
// xml: catalog_controller_product_view
class Netreviews_Avisverifies_Model_Observers_Checkout_Track {
        
    public function trackCheckout(Varien_Event_Observer $observer) {
        
        $ids = $observer->getEvent()->getOrderIds();
        $globalVar = Mage::registry('AV_OrderIds');
        if (empty($globalVar)) {
            Mage::register('AV_OrderIds',$ids);
        }
        $layout = Mage::app()->getLayout();
        $block = $layout->createBlock('avisverifies/observers_checkout')
                        ->setTemplate('avisverifies/observers/checkout.phtml');
        $layout->getBlock('content')->append($block);
    }
}
