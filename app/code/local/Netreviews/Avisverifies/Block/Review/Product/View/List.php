<?php
class Netreviews_Avisverifies_Block_Review_Product_View_List extends Mage_Review_Block_Product_View_List
{   
    public function setTemplate($template)
    {
        $array_template_list = array('avisverifies/catalogProductList.phtml','avisverifies/list.phtml');
        // force template to ours if is active
        if (Mage::helper('avisverifies/Data')->isActive()) {
            $template = (in_array($template, $array_template_list))? $template : 'avisverifies/reviewProductList.phtml';
            parent::setTemplate($template);
        }
        else {
            parent::setTemplate($template);
        }
    }
}
