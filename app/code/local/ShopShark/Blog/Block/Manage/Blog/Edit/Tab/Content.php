<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Manage_Blog_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('blog_form', array('legend' => Mage::helper('blog')->__('Post content'), 'class' => 'fieldset-wide'));

		$fieldset->addField('tags', 'text', array(
            'name' => 'tags',
            'label' => Mage::helper('blog')->__('Tags'),
            'title' => Mage::helper('blog')->__('tags'),
			'after_element_html' => '<p class="note"><span>' . Mage::helper('blog')->__('Use comma as separator') . '</span></p>'
        ));

		$fieldset->addField('post_image', 'image', array(
			'name' => 'post_image',
			'label' => Mage::helper('blog')->__('Post Image')
        ));

        try {
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $config->setData(Mage::helper('blog')->recursiveReplace(
                            '/blog_admin/', '/' . (string) Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName') . '/', $config->getData()
                    )
            );
        } catch (Exception $ex) {
            $config = null;
        }

        if (Mage::getStoreConfig('blog/blog/useshortcontent')) {
            $fieldset->addField('short_content', 'editor', array(
                'name' => 'short_content',
                'label' => Mage::helper('blog')->__('Short Content'),
                'title' => Mage::helper('blog')->__('Short Content'),
                'config' => $config,
            ));
        }
        $fieldset->addField('post_content', 'editor', array(
            'name' => 'post_content',
            'label' => Mage::helper('blog')->__('Content'),
            'title' => Mage::helper('blog')->__('Content'),
            'config' => $config
        ));

        if (Mage::getSingleton('adminhtml/session')->getBlogData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBlogData());
            Mage::getSingleton('adminhtml/session')->setBlogData(null);
        } elseif (Mage::registry('blog_data')) {
            Mage::registry('blog_data')->setTags(Mage::helper('blog')->convertSlashes(Mage::registry('blog_data')->getTags()));
            $form->setValues(Mage::registry('blog_data')->getData());
        }
        return parent::_prepareForm();
    }

}
