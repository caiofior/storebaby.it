<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Manage_Comment extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'manage_comment';
        $this->_blockGroup = 'blog';
        $this->_headerText = Mage::helper('blog')->__('Blog Comment Manager');
        parent::__construct();
        $this->setTemplate('blog/comments.phtml');
    }

    protected function _prepareLayout() {
        $this->_removeButton('add');
        return parent::_prepareLayout();
    }

}
