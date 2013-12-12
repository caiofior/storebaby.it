<?php class ShopShark_ThemeConfig_Model_Attachment
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('ThemeConfig')->__('Select')),
            array('value'=>'fixed', 'label'=>Mage::helper('ThemeConfig')->__('fixed')),
            array('value'=>'scroll', 'label'=>Mage::helper('ThemeConfig')->__('scroll'))     
        );
    }

}?>