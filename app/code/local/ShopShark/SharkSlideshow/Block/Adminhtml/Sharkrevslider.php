<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_sharkrevslider';
		$this->_blockGroup = 'sharkslideshow';
		$this->_headerText = Mage::helper('sharkslideshow')->__('Slideshow Items');
		$this->_addButtonLabel = Mage::helper('sharkslideshow')->__('Add Slide');
		parent::__construct();
	}
}