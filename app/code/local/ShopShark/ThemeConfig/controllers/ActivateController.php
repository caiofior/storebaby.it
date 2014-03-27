<?php

class ShopShark_ThemeConfig_ActivateController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
	    {
	        $this->getResponse()->setHeader('Content-type', 'application/json');
        	$this->getResponse()->setBody("ajax only");
	    }

	public function activateAction()
    {
        
		$_result = array();
		
        if(Mage::getStoreConfig('milanoconfig/activation_options/already_activated') != 'Activated'){
		
        try {
	       
            	    
			//web > default pages
            Mage::getConfig()->saveConfig('web/default/cms_home_page', 'milano_home', 'default', 0);
               
		    //design > themes
            Mage::getConfig()->saveConfig('design/theme/template', 'milano', 'default', 0);
			Mage::getConfig()->saveConfig('design/theme/skin', 'milano', 'default', 0);
			Mage::getConfig()->saveConfig('design/theme/layout', 'milano', 'default', 0);
			Mage::getConfig()->saveConfig('design/theme/default', 'milano', 'default', 0);

	        //CMS Pages adn Blocks
            Mage::getModel('ThemeConfig/Settings')->setupCms();
			
			Mage::getConfig()->saveConfig('milanoconfig/activation_options/already_activated', 'Activated', 'default', 0);
	        
		    $_result['text'] = 'The template was successfully activated.';
			$_result['type'] = 'success';
        }
        catch (Exception $e) {
            $_result['text'] = 'The template was not activated. Check the main log for details.';
			$_result['type'] = 'error';
        }
		
		} else {
			$_result['text'] = 'Your store is already activated!';
			$_result['type'] = 'error';
		}

        $this->getResponse()->setHeader('Content-type', 'text/plain; charset=UTF-8');
        $this->getResponse()->setBody(json_encode($_result));
    }

	private function _updateNewest()
	{

	}

	private function _updateSale()
	{

	}

}