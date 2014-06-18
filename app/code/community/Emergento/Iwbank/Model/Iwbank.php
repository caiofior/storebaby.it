<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */


/* Emergento */
class Emergento_Iwbank_Model_Iwbank extends Emergento_Iwbank_Model_Payment {
  var $p_name = 'Iwbank';
  var $p_code = 'iwbank';
  var $date_format = "Y-m-d H:i:s";

  var $currency = 242;
  var $moneyformat = array(2,'.','');
  
  var $has_checkmac = false;
  var $has_s2s = false;
  var $need_auth = TRUE;
  var $languages = array(
    'it_IT' => 'IT',
    'en_En' => 'EN',
    'en_Us' => 'EN',
    'fr_Fr' => 'FR',
    'de_DE' => 'DE'
    );

  var $results_ok_codes = array( "OK" );
  var $results_ok_codes_test = array( "OK");
  //urls
  var $pos_test_url = 'https://testcheckout.iwsmile.it/Pagamenti/';
  var $pos_url = 'https://checkout.iwsmile.it/Pagamenti/';

  var $notify_url;
  var $error_url;
  var $success_url;
  var $new_url;
  var $_DEBUG = TRUE;

  //------------------
  function getUrl(){

    if($this->isTestMode()){
      return 'https://testcheckout.iwsmile.it/Pagamenti/';
    }
    return 'https://checkout.iwsmile.it/Pagamenti/';
  }
  function getTrxCheckUrl(){
    return ($this->isTestMode()) ? 'https://testcheckout.iwsmile.it/Pagamenti/trx.check' : 'https://checkout.iwsmile.it/Pagamenti/trx.check';
  }
  function isS2s(){
   //return Mage::getStoreConfig('payment/iwbank_cc/s2s_mode');
    return false;
  }
  function isTestMode(){
   return Mage::getStoreConfig('payment/iwbank_cc/test_mode');
  }

  function checkTrx($p_ID,$t_ID,$v_sign,$amount){
    

    $params = array(
        'payer_id'     => $p_ID,
        'thx_id'   => $t_ID,
        'verify_sign'    => $v_sign,
        'amount' => $amount,
        'merchant_key' =>  Mage::getStoreConfig('payment/iwbank_cc/mac_key')
    );
    $client = new Zend_Http_Client($this->getTrxCheckUrl());
$this->ESITO = $client->setParameterPost($params)->request(Zend_Http_Client::POST)->getBody();
    Mage::log('esito ' . $this->ESITO);
    return $this;
  }
 


  public function setPayment($order_id,$card=null){

        $this->LANGUAGE = 'ITA';
        
        $order = Mage::getModel('sales/order')->loadByAttribute('increment_id',$order_id);
        $this->ORDER = $order;
        $this->EMAIL = $order->getCustomerEmail();
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        //converte in euro se la valuta è diversa da EUR
        if($currency != 'EUR'){
          $amount = Mage::helper('directory')->currencyConvert($amount, $currency, 'EUR');

        }
        $this->AMOUNT = number_format($order->getBaseGrandTotal(),2);
        $this->_currency = 'EUR';
        if($card) {
                $this->CARD_OWNER = $card['owner'];
                $this->PAN = $card['number'];
                $this->CARD_TYPE = $card['cctype'];
                $this->EXPIRE_DATE = ( (strlen($card['year']) == 2 ) ?  (20 . $card['year']) : $card['year']   ) . $card['month'];
                $this->CVV2 = $card['ccv'];
        }

        return $this;
    }

  public function callS2s(){

    $params = array(
        'alias'     => Mage::getStoreConfig('payment/iwbank_cc/terminal_id'),
        'importo'   => $this->AMOUNT,
        'divisa'    => 'EUR',
        'tipo_richiesta' => 'PA',
        'url' => Mage::getBaseUrl() . 'iwbank/processing/result',
        'urlpost' => Mage::getBaseUrl() . 'iwbank/processing/notification',

        'codTrans'  => $this->TRANSACTION_ID,
        'mail'      => $this->EMAIL,
        'pan'       => $this->PAN,
        'scadenza'  => $this->EXPIRE_DATE,
        'cv2'       => $this->CVV2,
        'mac'       => $this->setMacType('start')->getMac()

    );

    $client = new Zend_Http_Client($this->getUrl());



    $client->setParameterGet($params);
    $req = simplexml_load_string($client->request()->getBody());

    if (isset($req->AUTHRES->HTML_CODE)) {
      $this->LAST_MAC = $req->MAC;
      echo $req->AUTHRES->HTML_CODE;
      die();
    } else {
      $this->RESPONSE = json_decode(json_encode($req));
      list($this->DATE,$this->TIME) = explode('T', $this->RESPONSE->StoreResponse->dataOra);
      $this->CODAUT = $this->RESPONSE->StoreResponse->codiceAutorizzazione;
      $this->LAST_MAC = $this->RESPONSE->StoreResponse->mac;
      $this->ESITO = $this->RESPONSE->StoreResponse->codiceEsito;
      $this->submitOrder();
    }

   

    return $this;
  }


