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
 * Adminhtml form
 *
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Form extends Mage_Adminhtml_Block_Widget_Form 
{
    /**
     * Form field name suffix
     * 
     * @var string
     */
    protected $_formFieldNameSuffix;
    /**
     * Form HTML identifier prefix
     * 
     * @var string
     */
    protected $_formHtmlIdPrefix;
    /**
     * Form field set identifier
     * 
     * @var string
     */
    protected $_formFieldsetId;
    /**
     * Form field set legend
     * 
     * @var string
     */
    protected $_formFieldsetLegend;
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName;
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
     * Retrieve admin session model
     *
     * @return Mage_Admin_Model_Session
     */
    protected function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Get form field name suffix
     * 
     * @return string
     */
    public function getFormFieldNameSuffix()
    {
        return $this->_formFieldNameSuffix;
    }
    /**
     * Get form html identifier prefix
     * 
     * @return string
     */
    public function getFormHtmlIdPrefix()
    {
        return $this->_formHtmlIdPrefix;
    }
    /**
     * Get form field set identifier
     * 
     * @return string
     */
    public function getFormFieldsetId()
    {
        return $this->_formFieldsetId;
    }
    /**
     * Get form field set legend
     * 
     * @return string
     */
    public function getFormFieldsetLegend()
    {
        return $this->getTextHelper()->__($this->_formFieldsetLegend);
    }
    /**
     * Get model name
     * 
     * @return string
     */
    public function getModelName()
    {
        return $this->_modelName;
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
     * Retrieve registered model
     *
     * @return Varien_Object
     */
    protected function getModel()
    {
        $model = Mage::registry($this->getModelName());
        if (!$model) $model = new Varien_Object();
        return $model;
    }
    /**
     * Get Js object name
     * 
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getId().'JsObject';
    }
    /**
     * Get fieldset
     * 
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function getFieldset()
    {
        $form = $this->getForm();
        if ($form) {
            return $form->getElement($this->getFormFieldsetId());
        } else {
            return null;
        }
    }
    /**
     * Get fields
     * 
     * @return array of Varien_Data_Form_Element_Abstract
     */
    public function getFields()
    {
        $fields = array();
        $fieldset = $this->getFieldset();
        if ($fieldset) {
            foreach ($fieldset->getElements() as $element) {
                if (!($element instanceof Varien_Data_Form_Element_Button) && !($element instanceof Varien_Data_Form)) {
                    if ($element->getData('name')) {
                        $fields[$element->getData('name')] = $element;
                    }
                }
            }
        }
        return $fields;
    }
    /**
     * Get field names
     * 
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->getFields());
    }
    /**
     * Get defaults
     * 
     * @return array
     */
    public function getDefaults()
    {
        $defaults = array();
        foreach ($this->getFields() as $name => $field) {
            $defaults[$name] = $field->getData('default');
        }
        return $defaults;
    }
    /**
     * Prepare form before rendering
     *
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        if ($this->getFormFieldNameSuffix()) {
            $form->setFieldNameSuffix($this->getFormFieldNameSuffix());
        }
        if ($this->getFormHtmlIdPrefix()) {
            $form->setHtmlIdPrefix($this->getFormHtmlIdPrefix());
        }
        $form->addFieldset($this->getFormFieldsetId(), array('legend' => $this->getFormFieldsetLegend()));
        $this->setForm($form);
        return $this;
    }
    /**
     * Dispatch prepare form event
     * 
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Form
     */
    protected function dispatchPrepareFormEvent()
    {
        Mage::dispatchEvent($this->getFormHtmlIdPrefix().'_prepare_form', array('form' => $this->getForm()));
        return $this;
    }
}