<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(Mage::getBaseDir('lib').DS.'multisafepay'.DS.'MultiSafepay.combined.php');

class MultiSafepay_Msp_Model_Api_Paylink
{
    public $test = false;
    public $custom_api;
    public $extrapublics = '';
    public $use_shipping_xml;

    public $use_shipping_notification = false;

    // merchant data
    public $merchant = array(
        'account_id'    => '',
        'site_id'       => '',
        'api_key'       => '',
        'security_code' => '',
    );

    // transaction data
    public $transaction = array(
        'id'         => '',
        'currency'   => '',
        'amount'     => '',
    );

    public $signature;

    public $api_url;
    public $request_xml;
    public $reply_xml;
    public $payment_url;
    public $status;
    public $error_code;
    public $error;

    public $debug;

    public $parsed_xml;
    public $parsed_root;

    protected $_logFileName  = 'msp_paylink.log';

    /**
     * Starts a transaction and returns the payment url
     *
     * @return string
     */
    public function getPaymentLink()
    {
        $this->log('Request payment link for manual order');
		
	 return array('error' => false);
	/*

        $this->checkSettings();

        $this->createSignature();

        // create request
        $this->request_xml = $this->createTransactionRequest();

        // post request and get reply
        $this->api_url   = $this->getApiUrl();
        $this->reply_xml = $this->xmlPost($this->api_url, $this->request_xml);

        $this->log($this->api_url);
        $this->log($this->request_xml);
        $this->log($this->reply_xml);

        // communication error
        if (!$this->reply_xml) {
            return array(
                'error'       => true,
                'code'        => $this->error_code,
                'description' => $this->error
            );
        }

        // parse xml
        $rootNode = $this->parseXmlResponse($this->reply_xml);

        if ($this->error) {
            $this->log("Error %s: %s", $this->error_code, $this->error);
        }

        if (!$rootNode) {
            return array(
                'error'       => true,
                'code'        => $this->error_code,
                'description' => $this->error
            );
        }

        return array('error' => false);
*/
    }

    /**
     * Check the settings before using them
     *
     * @return void
     */
    public function checkSettings()
    {
        $this->merchant['account_id']    = trim($this->merchant['account_id']);
        $this->merchant['site_id']       = trim($this->merchant['site_id']);
        $this->merchant['api_key']       = trim($this->merchant['api_key']);
        $this->merchant['security_code'] = trim($this->merchant['security_code']);
    }

    /**
     * Creates the signature
     *
     * @return void
     */
    public function createSignature()
    {
        $this->signature = sha1(
            $this->merchant['site_id'] .
            $this->merchant['security_code'] .
            $this->transaction['id']
        );
    }

    /**
     * Returns the api url
     *
     * @return string
     */
    public function getApiUrl()
    {
        if ($this->custom_api) {
            return $this->custom_api;
        }

        if ($this->test) {
            return "https://testapi.multisafepay.com/ewx/";
        } else {
            return "https://api.multisafepay.com/ewx/";
        }
    }

    /**
     * Create the transaction request xml
     *
     * @return string
     */
    public function createTransactionRequest()
    {
        $request = '<?xml version="1.0" encoding="UTF-8"?>
    <refundtransaction ua="refund">
        <merchant>
            <account>' .   $this->xmlEscape($this->merchant['account_id']) . '</account>
            <site_id>' .   $this->xmlEscape($this->merchant['site_id']) . '</site_id>
            <api_key>' .   $this->xmlEscape($this->merchant['api_key']) . '</api_key>
            <signature>' . $this->xmlEscape($this->signature) . '</signature>
        </merchant>
        <transaction>
            <id>' .        $this->xmlEscape($this->transaction['id']) . '</id>
            <amount>' .    $this->xmlEscape($this->transaction['amount']) . '</amount>
            <currency>' .  $this->xmlEscape($this->transaction['currency']) . '</currency>
        </transaction>
    </refundtransaction>';

        return $request;
    }

