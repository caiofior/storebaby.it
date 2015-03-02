<?php
require_once 'Mage/Checkout/controllers/CartController.php';
class ShopShark_Ajax_IndexController extends Mage_Checkout_CartController
{
	public function addAction()
	{
		$cart   = $this->_getCart();
		$params = $this->getRequest()->getParams();
		if($params['isAjax'] == 1){
			$response = array();
			try {
				if (isset($params['qty'])) {
					$filter = new Zend_Filter_LocalizedToNormalized(
					array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$params['qty'] = $filter->filter($params['qty']);
				}

				$product = $this->_initProduct();
				$related = $this->getRequest()->getParam('related_product');

				/**
				 * Check product availability
				 */
				if (!$product) {
					$response['status'] = 'ERROR';
					$response['message'] = $this->__('Unable to find Product ID');
				}

				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}

				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);

				/**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
				array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);

				if (!$cart->getQuote()->getHasError()){
					$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName()));
					$response['status'] = 'SUCCESS';
					$response['message'] = $message;
					//New Code Here
					$this->loadLayout();
					Mage::register('referrer_url', $this->_getRefererUrl());
					$toplink = $this->getLayout()->getBlock('top_cart')->toHtml();
					$sidebar_block = $this->getLayout()->getBlock('cart_sidebar');
                    if($sidebar_block){
    					$sidebar = $sidebar_block->toHtml();
    					$response['sidebar'] = $sidebar;
                    }
                    if($toplink){
					   $response['topcart'] = $toplink;
                    }
				}
			} catch (Mage_Core_Exception $e) {
				$msg = "";
				if ($this->_getSession()->getUseNotice(true)) {
					$msg = $e->getMessage();
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$msg .= $message.'<br/>';
					}
				}

				$response['status'] = 'ERROR';
				$response['message'] = $msg;
			} catch (Exception $e) {
				$response['status'] = 'ERROR';
				$response['message'] = $this->__('Cannot add the item to shopping cart.');
				Mage::logException($e);
			}
			$this->getResponse()->setBody(str_replace(array('<','>','&','\'','\\"'),array('\u003C','\u003E','\u0026','\u0027','\u0022'),json_encode($response)));
			return;
		}else{
			return parent::addAction();
		}
	}
	public function optionsAction(){
		$productId = $this->getRequest()->getParam('product_id');
		// Prepare helper and params
		$viewHelper = Mage::helper('catalog/product_view');

		$params = new Varien_Object();
		$params->setCategoryId(false);
		$params->setSpecifyOptions(false);

		// Render page
		try {
			$viewHelper->prepareAndRender($productId, $this, $params);
		} catch (Exception $e) {
			if ($e->getCode() == $viewHelper->ERR_NO_PRODUCT_LOADED) {
				if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
					$this->_redirect('');
				} elseif (!$this->getResponse()->isRedirect()) {
					$this->_forward('noRoute');
				}
			} else {
				Mage::logException($e);
				$this->_forward('noRoute');
			}
		}
	}  
    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)
                  ->save();
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Cannot remove the item.'));
                Mage::logException($e);
            }
        }
        
        $string = $this->_getRefererUrl();
        $key = 'ajax';
        $cnt = preg_match_all("#\\b(".$key.")\\b#usi", $string, $pocket);
        

        
        if($cnt){
            $product_id = Mage::getSingleton('checkout/session')->getLastAddedProductId(true);
            
            $_product = Mage::getModel('catalog/product')->load($product_id);
            if(@$_product->getStatus()==1){
               $_categories = $_product->getCategoryIds();
               $_category = Mage::getModel('catalog/category')->load($_categories[0]);
               $url = $_category->getUrl();
                $this->_redirectUrl($url); 
             }else{
                $this->_redirectUrl(Mage::getBaseUrl()); 
             }   
        }else{
            $this->_redirectReferer(Mage::getUrl('*/*'));
        }
    }
	
