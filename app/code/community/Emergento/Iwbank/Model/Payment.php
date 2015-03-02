<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
abstract class Emergento_Iwbank_Model_Payment  extends Mage_Core_Model_Abstract {
  //system
  var $order;
  var $transaction;
  var $TRANSACTION_ID;
  var $pos_transaction_id;
  var $has_init= false;

  //customer
  var $CARD;

  // vpos
  var $TERMINAL_ID;
  var $PASSWORD;
  var $MAC_KEY;
  var $LAST_MAC;
  var $AMOUNT;
  var $EMAIL;
  var $DESC_ORDER;
  var $HTML_RESPONSE;
  var $DATETIME;
    // response
  var $CODAUT;
  var $ESITO;
  var $MAC_TYPE;




  abstract protected function getMac();


function __construct(){
  $this->TERMINAL_ID = Mage::getStoreConfig('payment/' . $this->p_code . '_cc/terminal_id');
}
  function getMoneyConvert($std_amount){
    $mf = $this->moneyformat;
    return number_format($std_amount,$mf[0],$mf[1],$mf[2]);
  }

function hasCard(){
  return ($this->CARD) ? true : false;
}

function getTransactionById($trs){
    $this->MAC_TYPE = 'start';
    return $transaction = Mage::getModel('sales/order_payment_transaction')
      ->getCollection()
      ->addPaymentInformation(array('method'))
      ->addFieldToFilter('method',$this->p_code . '_cc')->addFieldToFilter('txn_id',$trs)->getFirstItem();
  }

function getAmount($amount=NULL){
  if($amount == NULL) $amount = $this->ORDER->getGrandTotal();
  $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
  //converte in euro se la valuta è diversa da EUR
  if($currency != 'EUR') {
    $amount = Mage::helper('directory')->currencyConvert($amount, $currency, 'EUR');
  }
  return $this->getMoneyConvert($amount);    
 }

function getLanguage(){
  $locale = Mage::app()->getLocale()->getLocaleCode();
  return (isset($this->languages[$locale]) ? $this->languages[$locale] : reset($this->languages));
}
function setDatetime($str){

  $this->DATETIME =  date_create_from_format($this->date_format, $str)->setTimeZone(new DateTimeZone("GMT"));
    return $this;
}

 public function getHtmlResponse(){
  return $this->HTML_RESPONSE;
 }

  /*  CARD FUNCTIONS */
  
  function setPaymentByCard($card=null){
        if($card) {
                $this->CARD = Mage::getModel('emergento_' . $this->p_code . '/card')->newCard($card);
        }

        return $this;
    }
 function getCard(){return $this->CARD;}
  /* MAC & CHECK FUNCTIONS */

  function checkPayment(){
    if($this->_DEBUG){
      Mage::log('esito ' . $this->esitoSuccess());
      Mage::log('codice esito ' . $this->ESITO);
      if($this->has_checkmac){
        Mage::log('esito checkmac ' . $this->checkMac());
        Mage::log('esito checkmac ' . $this->checkMac());
      }
      
    }


    if((!$this->has_checkmac && !$this->esitoSuccess()) || ($this->has_checkmac && (!$this->checkMac() || !$this->esitoSuccess()))) {
      //esito ok, mac non controllato
      $this->REDIRECT_URL = 'error-payment';
      return false;
    }
      //esito ok && (mac ok || mac non controllato)
      $this->REDIRECT_URL = 'checkout/onepage/success';
      return true; 
  }

  public function checkPayed(){
    $transaction = $this->getTransactionById($this->TRANSACTION_ID);
    $this->REDIRECT_URL = ($transaction->getIsClosed()) ? 'checkout/onepage/success' : 'error-payment';
    return ($transaction->getIsClosed()) ? true : false;
  }
  function checkMac(){
    return ($this->LAST_MAC == $this->getMac()) ? true : false;
  }

  function getLastMac(){ return $this->LAST_MAC; }

  function setLastMac($mac){
    $this->LAST_MAC = $mac;
    return $this;
  }

  function esitoSuccess(){
      $results_ok = $this->getSuccessCodes();  
      foreach($results_ok as $res_ok){

        if($this->ESITO == $res_ok) return true;
      }
      return false;
  }
  function getSuccessCodes(){
    $res = ($this->isTestMode()) ? $this->results_ok_codes : $this->results_ok_codes_test;
    return  (is_array($res)) ? $res : array($res); 
  }

