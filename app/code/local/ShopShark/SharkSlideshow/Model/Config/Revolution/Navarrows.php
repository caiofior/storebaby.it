<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Config_Revolution_Navarrows
{
    public function toOptionArray()
    {
	    $options = array();
        $options[] = array(
            'value' => 'none',
            'label' => 'none',
        );
        $options[] = array(
            'value' => 'verticalcentered',
            'label' => 'verticalcentered',
        );

        return $options;
    }

}
