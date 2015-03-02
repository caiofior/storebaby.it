<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(Mage::getBaseDir('lib').DS.'multisafepay'.DS.'MultiSafepay.combined.php');

class MultiSafepay_Msp_CheckoutController extends Mage_Core_Controller_Front_Action
{
    protected $base;

    /**
     * Checkout redirect -> start checkout transaction
     */
    public function redirectAction()
    {
        /** @var $session Mage_Checkout_Model_Session */
        $session  =  Mage::getSingleton('checkout/session');

        /** @var $checkout MultiSafepay_Msp_Model_Checkout */
        $checkout = Mage::getModel("msp/checkout");

        // empty cart -> redirect
        if (!$session->getQuote()->hasItems()) {
            $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
			return;
        }

        // create new quote
        /** @var $storeQuote Mage_Sales_Model_Quote */
        $storeQuote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
        $storeQuote->setCustomerId(Mage::getModel('customer/session')->getCustomerId());
        $storeQuote->merge($session->getQuote());
        $storeQuote->setItemsCount($session->getQuote()->getItemsCount())->setItemsQty($session->getQuote()->getItemsQty())->setChangedFlag(false);
        $storeQuote->save();
    
        $baseCurrency = $session->getQuote()->getBaseCurrencyCode();
        $currency     = Mage::app()->getStore($session->getQuote()->getStoreId())->getBaseCurrency();
        $session->getQuote()->collectTotals()->save();
    
        // replace quote into session
        $oldQuote = $session->getQuote();
        $oldQuote->setIsActive(false)->save();
        $session->replaceQuote($storeQuote);
        Mage::getModel('checkout/cart')->init()->save();

        // checkout
        $checkoutLink = $checkout->startCheckout();
    
        header("Location: " . $checkoutLink);
        exit();
    }

