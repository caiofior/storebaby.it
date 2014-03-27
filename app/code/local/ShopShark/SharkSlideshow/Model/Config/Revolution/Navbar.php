<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Config_Revolution_Navbar
{
    public function toOptionArray()
    {
	    $options = array();
        $options[] = array(
            'value' => 'none',
            'label' => 'none',
        );
	    $options[] = array(
            'value' => 'bullet',
            'label' => 'bullet',
        );
        $options[] = array(
            'value' => 'thumb',
            'label' => 'thumb',
        );
        $options[] = array(
            'value' => 'both',
            'label' => 'both',
        );

        return $options;
    }

}
