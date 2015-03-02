<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Slideshow extends Mage_Core_Block_Template
{

	/**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();
			
		$this->addData(array(
            'cache_lifetime'    => 86400,
            'cache_tags'        => array(ShopShark_SharkSlideshow_Model_Sharkrevslider::CACHE_TAG),
        ));
		
		$this->setData('perPageOverride', 2);
    }
	
	public function _prepareLayout()
	{
		return parent::_prepareLayout();
	}

	public function getSlideshow()
	{
		if (!$this->hasData('sharkslideshow')) {
			$this->setData('sharkslideshow', Mage::registry('sharkslideshow'));
		}
		return $this->getData('sharkslideshow');

	}

	public function getSlides()
	{
		$model = Mage::getModel('sharkslideshow/sharkrevslider');
		
		$slides = $model->getCollection()
			->addStoreFilter(Mage::app()->getStore())
			->addFieldToSelect('*')
			->addFieldToFilter('status', 1)
			->setOrder('sort_order', 'asc');
		return $slides;
	}

}