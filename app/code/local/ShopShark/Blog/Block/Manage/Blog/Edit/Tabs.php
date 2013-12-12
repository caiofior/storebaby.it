<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Manage_Blog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('blog_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('blog')->__('Post Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('blog')->__('Post Information'),
            'title' => Mage::helper('blog')->__('Post Information'),
            'content' => $this->getLayout()->createBlock('blog/manage_blog_edit_tab_form')->toHtml(),
        ));
		
		$this->addTab('content_section', array(
            'label' => Mage::helper('blog')->__('Content'),
            'title' => Mage::helper('blog')->__('Content'),
            'content' => $this->getLayout()->createBlock('blog/manage_blog_edit_tab_content')->toHtml(),
        ));

        $this->addTab('options_section', array(
            'label' => Mage::helper('blog')->__('Advanced Options'),
            'title' => Mage::helper('blog')->__('Advanced Options'),
            'content' => $this->getLayout()->createBlock('blog/manage_blog_edit_tab_options')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
