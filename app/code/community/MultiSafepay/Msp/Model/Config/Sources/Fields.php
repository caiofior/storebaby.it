<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Config_Sources_Fields
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                "value" => 0,
                "label" => Mage::helper("msp")->__('Disabled')
            ),
            array(
                "value" => 1,
                "label" => Mage::helper("msp")->__('Mandatory')
            ),
             array(
                "value" => 2,
                "label" => Mage::helper("msp")->__('Optional')
            )
        );
    }

}
