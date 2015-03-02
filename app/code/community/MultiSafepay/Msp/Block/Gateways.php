<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Block_Gateways extends Mage_Payment_Block_Form
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $gateway_select = Mage::getStoreConfig("payment/msp/gateway_select");
        if ($gateway_select) {
            parent::_construct();
            $this->setTemplate('msp/gateways.phtml');
        }
    }
}