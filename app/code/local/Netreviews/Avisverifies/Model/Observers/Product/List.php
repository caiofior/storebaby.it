<?php 
class Netreviews_Avisverifies_Model_Observers_Product_List {
    public function updateProductRatingSummary(Varien_Event_Observer $observer){
        if (Mage::helper('avisverifies/Data')->isActive()) {
            $globalVar = Mage::registry('netreview_isProductlist');
            if (empty($globalVar)) {
                Mage::register('netreview_isProductlist',true);
            }
            $_productCollection = $observer->getData('collection');
            foreach ($_productCollection as $_product) {
                $_product->setData('rating_summary',true);
            }
        }
    }
} 

