<?php class ShopShark_ThemeConfig_Model_Colorschema
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('ThemeConfig')->__('Violet Red')),
            array('value'=>2, 'label'=>Mage::helper('ThemeConfig')->__('Honeysuckle')),
			array('value'=>3, 'label'=>Mage::helper('ThemeConfig')->__('African Violet')),
			array('value'=>4, 'label'=>Mage::helper('ThemeConfig')->__('Chocolate Truffle')),
			array('value'=>5, 'label'=>Mage::helper('ThemeConfig')->__('Regatta')),
			array('value'=>6, 'label'=>Mage::helper('ThemeConfig')->__('Lipstick Red')),
			array('value'=>7, 'label'=>Mage::helper('ThemeConfig')->__('Peapod')),
			array('value'=>8, 'label'=>Mage::helper('ThemeConfig')->__('Titanium'))
        );
    }

}?>