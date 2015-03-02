<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_MspPaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Notification for the 'old' module, just call the notification for the other controller
     */
    public function notificationAction()
    {
        $controllerFile = dirname(__FILE__) . '/StandardController.php';
        include_once($controllerFile);

        $standard = new MultiSafepay_Msp_StandardController($this->getRequest(), $this->getResponse());
        $standard->setGatewayModel('gateway_standard');
        $standard->notificationAction();
    }
}
