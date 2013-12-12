<?php class ShopShark_ThemeConfig_Model_Positiony
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('ThemeConfig')->__('Select')),
            array('value'=>'top', 'label'=>Mage::helper('ThemeConfig')->__('top')),
            array('value'=>'center', 'label'=>Mage::helper('ThemeConfig')->__('center')), 
            array('value'=>'bottom', 'label'=>Mage::helper('ThemeConfig')->__('bottom'))     
        );
    }

}?>