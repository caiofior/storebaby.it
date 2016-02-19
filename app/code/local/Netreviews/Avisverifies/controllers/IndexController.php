<?php
class Netreviews_Avisverifies_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->_redirectUrl(Mage::getBaseUrl(), 301);
    }
    
    public function ajaxloadAction()
    {	
        $id = Mage::app()->getRequest()->getParam('id_product');
        if (empty($id)) {
            $this->_redirectUrl(Mage::getBaseUrl(), 301);
        }
        else {
            $html = $this->getLayout()
                 ->createBlock('core/template')
                 ->setTemplate('avisverifies/pagination.phtml')
                 ->toHtml();
            echo $html;
        }
    }
    
}