<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Gateway_Standard extends MultiSafepay_Msp_Model_Gateway_Abstract
{
    protected $_module        = "payment";
    protected $_code          = "msp";
    protected $_formBlockType = 'msp/default';
    //protected $_loadSettingsConfig = false; // dont use default settings

    public function setParams($params)
    {
        if (isset($params['gateway'])) {
            $this->_gateway = preg_replace("|[^a-zA-Z]+|", "", $params['gateway']);
        }
    }

    public function getNotificationUrl()
    {
        return $this->getModelUrl("msp/standard/notification");
    }

    public function getOrderPlaceRedirectUrl()
    {
        return $this->getModelUrl("msp/standard/redirect/model/standard/gateway/" . $this->_gateway);
    }
}
