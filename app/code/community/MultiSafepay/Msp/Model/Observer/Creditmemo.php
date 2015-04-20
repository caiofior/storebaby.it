<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Observer_Creditmemo extends MultiSafepay_Msp_Model_Observer_Abstract
{
    const MSP_GENERAL_CODE         = 'msp';
    const MSP_FASTCHECKOUT_CODE    = 'mspcheckout';
    const MSP_GENERAL_PAD_CODE     = 'msp_payafter';
    const MSP_GATEWAYS_CODE_PREFIX = 'msp_';

    public $availablePaymentMethodCodes = array(
        'msp',
        'mspcheckout',
        'msp_ideal',
        'msp_payafter',
        'msp_mistercash',
        'msp_visa',
        'msp_mastercard',
        'msp_banktransfer',
        'msp_maestro',
        'msp_paypal',
        'msp_webgift',
        'msp_ebon',
        'msp_babygiftcard',
        'msp_boekenbon',
        'msp_erotiekbon',
        'msp_fijncadeau',
        'msp_parfumnl',
        'msp_parfumcadeaukaart',
        'msp_degrotespeelgoedwinkel',
        'msp_giropay',
        'msp_multisafepay',
        'msp_directebanking',
        'msp_directdebit',
        'msp_fastcheckout',
		'msp_amex',
    );

    public function sales_order_creditmemo_save_after(Varien_Event_Observer $observer)
    {
        /** @var $event Varien_Event */
        $event   = $observer->getEvent();

        $orderId = $event->getDataObject('order_id');

        /** @var $order Mage_Sales_Model_Order */
        $order   = Mage::getModel('sales/order')->load($orderId);

        /** @var $payment Mage_Payment_Model_Method_Abstract */
        $payment = $order->getPayment()->getMethodInstance();

        switch ($payment->getCode()) {
            // MSP - Fast Checkout
            case self::MSP_FASTCHECKOUT_CODE:
            $settingsPathPrefix     = 'mspcheckout/settings';
            break;

            // General (Main settings in the 'Payment Methods' tab
            case self::MSP_GENERAL_CODE:
            $settingsPathPrefix     = 'payment/msp';
            break;

            // MSP - Gateways (Pay After Delivery)
            case self::MSP_GENERAL_PAD_CODE:
            $settingsPathPrefix     = 'msp/' . self::MSP_GENERAL_PAD_CODE;
            break;

            // MSP - Gateways
            default:
            $settingsPathPrefix     = 'msp/settings';
            break;
        }

        // use refund by Credit Memo is enabled
        $pathCreditMemoIsEnabled = ($payment->getCode() == self::MSP_GENERAL_PAD_CODE) ? 'msp/settings' : $settingsPathPrefix;
        if (!Mage::getStoreConfigFlag($pathCreditMemoIsEnabled . '/use_refund_credit_memo', $order->getStoreId())) {
            return $this;
        }

        // check payment method is from MultiSafepayment
        if (!in_array($payment->getCode(), $this->availablePaymentMethodCodes)) {
            return $this;
        }

        // check order's payment method  is enabled now
        if (!in_array($payment->getCode(), $this->_getAllActivePaymentMethods($order->getStoreId()))) {
            return $this;
        }

        $baseGrandTotal = floatval($order->getBaseGrandTotal());

        // check is payment method PAD
        if ($payment->getCode() == self::MSP_GENERAL_PAD_CODE &&
            $event->getDataObject('grand_total') != $baseGrandTotal) {

            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('msp')->__('Impossible to do partial refund for orders that were paid by "Pay After Delivery". You can do partial refund from your MultiSafepay account by using the checkout editor'));
            return $this;
        }

        $config = Mage::getStoreConfig($settingsPathPrefix, $order->getStoreId());

        /** @var $api MultiSafepay_Msp_Model_Api_Creditmemo */
        $api = Mage::getModel('msp/api_creditmemo');

        if ($payment->getCode() == self::MSP_GENERAL_PAD_CODE) {
            $configMain = Mage::getStoreConfig('msp/settings', $order->getStoreId());
            $api->test  = ($config['test_api_pad'] == 'test');
            $suffix     = '';

            if ($api->test) {
                $suffix = '_test';
            }

            $api->merchant['account_id']    = $config['account_id_pad' . $suffix];
            $api->merchant['site_id']       = $config['site_id_pad' . $suffix];
            $api->merchant['security_code'] = $config['secure_code_pad' . $suffix];
            $api->merchant['api_key']       = $configMain['api_key'];
            $api->debug                     = $configMain['debug'];
        }
        else {
            $api->test                      = ($config['test_api'] == 'test');
            $api->merchant['account_id']    = $config['account_id'];
            $api->merchant['site_id']       = $config['site_id'];
            $api->merchant['security_code'] = $config['secure_code'];
            $api->merchant['api_key']       = $config['api_key'];
            $api->debug                     = $config['debug'];
        }

        if ($payment->getCode() == self::MSP_FASTCHECKOUT_CODE) {
            $api->transaction['id']     = $order->getQuoteId();
        } else {
            $api->transaction['id']     = $order->getIncrementId();
        }

        $api->transaction['amount']     = intval($event->getDataObject('grand_total') * 100);
        $api->transaction['currency']   = $event->getDataObject('order_currency_code');

        $response = $api->startTransaction();

        if ($response['error']) {
            Mage::getSingleton('adminhtml/session')->addError($response['code'] . ' - ' . $response['description']);
        } else {
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('msp')->__('Refund request has been sent successfully to MultiSafepay, your transaction has been refunded.'));
        }

        return $this;
    }
}