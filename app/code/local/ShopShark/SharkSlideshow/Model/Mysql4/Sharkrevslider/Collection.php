<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Mysql4_Sharkrevslider_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('sharkslideshow/sharkrevslider');
    }

	/**
     * Add Filter by store
     *
     * @param Mage_Core_Model_Store $store
	 * @param bool $withAdmin
	 * @return ShopShark_SharkSlideshow_Model_Mysql4_Sharkrevslider_Collection
	 */
	public function addStoreFilter($store, $withAdmin = true)
	{
		if ($store instanceof Mage_Core_Model_Store) {
			$store = array($store->getId());
		}

		$this->getSelect()->join(
			array('store_table' => $this->getTable('sharkslideshow/revolution_slides_store')),
			'main_table.slide_id = store_table.slide_id',
			array()
		)
		->where('store_table.store_id in (?)', ($withAdmin ? array(0, $store) : $store));

		return $this;
	}
}