<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function isSlideshowEnabled()
	{
		$config = Mage::getStoreConfig('sharkslideshow', Mage::app()->getStore()->getId());
		$request = Mage::app()->getFrontController()->getRequest();
		$route = Mage::app()->getFrontController()->getRequest()->getRouteName();
		$action = Mage::app()->getFrontController()->getRequest()->getActionName();
		$show = false;
		if ($config['config']['enabled']) {
			$show = true;
			if ($config['config']['show'] == 'home') {
				$show = false;
				if ($request->getModuleName() == 'cms' && $request->getControllerName() == 'index' && $request->getActionName() == 'index') {
					$show = true;
				}
			}
			if ($show && ($route == 'customer' && ($action == 'login' || $action == 'forgotpassword' || $action == 'create'))) {
				$show = false;
			}
		}
		return $show;
	}
}