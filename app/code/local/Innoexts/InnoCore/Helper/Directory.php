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
 * Directory helper
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Helper_Directory extends Mage_Directory_Helper_Data 
{
    /**
     * Json representation of regions data
     *
     * @var string
     */
    protected $_regionJson2;
    /**
     * Get core helper
     * 
     * @return Innoexts_InnoCore_Helper_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('innocore');
    }
    /**
     * Get version helper
     * 
     * @return Innoexts_InnoCore_Helper_Version
     */
    public function getVersionHelper()
    {
        return $this->getCoreHelper()->getVersionHelper();
    }
    /**
     * Get regions data json
     *
     * @return string
     */
    public function getRegionJson2()
    {
        if (!$this->_regionJson2) {
            $cacheKey = 'INNOCORE_DIRECTORY_REGIONS_JSON_STORE'.Mage::app()->getStore()->getId();
            if (Mage::app()->useCache('config')) {
                $json = Mage::app()->loadCache($cacheKey);
            }
            if (empty($json)) {
                $countryIds = array();
                foreach ($this->getCountryCollection() as $country) {
                    $countryIds[] = $country->getCountryId();
                }
                $collection = Mage::getModel('directory/region')->getResourceCollection()
                    ->addCountryFilter($countryIds)
                    ->load();
                $regions = array(
                    'config' => array(
                        'show_all_regions' => true,
                        'regions_required' => Mage::helper('core')->jsonEncode(array()), 
                    )
                );
                foreach ($collection as $region) {
                    if (!$region->getRegionId()) {
                        continue;
                    }
                    $regions[$region->getCountryId()][$region->getRegionId()] = array(
                        'code' => $region->getCode(),
                        'name' => $this->__($region->getName())
                    );
                }
                $json = Mage::helper('core')->jsonEncode($regions);
                if (Mage::app()->useCache('config')) {
                    Mage::app()->saveCache($json, $cacheKey, array('config'));
                }
            }
            $this->_regionJson2 = $json;
        }
        return $this->_regionJson2;
    }
}
