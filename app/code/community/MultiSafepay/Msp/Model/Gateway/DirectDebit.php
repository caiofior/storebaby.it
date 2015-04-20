<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Gateway_DirectDebit extends MultiSafepay_Msp_Model_Gateway_Abstract
{
    protected $_code  = "msp_directdebit";
    public $_model = "directDebit";
    public $_gateway  = "DIRDEB";
}
