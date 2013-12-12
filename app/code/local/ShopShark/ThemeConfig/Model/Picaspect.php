<?php class ShopShark_ThemeConfig_Model_Picaspect
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'1', 'label'=>Mage::helper('ThemeConfig')->__('1/1 Square')),
			array('value'=>'2', 'label'=>Mage::helper('ThemeConfig')->__('2/3 Portrait')),
            array('value'=>'3', 'label'=>Mage::helper('ThemeConfig')->__('3/4 Portrait')),
			array('value'=>'4', 'label'=>Mage::helper('ThemeConfig')->__('1/2 Portrait')) 
        );
    }

}?>