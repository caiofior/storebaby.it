<?php class ShopShark_ThemeConfig_Model_Productpages
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'default', 'label'=>Mage::helper('ThemeConfig')->__('Default')),
            array('value'=>'horizontal', 'label'=>Mage::helper('ThemeConfig')->__('Horizontal')),
            array('value'=>'vertical', 'label'=>Mage::helper('ThemeConfig')->__('Vertical'))   
        );
    }

}?>