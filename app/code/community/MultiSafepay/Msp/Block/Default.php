<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Block_Default extends Mage_Payment_Block_Form{

    public $_code;
    public $_issuer;
    public $_model;
    public $_countryArr = null;
    public $_country;

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->setTemplate('msp/default.phtml');
        parent::_construct();
    }
}
