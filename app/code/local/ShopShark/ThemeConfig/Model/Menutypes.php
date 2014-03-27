<?php class ShopShark_ThemeConfig_Model_Menutypes
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('ThemeConfig')->__('MegaMenu')),
            array('value'=>2, 'label'=>Mage::helper('ThemeConfig')->__('Standard'))            
        );
    }

}?>