    /**
     * Agreements page
     */
    public function agreementsAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock(
            'Mage_Checkout_Block_Agreements',
            '',
            array('template' => 'msp/agreements.phtml')
        );
        echo $block->toHtml();
    }

    /**
     * Return after transaction
     */
    public function returnAction()
    {
		$this->notificationAction(true);
        $transactionId = $this->getRequest()->getQuery('transactionid');
      
        // clear cart
        /** @var $session Mage_Checkout_Model_Session */
        $session = Mage::getSingleton("checkout/session");
        $session->unsQuoteId();
        $session->getQuote()->setIsActive(false)->save();

        // set some vars for the success page
        $session->setLastSuccessQuoteId($transactionId);
        $session->setLastQuoteId($transactionId);

        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getSingleton('sales/order')->loadByAttribute('ext_order_id', $transactionId);
        $session->setLastOrderId($order->getId());
        $session->setLastRealOrderId($order->getIncrementId());

        $storeId = Mage::app()->getStore()->getId();
        $config  = Mage::getStoreConfig('mspcheckout/settings', $storeId);

        // We now have an order so we can also request the customerID. With the customer ID we can login the user.
        if ($config["auto_login_fco_user"]) {
            $order_data = $order->getData();
            $customer = Mage::getModel('customer/customer')->load($order_data['customer_id']);
            $session  = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
        }

        // Just as an extra feature, option to redirect to the account instead of the thank you page.
        if ($config["redirect_to_account"]) {
            $this->_redirect("customer/account?utm_nooverride=1", array("_secure" => true));
        } else {
            $this->_redirect("checkout/onepage/success?utm_nooverride=1", array("_secure" => true));
        }
    }
  
    /**
     * Cancel action
     */
    public function cancelAction()
    {
        $this->_redirect("checkout", array("_secure" => true));
    }
  
    /**
     * Checks if this is a fastcheckout notification
     */
    public function isFCONotification($transId)
    {
        $storeId = Mage::app()->getStore()->getStoreId();
        $config  = Mage::getStoreConfig('mspcheckout/settings', $storeId);
   
        $msp = new MultiSafepay();
        $msp->test = ($config["test_api"] == 'test');
        $msp->merchant['account_id'] = $config["account_id"];
        $msp->merchant['site_id'] = $config["site_id"];
        $msp->merchant['site_code'] = $config["secure_code"];
        $msp->transaction['id'] = $transId;

        if ($msp->getStatus() == false) {
            //Mage::log("Error while getting status.", null, "multisafepay.log");
        } else {
            //Mage::log("Got status: ".$msp->details['ewallet']['fastcheckout'], null, "multisafepay.log");
            return $msp->details['ewallet']['fastcheckout'] == "YES";
        }
    }

    /**
     * Status notification
     */
	function notificationAction($return = false) 
    {
        $transactionId = $this->getRequest()->getQuery('transactionid');
        $isInitial     = ($this->getRequest()->getQuery('type') == 'initial') ? true : false;
        $isShipping    = ($this->getRequest()->getQuery('type') == 'shipping') ? true : false;

        /** @var $checkout MultiSafepay_Msp_Model_Checkout */
        $checkout = Mage::getModel('msp/checkout');

        // Is this notification about new shipping rates?
        if ($isShipping) {
            $this->handleShippingRatesNotification($checkout);
            return;
        }
        
        // Check if this is a fastcheckout notification
        if((!$isInitial) && (!$this->isFCONotification($transactionId))) {
            //Mage::log("Redirecting to standard method notification URL...", null, "multisafepay.log");
            $redirect = Mage::getUrl("msp/standard/notification/");
            header('HTTP/1.1 307 Temporary Redirect');
            header('Location: ' . $redirect);
			exit;
        }
        
        // Is this notification about new shipping address?
        if ($this->isShippingMethodsNotification()) {
            $this->handleShippingMethodsNotification($checkout);
            return;
        }
 
        $done = $checkout->notification($transactionId, $isInitial);

        if(!$return){	
			if ($isInitial)
			{
				$returnUrl = Mage::getUrl("msp/checkout/return", array("_secure" => true)) . '?transactionid=' . $transactionId;

				$storeId = Mage::getModel('sales/quote')->load($transactionId)->getStoreId();
				$storeName = Mage::app()->getGroup($storeId)->getName();

 			// display return message
 				$this->getResponse()->setBody('Return to <a href="' . $returnUrl . '">' . $storeName . '</a>');

 			}else{
	 			if ($done)
				{
	 				$this->getResponse()->setBody('ok');
	 			}
				else
				{
	 				$this->getResponse()->setBody('nok');
				}
			}
		}else{
			return true;
		}
    }

    /**
     * @return bool
     */
    public function isShippingMethodsNotification()
    {
        // Check for mandatory parameters
        $country       = $this->getRequest()->getQuery('country');
        $countryCode   = $this->getRequest()->getQuery('countrycode');
        $transactionId = $this->getRequest()->getQuery('transactionid');

        if (empty($country) || empty($countryCode) || empty($transactionId)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param $model
     */
    public function handleShippingMethodsNotification($model)
    {
        $country       = $this->getRequest()->getQuery('country');
        $countryCode   = $this->getRequest()->getQuery('countrycode');
        $transactionId = $this->getRequest()->getQuery('transactionid');
        $weight        = $this->getRequest()->getQuery('weight');
        $size          = $this->getRequest()->getQuery('size');

        header("Content-Type:text/xml");
        print($model->getShippingMethodsFilteredXML($country, $countryCode, $weight, $size, $transactionId));
    }

    /**
     * @param $model MultiSafepay_Msp_Model_Checkout
     */
    public function handleShippingRatesNotification($model)
    {
        $transactionId = $this->getRequest()->getQuery('transactionid');
        $countryCode   = $this->getRequest()->getQuery('countrycode');
        $zipCode       = $this->getRequest()->getQuery('zipcode');
        $settings      = array(
            'currency' => $this->getRequest()->getQuery('currency'),
            'country'  => $this->getRequest()->getQuery('country'),
            'weight'   => $this->getRequest()->getQuery('weight'),
            'amount'   => $this->getRequest()->getQuery('amount'),
            'size'     => $this->getRequest()->getQuery('size'),
        );

        header("Content-Type:text/xml");
        print $model->getShippingRatesFilteredXML($transactionId, $countryCode, $zipCode, $settings);
    }
}