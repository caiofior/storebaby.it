<?php
class Netreviews_Avisverifies_Block_Adminhtml_Form_Export_Export extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm() {
        $helper = Mage::helper('avisverifies');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('export_');
        $form->setFieldNameSuffix('export');
        
        $fieldset = $form->addFieldset('export', array(
            'legend'       => $helper->__('Export Settings'),
            'class'        => 'fieldset-wide',
        ));
// -----------------------------------------------------------------------------------------------------------        
        // set empty website
        $arra_websites[10000] = array('value' => 10000, 'label' => Mage::helper('avisverifies')->__('All websites'));
        $websites = Mage::app()->getWebsites();
        // loop on all websites
        foreach ($websites as $website) {
             $arra_websites[$website->getId()] = array('value' => $website->getId(), 'label' => $website->getName());
        }
        // get current website
        $websiteCode = Mage::app()->getRequest()->getParam('website');
        if ($websiteCode) {
            $website = Mage::getModel('core/website')->load($websiteCode);
            $websiteId = $website->getId();
            //do your magic with $websiteId
            $useProductSKU = $website->getConfig('avisverifies/extra/useProductSKU');
        }
        else {
            $websiteId = 10000;
            $default = Mage::getModel('core/website')->load('default');
            $useProductSKU = $default->getConfig('avisverifies/extra/useProductSKU');
        }

        $fieldset->addField('websitetoexport', 'text', array(
            'label'     => Mage::helper('avisverifies')->__('Choosen website to export orders from'),
            'name'      => 'websitetoexport',
            'value'     => $arra_websites[$websiteId]['label'],
            'readonly' => true,
        ));
        $fieldset->addField('websiteIdHidden', 'hidden', array(
            'required' => true,
            'name' => 'websiteIdHidden',
            'value' => $websiteId,
            ));
// -----------------------------------------------------------------------------------------------------------  
        //same thing as with website
        $arra_stores[10000] = array('value' => 10000, 'label' => Mage::helper('avisverifies')->__('All Stores'));
        $stores = Mage::app()->getStores();
        foreach ($stores as $store) {
             $arra_stores[$store->getId()] = array('value' => $store->getId(), 'label' => $store->getName());
        }
        $storeCode = Mage::app()->getRequest()->getParam('store');
        if ($storeCode) {
            $store = Mage::getModel('core/store')->load($storeCode);
            $storeId = $store->getId();
            $useProductSKU = $store->getConfig('avisverifies/extra/useProductSKU');
        }
        else {
            $storeId = 10000;
        }
        
        $fieldset->addField('storetoexport', 'text', array(
            'label'     => Mage::helper('avisverifies')->__('Choosen store from the website above to export orders from'),
            'name'      => 'websitetoexport',
            'value'     => $arra_stores[$storeId]['label'],
            'readonly' => true,
        ));
        $fieldset->addField('storeIdHidden', 'hidden', array(
            'required' => true,
            'name' => 'storeIdHidden',
            'value' => $storeId,
            ));
// -----------------------------------------------------------------------------------------------------------  
        $fieldset->addField('sku', 'select', array(
            'label'     => Mage::helper('avisverifies')->__('Use product sku'),
            'name'      => 'sku',
            'value'     => $useProductSKU,
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('avisverifies')->__('Yes'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('avisverifies')->__('No'),
                ),
            ),
         ));
// ----------------------------------------------------------------------------------------------------------- 
        $fieldset->addField('csv', 'select', array(
            'label'     => Mage::helper('avisverifies')->__('Run Export CSV'),
            'name'      => 'csv',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('avisverifies')->__('Yes'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('avisverifies')->__('No'),
                ),
            ),
        ));
		
        $fieldset->addField('until', 'select', array(
            'label'     => Mage::helper('avisverifies')->__('Until'),
            'name'      => 'until',
            'values'    => array(
                array(
                    'value'     => '1w',
                    'label'     => Mage::helper('avisverifies')->__('1 week'),
                ),
                array(
                    'value'     => '2w',
                    'label'     => Mage::helper('avisverifies')->__('2 weeks'),
                ),
                array(
                    'value'     => '1m',
                    'label'     => Mage::helper('avisverifies')->__('1 month'),
                ),			
                array(
                    'value'     => '2m',
                    'label'     => Mage::helper('avisverifies')->__('2 months'),
                ),		
                array(
                    'value'     => '3m',
                    'label'     => Mage::helper('avisverifies')->__('3 months'),
                ),		
                array(
                    'value'     => '4m',
                    'label'     => Mage::helper('avisverifies')->__('4 months'),
                ),		
                array(
                    'value'     => '5m',
                    'label'     => Mage::helper('avisverifies')->__('5 months'),
                ),		
                array(
                    'value'     => '6m',
                    'label'     => Mage::helper('avisverifies')->__('6 months'),
                ),		
                array(
                    'value'     => '7m',
                    'label'     => Mage::helper('avisverifies')->__('7 months'),
                ),		
                array(
                    'value'     => '8m',
                    'label'     => Mage::helper('avisverifies')->__('8 months'),
                ),	
                array(
                    'value'     => '9m',
                    'label'     => Mage::helper('avisverifies')->__('9 months'),
                ),	
                array(
                    'value'     => '10m',
                    'label'     => Mage::helper('avisverifies')->__('10 months'),
                ),	
                array(
                    'value'     => '11m',
                    'label'     => Mage::helper('avisverifies')->__('11 months'),
                ),				
                array(
                    'value'     => '12m',
                    'label'     => Mage::helper('avisverifies')->__('12 months'),
                )				
            ),
        ));
		
        $fieldset->addField('product', 'select', array(
            'label'     => Mage::helper('avisverifies')->__('Request product\'s review?'),
            'name'      => 'product',
            'values'    => array(
                array(
                    'value'     => 1,
                    'label'     => Mage::helper('avisverifies')->__('Yes'),
                ),
                array(
                    'value'     => 0,
                    'label'     => Mage::helper('avisverifies')->__('No'),
                ),
            ),
        ));
        
        if (Mage::registry('avisverifies')) {
            $form->setValues(Mage::registry('avisverifies')->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }

}