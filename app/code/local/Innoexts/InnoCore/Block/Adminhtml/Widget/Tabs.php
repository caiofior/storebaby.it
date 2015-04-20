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
 * Adminhtml tabs
 *
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Tabs extends Mage_Adminhtml_Block_Widget_Tabs 
{
    /**
     * Model name
     * 
     * @var string
     */
    protected $_modelName;
    /**
     * Child block type prefix
     * 
     * @var string
     */
    protected $_childBlockTypePrefix;
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
     * Get text helper
     * 
     * @return Varien_Object
     */
    public function getTextHelper()
    {
        return $this;
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
     * Translate html content
     * 
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }
    /**
     * Get block content
     * 
     * @param string $block
     * @return string
     */
    protected function _getBlockContent($block)
    {
        return $this->_translateHtml($this->getLayout()->createBlock($block)->toHtml());
    }
    /**
     * Get child block type prefix
     * 
     * @return string
     */
    protected function getChildBlockTypePrefix()
    {
        return $this->_childBlockTypePrefix;
    }
    /**
     * Get child block content
     * 
     * @param string $name
     * @return string
     */
    protected function getChildBlockContent($name)
    {
        return $this->_getBlockContent($this->getChildBlockTypePrefix().$name);
    }
}