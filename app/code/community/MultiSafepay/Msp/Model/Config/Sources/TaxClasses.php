<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class MultiSafepay_Msp_Model_Config_Sources_TaxClasses
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = Mage::getModel('tax/class')->getCollection()
            ->distinct(true)
            ->addFieldToFilter('class_type', array('like' => 'PRODUCT'))
            ->load();
        
        $classes = $collection->getColumnValues('class_id');
        
        $optionArray     = array();
        $optionArray[''] = array(
            'value' => '',
            'label' => Mage::helper('msp')->__('None')
        );
        foreach ($classes as $class) {
            if (empty($class)) {
                continue;
            }
            $optionArray[$class] = array(
                'value' => $class,
                'label' => Mage::getModel('tax/class')->load($class)->getClassName()
            );
        }
       
        return $optionArray;
    }
}