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
 * @package     Innoexts_InnoCore
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grid container
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Container extends Mage_Adminhtml_Block_Widget_Grid_Container 
{
    /**
     * Header label
     * 
     * @var string
     */
    protected $_headerLabel;
    /**
     * Add Label
     * 
     * @var string
     */
    protected $_addLabel;
    /**
     * Get text helper
     * 
     * @return Varien_Object
     */
    public function getTextHelper()
    {
        return $this;
    }
    /**
     * Get header label
     * 
     * @return string
     */
    public function getHeaderLabel()
    {
        return $this->_headerLabel;
    }
    /**
     * Get add label
     * 
     * @return string
     */
    public function getAddLabel()
    {
        return $this->_addLabel;
    }
    /**
     * Get admin session
     * 
     * @return @return Mage_Admin_Model_Session
     */
    protected function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Check is allowed action
     * 
     * @param   string $action
     * @return  bool
     */
    protected function isAllowedAction($action)
    {
        return true;
    }
    /**
     * Check if save action allowed
     * 
     * @return bool
     */
    public function isSaveAllowed()
    {
        return $this->isAllowedAction('save');
    }
    /**
     * Check if delete action allowed
     * 
     * @return bool
     */
    public function isDeleteAllowed()
    {
        return $this->isAllowedAction('delete');
    }
    /**
     * Add buttons
     * 
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Container
     */
    protected function _addButtons()
    {
        if ($this->isSaveAllowed()) {
            $this->_updateButton('add', 'label', $this->getTextHelper()->__($this->getAddLabel()));
        } else {
            $this->_removeButton('add');
        }
        return $this;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_headerText = $this->getTextHelper()->__($this->getHeaderLabel());
        parent::__construct();
        $this->_addButtons();
    }
}