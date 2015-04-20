<?php 
 /* Emergento.com - http://emergento.com
http://gateways.emergento.com
Vist our site for more information about this.
Associated domain: */
/* Emergento */
class Emergento_Iwbank_Block_Info extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('iwbank/info.phtml');
    }

    
    public function getMethodCode()
    {
        return $this->getInfo()->getMethodInstance()->getCode();
    }
 
    public function getCcTypeName()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }
        return (empty($ccType)) ? Mage::helper('payment')->__('N/A') : $ccType;
    } 
    
    protected function _formatCardDate($year, $month)
    {
        return sprintf('%s/%s', sprintf('%02d', $month), $year);
    }
    
    protected function _prepareSpecificInformation($transport = null)
    {    
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        // Example: Mage_Payment_Block_Info_Cc
        $data = array();

        $transport = parent::_prepareSpecificInformation($transport);
        
        $paymentconf = Mage::getStoreConfig('payment');
        $params = $paymentconf['iwbank_cc'];
        if ($params['s2s_mode'] == 1)
        {
            $info = $this->getInfo();
            $data[Mage::helper('payment')->__('Name on the Card')]          = $info->getCcOwner();
            $data[Mage::helper('payment')->__('Credit Card Type')]          = $this->getCcTypeName(); 
            $data[Mage::helper('payment')->__('Credit Card Number')]        = $info->getCcNumber();
            $data[Mage::helper('payment')->__('Expiration Date')]           = $this->_formatCardDate($info->getCcExpYear(), $info->getCcExpMonth());
        }
        
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
