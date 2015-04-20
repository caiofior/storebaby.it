<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Observer_Shipment extends MultiSafepay_Msp_Model_Observer_Abstract
{
    public $availablePaymentMethodCodes = array(
        'msp_payafter',
    );

    public function sales_order_shipment_save_after(Varien_Event_Observer $observer)
    {
        /** @var $event Varien_Event */
        $event    = $observer->getEvent();

        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $event->getShipment();

        /** @var $order Mage_Sales_Model_Order */
        $order = $shipment->getOrder();

        // use send request if enabled
        if (!Mage::getStoreConfigFlag('msp/msp_payafter/send_request_after_shipping', $order->getStoreId())) {
            return $this;
        }

        /** @var $payment Mage_Payment_Model_Method_Abstract */
        $payment = $order->getPayment()->getMethodInstance();

        // check payment method is from MultiSafepayment
        if (!in_array($payment->getCode(), $this->availablePaymentMethodCodes)) {
            return $this;
        }

        // check order's payment method  is enabled now
        if (!in_array($payment->getCode(), $this->_getAllActivePaymentMethods($order->getStoreId()))) {
            return $this;
        }

        $invoiceId  = $order->getInvoiceCollection()->getFirstItem()->getId();

        /** @var $checkout MultiSafepay_Msp_Model_Checkout */
        $checkout = Mage::getModel('msp/checkout');

        /** @var $base MultiSafepay_Msp_Model_Base */
        $base = $checkout->getBase($order->getId());

        $configPayAfter = Mage::getStoreConfig('msp/msp_payafter', $order->getStoreId());
        $configGateway  = Mage::getStoreConfig('msp/settings', $order->getStoreId());

        /** @var $api MultiSafepay_Msp_Model_Api_Shipment */
        $api = Mage::getSingleton('msp/api_shipment');

        $suffix = '';
        if ($configPayAfter['test_api_pad'] == 'test') {
            $suffix = '_test';
        }

        $api->test                      = ($configPayAfter['test_api_pad'] == 'test');
        $api->debug                     = $configGateway['debug'];

        $api->merchant['account_id']    = $configPayAfter['account_id_pad' . $suffix];
        $api->merchant['site_id']       = $configPayAfter['site_id_pad' . $suffix];
        $api->merchant['site_code']     = $configPayAfter['secure_code_pad' . $suffix];

        $api->transaction['id']         = $order->getIncrementId();
        $api->transaction['invoice_id'] = $invoiceId;
        $api->transaction['shipdate']   = date('Y-m-d H:i:s');
        $api->transaction['carrier']    = $order->getShippingDescription();

        if ($trackings = Mage::app()->getRequest()->getParam('tracking')) {
            $trackingNumbers = '';
            foreach ($trackings as $tracking) {
                $trackingNumbers .= $tracking['title'] .'|' . $tracking['number'] . ';';
            }
            $api->transaction['shipper_trace_code'] = trim($trackingNumbers, ';');
        } else {
            $api->transaction['shipper_trace_code'] = '';
        }

        $base->log("Invoice id: $invoiceId, Order id: {$order->getId()}, Transaction id: {$order->getIncrementId()}");

        // Send update XML
        $result = $api->updateTransaction();

        // Check error code
        if ($api->error) {
            $base->log("Error " . $api->error_code . ": " . $api->error);
         }

        if ($result) {
            Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('msp')->__('The order has been successfully set to shipped within your MultiSafepay transaction, the Pay After Delivery process continues.'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError($api->error_code . ' - ' . $api->error);
        }

        return $this;
    }
}