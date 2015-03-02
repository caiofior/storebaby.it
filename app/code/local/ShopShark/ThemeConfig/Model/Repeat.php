<?php class ShopShark_ThemeConfig_Model_Repeat
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('ThemeConfig')->__('Select')),
            array('value'=>'no-repeat', 'label'=>Mage::helper('ThemeConfig')->__('no-repeat')),
            array('value'=>'repeat-x', 'label'=>Mage::helper('ThemeConfig')->__('repeat-x')),   
            array('value'=>'repeat-y', 'label'=>Mage::helper('ThemeConfig')->__('repeat-y'))        
        );
    }

}?>