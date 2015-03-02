<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(Mage::getBaseDir('lib').DS.'multisafepay'.DS.'MultiSafepay.combined.php');

class MultiSafepay_Msp_Model_Checkout extends Mage_Payment_Model_Method_Abstract
{
    protected $_code         = "mspcheckout";
    protected $_settings     = 'mspcheckout';
    protected $_quote;
    protected $_storeId      = null;
    protected $total_amount  = 0;

    protected $api;
    protected $base;

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = false;
    protected $_canRefundInvoicePartial = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;

    protected $_cachedShippingInfo = array(); // Cache of possible shipping carrier-methods combinations per storeId

    public function canUseCheckout()
    {
        return false;
    }
  

    // need this for 1.3.x.x, or it will always be true
    public function isAvailable($quote = null)
    {
        // maybe call parent for higher versions?
        return (bool)(int)$this->getConfigData('active', ($quote ? $quote->getStoreId() : null));
    }

    /**
     * Returns an instance of the Base
     */
    public function getBase($id = null)
    {
        if ($this->base) {
            if ($id) {
                $this->base->setLogId($id);
                $this->base->setLockId($id);
            }

            return $this->base;
        }
    
        $config = Mage::getStoreConfig($this->_settings . "/settings", $this->_storeId);
    
        $this->base = Mage::getSingleton("msp/base");
        $this->base->setConfigObject($config);
        $this->base->setLogId($id);
        $this->base->setLockId($id);

        return $this->base;
    }
  
    /**
     * Returns an instance of the Api
     */
    public function getApi($id = null)
    {
        if ($this->api) {
            if ($id) {
                $this->getBase($id);
            }

            return $this->api;
        }

        $base      = $this->getBase($id);
        $this->api = $base->getApi();

        return $this->api;
    }
  
  
    /**
     * Start checkout
     */
    public function startCheckout()
    {
        $storeId        = Mage::app()->getStore()->getStoreId();
        $storeName      = Mage::app()->getStore()->getName();
        $this->_storeId = $storeId;

        $session        = Mage::getSingleton('checkout/session');
        $this->_quote   = $session->getQuote();
    
        $quoteId        = $this->_quote->getId();
        $api            = $this->getApi($quoteId);
    
        $this->getBase()->log('startCheckout');
    
        $api->merchant['notification_url'] = Mage::getUrl("msp/checkout/notification", array("_secure" => true)) . "?type=initial";
        $api->merchant['cancel_url']       = Mage::getUrl("checkout/cart/", array("_secure" => true));
        $api->merchant['redirect_url']     = Mage::getUrl("msp/checkout/return", array("_secure" => true));
    
        $api->transaction['id']            = $quoteId;
        $api->transaction['currency']      = 'EUR';
        $api->transaction['amount']        = intval((string)($this->_quote->getBaseGrandTotal() * 100)); // may include shipping costs?
        $api->transaction['description']   = 'Order #' . $quoteId . ' at ' . $storeName;
        $api->customer['locale']           = Mage::app()->getLocale()->getDefaultLocale();

        $customerSession = Mage::getSingleton('customer/session');
        $customer        = $customerSession ->getCustomer();

        $billing         = $customer->getPrimaryBillingAddress();
        $shipping        = $customer->getPrimaryShippingAddress();

        $lang = $this->getConfigData('lang');
        if (empty($lang)) {
            $api->customer['locale'] = Mage::app()->getLocale()->getLocaleCode();//Mage::app()->getLocale()->getDefaultLocale(); $lang
        }
        $api->customer['locale'] = $lang;
    
        if ($billing) {
           $this->api->parseCustomerAddress($billing->getStreet(1));
			
			if($this->api->customer['housenumber'] == '')
			{
				$this->api->customer['housenumber']         = 	$billing->getStreet(2);
				$this->api->customer['address1']         	= 	$billing->getStreet(1);
			}
            $api->customer['firstname']        = $billing->getFirstname();
            $api->customer['lastname']         = $billing->getLastname();
            $api->customer['address2']         = $billing->getStreet(2);
            $api->customer['zipcode']          = $billing->getPostcode();
            $api->customer['city']             = $billing->getCity();
            $api->customer['state']            = $billing->getState();
            $api->customer['country']          = $billing->getCountry();
            $api->customer['phone']            = $billing->getTelephone();
            $api->customer['email']            = $customer->getEmail();
        }
    
        if ($shipping) {
            $api->parseDeliveryAddress($shipping->getStreet(1));
            $api->delivery['firstname']        = $shipping->getFirstname();
            $api->delivery['lastname']         = $shipping->getLastname();
            $api->delivery['address2']         = $shipping->getStreet(2);
            $api->delivery['zipcode']          = $shipping->getPostcode();
            $api->delivery['city']             = $shipping->getCity();
            $api->delivery['state']            = $shipping->getState();
            $api->delivery['country']          = $shipping->getCountry();
            $api->delivery['phone']            = $shipping->getTelephone();
        }
        $api->customer['referrer']             = $_SERVER['HTTP_REFERER'];
        $api->customer['user_agent']           = $_SERVER['HTTP_USER_AGENT'];
        $api->customer['ipaddress']            = $_SERVER['REMOTE_ADDR'];
        $this->getItems();
		
		$table 						= 	new MspAlternateTaxTable();
		$table->name				=	'BTW0';
		$rule 						= 	new MspAlternateTaxRule('0.00');
		$table->AddAlternateTaxRules($rule);
		$this->api->cart->AddAlternateTaxTables($table);

		$discount_amount = $this->_quote->getData();
		$discount_amount_final	= $discount_amount['base_subtotal'] - $discount_amount['subtotal_with_discount'];
		$discount_amount_final	= number_format($discount_amount_final, 4, '.', '');
			
		//Add discount line item
		if($discount_amount_final != 0){
			$c_item = new MspItem('Discount', 'Discount', 1, -$discount_amount_final	, 'KG', 0);// Todo adjust the amount to cents, and round it up.
			$c_item->SetMerchantItemId('Discount');
			$c_item->SetTaxTableSelector('BTW0');
			$api->cart->AddItem($c_item);
		}
	

        $this->getShipping();//
        $this->_getShippingRates();
        $this->setTaxes();
        $this->getCustomFieldsFromFile();
		
    
        if ($this->getSectionConfigData('checkout_google_analytics/active')) {
            $api->ganalytics['account'] = $this->getSectionConfigData('checkout_google_analytics/account');
        }
        
        if ($this->getSectionConfigData('checkout_custom_fields/company_active')) {
            $this->getCompanyField();
        }
    
        if ($this->getSectionConfigData('checkout_custom_fields/agreements_active')) {
            $this->getAgreementField();
        }
    
        $this->getExtraFields();

        $url = $api->startCheckout();
    
        $this->getBase($quoteId)->log($api->request_xml);
        $this->getBase($quoteId)->log($api->reply_xml);
    
        if ($api->error) {
            $this->getBase()->log("Error %s: %s", $api->error_code, $api->error);
            echo "Error " . $api->error_code . ": " . $api->error;
            exit();
        }
    
        $this->getBase()->log('Payment URL: %s', $url);

        return $url;
    }

