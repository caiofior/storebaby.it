<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Adminhtml_SharkrevsliderController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed('shopshark/shark/sharkslideshow');
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('shopshark/shark/sharkslideshow/revolution_items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Revolution Slide Manager'), Mage::helper('adminhtml')->__('Revolution Slide Manager'));
		
		return $this;
	}
 
	public function indexAction() {
		$this->_initAction()
			->_addContent($this->getLayout()->createBlock('sharkslideshow/adminhtml_sharkrevslider'))
			->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('sharkslideshow/sharkrevslider')->load($id);

		if ($model->getId() || $id == 0) {

			$this->_initAction();

			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('sharkrevslider_data', $model);

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('sharkslideshow/adminhtml_sharkrevslider_edit'))
				->_addLeft($this->getLayout()->createBlock('sharkslideshow/adminhtml_sharkrevslider_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sharkslideshow')->__('Slide does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != null) {
                $result['file'] = '';
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('image');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(true);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media').DS.'shopshark'.DS.'revslider'.DS ;
					$result = $uploader->save($path, $_FILES['image']['name'] );
					
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . '  '. $path);
	                Mage::getSingleton('adminhtml/session')->setFormData($data);
	                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	                return;
		        }
	        
		        $data['image'] = 'shopshark'.DS.'revslider'.DS.$result['file'];
			}

            if(isset($_FILES['thumb']['name']) && $_FILES['thumb']['name'] != null) {
                $result['file'] = '';
				try {
					/* Starting upload */
					$uploader = new Varien_File_Uploader('thumb');
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(false);
					$path = Mage::getBaseDir('media') .DS.'shopshark'.DS.'shark'.DS.'revolution'.DS ;
					$result = $uploader->save($path, $_FILES['thumb']['name'] );
				} catch (Exception $e) {
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage() . '  '. $path);
	                Mage::getSingleton('adminhtml/session')->setFormData($data);
	                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
	                return;
		        }

		        $data['thumb'] = 'shopshark'.DS.'revslider'.DS.$result['file'];
			}
	  			
	  			
			$model = Mage::getModel('sharkslideshow/sharkrevslider');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));

			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('sharkslideshow')->__('Slide was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('sharkslideshow')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('sharkslideshow/sharkrevslider');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Slide was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $sharkslideshowIds = $this->getRequest()->getParam('sharkrevslider');
        if(!is_array($sharkslideshowIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select slide(s)'));
        } else {
            try {
                foreach ($sharkslideshowIds as $sharkslideshowId) {
                    $sharkslideshow = Mage::getModel('sharkslideshow/sharkrevslider')->load($sharkslideshowId);
                    $sharkslideshow->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($sharkslideshowIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $sharkslideshowIds = $this->getRequest()->getParam('sharkrevslider');
        if(!is_array($sharkslideshowIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select slide(s)'));
        } else {
            try {
                foreach ($sharkslideshowIds as $sharkslideshowId) {
                    $sharkslideshow = Mage::getSingleton('sharkslideshow/sharkrevslider')
                        ->load($sharkslideshowId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($sharkslideshowIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}