	public function compareAction(){
		$response = array();

		if ($productId = (int) $this->getRequest()->getParam('product')) {
			$product = Mage::getModel('catalog/product')
			->setStoreId(Mage::app()->getStore()->getId())
			->load($productId);

			if ($product->getId()/* && !$product->isSuper()*/) {
				Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
				$response['status'] = 'SUCCESS';
				$response['message'] = $this->__('The product %s has been added to comparison list.', Mage::helper('core')->escapeHtml($product->getName()));
				Mage::register('referrer_url', $this->_getRefererUrl());
				Mage::helper('catalog/product_compare')->calculate();
				Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
				$this->loadLayout();
				$sidebar_block = $this->getLayout()->getBlock('catalog.compare.sidebar');
				$sidebar_block->setTemplate('catalog/product/compare/sidebar.phtml');
				$sidebar = $sidebar_block->toHtml();
				$response['sidebar'] = $sidebar;
                //$top_block = $this->getLayout()->getBlock('catalog.compare.top');
                //$top_block->setTemplate('catalog/product/compare/top.phtml');
				//$top = $top_block->toHtml();
				//$response['top_block'] = $top;
			}
		}
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody( (string) $this->getRequest()->getParam('callback') . '(' . Mage::helper('core')->jsonEncode($response) . ')' );
		return;
	}

	protected function _getWishlist()
	{
		$wishlist = Mage::registry('wishlist');
		if ($wishlist) {
			return $wishlist;
		}

		try {
			$wishlist = Mage::getModel('wishlist/wishlist')
			->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
			Mage::register('wishlist', $wishlist);
		} catch (Mage_Core_Exception $e) {
			Mage::getSingleton('wishlist/session')->addError($e->getMessage());
		} catch (Exception $e) {
			Mage::getSingleton('wishlist/session')->addException($e,
			Mage::helper('wishlist')->__('Cannot create wishlist.')
			);
			return false;
		}

		return $wishlist;
	}
	
	public function wishlistAction()
	{

		$response = array();
		if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
			$response['status'] = 'ERROR';
			$response['message'] = $this->__('Wishlist Has Been Disabled By Admin');
		}
		if(!Mage::getSingleton('customer/session')->isLoggedIn()){
			$response['status'] = 'ERROR';
			$response['message'] = $this->__('Please Login First');
		}

		if(empty($response)){
			$session = Mage::getSingleton('customer/session');
			$wishlist = $this->_getWishlist();
			if (!$wishlist) {
				$response['status'] = 'ERROR';
				$response['message'] = $this->__('Unable to Create Wishlist');
			}else{

				$productId = (int) $this->getRequest()->getParam('product');
				if (!$productId) {
					$response['status'] = 'ERROR';
					$response['message'] = $this->__('Product Not Found');
				}else{

					$product = Mage::getModel('catalog/product')->load($productId);
					if (!$product->getId() || !$product->isVisibleInCatalog()) {
						$response['status'] = 'ERROR';
						$response['message'] = $this->__('Cannot specify product.');
					}else{

						try {
							$requestParams = $this->getRequest()->getParams();
							$buyRequest = new Varien_Object($requestParams);

							$result = $wishlist->addNewItem($product, $buyRequest);
							if (is_string($result)) {
								Mage::throwException($result);
							}
							$wishlist->save();

							Mage::dispatchEvent(
                				'wishlist_add_product',
							array(
			                    'wishlist'  => $wishlist,
			                    'product'   => $product,
			                    'item'      => $result
							)
							);

							Mage::helper('wishlist')->calculate();

							$message = $this->__('%1$s has been added to your wishlist.', $product->getName());
							$response['status'] = 'SUCCESS';
							$response['message'] = $message;

							Mage::unregister('wishlist');

							$this->loadLayout();
							$toplink = $this->getLayout()->getBlock('top.links')->toHtml();
							$sidebar_block = $this->getLayout()->getBlock('wishlist_sidebar');
							$sidebar = $sidebar_block->toHtml();
							$response['toplink'] = $toplink;
							$response['sidebar'] = $sidebar;
						}
						catch (Mage_Core_Exception $e) {
							$response['status'] = 'ERROR';
							$response['message'] = $this->__('An error occurred while adding item to wishlist: %s', $e->getMessage());
						}
						catch (Exception $e) {
							mage::log($e->getMessage());
							$response['status'] = 'ERROR';
							$response['message'] = $this->__('An error occurred while adding item to wishlist.');
						}
					}
				}
			}

		}

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody( (string) $this->getRequest()->getParam('callback') . '(' . Mage::helper('core')->jsonEncode($response) . ')' );
		return;
	}
}