<?php

class Netreviews_Avisverifies_Block_Rating_Entity_Detailed extends Mage_Rating_Block_Entity_Detailed {
    
    public function setTemplate($template)
    {
        if (Mage::helper('avisverifies/Data')->isActive()) {
            parent::setTemplate('avisverifies\review\helper\review_summary.phtml');
        }
        else {
            parent::setTemplate($template);
        }
    }
}