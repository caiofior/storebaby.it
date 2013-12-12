<?php class ShopShark_ThemeConfig_Model_Designvariation
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('ThemeConfig')->__('Default')),
            array('value'=>1, 'label'=>Mage::helper('ThemeConfig')->__('Flower Shop')),
			array('value'=>2, 'label'=>Mage::helper('ThemeConfig')->__('Clean Fashion')),
			array('value'=>3, 'label'=>Mage::helper('ThemeConfig')->__('Sushi')),
			array('value'=>4, 'label'=>Mage::helper('ThemeConfig')->__('Pizzeria')),
        );
    }

}?>