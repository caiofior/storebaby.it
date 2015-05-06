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
 * Database helper
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Helper_Database extends Mage_Core_Helper_Abstract 
{
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
     * Replace unique key
     * 
     * @param Mage_Core_Model_Resource_Setup $setup
     * @param string $tableName
     * @param string $keyName
     * @param array $keyAttributes
     * @return Innoexts_InnoCore_Helper_Database
     */
    public function replaceUniqueKey($setup, $tableName, $keyName, $keyAttributes)
    {
        $connection         = $setup->getConnection();
        $versionHelper      = $this->getVersionHelper();
        $table              = $setup->getTable($tableName);
        if ($versionHelper->isGe1600()) {
            $indexTypeUnique    = Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE;
            $indexes            = $connection->getIndexList($table);
            foreach ($indexes as $index) {
                if ($index['INDEX_TYPE'] == $indexTypeUnique) {
                    $connection->dropIndex($table, $index['KEY_NAME']);
                }
            }
            $keyName = $setup->getIdxName($tableName, $keyAttributes, $indexTypeUnique);
            $connection->addIndex($table, $keyName, $keyAttributes, $indexTypeUnique);
        } else {
            $connection->addKey($table, $keyName, $keyAttributes, 'unique');
        }
        return $this;
    }
    /**
     * Get table
     * 
     * @param string $entityName
     * @return string 
     */
    public function getTable($entityName)
    {
        return Mage::getSingleton('core/resource')->getTableName($entityName);
    }
}