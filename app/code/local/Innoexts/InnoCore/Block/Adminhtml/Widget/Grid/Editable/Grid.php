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
 * Editable grid block
 * 
 * @category   Innoexts
 * @package    Innoexts_Warehouse
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Editable_Grid extends Innoexts_InnoCore_Block_Adminhtml_Widget_Grid 
{
    /**
     * Add button label
     * 
     * @var string
     */
    protected $_addButtonLabel;
    /**
     * Form js object name
     * 
     * @var string
     */
    protected $_formJsObjectName;
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
     * Get add button label
     * 
     * @return string
     */
    public function getAddButtonLabel()
    {
        return $this->getTextHelper()->__($this->_addButtonLabel);
    }
    /**
     * Get form js object name
     * 
     * @return string
     */
    public function getFormJsObjectName()
    {
        return $this->_formJsObjectName;
    }
    /**
     * Prepare layout
     * 
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Editable_Grid
     */
    protected function _prepareLayout()
    {
        $this->setChild('add_button', 
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => $this->getAddButtonLabel(), 
                'onclick'   => $this->getFormJsObjectName().'.doAdd()', 
                'class'     => 'task'
            ))
        );
        parent::_prepareLayout();
        return $this;
    }
    /**
     * Get main button HTML
     * 
     * @return string
     */
    public function getMainButtonsHtml()
    {
        $html = parent::getMainButtonsHtml();
        return $this->getChildHtml('add_button').$html;
    }
}