<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Gateway_Paypal extends MultiSafepay_Msp_Model_Gateway_Abstract
{
    protected $_code           = "msp_paypal";
    public $_model          = "paypal";
    public $_gateway           = "PAYPAL";
    protected $_canUseCheckout = true;
}
