<?php
/**
 *
 * @category MultiSafepay
 * @package  MultiSafepay_Msp
 * @license  http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/** @var $this MultiSafepay_Msp_Model_Setup */
$this->startSetup();

/** @var $conn Varien_Db_Adapter_Pdo_Mysql */
$conn = $this->getConnection();

$additionalColumns = array(
    $this->getTable('sales/order') => array(
        'servicecost',
        'base_servicecost',
        'servicecost_invoiced',
        'base_servicecost_invoiced',
        'servicecost_tax',
        'base_servicecost_tax',
        'servicecost_tax_invoiced',
        'base_servicecost_tax_invoiced',
        'servicecost_refunded',
        'base_servicecost_refunded',
        'servicecost_tax_refunded',
        'base_servicecost_tax_refunded',
	 'servicecost_pdf',
    ),
    $this->getTable('sales/invoice') => array(
        'servicecost',
        'base_servicecost',
        'servicecost_tax',
        'base_servicecost_tax',
	'servicecost_pdf',
    ),
    $this->getTable('sales/quote') => array(
        'servicecost',
        'base_servicecost',
        'servicecost_tax',
        'base_servicecost_tax',
	'servicecost_pdf',
    ),
    $this->getTable('sales/creditmemo') => array(
        'servicecost',
        'base_servicecost',
        'servicecost_tax',
        'base_servicecost_tax',
	'servicecost_pdf',
    ),
);

foreach ($additionalColumns as $table => $columns) {
    foreach ($columns as $column) {
        $conn->addColumn($table, $column, array(
            'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'precision' => 12,
            'scale'     => 4,
            'nullable'  => true,
            'default'   => null,
            'comment'   => ucwords(str_replace('_', ' ', $column)),
        ));
    }
}

$this->endSetup();