    /**
     * Post the supplied XML data and return the reply
     *
     * @param $url
     * @param $request_xml
     * @param bool $verify_peer
     * @return bool|string
     */
    public function xmlPost($url, $request_xml, $verify_peer = false)
    {
        $curl_available = extension_loaded("curl");

        // generate request
        $header = array();

        if (!$curl_available) {
            $url = parse_url($url);

            if (empty($url['port'])) {
                $url['port'] = $url['scheme'] == "https" ? 443 : 80;
            }

            $header[] = "POST " . $url['path'] . "?" . $url['query'] . " HTTP/1.1";
            $header[] = "Host: " . $url['host'] . ":" . $url['port'];
            $header[] = "Content-Length: " . strlen($request_xml);
        }

        $header[] = "Content-Type: text/xml";
        $header[] = "Connection: close";

        // issue request
        if ($curl_available) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST,           true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS,     $request_xml);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT,        120);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verify_peer);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_MAXREDIRS,      5);
            curl_setopt($ch, CURLOPT_HEADER,         true);
            //curl_setopt($ch, CURLOPT_HEADER_OUT,     true);

            $reply_data = curl_exec($ch);
        } else {
            $request_data  = implode("\r\n", $header);
            $request_data .= "\r\n\r\n";
            $request_data .= $request_xml;
            $reply_data    = "";

            $errno  = 0;
            $errstr = "";

            $fp = fsockopen(($url['scheme'] == "https" ? "ssl://" : "") . $url['host'], $url['port'], $errno, $errstr, 30);

            if ($fp) {
                if (function_exists("stream_context_set_params")) {
                    stream_context_set_params($fp, array(
                        'ssl' => array(
                            'verify_peer'       => $verify_peer,
                            'allow_self_signed' => $verify_peer
                        )
                    ));
                }

                fwrite($fp, $request_data);
                fflush($fp);

                while (!feof($fp)) {
                    $reply_data .= fread($fp, 1024);
                }

                fclose($fp);
            }
        }

        // check response
        if ($curl_available) {
            if (curl_errno($ch)) {
                $this->error_code = -1;
                $this->error      = "curl error: " . curl_errno($ch);
                return false;
            }

            $reply_info = curl_getinfo($ch);
            curl_close($ch);
        } else {
            if ($errno) {
                $this->error_code = -1;
                $this->error      = "connection error: " . $errno;
                return false;
            }

            $header_size  = strpos($reply_data, "\r\n\r\n");
            $header_data  = substr($reply_data, 0, $header_size);
            $header       = explode("\r\n", $header_data);
            $status_line  = explode(" ", $header[0]);
            $content_type = "application/octet-stream";

            foreach ($header as $header_line) {
                $header_parts = explode(":", $header_line);

                if (strtolower($header_parts[0]) == "content-type") {
                    $content_type = trim($header_parts[1]);
                    break;
                }
            }

            $reply_info = array(
                'http_code'    => (int) $status_line[1],
                'content_type' => $content_type,
                'header_size'  => $header_size + 4
            );
        }

        if ($reply_info['http_code'] != 200) {
            $this->error_code = -1;
            $this->error      = "http error: " . $reply_info['http_code'];
            return false;
        }

        if (strstr($reply_info['content_type'], "/xml") === false) {
            $this->error_code = -1;
            $this->error      = "content type error: " . $reply_info['content_type'];
            return false;
        }

        // split header and body
        $reply_header = substr($reply_data, 0, $reply_info['header_size'] - 4);
        $reply_xml    = substr($reply_data, $reply_info['header_size']);

        if (empty($reply_xml)){
            $this->error_code = -1;
            $this->error      = "received empty response";
            return false;
        }

        return $reply_xml;
    }

    /**
     * Parse an xml response
     *
     * @param $response
     * @return bool
     */
    public function parseXmlResponse($response)
    {
        // strip xml line
        $response = preg_replace('#</\?xml[^>]*>#is', '', $response);

        // parse
        $parser = new msp_gc_xmlparser($response);
        $this->parsed_xml = $parser->GetData();
        $this->parsed_root = $parser->GetRoot();
        $rootNode = $this->parsed_xml[$this->parsed_root];

        // check if valid response?

        // check for error
        $result = $this->parsed_xml[$this->parsed_root]['result'];
        if ($result != "ok") {
            $this->error_code = $rootNode['error']['code']['VALUE'];
            $this->error      = $rootNode['error']['description']['VALUE'];

            return false;
        }

        return $rootNode;
    }

    /**
     * Returns the string escaped for use in XML documents
     *
     * @param $str string
     * @return string
     */
    public function xmlEscape($str)
    {
        return htmlspecialchars($str,ENT_COMPAT, "UTF-8");
    }

    /**
     * Returns the string with all XML escaping removed
     *
     * @param $str string
     * @return string
     */
    public function xmlUnescape($str)
    {
        return html_entity_decode($str,ENT_COMPAT, "UTF-8");
    }

    /**
     * Logging functions
     *
     * @return mixed
     */
    public function isDebug()
    {
        return $this->getConfigData('debug');
    }

    /**
     * @return void
     */
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

        if ($this->debug) {
            Mage::log($logData, null, $this->_logFileName);
        }
    }

}