<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Gateway_Ideal extends MultiSafepay_Msp_Model_Gateway_Abstract
{
    protected $_code          = "msp_ideal";
    public $_model         = "ideal";
    public $_gateway          = "IDEAL";
    protected $_formBlockType = 'msp/idealIssuers';

    public function getOrderPlaceRedirectUrl()
    {
     		if(isset($_POST['payment']['msp_ideal_bank']))
		{
			$bank = $_POST['payment']['msp_ideal_bank'];
		}elseif(isset($_POST['payment']['bankid']))
		{
			$bank = $_POST['payment']['bankid'];
		}
		else{
			$bank='';
		}

        $url = $this->getModelUrl("msp/standard/redirect/issuer/".$this->_issuer);
        if (!strpos($url, "?")) {
            $url .= '?bank=' . $bank;
        } else {
            $url .= '&bank=' . $bank;
        }

        return $url;
    }

    public function getPayment($storeId = null)
    {
        $payment = parent::getPayment($storeId);
        $payment->setIssuer($this->_issuer);

        return $payment;
    }

    public function getIdealIssuers($storeId = null)
    {
        $idealissuers = parent::getIdealIssuersHTML($storeId);

        return $idealissuers;
    }
}