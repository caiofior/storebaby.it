<?php class ShopShark_ThemeConfig_Model_Columncount
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'3', 'label'=>Mage::helper('ThemeConfig')->__('3 Column Grid')),
			array('value'=>'4', 'label'=>Mage::helper('ThemeConfig')->__('4 Column Grid')),
            array('value'=>'5', 'label'=>Mage::helper('ThemeConfig')->__('Mosaic')) 
        );
    }

}?>