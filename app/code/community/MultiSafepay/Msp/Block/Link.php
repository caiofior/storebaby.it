<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Block_Link extends Mage_Core_Block_Template
{
    /**
     * Construct
     */
    protected function _construct()
    {

    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        
        if (Mage::getModel('msp/checkout')->isAvailable($quote) && $quote->validateMinimumAmount()) {
            return parent::_toHtml();
        }

        return '';
    }
}
