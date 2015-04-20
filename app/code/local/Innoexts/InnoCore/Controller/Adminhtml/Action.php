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
 * Adminhtml action
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Controller_Adminhtml_Action extends Mage_Adminhtml_Controller_Action
{
    /**
     * Model names
     * 
     * @var array
     */
    protected $_modelNames = array();
    /**
     * Retrieve admin session
     *
     * @return Mage_Admin_Model_Session
     */
    protected function getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }
    /**
     * Get model name by type
     * 
     * @param string $type
     * @return string
     */
    protected function _getModelNameByType($type)
    {
        if (isset($this->_modelNames[$type])) {
            return $this->_modelNames[$type];
        } else {
            return null;
        }
    }
    /**
     * Get model
     * 
     * @param string $type
     * @return Mage_Core_Model_Abstract
     */
    protected function _getModel($type)
    {
        return Mage::getModel($this->_getModelNameByType($type));
    }
    /**
     * Initialize model
     * 
     * @param string $type
     * @param bool $isAjax
     * @param string $idParamName
     * @param string $indexActionName
     * @param string $notFoundMessage
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initModel($type, $isAjax, $idParamName, $indexActionName, $notFoundMessage)
    {
        $model = $this->_getModel($type);
        $id = (int) $this->getRequest()->getParam($idParamName);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError($notFoundMessage);
                if (!$isAjax) $this->_redirect('*/*/'.$indexActionName);
                return;
            }
        }
        Mage::register($type, $model);
        return $this;
    }
    /**
     * Edit action
     * 
     * @param string $type
     * @param bool $isAjax
     * @param string $menu
     * @param string $idParamName
     * @param string $indexActionName
     * @param string $newMessage
     * @param string $editMessage
     * @param array $breadcrumb
     * @param string $notFoundMessage
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _editAction($type, $isAjax, $menu, $idParamName, $indexActionName, $newMessage, $editMessage, 
        $breadcrumb = array(), $notFoundMessage)
    {
        $adminhtmlSession = $this->_getSession();
        $request = $this->getRequest();
        $model = $this->_getModel($type);
        $id = $request->getParam($idParamName);
        $error = false;
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $error = true;
                $adminhtmlSession->addError($notFoundMessage);
            }
        }
        if (!$isAjax) {
            if ($error) $this->_redirect('*/*/'.$indexActionName);
            $data = $adminhtmlSession->getFormData(true);
            if (!empty($data)) $model->setData($data);
            Mage::register($type, $model);
            if (count($breadcrumb)) {
                foreach ($breadcrumb as $label) {
                    $this->_title($label);
                }
            }
            $this->_title($model->getId() ? $model->getTitle() : $newMessage);
            $title = ($model->getId()) ? $editMessage : $newMessage;
            $this->loadLayout()->_setActiveMenu($menu);
            $this->_addBreadcrumb($title, $title);
            $this->renderLayout();
        } else {
            $data = $model->getData();
            $data['title'] = $model->getTitle();
            $this->_initLayoutMessages('adminhtml/session');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'error' => (($error) ? 1 : 0), 
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(), 
                'data' => $data, 
            )));
        }
        return $this;
    }
    /**
     * Prepare save
     * 
     * @param string $type
     * @param Mage_Core_Model_Abstract $model
     * @return Innoexts_GeoPricing_Adminhtml_Catalog_ProductController
     */
    protected function _prepareSave($type, $model)
    {
        return $this;
    }
    /**
     * Save action
     * 
     * @param string $type
     * @param bool $isAjax
     * @param string $idParamName
     * @param string $indexActionName
     * @param string $editActionName
     * @param string $savedMessage
     * @param string $errorMessage
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _saveAction($type, $isAjax, $idParamName, $indexActionName, $editActionName, $savedMessage, $errorMessage)
    {
        $adminhtmlSession = $this->_getSession();
        $request = $this->getRequest();
        $model = $this->_getModel($type);
        $data = $request->getPost($type);
        $error = false;
        if (isset($data[$idParamName]) && !empty($data[$idParamName])) {
            $id = $data[$idParamName];
        } else {
            $id = null;
        }
        if ($id) {
            $model->load($id);
        }
        $model->addData($data);
        if (!$model->getId()) $model->setId(null);
        $this->_prepareSave($type, $model);
        Mage::dispatchEvent($type.'_prepare_save', array('model' => $model, 'request' => $request));
        try {
            $model->save();
            $adminhtmlSession->addSuccess($savedMessage);
            if (!$isAjax) {
                $adminhtmlSession->setFormData(false);
                if ($request->getParam('back')) {
                    $this->_redirect('*/*/'.$editActionName, array($idParamName => $model->getId(), '_current' => true));
                    return;
                }
                $this->_redirect('*/*/'.$indexActionName);
                return $this;
            }
        } catch (Mage_Core_Exception $e) {
            $error = 1;
            $adminhtmlSession->addError($e->getMessage());
        } catch (Exception $e) {
            $error = 1;
            $adminhtmlSession->addException($e, sprintf($errorMessage, $e->getMessage()));
        }
        if ($isAjax) {
            $this->_initLayoutMessages('adminhtml/session');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'error' => (($error) ? 1 : 0), 
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(), 
            )));
        } else {
            $adminhtmlSession->setFormData($data);
            $this->_redirect('*/*/'.$editActionName, array($idParamName => $id));
            return $this;
        }
        return $this;
    }
    /**
     * Delete action
     * 
     * @param string $type
     * @param bool $isAjax
     * @param string $idParamName
     * @param string $indexActionName
     * @param string $editActionName
     * @param string $notFoundMessage
     * @param string $deletedMessage
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _deleteAction($type, $isAjax, $idParamName, $indexActionName, $editActionName, $notFoundMessage, $deletedMessage)
    {
        $adminhtmlSession = $this->_getSession();
        $request = $this->getRequest();
        $model = $this->_getModel($type);
        $id = $request->getParam($idParamName);
        $error = false;
        if ($id) $model->load($id);
        if ($model->getId()) {
            try {
                $title = $model->getTitle();
                $model->delete();
                $adminhtmlSession->addSuccess($deletedMessage);
                Mage::dispatchEvent($type.'_on_delete', array('title' => $title, 'status' => 'success'));
            } catch (Exception $e) {
                $error = true;
                Mage::dispatchEvent($type.'_on_delete', array('title' => '', 'status' => 'fail'));
                $adminhtmlSession->addError($e->getMessage());
                if (!$isAjax) $this->_redirect('*/*/'.$editActionName, array($idParamName => $id));
            }
        } else {
            $error = true;
            Mage::dispatchEvent($type.'_on_delete', array('title' => '', 'status' => 'fail'));
            $adminhtmlSession->addError($notFoundMessage);
        }
        if ($isAjax) {
            $this->_initLayoutMessages('adminhtml/session');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'error' => (($error) ? 1 : 0), 
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(), 
            )));
        } else {
            $this->_redirect('*/*/'.$indexActionName);
        }
        return $this;
    }
    /**
     * Mass delete action
     * 
     * @param string $type
     * @param bool $isAjax
     * @param string $idParamName
     * @param string $indexActionName
     * @param string $selectMessage
     * @param string $deletedMessage
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _massDeleteAction($type, $isAjax, $idParamName, $indexActionName, $selectMessage, $deletedMessage)
    {
        $adminhtmlSession = $this->_getSession();
        $request = $this->getRequest();
        $error = false;
        $ids = $request->getParam($idParamName);
        if (is_array($ids) && !empty($ids)) {
            try {
                foreach ($ids as $id) {
                    $model = $this->_getModel($type);
                    $model->load($id);
                    $title = $model->getTitle();
                    $model->delete();
                    Mage::dispatchEvent($type.'_on_delete', array('title' => $title, 'status' => 'success'));
                }
                $adminhtmlSession->addSuccess(sprintf($deletedMessage, count($ids)));
            } catch (Exception $e) {
                $error = true;
                $adminhtmlSession->addError($e->getMessage());
                Mage::dispatchEvent($type.'_on_delete', array('title' => '', 'status' => 'fail'));
            }
        } else {
            $error = true;
            $adminhtmlSession->addError($selectMessage);
            Mage::dispatchEvent($type.'_on_delete', array('title' => '', 'status' => 'fail'));
        }
        if ($isAjax) {
            $this->_initLayoutMessages('adminhtml/session');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'error' => (($error) ? 1 : 0), 
                'messages' => $this->getLayout()->getMessagesBlock()->getGroupedHtml(), 
            )));
        } else {
            $this->_redirect('*/*/'.$indexActionName);
        }
        return $this;
    }
    /**
     * Grid action
     * 
     * @param string $type
     * @param bool $isAjax
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _gridAction($type, $isAjax)
    {
        if ($isAjax) {
            $this->loadLayout();
            $this->renderLayout();
        }
        return $this;
    }
    /**
     * Index action
     * 
     * @param string $type
     * @param bool $isAjax
     * @param string $menu
     * @param array $breadcrumb
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _indexAction($type, $isAjax, $menu, $breadcrumb = array())
    {
        if (!$isAjax) {
            $this->loadLayout();
            $this->_setActiveMenu($menu);
            if (count($breadcrumb)) {
                foreach ($breadcrumb as $label) {
                    $this->_title($label);
                    $this->_addBreadcrumb($label, $label);
                }
            }
            $this->renderLayout();
        }
        return $this;
    }
    /**
     * Regions action
     */
    public function regionsAction() {
        $arrRes = array();
        $countryId = $this->getRequest()->getParam('parent');
        $arrRegions = Mage::getResourceModel('directory/region_collection')->addCountryFilter($countryId)->load()->toOptionArray();
        if (!empty($arrRegions)) { foreach ($arrRegions as $region) $arrRes[] = $region; }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($arrRes));
    }
    /**
     * Export CSV action
     * 
     * @param string $fileName
     * @param string $blockType
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _exportCsvAction($fileName, $blockType)
    {
        $this->_prepareDownloadResponse(
            $fileName, 
            $this->getLayout()->createBlock($blockType)->getCsvFile()
        );
        return $this;
    }
    /**
     * Export XML action
     * 
     * @param string $fileName
     * @param string $blockType
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _exportXmlAction($fileName, $blockType)
    {
        $this->_prepareDownloadResponse(
            $fileName, 
            $this->getLayout()->createBlock($blockType)->getExcelFile()
        );
        return $this;
    }
}