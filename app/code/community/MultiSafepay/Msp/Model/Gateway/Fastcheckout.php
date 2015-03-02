<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Gateway_Fastcheckout extends MultiSafepay_Msp_Model_Gateway_Abstract
{
    protected $_code  = "msp_fastcheckout";
    public $_model = "fastcheckout";
    public $_gateway  = "FASTCHECKOUT";
    //protected $_formBlockType = 'msp/gateways';
}
