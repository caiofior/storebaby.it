<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */

/* Emergento */
class Emergento_Iwbank_Model_Config extends Mage_Payment_Model_Config
{
    public function getTransactionModes()
    {
        $modes = array();
        foreach (Mage::getConfig()->getNode('global/payment/transaction/modes')->asArray() as $data) {
            $modes[$data['code']] = $data['name'];
        }
        return $modes;
    }
    
    public function getPlatformModes()
    {
        $modes = array();
        foreach (Mage::getConfig()->getNode('global/payment/platform/modes')->asArray() as $data) {
            $modes[$data['code']] = $data['name'];
        }
        return $modes;
    }
    public function getTerminalId()
    {
        return Mage::getStoreConfig('payment/iwbank_cc/terminal_id');
    }
       public function getCcTypes($mode='normal')
    {
        $_types = Mage::getConfig()->getNode('global/payment_iwbank/cc/types')->asArray();
        uasort($_types, array('Emergento_Iwbank_Model_Config', 'compareCcTypes'));
        $types = array();
        switch ($mode) {
            case 'normal':
                foreach ($_types as $data) {
                    if (isset($data['code']) && isset($data['name'])) {
                        $types[$data['code']] = $data['name'];
                    }
                }
                break;
            case 'full':
                foreach ($_types as $data) {
                    if (isset($data['code']) && isset($data['name'])) {
                        $types[$data['code']] = array('name' => $data['name'],'frontcode' => $data['frontcode']);
                    }
                }
                break;
            default:
                # code...
                break;
        }
        return $types;
    }
}
