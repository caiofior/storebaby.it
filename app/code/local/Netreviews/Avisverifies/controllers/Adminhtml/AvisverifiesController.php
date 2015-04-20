<?php
class Netreviews_Avisverifies_Adminhtml_AvisverifiesController extends Mage_Adminhtml_Controller_Action
{  
    
    public function indexAction() {
        $this->loadLayout();
        
        $this->_setActiveMenu('catalog/avisverifies');
        $this->_addBreadcrumb(Mage::helper('avisverifies')->__('Form'), Mage::helper('avisverifies')->__('Form'));
        
        // add block to layout
        $layout = $this->getLayout();
        
        $block = $layout->createBlock('core/template');
        $block->setData('area','frontend');
        $block->setTemplate('avisverifies/admin/export.phtml');
        $layout->getBlock('content')->append($block);
        
        $block = $layout->createBlock('core/template');
        $block->setData('area','frontend');
        $block->setTemplate('avisverifies/admin/export_tab.phtml');
        $layout->getBlock('left')->append($block);
        
        $this->renderLayout();
    }
				
    public function saveAction() {
        $result = $this->getRequest()->getParams();
		$isExport = (isset($result['export']) && isset($result['export']['csv']) && $result['export']['csv'] == 1);
        
        $curWebsite = ($result['export']['websiteIdHidden'] == 10000)? NULL : $result['export']['websiteIdHidden'];
        $curStore = ($result['export']['storeIdHidden'] == 10000)? NULL : $result['export']['storeIdHidden'];
        $useProductSKU = $result['export']['sku'];
        // all config
        if (is_null($curWebsite) && is_null($curStore)) {
            $stores = Mage::app()->getStores();
            foreach($stores as $store){
                $arrStores[] = $store->getData('store_id');
                $this->updateSKUConfig($curWebsite,$curStore,$useProductSKU);
            }
        }
        elseif ($curWebsite && is_null($curStore)) {
            $webiste = Mage::getModel('core/website')->load($curWebsite);
            foreach ($webiste->getStores() as $key => $store) {
                $arrStores[] = $store->getData('store_id');
                $this->updateSKUConfig($curWebsite,$curStore,$useProductSKU);
            }
        }
        elseif ($curWebsite && $curStore ) {
            $store = Mage::getModel('core/store')->load($curStore);
            $arrStores[] = $store->getData('store_id');
            $this->updateSKUConfig($curWebsite,$curStore,$useProductSKU);
        }
        else {
            $isExport = false;
        }
        
		if ($isExport) {
            $type = substr($result['export']['until'], -1);
            // casting to int, for sql injection.
            $period = (int)str_replace($type, "", $result['export']['until']);
            $type = ($type == "m")? 'MONTH' : 'WEEK';
            $from = "DATE_SUB(CURDATE(), INTERVAL $period $type)";
            $to = "DATE_ADD(curdate(),INTERVAL 1 DAY)";
            $helperData = Mage::helper('avisverifies/Export');
            $helperData->createStoresIds($arrStores);
            $helperData->exportStruct($result['export']['product']);
			//construction du CSV
			$helperData->createExportCSV($from,$to);
           	$content = $helperData->getCSVFile();
            $this->_prepareDownloadResponse("lastOrders_AV_".date("dmYHis").'.csv', $content, 'text/csv');
            
		} 
        else {
			 $url = Mage::helper("adminhtml")->getUrl("avisverifies/adminhtml_avisverifies/index");
			 $this->_redirectUrl($url);
		}
    }
    
    protected function updateSKUConfig ($curWebsite,$curStore,$useProductSKU) {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        $mageselc = new Mage_Core_Model_Config();
        
        if (is_null($curWebsite) && is_null($curStore)) {
            $mageselc->saveConfig('avisverifies/extra/useProductSKU',$useProductSKU,'default',0);
        }
        
        foreach ($storeModel->getWebsiteCollection() as $website) {
            if (is_null($curWebsite) || ($curWebsite == $website->getCode() && is_null($curStore))) {
                $mageselc->saveConfig('avisverifies/extra/useProductSKU',$useProductSKU,'websites',$website->getId());
            }
        }
        foreach ($storeModel->getStoreCollection() as $store) {
            if (is_null($curStore) || $curStore == $store->getCode()) {
                $mageselc->saveConfig('avisverifies/extra/useProductSKU',$useProductSKU,'websites',$store->getId());
            }
        }
    }


