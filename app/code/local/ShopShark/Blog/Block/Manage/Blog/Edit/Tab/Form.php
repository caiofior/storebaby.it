<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Manage_Blog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => Mage::helper('blog')->__('Post information')));

        $fieldset->addField('title', 'text', array(
            'label' => Mage::helper('blog')->__('Post Title'),
            'required' => true,
            'name' => 'title'
        ));

        $fieldset->addField('identifier', 'text', array(
            'label' => Mage::helper('blog')->__('URL Key'),
            'class' => 'blog-validate-post-identifier',
            'required' => true,
            'name' => 'identifier',
            'after_element_html' => '<p class="note"><span>' . Mage::helper('blog')->__('Relative to the base blog URL') . '</span></p>'.
			"<script>Validation.add('blog-validate-post-identifier', '".addslashes(Mage::helper('blog')->__("Please use only letters (a-z or A-Z), numbers (0-9) or symbols '-' and '_' in this field"))."', function(v, elm){ var regex = new RegExp(/^[a-zA-Z0-9_-]+$/); return v.match(regex); });</script>")
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('cms')->__('Store View'),
                'title' => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true)
            ));
        }

        $categories = array();
        $collection = Mage::getModel('blog/cat')->getCollection()->setOrder('sort_order', 'asc');
        foreach ($collection as $cat) {
            $categories[] = ( array(
                'label' => (string) $cat->getTitle(),
                'value' => $cat->getCatId()
                    ));
        }

        $fieldset->addField('cat_id', 'multiselect', array(
            'name' => 'cats[]',
            'label' => Mage::helper('blog')->__('Category'),
            'title' => Mage::helper('blog')->__('Category'),
            'required' => true,
            'style' => 'height:100px',
            'values' => $categories,
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('blog')->__('Status'),
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('blog')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('blog')->__('Disabled'),
                ),
                array(
                    'value' => 3,
                    'label' => Mage::helper('blog')->__('Hidden'),
                ),
            ),
            'after_element_html' => '<p class="note"><span>' . Mage::helper('blog')->__('Hidden posts will not show in the blog but can be accessed directly') . '</span></p>'
        ));

        $fieldset->addField('comments', 'select', array(
            'label' => Mage::helper('blog')->__('Enable Comments'),
            'name' => 'comments',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => Mage::helper('blog')->__('Enabled'),
                ),
                array(
                    'value' => 1,
                    'label' => Mage::helper('blog')->__('Disabled'),
                ),
            )
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