  /* TRANSACTION FUNCTIONS */
  function setTransactionInfo($infos){
    $trx = $this->getTransactionById($this->TRANSACTION_ID);

      $trx->setAdditionalInformation(
        Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
        $infos);
    
    $trx->save();
    return $this;
  }
  public function newTransactionId(){
      $transactions = Mage::getModel('sales/order_payment_transaction')
          ->getCollection()->addPaymentInformation(array('method'))
          ->addFieldToFilter('method',$this->p_code . '_cc')
          ->addFieldToFilter('txn_id',array('like' => ($this->getPrefix() . '%')));
    foreach ($transactions as $key => $t) {
      if(preg_match('/^(' . $this->getPrefix() . ').*([0-9])$/', $t->getTxnId() )){
        $txn = preg_replace('/^' . preg_quote($this->getPrefix(), '/') . '/', '', $t->getTxnId());
        $t_key[] = ltrim($txn,0);
      }
    }
      $num = 0;
    if($t_key) $num = max ($t_key);
      return $this->getPrefix() . str_pad(($num + 1),10,0,STR_PAD_LEFT);
    }
  public function setTransaction(){
    if(!$this->TRANSACTION_ID){
        $payment = $this->ORDER->getPayment();
          $this->TRANSACTION_ID = $this->newTransactionId();
          $transaction = $payment
            ->setSkipTransactionCreation(false)
            ->setTransactionId ($this->TRANSACTION_ID)
            ->setIsClosed(false)
            ->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $this->ORDER, false, null )
            ->save();
          $transaction->setIsClosed(false)->save();
          return $this;
      }
  }
  function setPaymentByTransactionId($trs=null){
      if($trs==null) $trs = $this->TRANSACTION_ID;
      if(!$trs) Mage::throwException('Nessuna corrispondenza con id pos');
        
      $transaction = $this->getTransactionById($trs);
      $this->TRANSACTION_ID = $trs;
      $this->TRANSACTION = $transaction;
       $order = Mage::getModel('sales/order')->load($transaction->getOrderId());
        $this->ORDER = $order;
        $this->EMAIL = $order->getCustomerEmail();
        $this->AMOUNT = $this->getAmount();
        return $this;
  }


    /* ORDER FUNCTION */
  public function prepare($order_id=null){
    if($order_id == null){
      $order_id = $this->ORDER->getIncrementId();
    }
    $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($order_id);
        $order->setState(Mage_Sales_Model_Order::STATE_NEW, true)
          ->setStatusHistory(Mage::helper($this->p_code . '')->__('Il cliente è stato inviato al sito BNL Gatway Payment.'))->save();
        $this->ORDER = $order;
        $this->EMAIL = $order->getCustomerEmail();
        $this->AMOUNT = $this->getAmount();
        $this->DATETIME = new DateTime(date("Y:m:d H:i:s", Mage::getModel('core/date')->gmtDate(time())));
        return $this;
  }


  function confirmOrder(){
        $order = $this->ORDER;
        if (!$order->getId()) return false;
        
        if($order->canUnhold()) {
          $order->unhold();
          $order->save();
        }
        
        if (!$order->canInvoice()) return false;
        
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice( array());
        if (!$invoice->getTotalQty()) return false;
   
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->register();

        $invoice->getOrder()->setCustomerNoteNotify(false);
        $invoice->getOrder()->setIsInProcess(true);

        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transactionSave->save();
        $order->sendNewOrderEmail();
        $transaction = $this->getTransactionById($this->TRANSACTION_ID);
        $transaction->setOrderPaymentObject($order->getPayment())->close();
    }
  function submitOrder(){
        if($this->checkPayment()) $this->confirmOrder();
    else $this->cancelOrder();
        return $this;
  }

  function cancelOrder(){
      $order = $this->getOrder();
        if (!$order->getId()) {
            return false;
        }
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
        //$this->sendTransactionEmail();
  }




    /* SYSTEM FUNCIONS */

  function createform($url,$method,$elements,$js=null){




    $form = new Varien_Data_Form();
    $form->setAction($url)
      ->setId($this->p_code)
      ->setName($this->p_code)
      ->setMethod($method)
            ->setUseContainer(true);

    foreach($elements as $key => $element){
      $form->addField($key,
                            'hidden',
                            array('name' => $key, 'value' => $element )
                        );
    }

    
    $html = '<html><head></head><body>';
    $html .= $form->toHtml();
    if($js){
      foreach($js as $j){
        $html.= $j;
      }
     
    }
    else {
      $html .='<script type="text/javascript">document.getElementById("' . $this->p_code . '").submit()</script>';
    }
    $html .='</body></html>';



         return $html;
  }
  /* PAY BY MAIL */

     function sendTransactionEmail(){
    $encr_key = $this->TERMINAL_ID;
    $order = $this->ORDER;
    $translate = Mage::getSingleton('core/translate')->setTranslateInline(false);
    $email = Mage::getModel('core/email_template');
    $template =  Mage::getModel('core/email_template') ->loadByCode('payment_request')->getTemplateId();
    $sender  = array(
      'name' => Mage::getStoreConfig('trans_email/ident_support/name', Mage::app()->getStore()->getId()),
      'email' => Mage::getStoreConfig('trans_email/ident_support/email', Mage::app()->getStore()->getId())
    );
    $url = Mage::getUrl($this->p_code . '/processing/paybymail/track/' . sha1($order->getIncrementId() . $encr_key));
  $email->setDesignConfig(array('area'=>'frontend', 'store'=>Mage::app()->getStore()->getId()))
        ->sendTransactional(
            $template,
            $sender,
            $order->getCustomerEmail(),
            $order->getCustomerName(),
            array(
                'customer'    => $order->getCustomerName(),
                'email'   => $order->getCustomerEmail(),
                'url'     => $url,
                'order'   => $order->getIncrementId()
            )
        );
    }


    function checklink($track){
            $orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('status',array(
        'nin' => array(
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage_Sales_Model_Order::STATE_CLOSED
            )
        )
      );
      foreach($orders as $order){
        if(sha1($order->getIncrementId() . $this->TERMINAL_ID) == $track){


         $o = $order;
        }
      }
      if(!$o) die('link scaduto, rieffettua il tuo acquisto');
      return $o;
    }

    function recoveryOrderByTrack($track){
      $o = $this->checklink($track);
       if($o) {
      $quoteObj = Mage::getModel('sales/quote')->load($o->getQuoteId());
      $quoteObj->setIsActive(true)->save();
     $customer = Mage::getModel('customer/customer')->load( $o->getCustomerId());
      Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);

     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setQuote($quoteObj);
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId($quoteObj->getId());
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastQuoteId($quoteObj->getId());
     Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastOrderId($o->getId());

     
    }
    return null;
  }






  function getRedirectUrl($t=false){return ($t) ? $this->REDIRECT_URL : Mage::getBaseUrl() . $this->REDIRECT_URL;}

  function getPrefix(){ return Mage::getStoreConfig('payment/' . $this->p_code . '_cc/transaction_prefix');   }

  function getOrder(){ return $this->ORDER;}


  function getUrl(){ return ($this->isTestMode()) ? $this->pos_test_url : $this->pos_url;}

  function isS2s(){ return ($this->has_s2s) ? Mage::getStoreConfig('payment/' . $this->p_code . '_cc/s2s_mode') : false;}
  
  function isTestMode(){ return Mage::getStoreConfig('payment/' . $this->p_code . '_cc/test_mode'); }

  function getBillingAddress() {return $this->ORDER->getBillingAddress();}
  function getPostTransactionId(){return $this->pos_transaction_id;}
  function setPosTransactionId($var){
    $this->pos_transaction_id = $var; 
      $t = $this->getTransactionById($this->TRANSACTION_ID)->load();
     $t->setOrderPaymentObject($this->ORDER->getPayment())->setAdditionalInformation('pos_transaction_id',$var)->save();
 
    return $this;
  }

  function firstcheck(){}
  function setMacType($var) {$this->MAC_TYPE = $var; return $this;}
  function getTransactionId(){return $this->TRANSACTION_ID;}
  function getNotifyUrl(){ return  Mage::getBaseUrl() . $this->p_code . '/processing/notification/'; }
  function getSuccessUrl(){ return  Mage::getBaseUrl() . $this->p_code . '/processing/result/';}
  function getErrorUrl(){ return  Mage::getBaseUrl() . $this->p_code . '/processing/result/';}
  function setMoneyFormat($var){ $this->moneyformat=$var; return $this;}
  function getMoneyFormat(){return $this->moneyformat;}
  function getTransactionByPosTransactionId($pos_trx_id){
  $transactions = Mage::getModel('sales/order_payment_transaction')
      ->getCollection()
      ->addPaymentInformation(array('method'))
      ->addFieldToFilter('method',$this->p_code . '_cc');
  foreach ($transactions as $t) {
    $ai = $t->getAdditionalInformation();
    if($ai['pos_transaction_id'] == $pos_trx_id){
           $order = Mage::getModel('sales/order')->load($t->getOrderId());
        $this->ORDER = $order;  
      $this->TRANSACTION = $t;
      $this->TRANSACTION_ID = $t->getTxnId();
      return $this;
    }
  }
  Mage::throwException('Nessuna corrispondenza con id pos');
  }


}