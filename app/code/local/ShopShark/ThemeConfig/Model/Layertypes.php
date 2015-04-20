<?php class ShopShark_ThemeConfig_Model_Layertypes
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('ThemeConfig')->__('Collapsed')),
            array('value'=>2, 'label'=>Mage::helper('ThemeConfig')->__('Opened'))            
        );
    }

}?>