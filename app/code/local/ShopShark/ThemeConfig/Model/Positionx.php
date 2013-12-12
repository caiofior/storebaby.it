<?php class ShopShark_ThemeConfig_Model_Positionx
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('ThemeConfig')->__('Select')),
            array('value'=>'left', 'label'=>Mage::helper('ThemeConfig')->__('left')),
            array('value'=>'center', 'label'=>Mage::helper('ThemeConfig')->__('center')), 
            array('value'=>'right', 'label'=>Mage::helper('ThemeConfig')->__('right'))     
        );
    }

}?>