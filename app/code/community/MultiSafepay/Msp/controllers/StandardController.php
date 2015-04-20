<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(Mage::getBaseDir('lib').DS.'multisafepay'.DS.'MultiSafepay.combined.php');

class MultiSafepay_Msp_StandardController extends Mage_Core_Controller_Front_Action
{
    private $gatewayModel = null;

    /**
     * Set gateway model
     */
    public function setGatewayModel($model)
    {
        $this->gatewayModel = $model;
    }

    /**
     * Get the current model
     *    - first check if set (gatewayModel)
     *    - check if we have one in the query string
     *    - if not return default
     */
   public function getGatewayModel() 
	{
		if ($this->gatewayModel)
		{
			return $this->gatewayModel;
		}


		$orderId  = $this->getRequest()->getQuery('transactionid');
		$order =Mage::getModel('sales/order')->loadByIncrementId($orderId); //use a real increment order id here

		$model = $this->getRequest()->getParam('model');
    
		// filter
		$model = preg_replace("|[^a-zA-Z]+|", "", $model);
	



		if (empty($model)){
			if($orderId == '')
			{
				return "gateway_default";
			}else{
				$model = $order->getPayment()->getMethodInstance()->_model;
				if($model =='')
				{
					return "gateway_default";
				}else{
					return "gateway_" . $model;
				}
			}
		}else{
			return "gateway_" . $model;
		}
	}
    /**
     * Payment redirect -> start transaction
     */
    public function redirectAction()
    {
        $paymentModel     = Mage::getSingleton("msp/" . $this->getGatewayModel());
        $selected_gateway = '';
        if (isset($paymentModel->_gateway)) {
            $selected_gateway = $paymentModel->_gateway;
        }

        $paymentModel->setParams($this->getRequest()->getParams());

        if ($selected_gateway != 'PAYAFTER') {
            $paymentLink = $paymentModel->startTransaction();
        } else {
            $paymentLink = $paymentModel->startPayAfterTransaction();
        }

        //header("Location: " . $paymentLink);

        header('Content-type: text/html; charset=utf-8');
        header("Location: ".$paymentLink, true);
        header("Connection: close", true);
        header("Content-Length: 0", true);
        flush();
        @ob_flush();

        exit();
    }

    /**
     * Return after transaction
     */
    public function returnAction()
    {
		//$this->notificationAction(true);
		
        // Fix for emptying cart after success
       // $this->getOnepage()->getQuote()->setIsActive(false);
       // $this->getOnepage()->getQuote()->save();

	 $session = Mage::getSingleton("checkout/session");
        $session->unsQuoteId();
        $session->getQuote()->setIsActive(false)->save();

        // End fix
        $this->_redirect("checkout/onepage/success?utm_nooverride=1", array("_secure" => true));
    }

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Cancel action
     */
    public function cancelAction()
    {
        // cancel order
        $checkout = Mage::getSingleton("checkout/session");
        $order_id = $checkout->getLastRealOrderId();
        $order    = Mage::getSingleton('sales/order')->loadByIncrementId($order_id);

        if ($order_id) {
            $order->cancel();
            $order->save();
        }
    
        //Validate this function. Do we need this one as an extra setting? Why not just detect it on checkout -> ???
        if (Mage::getStoreConfig("msp/settings/use_onestepcheckout") || Mage::getStoreConfig("payment/msp/use_onestepcheckout")) {
            $this->_redirect("onestepcheckout?utm_nooverride=1", array("_secure" => true));
        } else {
            $this->_redirect("checkout?utm_nooverride=1", array("_secure" => true));
        }
    }

    /**
     * Checks if this is a fastcheckout notification
     */
    public function isFCONotification($transId)
    {
        //Mage::log("Checking if FCO notification...", null, "multisafepay.log");

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->load($transId);

        $storeId = Mage::app()->getStore()->getStoreId();
        if ($quote) {
            $storeId = $quote->getStoreId();
        }

        $config = Mage::getStoreConfig('mspcheckout/settings', $storeId);

        if (isset($config['account_id']) && isset($config['test_api']) &&
            isset($config['site_id']) && isset($config['secure_code'])) {

            $msp = new MultiSafepay();
            $msp->test                   = ($config["test_api"] == 'test');
            $msp->merchant['account_id'] = $config["account_id"];
            $msp->merchant['site_id']    = $config["site_id"];
            $msp->merchant['site_code']  = $config["secure_code"];
            $msp->transaction['id']      = $transId;

            if ($msp->getStatus() == false) {
                //Mage::log("Error while getting status.", null, "multisafepay.log");
            } else {
                //Mage::log("Got status: ".$msp->details['ewallet']['fastcheckout'], null, "multisafepay.log");
                if ($msp->details['ewallet']['fastcheckout'] == "YES") {
                    return true;
                } else {
                    return false;
                }
            }
        }
        else {
           // Mage::log("No FCO transaction so default to normal notification", null, "multisafepay.log");
            return false;
        }
    }

    /**
     * Status notification
     */
   public function notificationAction($return = false) 
	{
			$orderId  = $this->getRequest()->getQuery('transactionid');
			$initial  = ($this->getRequest()->getQuery('type') == 'initial') ? true : false;
			$transactionid = $this->getRequest()->getQuery('transactionid');
			
			// Check if this is a fastcheckout notification and redirect
		   
			
			//check if FCO transaction
			$storeId = Mage::app()->getStore()->getStoreId();
			$config = Mage::getStoreConfig('mspcheckout' . "/settings", $storeId);
			
			if(isset($config["account_id"]))
			{
				$msp = new MultiSafepay();
				$msp->test = ($config["test_api"] == 'test');
				$msp->merchant['account_id'] = $config["account_id"];
				$msp->merchant['site_id'] = $config["site_id"];
				$msp->merchant['site_code'] = $config["secure_code"];
				$msp->transaction['id'] = $transactionid ;

				if($msp->getStatus() == false)
				{
					//Mage::log("Error while getting status.", null, "multisafepay.log");
				}
				else
				{  
					if($msp->details['ewallet']['fastcheckout'] == "YES")
					{
						$transactionid = $this->getRequest()->getQuery('transactionid');
						$initial       = ($this->getRequest()->getQuery('type') == 'initial') ? true : false;
						$checkout = Mage::getModel("msp/checkout");
						$done = $checkout->notification($transactionid, $initial);
						
						if ($initial)
						{
							$returnUrl = Mage::getUrl("msp/checkout/return", array("_secure" => true)) . '?transactionid=' . $transactionid;

							$storeId = Mage::getModel('sales/quote')->load($transactionid)->getStoreId();
							$storeName = Mage::app()->getGroup($storeId)->getName();

							// display return message
							echo 'Return to <a href="' . $returnUrl . '?transactionid='.$orderId.'">' . $storeName . '</a>';

						}else{
							if ($done)
							{
								echo 'ok';
							}
							else
							{
								echo 'nok';
							}
						}
						exit;
					}
				}
			}
			$paymentModel = Mage::getSingleton("msp/" . $this->getGatewayModel());


			$done = $paymentModel->notification($orderId, $initial);
		
			if(!$return)
			{
				if ($initial)
				{
					$returnUrl = $paymentModel->getReturnUrl();
				
					$order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);
					$storename  = $order->getStoreGroupName();

					// display return message
					$this->getResponse()->setBody('Return to <a href="' . $returnUrl . '?transactionid='.$orderId.'">' . $storename . '</a>');	
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
	}
