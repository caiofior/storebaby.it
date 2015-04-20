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
 * Form container
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Form_Container extends Mage_Adminhtml_Block_Widget_Form_Container 
{
    /**
     * Block sub group
     * 
     * @var string
     */
    protected $_blockSubGroup;
    /**
     * Add Label
     * 
     * @var string
     */
    protected $_addLabel;
    /**
     * Edit label
     * 
     * @var string
     */
    protected $_editLabel;
    /**
     * Save label
     * 
     * @var string
     */
    protected $_saveLabel;
    /**
     * Save and continue label
     * 
     * @var string
     */
    protected $_saveAndContinueLabel;
    /**
     * Delete label
     * 
     * @var string
     */
    protected $_deleteLabel;
    /**
     * Save and continue enabled
     * 
     * @var bool
     */
    protected $_saveAndContinueEnabled;
    /**
     * Tab enabled
     * 
     * @var bool
     */
    protected $_tabEnabled;
    /**
     * Tabs block type
     * 
     * @var string
     */
    protected $_tabsBlockType;
    /**
     * Tabs block identifier
     * 
     * @var string
     */
    protected $_tabsBlockId;
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
     * Get add label
     * 
     * @return string
     */
    public function getAddLabel()
    {
        return $this->_addLabel;
    }
    /**
     * Get edit label
     * 
     * @return string
     */
    public function getEditLabel()
    {
        return $this->_editLabel;
    }
    /**
     * Get save label
     * 
     * @return string
     */
    public function getSaveLabel()
    {
        return $this->_saveLabel;
    }
    /**
     * Get save and continue label
     * 
     * @return string
     */
    public function getSaveAndContinueLabel()
    {
        return $this->_saveAndContinueLabel;
    }
    /**
     * Get delete label
     * 
     * @return string
     */
    public function getDeleteLabel()
    {
        return $this->_deleteLabel;
    }
    /**
     * Check if save and continue is enabled
     * 
     * @return bool
     */
    public function isSaveAndContinueEnabled()
    {
        return $this->_saveAndContinueEnabled;
    }
    /**
     * Check if tab is enabled
     * 
     * @return bool
     */
    public function isTabEnabled()
    {
        return $this->_tabEnabled;
    }
    /**
     * Get tabs block type
     * 
     * @return string
     */
    public function getTabsBlockType()
    {
        return $this->_tabsBlockType;
    }
    /**
     * Get tabs block identifier
     * 
     * @return string
     */
    public function getTabsBlockId()
    {
        return $this->_tabsBlockId;
    }
    /**
     * Get tabs block
     * 
     * @return Mage_Adminhtml_Block_Widget_Tabs
     */
    public function getTabsBlock()
    {
        return $this->getLayout()->getBlock($this->getTabsBlockType());
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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_addButtons();
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
     * Get Save and continue URL
     * 
     * @return  string
     */
    protected function getSaveAndContinueUrl() {
        $params = array('_current' => true, 'back' => 'edit');
        if ($this->isTabEnabled()) $params['active_tab'] = '{{tab_id}}';
        return $this->getUrl('*/*/save', $params);
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
     * Get header text
     * 
     * @return  string
     */
    public function getHeaderText()
    {
        $textHelper = $this->getTextHelper();
        $model = $this->getModel();
        if ($model && $model->getId()) {
            return $textHelper->__($this->getEditLabel(), $this->htmlEscape($model->getTitle()));
        } else {
            return $textHelper->__($this->getAddLabel());
        }
    }
    /**
     * Add buttons
     * 
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Form_Container
     */
    protected function _addButtons()
    {
        if ($this->isSaveAllowed()) {
            $this->_updateButton('save', 'label', $this->getSaveLabel());
            if ($this->isSaveAndContinueEnabled()) {
                $this->_addButton('saveandcontinue', array(
                    'label'     => $this->getSaveAndContinueLabel(), 
                    'onclick'   => 'saveAndContinueEdit(\''.$this->getSaveAndContinueUrl().'\')', 
                    'class'     => 'save', 
                ), -100);
            }
        } else {
            $this->_removeButton('save');
        }
        if ($this->isDeleteAllowed()) {
            $this->_updateButton('delete', 'label', $this->getDeleteLabel());
        } else {
            $this->_removeButton('delete');
        }
    }
    /**
     * Preparing block layout
     * 
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Form_Container
     */
    protected function __prepareLayout()
    {
        if ($this->isSaveAndContinueEnabled()) {
            $tabsBlock = $this->getTabsBlock();
            if ($tabsBlock) {
                $tabsBlockJsObject = $tabsBlock->getJsObjectName();
                $tabsBlockPrefix = $tabsBlock->getId().'_';
            } else {
                $tabsBlockJsObject = $this->getTabsBlockId(). 'JsTabs';
                $tabsBlockPrefix = $this->getTabsBlockId().'_';
            }
            $this->_formScripts[] = <<<END
function saveAndContinueEdit(urlTemplate) {
    var tabsIdValue = {$tabsBlockJsObject}.activeTab.id;
    var tabsBlockPrefix = '{$tabsBlockPrefix}';
    if (tabsIdValue.startsWith(tabsBlockPrefix)) {
        tabsIdValue = tabsIdValue.substr(tabsBlockPrefix.length);
    }
    var template = new Template(urlTemplate, /(^|.|\\r|\\n)({{(\w+)}})/);
    var url = template.evaluate({tab_id:tabsIdValue});
    editForm.submit(url);
}
END;
        }
        return $this;
    }
    /**
     * Get form block type
     * 
     * @return string
     */
    protected function getFormBlockType()
    {
        return $this->_blockGroup.'/'.(($this->_blockSubGroup) ? $this->_blockSubGroup.'_':'').$this->_controller.'_'.$this->_mode.'_form';
    }
    /**
     * Preparing block layout
     * 
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Form_Container
     */
    protected function _prepareLayout()
    {
        $this->__prepareLayout();
        if ($this->_blockGroup && $this->_controller && $this->_mode) {
            $this->setChild('form', $this->getLayout()->createBlock($this->getFormBlockType()));
        }
        foreach ($this->_buttons as $level => $buttons) {
            foreach ($buttons as $id => $data) {
                $childId = $this->_prepareButtonBlockId($id);
                $this->_addButtonChildBlock($childId);
            }
        }
        return $this;
    }
}