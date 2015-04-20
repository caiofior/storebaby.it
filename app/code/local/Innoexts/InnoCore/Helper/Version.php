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
 * Version helper
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Helper_Version extends Mage_Core_Helper_Abstract
{
    /**
     * Get the current Magento version
     *
     * @return string
     */
    public function getCurrent()
    {
        return Mage::getVersion();
    }
    /**
     * Compare versions
     * 
     * @param string $version1
     * @param string $version2
     * @param string $operator
     * @return int
     */
    protected function _compare($version1, $version2, $operator = null)
    {
        return version_compare($version1, $version2, $operator);
    }
    /**
     * Compare version to the current
     * 
     * @param string $version
     * @param string $operator
     * @return int
     */
    public function compare($version, $operator = null)
    {
        return $this->_compare($this->getCurrent(), $version, $operator);
    }
    /**
     * Check if current version is greater or equal
     * 
     * @return bool
     */
    public function isGe($version)
    {
        return $this->compare($version, '>=');
    }
    /**
     * Check if current version is less or equal
     * 
     * @return bool
     */
    public function isLe($version)
    {
        return $this->compare($version, '<=');
    }
    /**
     * Check if current version is greater
     * 
     * @return bool
     */
    public function isGt($version)
    {
        return $this->compare($version, '>');
    }
    /**
     * Check if current version is less
     * 
     * @return bool
     */
    public function isLt($version)
    {
        return $this->compare($version, '<');
    }
    /**
     * Check if current version is equal
     * 
     * @return bool
     */
    public function isEq($version)
    {
        return $this->compare($version, '==');
    }
    /**
     * Check if current version is equal or greater then 1.5.0.0 
     * 
     * @return bool
     */
    public function isGe1500()
    {
        return $this->isGe('1.5.0.0');
    }
    /**
     * Check if current version is equal or greater then 1.5.1.0 
     * 
     * @return bool
     */
    public function isGe1510()
    {
        return $this->isGe('1.5.1.0');
    }
    /**
     * Check if current version is equal or greater then 1.6.0.0 
     * 
     * @return bool
     */
    public function isGe1600()
    {
        return $this->isGe('1.6.0.0');
    }
    /**
     * Check if current version is equal or greater then 1.6.1.0 
     * 
     * @return bool
     */
    public function isGe1610()
    {
        return $this->isGe('1.6.1.0');
    }
    /**
     * Check if current version is equal or greater then 1.6.2.0 
     * 
     * @return bool
     */
    public function isGe1620()
    {
        return $this->isGe('1.6.2.0');
    }
    /**
     * Check if current version is equal or greater then 1.7.0.0 
     * 
     * @return bool
     */
    public function isGe1700()
    {
        return $this->isGe('1.7.0.0');
    }
    /**
     * Check if current version is equal or greater then 1.7.1.0 
     * 
     * @return bool
     */
    public function isGe1710()
    {
        return $this->isGe('1.7.1.0');
    }
}