<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Abstract extends Mage_Payment_Model_Method_Abstract
{   
    protected $_helper = '';
    protected $_order = '';
    protected $_debugEmail = '';
    protected $_billingInfo = '';
    protected $_shippingInfo = '';
    protected $_session = '';


    /**
     * Retrieves instance of the last used order
     */
    protected function _loadLastOrder()
    {
        if (!empty($this->_order)) {
            return;
        }

        $session = Mage::getSingleton('checkout/session');
        $orderId = $session->getLastRealOrderId();
        if (!empty($orderId)) {
            $this->_order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        }
    }

    public function setHelper($helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    public function getHelper()
    {
        return $this->_helper;
    }

    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function setLastOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    public function getLastOrder()
    {
        return $this->_order;
    }

    public function setDebugEmail($debugEmail)
    {
        $this->_debugEmail = $debugEmail;
        return $this;
    }

    public function getDebugEmail()
    {
        return $this->_debugEmail;
    }

    public function setBillingInfo($billingInfo)
    {
        $this->_billingInfo = $billingInfo;
        return $this;
    }

    public function getBillingInfo()
    {
        return $this->_billingInfo;
    }

    public function setShippingInfo($shippingInfo)
    {
        $this->_shippingInfo = $shippingInfo;
        return $this;
    }

    public function getShippingInfo()
    {
        return $this->_shippingInfo;
    }

    public function setSession($session)
    {
        $this->_session = $session;
        return $this;
    }

    public function getSession()
    {
        return $this->_session;
    }

    public function __construct()
    {
        return Varien_Object::__construct(func_get_args());
    }

    protected function _construct()
    {
        $this->setHelper(Mage::helper('msp'));
        $this->_loadLastOrder();
        $this->setSession(Mage::getSingleton('core/session'));
        $this->_setOrderBillingInfo();
        $this->_setOrderShippingInfo();

        $this->_checkExpired();
    }

    public function setOrderBillingInfo()
    {
        return $this->_setOrderBillingInfo();
    }

    /**
     * retrieve billing information from order
     *
     */
    protected function _setOrderBillingInfo()
    {
        if (empty($this->_order)) {
            return $this;
        }
        $billingAddress = $this->_order->getBillingAddress();
                
        $billingInfo = array(
            'firstname'   => $billingAddress->getFirstname(),
            'lastname'    => $billingAddress->getLastname(),
            'city'        => $billingAddress->getCity(),
            'state'       => $billingAddress->getState(),
            'address'     => $billingAddress->getStreetFull(),
            'zip'         => $billingAddress->getPostcode(),
            'email'       => $this->_order->getCustomerEmail(),
            'telephone'   => $billingAddress->getTelephone(),
            'fax'         => $billingAddress->getFax(),
            'countryCode' => $billingAddress->getCountry()
        );
        
        return $this->setBillingInfo($billingInfo);
    }

    public function setOrderShippingInfo()
    {
        return $this->_setOrderShippingInfo();
    }
}