    public function checkInstallationAction() {
        
        $old_Version = array('1.3.3.0');
        $is_old_version = (in_array(Mage::getVersion(), $old_Version));
        
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $table = $resource->getTableName('avisverifies_products_reviews');
        $table2 = $resource->getTableName('avisverifies_products_average');
        $table3 = $resource->getTableName('sales/order');
        
        $q = "SHOW TABLES";
        $results = $read->query($q)->fetchAll();
        $tables = array('Reviews'=>false,'Average'=>false);
        foreach ($results as $val) {
            foreach($val as $i=>$v) {
                if ($v == $table)
                    $tables['Reviews'] = true;
                elseif ($v == $table2)
                    $tables['Average'] = true;
            }
        }
        
        $orders = array('AV_Flag'=>false,'AV_Horodate_Get'=>false);
        $results = $read->query("DESCRIBE $table3")->fetchAll();
        foreach ($results as $val) {
           foreach($val as $i=>$v) {
                if ($v == 'av_flag')
                    $orders['AV_Flag'] = true;
                elseif ($v == 'av_horodate_get')
                    $orders['AV_Horodate_Get'] = true;
            }     
        }
        
        try{
            $res = $read->query("SELECT COUNT(*) FROM $table")->fetch();
            $count['Reviews'] = $res['COUNT(*)'];
        } catch (Exception $ex) {

        }
        
        try{
            $res = $read->query("SELECT COUNT(*) FROM $table2")->fetch();
            $count['Average'] = $res['COUNT(*)'];
        } catch (Exception $ex) {

        }
        
        
        if ($orders['AV_Flag']) {
            $res = $read->query("SELECT COUNT(*) FROM $table3 WHERE av_flag = 1")->fetch();
            $count['Order_Set_Flag_1'] = $res['COUNT(*)'];
            $res = $read->query("SELECT COUNT(*) FROM $table3 WHERE av_flag = 0")->fetch();
            $count['Order_Set_Flag_0'] = $res['COUNT(*)'];
        }
        
        $orderSess = Mage::getSingleton('admin/session')->getNetReviewsDebugOrder();
        
        if (!empty($orderSess)) {
            $checkOrders = Mage::helper('avisverifies/Install')->checkOrder($orderSess);
            Mage::getSingleton('admin/session')->setNetReviewsDebugOrder(NULL);
        }
        else {
          $checkOrders = array();  
        }
        
            
        $this->loadLayout();
        $layout  = $this->getLayout();
        
        // add block to layout
        $block = $layout->createBlock('core/template');
        $block->setData('area','frontend');
        $block->setTemplate('avisverifies/admin/checkinstallation.phtml');
        $block->setData('tables',$tables);
        $block->setData('orders',$orders);
        $block->setData('checkOrders',$checkOrders);
        $block->setData('count',$count);
        $block->setData('isOldVersion',$is_old_version);
        $layout->getBlock('content')->append($block);
        
        $block = $layout->createBlock('core/template');
        $block->setData('area','frontend');
        $block->setTemplate('avisverifies/admin/checkinstallation_tab.phtml');
        $layout->getBlock('left')->append($block);
        
        $this->renderLayout();
    }
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            if (isset($post['debug']['reinstall']) && $post['debug']['reinstall'] == 'yes') {
                Mage::helper('avisverifies/Install')->createTables();
            }
            if (isset($post['debug']['flag']) && $post['debug']['flag'] == 'yes') {
                Mage::helper('avisverifies/Install')->flagAll();
            }
            if (isset($post['debug']['fields']) && $post['debug']['fields'] == 'yes') {
                Mage::helper('avisverifies/Install')->addFields();
            }
            if (!empty($post['debug']['UpdateFields'])) {
                Mage::helper('avisverifies/Install')->addUpdateFields();
            }
            if (!empty($post['debug']['order'])) {
                Mage::getSingleton('admin/session')->setNetReviewsDebugOrder($post['debug']['order']);
            }
            $message = $this->__('Your form has been submitted successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('AvisVerifies/adminhtml_avisverifies/checkInstallation');
    }
    
}