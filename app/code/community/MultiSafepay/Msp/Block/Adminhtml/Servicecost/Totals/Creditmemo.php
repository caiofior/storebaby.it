<?php

class MultiSafepay_Msp_Block_Adminhtml_Servicecost_Totals_Creditmemo extends Mage_Adminhtml_Block_Sales_Order_Creditmemo_Totals {
		protected function _initTotals() {
		parent::_initTotals();
		$order = $this->getSource()->getOrder();
		$amount = $order->getServicecostPdf();
		$tax = $order->getServicecostTax();
		
		 $code  =  $order->getPayment()->getMethod();

		$method = $order->getPayment()->getMethodInstance();
		
		if ($amount) {
			$this->addTotalBefore(new Varien_Object(array(
				'code'      => 'servicecost',
				'value'     => $amount,
				'base_value'=> $amount,
				'label'     =>  Mage::helper('msp')->getFeeLabel($code)
				), array('tax'))
			);
		

			$creditmemo = $this->getCreditMemo();
			$creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $tax);
			$creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $tax);
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $amount + $tax);
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount + $tax);
		}
		return $this;
	}

}