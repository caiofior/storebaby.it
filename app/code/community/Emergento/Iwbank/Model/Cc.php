<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
/* Emergento */
class Emergento_Iwbank_Model_Cc extends Emergento_Iwbank_Model_Method_Abstract {

    protected $_code                    = 'iwbank_cc';
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_paymentMethod           = 'cc';
    protected $_formBlockType           = 'iwbank/form';
    protected $_defaultLocale               = 'it';
    protected $_UrlTest                 = 'https://cartasi-test.x-pay.it/IwbankVPOSTEST/XPServlet';
    protected $_UrlKeyClient            = 'https://ecommerce.keyclient.it/ecomm/ecomm/DispatcherServlet';
    protected $_UrlS2S                  = 'https://ecommerce.keyclient.it/ecomm/ecomm/Servlet3DS2S';
    protected $_Ssl                     = 'ssl://cartasi.x-pay.it';
    protected $_SslTest                 = 'ssl://cartasi-test.x-pay.it';

    public function getOrder() {
        if (!$this->_order) {
            $paymentInfo = $this->getInfoInstance();
            $this->_order = Mage::getModel('sales/order')
                    ->loadByIncrementId($paymentInfo->getOrder()->getRealOrderId());
        }
        return $this->_order;
    }


    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('iwbank/processing/redirect');
    }


    public function capture(Varien_Object $payment, $amount) {
        $payment->setStatus(self::STATUS_APPROVED)
                ->setLastTransId($this->getTransactionId());

        return $this;
    }


    public function cancel(Varien_Object $payment) {
        $payment->setStatus(self::STATUS_DECLINED)
                ->setLastTransId($this->getTransactionId());

        return $this;
    }


    public function getRedirectBlockType() {
        return $this->_redirectBlockType;
    }


    public function getPaymentMethodType() {
        return $this->_paymentMethod;
    }


    public function getUrl() {

        return $this->_UrlKeyClient;
    }

    public function getSsl() {
        if ($this->getConfigData('test_mode') == 1)
            return $this->_SslTest;
        return $this->_Ssl;        
    }

    public function getFormFields() {
        $current_order = $this->getOrder();
        
        $amount     = number_format($current_order->getBaseGrandTotal(),2,'.','');
        $billing    = $current_order->getBillingAddress();
        $street     = $billing->getStreet();
        $hashStr    = '';

        $locale = explode('_', Mage::app()->getLocale()->getLocaleCode());
        if (is_array($locale) && !empty($locale))
            $locale = $locale[0];
        else
            $locale = $this->getDefaultLocale();

        $params =   array(
                'desc'        =>    Mage::helper('iwbank')->__('Acquistato su') . ' ' . Mage::app()->getStore()->getName(),
                'lang'        =>    $locale,
                'orderid'  =>   $current_order->getRealOrderId()
        );

        if (Mage::getStoreConfig('payment/iwbank_cc/s2s_mode') == 1)
        {

            $info = $this->getInfoInstance();
            $params['card_owner'] = $info->getCcOwner();
            
            list($params['card_ccv'],$params['card_number']) = explode(' - ', $info->getCcNumber());

            if ($info->getCcExpMonth() < 10)
                $params['card_month'] = '0'.$info->getCcExpMonth();
            else
                $params['card_month'] = $info->getCcExpMonth();
            $params['card_year'] = substr($info->getCcExpYear(), 2, 2);
            
            $params['card_cctype'] = $info->getCcType();
        }
        return $params;
    }
        public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear());

        return $this;
    }
    
    public function prepareSave()
    {
    $info = $this->getInfoInstance();

        $info->setCcNumberEnc($info->encrypt($info->getCcCid().' - '.$info->getCcNumber()));
           $info->setCcCidEnc($info->encrypt($info->getCcCid()));

    // Uncommented this line

    $info->setCcNumber(null)->setCcCid(null); 

    return $this;
    }

}
