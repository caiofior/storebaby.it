<?php

class MultiSafepay_Msp_Block_Adminhtml_Servicecost_Totals_Invoice extends Mage_Adminhtml_Block_Sales_Order_Invoice_Totals {

	protected function _initTotals() {
		parent::_initTotals();
		
		$source = $this->getSource();
		$amount = $source->getOrder()->getServicecostPdf();
		$method = $source->getOrder()->getPayment()->getMethodInstance();
		
		 $code  =  $source->getOrder()->getPayment()->getMethod();

		if ($amount) {
			$this->addTotalBefore(new Varien_Object(array(
				'code'      => 'servicecost',
				'value'     => $amount,
				'base_value'=> $amount,
				'label'     => Mage::helper('msp')->getFeeLabel($code)
				), array('tax'))
			);
		}

		return $this;
	}

}