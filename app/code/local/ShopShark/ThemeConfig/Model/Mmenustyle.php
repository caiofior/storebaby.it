<?php class ShopShark_ThemeConfig_Model_Mmenustyle
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('ThemeConfig')->__('HTML Block')),
            array('value'=>2, 'label'=>Mage::helper('ThemeConfig')->__('Thumbnails')),
			array('value'=>3, 'label'=>Mage::helper('ThemeConfig')->__('No Additional Blocks'))            
        );
    }

}?>