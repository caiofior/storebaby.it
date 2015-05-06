<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_StorePricing
 * @copyright   Copyright (c) 2013 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog rule main tab
 * 
 * @category   Innoexts
 * @package    Innoexts_StorePricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_StorePricing_Block_Adminhtml_Promo_Catalog_Edit_Tab_Main 
    extends Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Main 
{
    /**
     * Get store pricing helper
     * 
     * @return Innoexts_StorePricing_Helper_Data
     */
    protected function getStorePricingHelper()
    {
        return Mage::helper('storepricing');
    }
    /**
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getStorePricingHelper()->getVersionHelper();
    }
    /**
     * Get catalog rule
     * 
     * @return Mage_CatalogRule_Model_Rule
     */
    protected function getCatalogRule()
    {
        return Mage::registry('current_promo_catalog_rule');
    }
    /**
     * Get store values
     * 
     * @return array
     */
    protected function getStoreValues()
    {
        if ($this->getVersionHelper()->isGe1700()) {
            return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm();
        } else {
            return Mage::getSingleton('adminhtml/system_config_source_store')->toOptionArray();
        }
    }
    /**
     * Get store renderer
     * 
     * @return Innoexts_StorePricing_Block_Adminhtml_Store_Switcher_Form_Renderer_Fieldset_Element
     */
    protected function getStoreRenderer()
    {
        return $this->getLayout()->createBlock('storepricing/adminhtml_store_switcher_form_renderer_fieldset_element');
    }
    /**
     * Prepare form
     * 
     * @return Mage_Adminhtml_Block_Promo_Catalog_Edit_Tab_Main
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper         = $this->getStorePricingHelper();
        $catalogRule    = $this->getCatalogRule();
        $isReadonly     = $catalogRule->isReadonly();
        $form           = $this->getForm();
        if ($form) {
            $fieldset       = $form->getElement('base_fieldset');
            if ($fieldset) {
                if ($helper->isSingleStoreMode()) {
                    $storeId        = $helper->getStoreById(true)->getId();
                    $fieldset->addField('store_ids', 'hidden', array(
                        'name'          => 'store_ids[]', 
                        'value'         => $storeId, 
                        'readonly'      => $isReadonly, 
                        'disabled'      => $isReadonly, 
                    ), 'website_ids');
                    $catalogRule->setStoreIds($storeId);
                } else {
                    $field = $fieldset->addField('store_ids', 'multiselect', array(
                        'name'          => 'store_ids[]', 
                        'label'         => $helper->__('Stores'), 
                        'title'         => $helper->__('Stores'), 
                        'required'      => true, 
                        'value'         => $catalogRule->getStoreIds(), 
                        'values'        => $this->getStoreValues(), 
                        'readonly'      => $isReadonly, 
                        'disabled'      => $isReadonly, 
                    ), 'website_ids');
                    $field->setRenderer($this->getStoreRenderer());
                }
            }
        }
        return $this;
    }
}