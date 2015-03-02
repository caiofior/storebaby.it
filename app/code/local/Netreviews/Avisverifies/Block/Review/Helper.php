<?php
class Netreviews_Avisverifies_Block_Review_Helper extends Mage_Review_Block_Helper
{
   // override default template
   public function getSummaryHtml($product, $templateType, $displayIfNoReviews) {
        if (Mage::helper('avisverifies/Data')->isActive()) {
            $debug = debug_backtrace();
            $fileArray = explode("/", $debug[1]['file']);
            if (count($fileArray) == 1) {
                $fileArray = explode("\\", $debug[1]['file']);
            }
            // Test if it's in product page call
            if (in_array('view.phtml',$fileArray) && in_array('product',$fileArray) && in_array('catalog',$fileArray)) {
                $template = 'avisverifies/review/helper/product_summary.phtml';
            }
            else { // not product widget
                $template = 'avisverifies/review/helper/list_summary.phtml';
            }
            // set the product var
            $this->setProduct($product);
            // set the template
            $this->setTemplate($template);
            return $this->toHtml();
        }
        else {
            return parent::getSummaryHtml($product, $templateType, $displayIfNoReviews);
        }
    }
    
    public function getReviewsUrl($forceReviewsUrl = FALSE) {
        if (Mage::getStoreConfig('avisverifies/extra/useProductUrl') && Mage::helper('avisverifies/Data')->isActive() && $forceReviewsUrl == FALSE) {
            return $this->getProduct()->getProductUrl();
        }
        else {
            return parent::getReviewsUrl();
        }
    }
}