    public function getAgreementField()
    {
        $field = new MspCustomField('acceptagreements', 'checkbox', '');
    
        // description
        $config_url = $this->getSectionConfigData('checkout_custom_fields/agreements_url');
        if (!empty($config_url)) {
            $link = $config_url;
        } else {
            $link = Mage::getUrl("msp/checkout/agreements/", array("_secure" => true));
        }
    
        $description = array(
            'nl' => 'Ik ga akkoord met de <a href="'.$link.'" target="_blank">algemene voorwaarden</a>', 
            'en' => 'I accept the <a href="'.$link.'" target="_blank">terms and conditions</a>', 
        );
        $field->descriptionRight = array('value' => $description);
    
        // validation
        $error = array(
            'nl' => 'U dient akkoord te gaan met de algemene voorwaarden', 
            'en' => 'Please accept the terms and conditions', 
        );
        $validation = new MspCustomFieldValidation('regex', '^[1]$', $error);
        $field->AddValidation($validation);
    
        $this->api->fields->AddField($field);
    }

    public function getExtraFields()
    {
        $option = $this->getSectionConfigData('checkout_custom_fields/company_active');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('companyname', 2 == $option );
            $this->api->fields->AddField($field);           
        }
    
        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_salutation'); 
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('salutation', 2 == $option );
            $this->api->fields->AddField($field);           
        }                   

        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_sex');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('sex', 2 == $option );
            $this->api->fields->AddField($field);           
        }
      
        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_comment');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('comment', 2 == $option );
            $this->api->fields->AddField($field);           
        }
      
        //checkbox     
        if ($this->getSectionConfigData('checkout_custom_fields/xtra_newsletter')) {
            $field = new MspCustomField();
            $field->SetStandardField('newsletter', 1);
            $this->api->fields->AddField($field);           
        }

        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_vatnumber');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('vatnumber', 2 == $option );
            $this->api->fields->AddField($field);           
        }

        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_birthday');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('birthday', 2 == $option );
            $this->api->fields->AddField($field);           
        }

        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_chamberofcommerce');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('chamberofcommerce', 2 == $option );
            $this->api->fields->AddField($field);           
        }
      
        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_passportnumber');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('passportnumber', 2 == $option );
            $this->api->fields->AddField($field);           
        }

        $option = $this->getSectionConfigData('checkout_custom_fields/xtra_driverslicense');
        if ($option) {
            $field = new MspCustomField();
            $field->SetStandardField('driverslicense', 2 == $option );
            $this->api->fields->AddField($field);           
        }               
    }
  
    /**
     * Notification
     */
    public function notification($quoteId, $initial = false)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('sales/quote')->load($quoteId);

        $this->_storeId = $quote->getStoreId();
        $base = $this->getBase($quoteId);
    
		//TEMP locking disabled because of check on redirect url
	
        // check lock
       /* if ($base->isLocked()) {
            $base->preventLockDelete();
        
            if ($initial) {
                return;
            } else {
                echo 'locked';
               exit();
            }
        }
        // lock
        $base->lock();
		*/
        $base->log('notification');
    
        $api = $this->getApi();
        $api->transaction['id'] = $quoteId;
        $status = $api->getStatus();

        /** @var $helper MultiSafepay_Msp_Helper_Data */
        $helper = Mage::helper('msp');
        $isRestored = $helper->restoreCart($quote, $status);
        $base->log("Quote was restored: " . ($isRestored ? 'TRUE' : 'FALSE'));
    
        $base->log($api->request_xml);
        $base->log($api->reply_xml);

        if ($api->error) {
            $base->log("Error %s: %s", $api->error_code, $api->error);
            echo "Error " . $api->error_code . ": " . $api->error;
            $base->unlock();
            exit();
        }
    
        $this->_createOrder($quoteId);
    
        $base->log("Quote ID: $quoteId");
        // get the order
        $order = Mage::getSingleton('sales/order')->loadByAttribute('ext_order_id', $quoteId);
        if (!$order) {
            $this->getBase()->log("Failed to load order");
            echo 'Failed to load order ' . $quoteId;
        }
        $ret = $base->updateStatus($order, $status, $api->details);
        // cancel order if needed
        if ($base->cancel) {
            $order->cancel;
        }
    
        // unlock
        $base->unlock();

        return $ret;
    }

    public function getCustomFieldsFromFile()
    {
        $ret  = '';
        $file = dirname(dirname(__FILE__)) . DS . 'etc' . DS . 'customfields.xml';
        if (is_readable($file)) {
            $ret = @file_get_contents($file);
        }
        $this->api->fields->SetRaw($ret);
    }
  
    protected function getItems()
    {
        foreach ($this->_quote->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            $taxClass = ($item->getTaxClassId() == 0 ? 'none' : $item->getTaxClassId());
            $weight = (float) $item->getWeight();
      
            // name and options
            $itemName = $item->getName();
            $options = $this->getProductOptions($item);
            if (!empty($options)) {
                $optionString = '';
                foreach ($options as $option) {
                    $optionString = $option['label'] . ": " . $option['print_value'] . ",";
                }
                $optionString = substr($optionString, 0, -1);
        
                $itemName .= ' (';
                $itemName .= $optionString;
                $itemName .= ')';
            }
      
            $price = round($item->getBaseCalculationPrice(), 2); // Magento uses the rounded number, so we do too
		
		/*
			if($_SERVER['REMOTE_ADDR'] == '176.74.248.173')
			{
				
				$price	= number_format($item->getPrice(), 4, '.', ''); echo $price;exit;
			}
*/

			//code below was disabled, this is now activated again to avoid rounding problems
			if (Mage::helper("Tax")->priceIncludesTax()){
					$priceIncl = $item->getBasePriceInclTax() ;
					$taxRate   = $item->getTaxPercent();
					$price     = ($priceIncl / (1+($taxRate/100)));
				}
			
			//$price	= number_format($item->getPrice(), 4, '.', '');	   
            $this->total_amount += ($item->getBasePriceInclTax() * $item->getQty());
            // create item
            $c_item = new MspItem($itemName, $item->getDescription(), $item->getQty(), $price, 'KG', $item->getWeight());
            $c_item->SetMerchantItemId($item->getSku());
            $c_item->SetTaxTableSelector($taxClass);
            $this->api->cart->AddItem($c_item);
        }
    }

    public function getProductOptions($item)
    {
        $options = array();
        if ($optionIds = $item->getOptionByCode('option_ids')) {
            $options = array();
            foreach (explode(',', $optionIds->getValue()) as $optionId) {
                if ($option = $item->getProduct()->getOptionById($optionId)) {
                    $quoteItemOption = $item->getOptionByCode('option_' . $option->getId());
                    $group = $option->groupFactory($option->getType())->setOption($option)->setQuoteItemOption($quoteItemOption);
                    $options[] = array(
                        'label'       => $option->getTitle(),
                        'value'       => $group->getFormattedOptionValue($quoteItemOption->getValue()),
                        'print_value' => $group->getPrintableOptionValue($quoteItemOption->getValue()),
                        'option_id'   => $option->getId(),
                        'option_type' => $option->getType(),
                        'custom_view' => $group->isCustomizedView()
                    );
                }
            }
        }

        if ($addOptions = $item->getOptionByCode('additional_options')) {
            $options = array_merge($options, unserialize($addOptions->getValue()));
        }

        return $options;
    }
  
    protected function getShipping()
    {
        if (!$this->getFreeShipping()) {
            $this->getFlatRateShipping();
            $this->getTableRateShipping();
            $this->getPickup();
        }
    }

    protected function _getShippingRates()
    {
        $this->_getFreeShippingRates();
        $this->_getFlatRateShippingRates();
        $this->_getAvailableShippingRates(array('flatrate_flatrate', 'freeshipping_freeshipping'));
    }

    protected function _getFreeShippingRates()
    {
        if (!Mage::getStoreConfigFlag('carriers/freeshipping/active')) {
            return $this;
        }

        $config = Mage::getStoreConfig('carriers/freeshipping');

        $allowSpecific     = $config['sallowspecific'];
        $specificCountries = $config['specificcountry'];
        $title             = $config['title'];
        $name              = $config['name'];
        $price             = $config['free_shipping_subtotal'];

        if ($price <= $this->total_amount) {
            $shipping_method = new MspFlatRateShipping($title.' - '.$name, '0.00');
            if ($allowSpecific == 1 && $specificCountries) {
                $filter = new MspShippingFilters();
                foreach (explode(',', $specificCountries) as $country) {
                    $filter->AddAllowedPostalArea($country);
                }
                $shipping_method->AddShippingRestrictions($filter);
            }
            $this->api->cart->AddShipping($shipping_method);
        }

        return $this;
    }

    protected function _getFlatRateShippingRates()
    {
        if (!Mage::getStoreConfigFlag('carriers/flatrate/active')) {
            return $this;
        }

        $config = Mage::getStoreConfig('carriers/flatrate');

        $allowSpecific     = $config['sallowspecific'];
        $specificCountries = isset($config['specificcountry']) ? $config['specificcountry'] : '';
        $title             = $config['title'];
        $name              = $config['name'];
        $type              = $config['type'];
        $price             = $config['price'];
        $handlingType      = $config['handling_type'];
        $handlingFee       = isset($config['handling_fee']) ? $config['handling_fee'] : 0;

        switch ($type) {
            case 'O':
                $shippingPrice = $price;
                break;
            case 'I':
                $shippingPrice = $price * Mage::getSingleton('checkout/cart')->getSummaryQty();
                break;
            default:
                $shippingPrice = 0;
                break;
        }

        if ($handlingFee) {
            switch ($handlingType) {
                case 'F':
                    $shippingPrice = $shippingPrice +$handlingFee;
                    break;
                case 'P':
                    $shippingPrice = $shippingPrice + $shippingPrice*$handlingFee;
                    break;
            }
        }

        $shipping_method = new MspFlatRateShipping($title.' - '.$name, number_format($shippingPrice, '2', '.', ''));
        if ($allowSpecific == 1 && $specificCountries) {
            $filter = new MspShippingFilters();
            foreach (explode(',', $specificCountries) as $country) {
                $filter->AddAllowedPostalArea($country);
            }
            $shipping_method->AddShippingRestrictions($filter);
        }
        $this->api->cart->AddShipping($shipping_method);

        return $this;
    }

    protected function _getAvailableShippingRates($exceptMethods)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('sales/quote');

        /** @var $shippingAddress Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCountryId(null);
        $shippingAddress->setPostcode(null);
        $shippingAddress->setCollectShippingRates(true);

        $rates = $shippingAddress->collectShippingRates()->getGroupedAllShippingRates();

        foreach ($rates as $carrier) {
            foreach ($carrier as $rate) {
                if (!in_array($rate->getCode(), $exceptMethods)) {
                    $shipping_method = new MspFlatRateShipping($rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle(), number_format($rate->getPrice(), '2', '.', ''));
                    /* $config = Mage::getStoreConfig('carriers/'.$rate->getCode());
                        if ($allowSpecific == 1 && $specificCountries) {
                        $filter = new MspShippingFilters();
                        foreach (explode(',', $specificCountries) as $country) {
                            $filter->AddAllowedPostalArea($country);
                        }
                        $shipping_method->AddShippingRestrictions($filter);
                    }*/
                    $this->api->cart->AddShipping($shipping_method);
                }
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getFreeShipping()
    {
        if (!$this->getSectionConfigData('freeshipping/active')) {
            return false;
        }

        $allowSpecific     = $this->getSectionConfigData('freeshipping/sallowspecific');
        $specificCountries = $this->getSectionConfigData('freeshipping/specificcountry');
        $title             = $this->getSectionConfigData('freeshipping/title');
        $price             = $this->getSectionConfigData('freeshipping/free_shipping_subtotal');
        
        $free_price = '0.00';

        if ($price <= $this->total_amount) {
            $shipping_method = new MspFlatRateShipping($title, $free_price);
            if ($allowSpecific == 1 && $specificCountries) {
                $filter = new MspShippingFilters();
                foreach (explode(',', $specificCountries) as $country) {
                    $filter->AddAllowedPostalArea($country);
                }
                $shipping_method->AddShippingRestrictions($filter);
            }
            $this->api->cart->AddShipping($shipping_method);
            
            return true; //shipping is free so return true to skip the other shippingmethods
        }

        return false;
    }

    /**
     * @return void
     */
    public function getPickup()
    {
        if (!$this->getSectionConfigData('checkout_shipping_pickup/active')) {
            return;
        }
        $title = $this->getSectionConfigData('checkout_shipping_pickup/title');
        $price = $this->getSectionConfigData('checkout_shipping_pickup/price');
        $price = (float) Mage::helper('tax')->getShippingPrice($price, false, false);
    
        $shipping = new MspPickup($title, $price);
        $this->api->cart->AddShipping($shipping);
    }

    /**
     * @return void
     */
    public function getFlatRateShipping()
    {
        if (!$this->getSectionConfigData('checkout_shipping_flatrate/active')) {
            return;
        }

        // flat rate
        for ($xml='', $i=1; $i<=3; $i++) {
            $title = $this->getSectionConfigData('checkout_shipping_flatrate/title_'.$i);
            $price = $this->getSectionConfigData('checkout_shipping_flatrate/price_'.$i);

            if (empty($title) || empty($price)) {
                continue;
            }

            $allowSpecific     = $this->getSectionConfigData('checkout_shipping_flatrate/sallowspecific_'.$i);
            $specificCountries = $this->getSectionConfigData('checkout_shipping_flatrate/specificcountry_'.$i);

            $price = number_format($price, 2, '.','');
            $price = (float) Mage::helper('tax')->getShippingPrice($price, false, false);

            $shipping = new MspFlatRateShipping($title, $price);
            if ($allowSpecific == 1 && $specificCountries) {
                $filter = new MspShippingFilters();
                foreach (explode(',', $specificCountries) as $country) {
                    $filter->AddAllowedPostalArea($country);
                }
                $shipping->AddShippingRestrictions($filter);
            }
            $this->api->cart->AddShipping($shipping);
        }
    }

    /**
     * @return array
	 *
     */
    public function getTableRateCountries()
    {
        $countries = array();
        $websiteId = $this->_quote->getStore()->getWebsiteId();

        $prefix = Mage::getConfig()->getTablePrefix();
        $sql    = "SELECT DISTINCT dest_country_id FROM ".$prefix."shipping_tablerate WHERE website_id = '".$websiteId."'";

        //$sql = "SELECT DISTINCT dest_country_id FROM shipping_tablerate WHERE website_id = '".$websiteId."'";

        $getData = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);
    
        foreach ($getData as $row) {
            $countries[] = $row['dest_country_id'];
        }

        return $countries;
    }

    /**
     * @return void
     */
    public function getTableRateShipping()
    {
        if (!$this->getSectionConfigData('checkout_shipping_tablerate/active')) {
            return;
        }
  
        $address   = $this->_quote->getShippingAddress();
        $countries = $this->getTableRateCountries();
        $rates     = array();

        foreach ($countries as $country) {
            if ($country == '0') { // *, all countries
                // can't query on 0, so set to non-existing country
                $country = 'ALL';
            }
            $address->setCountryId($country);
            $address->setCollectShippingRates(true)->collectShippingRates();

            // get shipping rate by code (getShippingRateByCode doens't check on isDeleted(), getAllShippingRates() does)
            $rate = null;
            $allRates = $address->getAllShippingRates();
            foreach ($allRates as $r) {
                if ($r->getCode()=='tablerate_bestway') {
                    $rate = $r;
                    break;
                }
            }
          
            if ($rate === null) {
                continue;
            }
          
            if ($rate instanceof Mage_Shipping_Model_Rate_Result_Error) {
                continue;
            }
          
            // Todo add function to enable disable tax for the rate shipping method to avoid problems when merchant uses prices including the tax
          
            $rates[] = array(
                'country' => $country,
                'price'   => $rate->getPrice(), //'price'   => (float) Mage::helper('tax')->getShippingPrice($rate->getPrice(), false, false), test met optie voor verzendmethode btw aan of uit
                'code'    => $rate->getCode(),
                'method'  => $rate->getMethodTitle(),
                'carrier' => $rate->getCarrierTitle(),
            );
        }

        foreach($rates as $rate) {
            $title = $rate['carrier'] . ' - ' . $rate['method'];
            $shipping = new MspFlatRateShipping($title, $rate['price']);
          
            $filter = new MspShippingFilters();
            if ($rate['country'] != 'ALL') {
                $filter->AddAllowedPostalArea($rate['country']);
                $shipping->AddShippingRestrictions($filter);
            } else {
                // wildcard country, exclude all countries we already have a rate for
                foreach($countries as $country) {
                    if ($country != '0') {
                        $filter->AddExcludedPostalArea($country);
                    }
                }
                $shipping->AddShippingRestrictions($filter);
            }
            $this->api->cart->AddShipping($shipping);
        }
    }
  
    protected function setTaxes()
    {
        $this->_getTaxTable($this->_getShippingTaxRules(), 'default');
        $this->_getTaxTable($this->_getTaxRules(), 'alternate');
        // add 'none' group?
    }

    protected function _getTaxTable($rules, $type)
    {
        if (is_array($rules)) {
            foreach ($rules as $group=>$taxRates) {
                if ($type != 'default') {
                    $table = new MspAlternateTaxTable($group, 'true');
                    $shippingTaxed = 'false';
                } else {
                    $shippingTaxed = 'true';
                }

                if (is_array($taxRates)) {
                    foreach ($taxRates as $rate) {
                        if ($type != 'default') {
                            $rule = new MspAlternateTaxRule($rate['value']);
                            $rule->AddPostalArea($rate['country']);
                            $table->AddAlternateTaxRules($rule);
                        } else {
                            $rule = new MspDefaultTaxRule($rate['value'], $shippingTaxed);
                            $rule->AddPostalArea($rate['country']);
                            $this->api->cart->AddDefaultTaxRules($rule);
                        }
                    }    
                } else {
                    $taxRate = $taxRates/100;
                    if ($type != 'default') {
                        $rule = new MspAlternateTaxRule($taxRate);
                        $rule->SetWorldArea();
                        $table->AddAlternateTaxRules($rule);
                    } else {
                        $rule = new MspDefaultTaxRule($taxRate, $shippingTaxed);
                        $rule->SetWorldArea();
                        $this->api->cart->AddDefaultTaxRules($rule);
                    }
                }  
                if ($type != 'default') {
                    $this->api->cart->AddAlternateTaxTables($table);
                }
            }
        } else {
            if (is_numeric($rules)) {
                $taxRate = $rules/100;
                if ($type != 'default') {
                    $table = new MspAlternateTaxTable();
                    $rule = new MspAlternateTaxRule($taxRate);
                    $rule->SetWorldArea();
                    $table->AddAlternateTaxRules($rule);
                    $this->api->cart->AddAlternateTaxTables($table);
                    print_r($table);//Validate this one!
                } else {
                    $rule = new MspDefaultTaxRule($taxRate, 'true');
                    $rule->SetWorldArea();
                    $this->api->cart->AddDefaultTaxRules($rule);
                }
            }
        }   
    }
  
    protected function _getTaxRules()
    {
        $customerTaxClass = $this->_getCustomerTaxClass();
        if (Mage::helper('tax')->getTaxBasedOn() == 'origin') {
            $request = Mage::getSingleton('tax/calculation')->getRateRequest();

            return Mage::getSingleton('tax/calculation')->getRatesForAllProductTaxClasses($request->setCustomerClassId($customerTaxClass));
        } else {
            $customerRules = Mage::getSingleton('tax/calculation')->getRatesByCustomerTaxClass($customerTaxClass);
            $rules = array();
            foreach ($customerRules as $rule) {
                $rules[$rule['product_class']][] = $rule;
            }

            return $rules;
        }
    }

    protected function _getShippingTaxRules()
    {
        $customerTaxClass = $this->_getCustomerTaxClass();
        if ($shippingTaxClass = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->_quote->getStoreId())) {
            if (Mage::helper('tax')->getTaxBasedOn() == 'origin') {
                $request = Mage::getSingleton('tax/calculation')->getRateRequest();
                $request->setCustomerClassId($customerTaxClass)->setProductClassId($shippingTaxClass);

                return Mage::getSingleton('tax/calculation')->getRate($request);
            }
            $customerRules = Mage::getSingleton('tax/calculation')->getRatesByCustomerAndProductTaxClasses($customerTaxClass, $shippingTaxClass);
            $rules = array();
            foreach ($customerRules as $rule) {
                $rules[$rule['product_class']][] = $rule;
            }
            return $rules;
        }

        return array();
    }

    protected function _getCustomerTaxClass()
    {
        $customerGroup = $this->_quote->getCustomerGroupId();
        if (!$customerGroup) {
            $customerGroup = Mage::getStoreConfig('customer/create_account/default_group', $this->_quote->getStoreId());
        }

        return Mage::getModel('customer/group')->load($customerGroup)->getTaxClassId();
    }

    protected function _createOrder($quoteId)
    {
        $this->getBase()->log("Creating order");
    
        $api = $this->api;

        // check if an order is already created
        $orders = Mage::getModel('sales/order')->getCollection()->addAttributeToFilter('ext_order_id', $quoteId);
        
        if (count($orders)) {
            $this->getBase()->log("Existing order found (%d), canceling create order", count($orders));
            return;
        }

        $storeId = Mage::app()->getStore()->getId();
        $config  = Mage::getStoreConfig('mspcheckout' . "/settings", $storeId);
        
        // load quote
        $this->getBase()->log("Loading quote");
        $quote = Mage::getModel('sales/quote')->setStoreId($storeId)->load($quoteId)->setIsActive(true);

        // load details
        $this->getBase()->log("Loading details");
        $billing = $this->_importAddress($api->details['customer']);
    
        if (isset($api->details['custom-fields']['company'])) {
            $billing->setCompany($api->details['custom-fields']['company']);
        }
    
        $quote->getPayment()->importData(array('method'=>'mspcheckout'));
        $this->_importTotals($quote->getShippingAddress());

        //addresses that we use when creating the new customer account

        $shipping_address = array(
            'firstname'  => $this->_getArrayValue($api->details, 'customer-delivery', 'firstname'),
            'lastname'   => $this->_getArrayValue($api->details, 'customer-delivery', 'lastname'),
            'street'     =>  array(
                                 '0' => $this->_getArrayValue($api->details, 'customer-delivery', 'address1'),
                                 '1' => $this->_getArrayValue($api->details, 'customer-delivery', 'housenumber'),
                             ),
            'city'       =>   $this->_getArrayValue($api->details, 'customer-delivery', 'city'),
            'region_id'  =>  '',
            'region'     =>  '',
            'postcode'   =>  $this->_getArrayValue($api->details, 'customer-delivery', 'zipcode'),
            'country_id' =>  $this->_getArrayValue($api->details, 'customer-delivery', 'country'), /* Croatia */
            'telephone'  =>  $this->_getArrayValue($api->details, 'customer-delivery', 'phone1'),
        );
                                        
        $billing_address = array(
            'firstname'  => $api->details['customer']['firstname'],
            'lastname'   => $api->details['customer']['lastname'],
            'street'     => array(
                                '0' => $api->details['customer']['address1'],
                                '1' => $api->details['customer']['housenumber'],
                            ),
            'city'       => $api->details['customer']['city'],
            'region_id'  => '',
            'region'     => '',
            'postcode'   => $api->details['customer']['zipcode'],
            'country_id' => $api->details['customer']['country'],
            'telephone'  => $api->details['customer']['phone1'],
        );
        /**
         *    Start adding customer code
         */
        // Website and Store details
        $websiteId = Mage::app()->getWebsite()->getId();
        $store     = Mage::app()->getStore();
        
        $customer = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setStore($store);

        // Customer Information
        $firstname      =     $api->details['customer']['firstname'];
        $lastname       =     $api->details['customer']['lastname'];
        $email          =     $api->details['customer']['email'];
        $passwordLength =     10;
        $password       =     $customer->generatePassword($passwordLength);
    
        try {
            $customer->firstname     = $firstname;
            $customer->lastname      = $lastname;
            $customer->email         = $email;
            $customer->password_hash = md5($password);
            $customer->setPassword($password);
            
            if ($customer->save()) {
                $customAddress = Mage::getModel('customer/address');
                $customAddress->setData($shipping_address)->setCustomerId($customer->getId())->setIsDefaultBilling('0')->setIsDefaultShipping('1')->setSaveInAddressBook('1');
                try {
                    $customAddress->save();
                }
                catch (Exception $ex) {

                }
                
                $customAddress = Mage::getModel('customer/address');
                $customAddress->setData($billing_address)->setCustomerId($customer->getId())->setIsDefaultBilling('1')->setIsDefaultShipping('0')->setSaveInAddressBook('1');
                try {
                    $customAddress->save();
                }
                catch (Exception $ex) {
                }
            
                $customerId = $customer->getId();
                
                if ($config["send_new_account_email"]) {
                    $customer->sendNewAccountEmail(); 
                }
            } else {
                $customerId = '0'; 
            }
        } catch(Exception $e) {
            $customer ->loadByEmail( $email );
            $customerId = $customer->getId();
        }
        
        $customerObj = Mage::getModel('customer/customer')->load($customerId);
        $quote->assignCustomer($customerObj);
        //we add the customer to quote that will be converted to an order later, but because the customer could exist already then the addresses that are used are wrong. We need to update the addresses within the quote with
        //the addresses used at FCO, so set the billing and shipping addresses again
        $quote->setBillingAddress($billing);
        $customerDelivery = isset($api->details['customer-delivery']) ? $api->details['customer-delivery'] : array();
        $shipping = $this->_importAddress($customerDelivery);
        $quote->setShippingAddress($shipping);
        //Finished resetting the addresses
        
        /**
         *    End adding customer code
         */
        
        // create order
        $this->getBase()->log("Create order");
        $convertQuote = Mage::getSingleton('sales/convert_quote');

        $order = $convertQuote->toOrder($quote);

        if ($quote->isVirtual()) {
            $convertQuote->addressToOrder($quote->getBillingAddress(), $order);
        } 
        else {
            $convertQuote->addressToOrder($quote->getShippingAddress(), $order);
        }
    
        $order->setExtOrderId($quoteId);
        $order->setExtCustomerId($customerId);
    
        if (!$order->getCustomerEmail()) {
            $order->setCustomerEmail($billing->getEmail())
                ->setCustomerPrefix($billing->getPrefix())
                ->setCustomerFirstname($billing->getFirstname())
                ->setCustomerMiddlename($billing->getMiddlename())
                ->setCustomerLastname($billing->getLastname())
                ->setCustomerSuffix($billing->getSuffix());
        }
    
        $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));
        if (!$quote->isVirtual()) {
            $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
        }
    
        foreach ($quote->getAllItems() as $item) {
            $orderItem = $convertQuote->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }
    
        $payment = Mage::getModel('sales/order_payment')->setMethod('mspcheckout');
        $order->setPayment($payment);
        $order->setCanShipPartiallyItem(false);
    
        $new_status = $this->getSectionConfigData("settings/order_status");
    
        $customFieldData = '';
        if (!empty($api->details['custom-fields'])) {
            $customFieldData .= Mage::helper("msp")->__("<br/><br/><strong>Custom field data:</strong><br/>");
        
            foreach($api->details['custom-fields'] as $name => $value) {
                $customFieldData .= $name . ': ' . $value . '<br/>';
            }
        }
    
        $order->addStatusToHistory($new_status, Mage::helper("msp")->__("Order created").'<br/>'.Mage::helper("msp")->__("Quote ID: <strong>%s</strong>", $quoteId).$customFieldData);
    
        $this->getBase()->log("Placing and saving order");

        // Update stock
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order'=>$order, 'quote'=>$quote));

        $order->place();
        $order->save();
        $this->getBase()->log("Sending order e-mail");
        $order->sendNewOrderEmail();
    }
  
    protected function _importAddress($values, Varien_Object $qAddress=null)
    {
        if (is_array($values)) {
            $values = new Varien_Object($values);
        }

        if (!$qAddress) {
            $qAddress = Mage::getModel('sales/quote_address');
        }

        $qAddress->setFirstname($values->firstname)->setLastname($values->lastname);

        //$region = Mage::getModel('directory/region')->loadByCode('', $values->country);

        $qAddress->setCompany($values->company)
            ->setEmail($values->email)
            ->setStreet(trim($values->address1 . ' ' . $values->housenumber ."\n" . $values->address2))
            ->setCity($values->city)
            //->setRegion($values->state)
            //->setRegionId($region->getId())
            ->setPostcode($values->zipcode)
            ->setCountryId($values->country)
            ->setTelephone($values->phone1)
            ->setFax($values->phone2);

        return $qAddress;
    }
    
    protected function _getShippingInfos($storeId = null)
    {
        $cacheKey = ($storeId === null) ? 'nofilter' : $storeId;
        if (!isset($this->_cachedShippingInfo[$cacheKey])) {
            /* @var $shipping Mage_Shipping_Model_Shipping */
            $shipping = Mage::getModel('shipping/shipping');
            $carriers = Mage::getStoreConfig('carriers', $storeId);
            $infos = array();

            foreach (array_keys($carriers) as $carrierCode) {
                $carrier = $shipping->getCarrierByCode($carrierCode);
                if (!$carrier) {
                    continue;
                }

                if ($carrierCode == 'googlecheckout') {
                    // Add info about internal google checkout methods
                    $methods = array_merge($carrier->getAllowedMethods(), $carrier->getInternallyAllowedMethods());
                    $carrierName = 'Google Checkout';
                } else {
                    $methods = $carrier->getAllowedMethods();
                    $carrierName = Mage::getStoreConfig('carriers/' . $carrierCode . '/title', $storeId);
                }

                foreach ($methods as $methodCode => $methodName) {
                    $code = $carrierCode . '_' . $methodCode;
                    $name = sprintf('%s - %s', $carrierName, $methodName);
                    $infos[$code] = array(
                        'code'    => $code,
                        'name'    => $name, // Internal name for google checkout api - to distinguish it in google requests
                        'carrier' => $carrierCode,
                        'carrier_title' => $carrierName,
                        'method'  => $methodCode,
                        'method_title'  => $methodName
                    );
                }
            }
            $this->_cachedShippingInfo[$cacheKey] = $infos;
        }

        return $this->_cachedShippingInfo[$cacheKey];
    }
    
    protected function _createShippingRate($code, $storeId = null)
    {
        $rate = Mage::getModel('sales/quote_address_rate')->setCode($code);

        $infos = $this->_getShippingInfos($storeId);
        if (isset($infos[$code])) {
            $info = $infos[$code];
            $rate->setCarrier($info['carrier'])->setCarrierTitle($info['carrier_title'])->setMethod($info['method'])->setMethodTitle($info['method_title']);
        }

        return $rate;
    }

    protected function _importTotals($qAddress)
    {
        $details = $this->api->details;
    
        $qAddress->setTaxAmount($this->_reCalculateToStoreCurrency($details['total-tax']['total'], $qAddress->getQuote()));
        $qAddress->setBaseTaxAmount($details['total-tax']['total']);

        if ($details['shipping']['type'] == 'flat-rate-shipping') {
            $method = 'mspcheckout_flatrate';
        } elseif($details['shipping']['type'] == 'pickup') {
            $method = 'mspcheckout_pickup';
        }
        
        if (!empty($method)) {
            //Mage::getSingleton('tax/config')->setShippingPriceIncludeTax(false);
            
            $excludingTax = $details['shipping']['cost'];
            
            $qAddress->setShippingMethod($method)
                ->setShippingDescription($details['shipping']['name'])
                ->setShippingAmount($this->_reCalculateToStoreCurrency($excludingTax, $qAddress->getQuote()), true)
                ->setBaseShippingAmount($excludingTax, true);

            $includingTax = Mage::helper('tax')->getShippingPrice($excludingTax, true, $qAddress, $qAddress->getQuote()->getCustomerTaxClassId());
            $shippingTax = $includingTax - $excludingTax;
            $qAddress->setShippingTaxAmount($this->_reCalculateToStoreCurrency($shippingTax, $qAddress->getQuote()))
              ->setBaseShippingTaxAmount($shippingTax)
              ->setShippingInclTax($includingTax)
              ->setBaseShippingInclTax($this->_reCalculateToStoreCurrency($includingTax, $qAddress->getQuote()));
        } else {
            $qAddress->setShippingMethod(null);
        }

        $qAddress->setGrandTotal($this->_reCalculateToStoreCurrency($details['order-total']['total'], $qAddress->getQuote()));
        $qAddress->setBaseGrandTotal($details['order-total']['total']);
    }
    
    protected function _reCalculateToStoreCurrency($amount, $quote)
    {
        if ($quote->getQuoteCurrencyCode() != $quote->getBaseCurrencyCode()) {
            $amount = $amount * $quote->getStoreToQuoteRate();
        }

        return $amount;
    }
    
    public function getConfigData($field, $storeId = null)
    {
        if ($field == 'title') {
            return 'MultiSafepay fast checkout';
        }
    
        if (null === $storeId) {
            if ($this->_storeId !== null) {
                $storeId = $this->_storeId;
            } else {
                $storeId = $this->getStore();
            }
        }
        $path = $this->_settings . "/settings/" . $field;

        return Mage::getStoreConfig($path, $storeId);
    }
    
    public function getSectionConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            if ($this->_storeId !== null) {
                $storeId = $this->_storeId;
            } else {
                $storeId = $this->getStore();
            }
        }
        $path = $this->_settings . "/" . $field;

        return Mage::getStoreConfig($path, $storeId);
    }

    // Get shipping methods for given parameters
    // Result as an array:
    // 'name' => 'test-name'
    // 'cost' => '123'
    // 'currency' => 'EUR' (currently only this supported)
    public function getShippingMethodsFiltered($country, $countryCode, $weight = '', $size = '', $transactionId)
    {
        $out = array();

        // Pickup
        if ($this->getSectionConfigData('checkout_shipping_pickup/active')) {
            $title = $this->getSectionConfigData('checkout_shipping_pickup/title');
            $price = $this->getSectionConfigData('checkout_shipping_pickup/price');
            $price = (float) Mage::helper('tax')->getShippingPrice($price, false, false);

            $shipping = array();
            $shipping['name'] = $title;
            $shipping['cost'] = $price;
            $shipping['currency'] = 'EUR';
            $out[] = $shipping;
        }

        // Flatrate
        if ($this->getSectionConfigData('checkout_shipping_flatrate/active')) {
            // flat rate
            for ($i=1; $i<=3; $i++) {
                $allowSpecific = $this->getSectionConfigData('checkout_shipping_flatrate/sallowspecific_'.$i);
                // Only specyfic countries?
                if ($allowSpecific) {
                    $specificCountries = $this->getSectionConfigData('checkout_shipping_flatrate/specificcountry_'.$i);
                    // Is our countryCode in selected specyfic allowed countries?
                    if (stristr($specificCountries, $countryCode) == false) {
                        continue; // No - skip to another method
                    }
                }

                $title = $this->getSectionConfigData('checkout_shipping_flatrate/title_'.$i);
                $price = $this->getSectionConfigData('checkout_shipping_flatrate/price_'.$i);
                $price = number_format($price, 2, '.','');
                $price = (float) Mage::helper('tax')->getShippingPrice($price, false, false);
                
                if (empty($title) || $price <= 0) { // Do not include invalid entries
                    continue;
                }

                $shipping = array();
                $shipping['name'] = $title;
                $shipping['cost'] = $price;
                $shipping['currency'] = 'EUR';
                $out[] = $shipping;
            }
        }

        return $out;
    }

    public function getShippingRatesFiltered($transactionId, $countryCode, $zipCode, $settings)
    {
        $output = array();

        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->load($transactionId);

        /** @var $shippingAddress Mage_Sales_Model_Quote_Address */
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCountryId($countryCode);
        $shippingAddress->setPostcode($zipCode);
        $shippingAddress->setCollectShippingRates(true);

        $rates = $shippingAddress->collectShippingRates()->getGroupedAllShippingRates();

        foreach ($rates as $carrier) {
            foreach ($carrier as $rate) {
                $shipping = array();
                $shipping['id']       = $rate->getCode();
                $shipping['name']     = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                $shipping['cost']     = number_format($rate->getPrice(), 2, '.', '');
                $shipping['currency'] = $quote->getQuoteCurrencyCode();

                $output[] = $shipping;
            }
        }

        return $output;
    }

    public function getShippingMethodsFilteredXML($country, $countryCode, $weight = '', $size = '', $transactionId)
    {
        $methods = $this->getShippingMethodsFiltered($country, $countryCode, $weight, $size, $transactionId);
        
        $outxml = '<shipping-info>';
        foreach ($methods as $method) {
            $outxml .= '<shipping>';
            $outxml .= '<shipping-name>';
            $outxml .= htmlentities( $method['name'] );
            $outxml .= '</shipping-name>';
            $outxml .= '<shipping-cost currency="'.$method['currency'].'">';
            $outxml .= $method['cost'];
            $outxml .= '</shipping-cost>';
            $outxml .= '</shipping>';
        }
        $outxml .= '</shipping-info>';
        
        return $outxml;
    }

    /**
     * @param $transactionId integer
     * @param $countryCode   string
     * @param $postCode      string
     * @param $settings      array
     * @return string
     */
    public function getShippingRatesFilteredXML($transactionId, $countryCode, $zipCode, $settings)
    {
        $rates = $this->getShippingRatesFiltered($transactionId, $countryCode, $zipCode, $settings);

        $outxml = '<?xml version="1.0" encoding="UTF-8"?>';
        $outxml .= '<shipping-info>';
        foreach ($rates as $rate) {
            $outxml .= '<shipping>';
            $outxml .= '<shipping-name>' . htmlentities($rate['name']) . '</shipping-name>';
            $outxml .= '<shipping-cost currency="' . $rate['currency'] . '">' . $rate['cost'] . '</shipping-cost>';
            $outxml .= '<shipping-id>' . $rate['id'] . '</shipping-id>';
            $outxml .= '</shipping>';
        }
        $outxml .= '</shipping-info>';

        return $outxml;
    }

    /**
     * @param $array array
     * @param $key1  string
     * @param $key2  string
     * @return string
     */
    protected function _getArrayValue($array, $key1, $key2)
    {
        if (isset($array[$key1])) {
            if (isset($array[$key1][$key2])) {
                return $array[$key1][$key2];
            }
        }

        return '';
    }
}

