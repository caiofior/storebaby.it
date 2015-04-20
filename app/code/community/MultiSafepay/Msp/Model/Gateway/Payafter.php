<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Gateway_PayAfter extends MultiSafepay_Msp_Model_Gateway_Abstract
{
    protected $_code           = "msp_payafter";
    public $_model          = "payafter";
    public $_gateway           = "PAYAFTER";
	protected $_formBlockType 		= 'msp/bno';  
    protected $_canUseCheckout = true;

    public function __construct()
    {
        $availableByIP       = true;
        if (Mage::getStoreConfig('msp/msp_payafter/ip_check')) {
            if ($this->_isTestMode()) {
                $data  = Mage::getStoreConfig('msp/msp_payafter/ip_filter_test');
            } else {
                $data  = Mage::getStoreConfig('msp/msp_payafter/ip_filter');
            }

            if (!in_array($_SERVER["REMOTE_ADDR"], explode(';', $data))) {
                $availableByIP = false;
            }
        }

        $currencies = explode(',', Mage::getStoreConfig('msp/'.$this->_code.'/allowed_currency'));
        $isAllowConvert = Mage::getStoreConfigFlag('msp/settings/allow_convert_currency');

        if ($isAllowConvert) {
            $availableByCurrency = true;
        } else {
            if (in_array(Mage::app()->getStore()->getCurrentCurrencyCode(), $currencies)) {
                $availableByCurrency = true;
            } else {
                $availableByCurrency = false;
            }
        }
        $this->_canUseCheckout = $availableByIP && $availableByCurrency;
    }
	
	
	
	public function getOrderPlaceRedirectUrl() 
	{	
		if(isset($_POST['payment']['birthday']))
		{
			$birthday = $_POST['payment']['birthday'];
		}else{
			$birthday='';
		}

		if(isset($_POST['payment']['accountnumber']))
		{
			$accountnumber = $_POST['payment']['accountnumber'];
		}
		else{
			$accountnumber ='';
		}
	
		$url = $this->getModelUrl("msp/standard/redirect/issuer/".$this->_issuer);
		if (!strpos($url, "?")) $url .= '?birthday=' . $birthday .'&accountnumber='.$accountnumber;
		else $url .= '&birthday=' . $birthday.'&accountnumber='.$accountnumber;
		return $url;
	}
	
	
    /**
     * Is Test Mode
     *
     * @param null|integer|Mage_Core_Model_Store $store
     * @return bool
     */
    protected function _isTestMode($store = null)
    {
        $mode = Mage::getStoreConfig('msp/msp_payafter/test_api_pad', $store);

        return $mode == MultiSafepay_Msp_Model_Config_Sources_Accounts::TEST_MODE;
    }
}