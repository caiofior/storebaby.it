<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(Mage::getBaseDir('lib').DS.'multisafepay'.DS.'MultiSafepay.combined.php');

class MultiSafepay_Msp_Model_Api_Shipment extends MultiSafepay
{
    /**
     * Send update transaction
     */
    function updateTransaction()
    {
        $this->checkSettings();

        // generate request
        $this->request_xml = $this->createUpdateTransactionRequest();

        // post request and get reply
        $this->api_url = $this->getApiUrl();

        $this->reply_xml = $this->xmlPost($this->api_url, $this->request_xml);

        $this->log($this->request_xml);
        $this->log($this->api_url);
        $this->log($this->reply_xml);

        // communication error
        if (!$this->reply_xml)
            return false;

        // parse xml
        $rootNode = $this->parseXmlResponse($this->reply_xml);

        if (!$rootNode)
            return false;

        // parse all the order details
        $details = $this->processStatusReply($rootNode);
        $this->details = $details;

        $this->log($this->details);

        return true;
    }

    /**
     * Create the update transaction request xml
     */
    function createUpdateTransactionRequest()
    {
        $request = '<?xml version="1.0" encoding="UTF-8"?>
    <updatetransaction>
        <merchant>
            <account>' .          $this->xmlEscape($this->merchant['account_id'])    . '</account>
            <site_id>' .          $this->xmlEscape($this->merchant['site_id'])       . '</site_id>
            <site_secure_code>' . $this->xmlEscape($this->merchant['site_code'])     . '</site_secure_code>
        </merchant>
        <transaction>
            <id>' .               $this->xmlEscape($this->transaction['id'])         . '</id>
            <invoiceid>' .        $this->xmlEscape($this->transaction['invoice_id']) . '</invoiceid>
            <shipdate>' .         $this->xmlEscape($this->transaction['shipdate'])   . '</shipdate>';

        if ($this->xmlEscape($this->transaction['shipper_trace_code'])) {
            $request .= '
            <tracktracecode>' .   $this->xmlEscape($this->transaction['shipper_trace_code'])   . '</tracktracecode>';
        }

        if ($this->xmlEscape($this->transaction['carrier'])) {
            $request .= '
            <carrier>' .          $this->xmlEscape($this->transaction['carrier'])   . '</carrier>';
        }

        $request .= '
        </transaction>
    </updatetransaction>';

        return $request;
    }

    public function log()
    {
        $argv = func_get_args();
        $data = array_shift($argv);

        if (is_string($data)) {
            $logData = @vsprintf($data, $argv);

            // if vsprintf failed, just use the data
            if (!$logData) {
                $logData = $data;
            }
        } else {
            $logData = $data;
        }

        Mage::log($logData, null, 'msp_pdashipping');
    }

}