<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('sharkrevslider_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('sharkslideshow')->__('Revolution Slide Information'));
  }

  protected function _beforeToHtml()
  {	  	  
	  $this->addTab('settings_section', array(
          'label'     => Mage::helper('sharkslideshow')->__('Slide Settings'),
          'title'     => Mage::helper('sharkslideshow')->__('Slide Settings'),
          'content'   => $this->getLayout()->createBlock('sharkslideshow/adminhtml_sharkrevslider_edit_tab_settings')->toHtml(),
      ));
	  
	  $this->addTab('image_section', array(
          'label'     => Mage::helper('sharkslideshow')->__('Main Image Settings'),
          'title'     => Mage::helper('sharkslideshow')->__('Main Image Settings'),
          'content'   => $this->getLayout()->createBlock('sharkslideshow/adminhtml_sharkrevslider_edit_tab_image')->toHtml(),
      ));
	  
      $this->addTab('content_section', array(
          'label'     => Mage::helper('sharkslideshow')->__('Content And Captions'),
          'title'     => Mage::helper('sharkslideshow')->__('Content And Captions'),
          'content'   => $this->getLayout()->createBlock('sharkslideshow/adminhtml_sharkrevslider_edit_tab_content')->toHtml(),
      ));

     
      return parent::_beforeToHtml();
  }
}