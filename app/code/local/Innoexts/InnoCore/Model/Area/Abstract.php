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
 * Area abstract model
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Model_Area_Abstract extends Innoexts_InnoCore_Model_Abstract
{
    /**
     * Get address helper
     * 
     * @return Innoexts_InnoCore_Helper_Address
     */
    public function getAddressHelper()
    {
        return $this->getCoreHelper()->getAddressHelper();
    }
    /**
     * Filter country
     * 
     * @param mixed $value
     * @return string
     */
    public function filterCountry($country)
    {
        if ($country) {
            $country = $this->getAddressHelper()->castCountryId($country);
        }
        if ($country) {
            return $country;
        } else {
            return '0';
        }
    }
    /**
     * Get country filter
     * 
     * @return Zend_Filter
     */
    protected function getCountryFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterCountry'), 
        )));
    }
    /**
     * Filter region
     * 
     * @param mixed $value
     * @param string $countryField
     * @return string
     */
    public function filterRegion($region, $countryField)
    {
        $countryId = $this->filterCountry($this->getData($countryField));
        if ($countryId && $region) {
            $region = $this->getAddressHelper()->castRegionId($countryId, $region);
        }
        if ($region) {
            return $region;
        } else {
            return '0';
        }
    }
    /**
     * Get destination region filter
     * 
     * @param string $countryField
     * @return Zend_Filter
     */
    protected function getRegionFilter($countryField)
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterRegion'), 
            'options' => array($countryField), 
        )));
    }
    /**
     * Filter zip
     * 
     * @param mixed $value
     * @return string
     */
    public function filterZip($value)
    {
        return ($value == '' || $value == '*') ? '' : $value;
    }
    /**
     * Get zip filter
     * 
     * @return Zend_Filter
     */
    protected function getZipFilter()
    {
        return $this->getTextFilter()->appendFilter(new Zend_Filter_Callback(array(
            'callback' => array($this, 'filterZip'), 
        )));
    }
    /**
     * Get filters
     * 
     * @return array
     */
    protected function getFilters()
    {
        return array(
            'country_id'     => $this->getCountryFilter(), 
            'region_id'      => $this->getRegionFilter('country_id'), 
            'zip'            => $this->getZipFilter(), 
        );
    }
    /**
     * Get validators
     * 
     * @return array
     */
    protected function getValidators()
    {
        return array(
            'country_id'     => $this->getTextValidator(false, 0, 4), 
            'region_id'      => $this->getIntegerValidator(false, 0), 
            'zip'            => $this->getTextValidator(false, 0, 10), 
        );
    }
    /**
     * Get title
     * 
     * @return string
     */
    public function getTitle()
    {
        $addressHelper = $this->getAddressHelper();
        $title = null;
        $country = $region = null;
        if ($this->getCountryId()) $country = $addressHelper->getCountryById($this->getCountryId());
        if ($this->getRegionId()) $region = $addressHelper->getRegionById($this->getRegionId());
        $zip = $this->getZip();
        $title = implode(', ', array(
            (($region) ? $region->getName() : '*'), 
            (($zip) ? $zip : '*'), 
            (($country) ? $country->getName() : '*')
        ));
        return $title;
    }
}