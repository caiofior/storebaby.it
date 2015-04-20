<?php
class ShopShark_ThemeConfig_Model_Catmmenustyle extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('eav')->__('Use Config Settings')
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('eav')->__('HTML Block')
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('eav')->__('Thumbnails')
                ),
                array(
                    'value' => 3,
                    'label' => Mage::helper('eav')->__('No Additional Blocks')
                )
            );
        }
        return $this->_options;
    }
}
?>