<?php     
class Netreviews_Avisverifies_Model_Mysql4_Average extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('avisverifies/average', 'ref_product');
        $this->_isPkAutoIncrement = false;
    }
}