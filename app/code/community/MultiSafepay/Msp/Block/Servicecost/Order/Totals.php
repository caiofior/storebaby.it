<?php

class MultiSafepay_Msp_Block_Servicecost_Order_Totals extends Mage_Sales_Block_Order_Totals {
	protected function _initTotals() {
		parent::_initTotals();
		
		$order = $this->getSource();
		$code  =  $this->getOrder()->getPayment()->getMethod();
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