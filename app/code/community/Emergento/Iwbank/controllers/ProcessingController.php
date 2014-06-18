<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
/* Emergento */
class Emergento_Iwbank_ProcessingController extends Mage_Core_Controller_Front_Action {
    
    protected $_redirectBlockType     = 'iwbank/processing'; // OK
    protected $_resultBlockType       = 'iwbank/success'; // OK
    protected $_errorBlockType        = 'iwbank/failure'; // OK
    protected $_order                 = NULL;
    protected $_paymentInst           = NULL;


    protected function _expireAjax() {
        if (! $this->getCheckout()->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
            exit();
        }
    }


    public function getCheckout() {
        return Mage::getSingleton ('checkout/session');
    }

    public function payemailAction(){
      $req = $this->getRequest();
        if($req->getParam('codTrans')){

        }
    }

    public function redirectAction() {
      $session = $this->getCheckout();
  
       if($session->getLastRealOrderId() == false ){
       $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
       return;
       }
       
      $r = $this->getRequest();

       $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());

       $payment = $order->getPayment()->getMethodInstance();
       $iwbank = Mage::getModel('emergento_iwbank/iwbank')->prepare($session->getLastRealOrderId());
       $iwbank->newTransactionId();
           $iwbank->setTransaction();
    
       $iwbank->standardcall();
       $html = $iwbank->getHtmlResponse();
       
       $this->getResponse()->setBody($html);
    }
    
    public function notificationAction()
    {
      $r = $this->getRequest()->getParams();
      $iwbank = Mage::getModel('emergento_iwbank/iwbank')
        ->setPaymentByTransactionId($r['custom']);
      $iwbank->checkTrx($r['payer_id'],$r['thx_id'],$r['verify_sign'],$r['amount'])->submitOrder();

           
    }

    public function resultAction()
    {
 $req = $this->getRequest();
 $r = $req->getParams();

$iwbank = Mage::getModel('emergento_iwbank/iwbank')
  ->setPaymentByTransactionId($r['custom']);

  $iwbank->checkPayed();
      $store_id = $iwbank->getOrder()->getStoreId();
      Mage::app()->setCurrentStore($store_id);

      $this->_redirect ($iwbank->getRedirectUrl(true), array('_secure' => true ));
  }
    

    
    public function annulmentAction()
    {
        if ($session->getLastRealOrderId()) {
              $order = $orders->loadByIncrementId ($session->getLastRealOrderId());
              if ($order->getId()) {
                  $order->cancel()->save();
                  $session->getQuote()->setIsActive(false)->save();
             }
        }
        $this->_redirect ('iwbankannulment');
    }
    public function confirms2sAction(){
     $p = $this->getRequest()->getParams();
      $track = $this->getRequest()->getParam('track');
      $o = $this->checklink($track);
      $quote = Mage::getModel('sales/quote')->load($o->getQuoteId())->setIsActive(false)->save();
      $date = explode('/', $p['expiry_date']);
      $card = array(
        'owner' => $p['name_on_card'],
        'number' => $p['card_number'],
        'cctype' => $p['card_type'],
        'year' => $date[1],
        'month' => $date[0],
        'ccv' => $p['cvv']
         );
        $customer = Mage::getModel('customer/customer')->load( $o->getCustomerId());
      Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setQuote($quote);
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId($quote->getId());
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastQuoteId($quote->getId());
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastOrderId($o->getId());
      $iwbank= Mage::getModel('emergento_iwbank/iwbank')->setPayment($o->getIncrementId(),$card)->setTransaction();


      $iwbank->callS2s();
      
                try {
                    Mage::app()->getFrontController()->getResponse()->setRedirect($iwbank->getRedirectUrl());
                } catch (Exception $e) {
                    echo 'Exception: ',  $e->getMessage(), "\n";
                }
    }
    function checklink($track){
            $orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status',array(
        'nin' => array(
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CLOSED)
        )
      );
      foreach($orders as $order){
        if(sha1($order->getIncrementId() .Mage::getStoreConfig('payment/gestpay_cc/mac_key')) == $track){
         $o = $order;
        }
      }
      if(!$o) die('link scaduto, rieffettua il tuo acquisto');
      return $o;
    }




    public function paybymailAction(){
      $track = $this->getRequest()->getParam('track');
      $o = $this->checklink($track);

      $quote = Mage::getModel('sales/quote')->load($o->getQuoteId())->setIsActive(false)->save();

      


      $iwbank= Mage::getModel('emergento_iwbank/iwbank')->setPayment($o->getIncrementId())->setTransaction();
      if($iwbank->isS2s()){
           $this->loadLayout();
           $date = Mage::app()->getLocale()->date(strtotime($o->getCreatedAtDate()), null, null, false)->toString('dd/MM/yyyy');
          
          $cards = $iwbank->getAllowedCards('json');

          $block = $this->getLayout()->getBlock('credit_card')
            ->setData('order_number', $o->getIncrementId())
            ->setData('items', $quote->getAllVisibleItems())
            ->setData('order_total',number_format($o->getBaseGrandTotal(),2,',',''))
            ->setData('orderdate',$date)
            ->setData('logo_src',Mage::getStoreConfig('design/header/logo_src'))
            ->setData('track',$track)
            ->setData('json_card_string',$cards);


          $this->renderLayout();
      }
      else{
        $customer = Mage::getModel('customer/customer')->load( $o->getCustomerId());
      Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setQuote($quote);
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId($quote->getId());
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastQuoteId($quote->getId());
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastOrderId($o->getId());
        echo $iwbank->callStandard()->getHtmlResponse();
      }
      

    }
}
?>