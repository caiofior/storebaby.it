<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Manage_Cat_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
                    'id' => 'edit_form',
                    'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                    'method' => 'post',
                ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('category_form', array('legend' => Mage::helper('blog')->__('Category Information')));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('blog')->__('Title'),
            'name' => 'title',
            'required' => true
        ));

        $fieldset->addField('identifier', 'text', array(
            'label' => Mage::helper('blog')->__('Identifier'),
            'name' => 'identifier',
			'class' => 'blog-validate-category-identifier',
            'required' => true,
			'after_element_html' => '<p class="note"><span>' . Mage::helper('blog')->__('Relative to the base URL. You can also use subfolder format like that: category/subcategory/subsubcategory') . '</span></p>'.
			"<script>Validation.add('blog-validate-category-identifier', '".addslashes(Mage::helper('blog')->__("Please use only letters (a-z or A-Z), numbers (0-9) or symbols '-' '_' and '/' in this field"))."', function(v, elm){ var regex = new RegExp(/^[a-zA-Z0-9_\/-]+$/); return v.match(regex); });</script>"
        ));

        $fieldset->addField('sort_order', 'text', array(
            'label' => Mage::helper('blog')->__('Sort Order'),
            'name' => 'sort_order',
			'class' => 'validate-number'
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('cms')->__('Store View'),
                'title' => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

        $fieldset->addField('meta_keywords', 'editor', array(
            'name' => 'meta_keywords',
            'label' => Mage::helper('blog')->__('Keywords'),
            'title' => Mage::helper('blog')->__('Meta Keywords'),
        ));

        $fieldset->addField('meta_description', 'editor', array(
            'name' => 'meta_description',
            'label' => Mage::helper('blog')->__('Description'),
            'title' => Mage::helper('blog')->__('Meta Description'),
        ));

        if (Mage::getSingleton('adminhtml/session')->getBlogData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
            Mage::getSingleton('adminhtml/session')->setBlogData(null);
        } elseif (Mage::registry('blog_data')) {
            $form->setValues(Mage::registry('blog_data')->getData());
        }
        return parent::_prepareForm();
    }

}
