<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_InnoCore
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Area grid
 * 
 * @category   Innoexts
 * @package    Innoexts_GeoPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Area_Grid 
    extends Innoexts_InnoCore_Block_Adminhtml_Widget_Grid 
{
    /**
     * Get country options
     * 
     * @return array
     */
    protected function getCountryOptions()
    {
        $options = array();
        $countries = Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(false);
        if (isset($countries[0])) {
            $countries[0] = array('value' => '0', 'label' => '*', );
        }
        foreach ($countries as $country) { $options[$country['value']] = $country['label']; }
        return $options;
    }
    /**
     * Get child block type prefix
     * 
     * @return string
     */
    protected function getAreaChildBlockTypePrefix()
    {
        return 'innocore/adminhtml_widget_grid_area_';
    }
    /**
     * Add columns to grid
     *
     * @return Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Editable_Area_Grid
     */
    protected function _prepareColumns()
    {
        $textHelper = $this->getTextHelper();
        $this->addColumn('country_id', array(
            'header'        => $textHelper->__('Country'), 
            'align'         => 'left', 
            'index'         => 'country_id', 
            'filter_index'  => 'main_table.country_id', 
            'type'          => 'options', 
            'options'       => $this->getCountryOptions(), 
        ));
        $this->addColumn('region', array(
            'header'        => $textHelper->__('Region/State'), 
            'align'         => 'left', 
            'index'         => 'region', 
            'filter_index'  => 'region_table.code', 
            'filter'	    => $this->getAreaChildBlockTypePrefix().'column_filter_region', 
            'default'       => '*', 
        ));
        $this->addColumn('zip', array(
            'header'        => $textHelper->__('Zip/Postal Code'), 
            'align'         => 'left', 
            'index'         => 'zip', 
            'filter'	    => $this->getAreaChildBlockTypePrefix().'column_filter_zip', 
            'renderer'	    => $this->getAreaChildBlockTypePrefix().'column_renderer_zip', 
            'default'       => '*', 
        ));
        return $this;
    }
}