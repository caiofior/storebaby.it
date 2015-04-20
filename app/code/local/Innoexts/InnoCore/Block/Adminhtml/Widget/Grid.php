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
 * Adminhtml grid
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid 
{
    /**
     * Object identifier
     * 
     * @var string
     */
    protected $_objectId;
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
     * Get object identifier
     * 
     * @return string
     */
    public function getObjectId()
    {
        return $this->_objectId;
    }
    /**
     * Prepare collection object
     *
     * @return Varien_Data_Collection
     */
    protected function __prepareCollection()
    {
        return null;
    }
    /**
     * Prepare collection object
     *
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->__prepareCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
    /**
     * Get row URL
     * 
     * @param   Varien_Object $row
     * @return  string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array($this->getObjectId() => $row->getId()));
    }
    /**
     * Get grid URL
     * 
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
    /**
     * Get admin session
     * 
     * @return Mage_Admin_Model_Session
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
     * Check if view action allowed
     * 
     * @return bool
     */
    public function isViewAllowed()
    {
        return $this->isAllowedAction('view');
    }
}