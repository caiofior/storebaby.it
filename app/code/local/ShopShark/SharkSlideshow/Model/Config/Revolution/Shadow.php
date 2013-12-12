<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Config_Revolution_Shadow
{
    public function toOptionArray()
    {
	    $options = array();
        $options[] = array(
            'value' => '0',
            'label' => '0',
        );
	    $options[] = array(
            'value' => '1',
            'label' => '1',
        );
        $options[] = array(
            'value' => '2',
            'label' => '2',
        );
        $options[] = array(
            'value' => '3',
            'label' => '3',
        );

        return $options;
    }

}