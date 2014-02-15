<?php
class ShopShark_ThemeConfig_Model_Catpicaspect extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
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
                    'label' => Mage::helper('eav')->__('1/1 Square')
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('eav')->__('2/3 Portrait')
                ),
                array(
                    'value' => 3,
                    'label' => Mage::helper('eav')->__('3/4 Portrait')
                ),
				array(
                    'value' => 4,
                    'label' => Mage::helper('eav')->__('1/2 Portrait')
                )
            );
        }
        return $this->_options;
    }
}
?>