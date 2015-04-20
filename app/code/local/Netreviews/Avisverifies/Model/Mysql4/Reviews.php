<?php     
class Netreviews_Avisverifies_Model_Mysql4_Reviews extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {   
        $this->_init('avisverifies/reviews', 'id_product_av');
        $this->_isPkAutoIncrement = false;
    }
}