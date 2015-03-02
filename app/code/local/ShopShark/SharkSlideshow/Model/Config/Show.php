<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Config_Show
{
    public function toOptionArray()
    {
	    $options = array();
	    $options[] = array(
            'value' => 'home',
            'label' => 'HomePage Only',
        );
        $options[] = array(
            'value' => 'all',
            'label' => 'All Pages',
        );

        return $options;
    }

}
