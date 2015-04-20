<?php

class Netreviews_Avisverifies_Model_Observers_System {
        
    public function save(Varien_Event_Observer $observer) {
        
        $curWebsite = Mage::app()->getRequest()->getParam('website');
        $curStore   = Mage::app()->getRequest()->getParam('store');
        
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        $mageselc = new Mage_Core_Model_Config();
        $POST =  Mage::app()->getRequest()->getPost();
        $secretkey = isset($POST['groups']['system']['fields']['secretkey']['value'])? $POST['groups']['system']['fields']['secretkey']['value'] : NUll;
        $idwebsite = (isset($POST['groups']['system']['fields']['idwebsite']['value']))? $POST['groups']['system']['fields']['idwebsite']['value'] : NULL ;
        $enabledwebsite = (isset($POST['groups']['system']['fields']['enabledwebsite']['value']))? $POST['groups']['system']['fields']['enabledwebsite']['value'] : NULL ;
        $forceParentIds = (isset($POST['groups']['extra']['fields']['force_product_parent_id']['value']))? $POST['groups']['extra']['fields']['force_product_parent_id']['value'] : NULL ;
        $addReviewToProductPage = (isset($POST['groups']['extra']['fields']['addReviewToProductPage']['value']))? $POST['groups']['extra']['fields']['addReviewToProductPage']['value'] : NULL ;
        $useProductSKU = (isset($POST['groups']['extra']['fields']['useProductSKU']['value']))? $POST['groups']['extra']['fields']['useProductSKU']['value'] : NULL ;
        $productLightWidget = (isset($POST['groups']['extra']['fields']['productLightWidget']['value']))? $POST['groups']['extra']['fields']['productLightWidget']['value'] : NULL ;
        $hasjQuery = (isset($POST['groups']['extra']['fields']['hasjQuery']['value']))? $POST['groups']['extra']['fields']['hasjQuery']['value'] : NULL ;
        $useProductUrl = (isset($POST['groups']['extra']['fields']['useProductUrl']['value']))? $POST['groups']['extra']['fields']['useProductUrl']['value'] : NULL ;
        
        foreach ($storeModel->getWebsiteCollection() as $website) {
            if (is_null($curWebsite) || ($curWebsite == $website->getCode() && is_null($curStore))) {
                $mageselc->saveConfig('avisverifies/system/secretkey',$secretkey,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/system/idwebsite',$idwebsite,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/system/enabledwebsite',$enabledwebsite,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/force_product_parent_id',$forceParentIds,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/addReviewToProductPage',$addReviewToProductPage,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/useProductSKU',$useProductSKU,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/productLightWidget',$productLightWidget,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/hasjQuery',$hasjQuery,'websites',$website->getId());
                $mageselc->saveConfig('avisverifies/extra/useProductUrl',$useProductUrl,'websites',$website->getId());
            }
        }
        
        foreach ($storeModel->getStoreCollection() as $store) {
            if (is_null($curStore) || $curStore == $store->getCode()) {
                $mageselc->saveConfig('avisverifies/system/secretkey',$secretkey,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/system/idwebsite',$idwebsite,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/system/enabledwebsite',$enabledwebsite,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/force_product_parent_id',$forceParentIds,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/addReviewToProductPage',$addReviewToProductPage,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/useProductSKU',$useProductSKU,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/productLightWidget',$productLightWidget,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/hasjQuery',$hasjQuery,'stores',$store->getId());
                $mageselc->saveConfig('avisverifies/extra/useProductUrl',$useProductUrl,'stores',$store->getId());
            }
        }
        
        // first loop on the disactive stores, an disactive store can not be related to other stores.
        $resource = Mage::getModel("core/config_data")->getCollection()
                ->addFieldToFilter('scope','stores')
                ->addFieldToFilter('path','avisverifies/system/enabledwebsite')
                ->addFieldToFilter('value','0');
        foreach ($resource as $store) {
            $mageselc->saveConfig('avisverifies/extra/relatedstoreslist','','stores',$store->getData('scope_id'));
        }
        // i need to get all the idWebsite from database and then update accordingly
        $resource = Mage::getModel("core/config_data")->getCollection()
                    ->addFieldToFilter('path','avisverifies/system/idwebsite');
        $allIdwebsite = array();
        foreach ($resource as $value) {
            $allIdwebsite[$value->getData('value')] = 1;
        }
        $allIdwebsite = array_keys($allIdwebsite);
        foreach ($allIdwebsite as $idwebsite) {
            // now we get the related stores list
            $relatedstoreslist = Mage::helper('avisverifies/Data')->getModuleActiveStoresIds($idwebsite);
            foreach ($relatedstoreslist as $storeId) {
                $mageselc->saveConfig('avisverifies/extra/relatedstoreslist',implode(';',$relatedstoreslist),'stores',$storeId);
            }
        }
    }
}

