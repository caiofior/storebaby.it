<?php

class MultiSafepay_Msp_Block_Adminhtml_Servicecost_Totals_Order extends Mage_Adminhtml_Block_Sales_Order_Totals {
	protected function _initTotals() {
	parent::_initTotals();
		
	$order = $this->getSource();
	 $code  =  $order ->getPayment()->getMethod();
	$amount = $order->getServicecostPdf();
	$method = $order->getPayment()->getMethodInstance();
	if ($amount) {	
			$this->addTotalBefore(new Varien_Object(array(
			'code'      => 'servicecost',
			'value'     => $amount,
			'base_value'=> $amount,
			'label'     =>  Mage::helper('msp')->getFeeLabel($code)
			), array('tax'))
		);
	
	}
	return $this;
	}
}