<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Block_IdealIssuers extends Mage_Payment_Block_Form
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $gateway_select = Mage::getStoreConfig("msp/msp_ideal/bank_select");
        if ($gateway_select) {
            parent::_construct();
            $this->setTemplate('msp/idealissuers.phtml');
        }
    }

    /**
     * @return array
     */
    public function getIdealIssuers()
    {
        /** @var $msp MultiSafepay_Msp_Model_Gateway_Ideal */
        $msp = Mage::getSingleton("msp/gateway_ideal");
        $base = $msp->getIdealIssuers();

        return $base;
    }

}