  function setStandardResponse($date,$iwbank_thx,$codaut,$mac,$payer_id){
    $this->PAYER_ID = $payer_id;
    $this->IWBANK_THX = $iwbank_thx;
    $this->DATE = $date;
    $this->CODAUT = $codaut;
    $this->LAST_MAC = $mac;
    return $this;
  }

function checkMac(){
    return ($this->setMacType('result')->getMac() == $this->getLastMac()) ? true : false;

}


function getLastMac(){
  return $this->LAST_MAC;
}

function getRedirectUrl($t=false){

  return ($t) ? $this->REDIRECT_URL : Mage::getBaseUrl() . $this->REDIRECT_URL;
}



  function standardcall(){
                $form = new Varien_Data_Form();
                $form->setAction($this->getUrl())->setId('iwbank')->setName('iwbank')->setMethod('GET')->setUseContainer(true);

                $values_form = array(
                        'ACCOUNT'     => Mage::getStoreConfig('payment/iwbank_cc/terminal_id'),
                        'PAYER_EMAIL' => $this->EMAIL,
                        'LANG_COUNTRY' => $this->getLanguage(),
                        'FLAG_ONLY_IWS' => 0,
                        'FLAG_ONLY_CC' => 0,
                        'AMOUNT' => $this->AMOUNT,
                        'URL_OK' => Mage::getBaseUrl() . 'iwbank/processing/result',
                        'URL_CALLBACK' => Mage::getBaseUrl() . 'iwbank/processing/notification',
//                        'mac' => $this->getMac('start'),
                        'CUSTOM' =>  $this->TRANSACTION_ID,
                        'ITEM_NAME' => $this->getOrder()->getIncrementId(),
                        'ITEM_NAME' => $this->getOrder()->getIncrementId(),
                        'URL_BAD' => Mage::getBaseUrl() . 'iwbank/processing/result'
                        );


                foreach($values_form as $key => $value) {
                        $form->addField(
                            $key,
                            'hidden',
                            array('name' => $key, 'value' => $value )
                        );
                }
                $html = '<html><body>';
                $html .= 'Fra pochi secondi sarai reindirizzato sul sito di X-Pay Payment Gateway.';
                $html .= $form->toHtml();
                $html .= '<script type="text/javascript">document.getElementById("iwbank").submit();</script>';
                $html .= '</body></html>';  
                $this->HTML_RESPONSE = $html;
                return $this;
  }

 
  // Metodi di valorizzazione attributi
  function getMac(){ 
        $type = $this->MAC_TYPE;
        $mac_key =  Mage::getStoreConfig('payment/iwbank_cc/mac_key');

      switch ($type) {
        case 'start':
          $mac = 'codTrans=' . $this->TRANSACTION_ID . 'divisa=' . $this->_currency . 'importo=' . $this->AMOUNT . $mac_key;
          
          break;
        case 'authres':
          $mac = '<TERMINAL_ID>' . $this->TERMINAL_ID . '<TERMINAL_ID>' .
                 '<TRANSACTION_ID>' . $this->TRANSACTION_ID . '<TRANSACTION_ID>' . 
                 '<HTML_CODE>' . $this->_html_code . '</HTML_CODE>';
          break;
        case 'result':
         if($this->isS2s()) {
        $mac = 'codTrans=' . $this->TRANSACTION_ID . 'divisa=' . $this->_currency . 'importo=' . $this->AMOUNT . 'codAut=' . $this->CODAUT . 'data=' . $this->DATE . 'orario=' . $this->TIME . $mac_key;
        } else {

          $mac = 'codTrans=' . $this->TRANSACTION_ID . 'esito=' . $this->ESITO . 'importo=' . $this->AMOUNT . 'divisa=' . $this->_currency . 
             'data=' . $this->DATE . 'orario=' . $this->TIME .  'codAut=' . $this->CODAUT . $mac_key;
        }
          break;
        
        default:
          # code...
          break;
      }
      
      return $mac = sha1($mac);
  }


}