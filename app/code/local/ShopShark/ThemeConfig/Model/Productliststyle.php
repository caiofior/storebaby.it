<?php class ShopShark_ThemeConfig_Model_Productliststyle
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('ThemeConfig')->__('Normal')),
			array('value'=>'1', 'label'=>Mage::helper('ThemeConfig')->__('Boxed')),
        );
    }

}?>