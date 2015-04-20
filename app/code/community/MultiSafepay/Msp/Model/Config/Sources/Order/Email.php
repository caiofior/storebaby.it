<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Config_Sources_Order_Email
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                "value" => "after_confirmation",
                "label" => Mage::helper("msp")->__("After order confirmation")
            ),
            array(
                "value" => "after_payment",
                "label" => Mage::helper("msp")->__("After payment complete")
            ),
            array(
                "value" => "after_notify_with_cancel",
                "label" => Mage::helper("msp")->__("After notification, including cancelled order")
            ),
            array(
                "value" => "after_notify_without_cancel",
                "label" => Mage::helper("msp")->__("After notification, excluding cancelled order")
            )
        );
    }

}
