<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Model_Config_Revolution_Navbullets
{
    public function toOptionArray()
    {
	    $options = array();
        $options[] = array(
            'value' => 'round',
            'label' => 'round',
        );
	    $options[] = array(
            'value' => 'navbar',
            'label' => 'navbar',
        );
        $options[] = array(
            'value' => 'round-old',
            'label' => 'round-old',
        );
        $options[] = array(
            'value' => 'square-old',
            'label' => 'square-old',
        );
        $options[] = array(
            'value' => 'navbar-old',
            'label' => 'navbar-old',
        );

        return $options;
    }

}