<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Manage_Blog_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => Mage::helper('blog')->__('Meta Data'), 'class' => 'fieldset-wide'));

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

        $fieldset = $form->addFieldset('blog_options', array('legend' => Mage::helper('blog')->__('Advanced Post Options')));

        $fieldset->addField('user', 'text', array(
            'label' => Mage::helper('blog')->__('Poster'),
            'name' => 'user',
            'after_element_html' => '<p class="note"><span>' . Mage::helper('blog')->__('Put a custom name or leave blank for current username').'</span></p>',
        ));


        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $fieldset->addField('created_time', 'date', array(
            'name' => 'created_time',
            'label' => $this->__('Created on'),
            'title' => $this->__('Created on'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => $outputFormat,
            'time' => true,
			'after_element_html' => '<p class="note"><span>' . Mage::helper('blog')->__('Put a custom date or leave blank for current date').'</span></p>',
        ));



        if (Mage::getSingleton('adminhtml/session')->getBlogData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
            Mage::getSingleton('adminhtml/session')->setBlogData(null);
        } elseif ($data = Mage::registry('blog_data')) {

            $form->setValues(Mage::registry('blog_data')->getData());

            if ($data->getData('created_time')) {
                $form->getElement('created_time')->setValue(
                        Mage::app()->getLocale()->date($data->getData('created_time'), Varien_Date::DATETIME_INTERNAL_FORMAT)
                );
            }
        }

        return parent::_prepareForm();
    }

}
