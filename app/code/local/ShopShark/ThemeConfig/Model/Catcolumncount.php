<?php
class ShopShark_ThemeConfig_Model_Catcolumncount extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'value' => 3,
                    'label' => Mage::helper('eav')->__('3 Column Grid')
                ),
                array(
                    'value' => 4,
                    'label' => Mage::helper('eav')->__('4 Column Grid')
                ),
                array(
                    'value' => 5,
                    'label' => Mage::helper('eav')->__('Mosaic')
                )
            );
        }
        return $this->_options;
    }
}
?>