<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Config_Sources_Order_Currency
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                "value" => "EUR",
                "label" => Mage::helper("msp")->__("EUR")
            ),
            array(
                "value" => "USD",
                "label" => Mage::helper("msp")->__("USD")
            ),
            array(
                "value" => "GBP",
                "label" => Mage::helper("msp")->__("GBP")
            )
        );
    }

}
