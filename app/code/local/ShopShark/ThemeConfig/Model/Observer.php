<?php
class ShopShark_ThemeConfig_Model_Observer
{
    /**
     * Adds custom layout handles based on theme configuration
	 *
	 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
	 * @copyright Copyright (C) 2010 - 2013 ShopShark
     * Event: controller_action_layout_load_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function addThemeConfigHandles(Varien_Event_Observer $observer)
    {
        $category = Mage::registry('current_category');
 
        /**
         * Return if it is not category page
         */
        if (!($category instanceof Mage_Catalog_Model_Category)) {
            return;
        }
 
 		if($category->getSingleColumnLayout() == 1) {
        //$attributeSet = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId());
         
        	/* @var $update Mage_Core_Model_Layout_Update */
        	$update = $observer->getEvent()->getLayout()->getUpdate();
        	$update->addHandle('catalog_category_single_column');
		}
    }
}
?>