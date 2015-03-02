<?php
class MultiSafepay_Msp_Model_Servicecost_Observer{
	public function invoiceSaveAfter(Varien_Event_Observer $observer)
	{
	$invoice = $observer->getEvent()->getInvoice();
		if ($invoice->getServicecost()) {
			$order = $invoice->getOrder();
			$order->setServicecostInvoiced($invoice->getServicecost());
			$order->setBaseServicecostInvoiced($invoice->getBaseServicecost());
			$order->setServicecostTaxInvoiced($invoice->getServicecostTax());
			$order->setBaseServicecostTaxInvoiced($invoice->getBaseServicecostTax());
		}
		return $this;
	}
	public function creditmemoSaveAfter(Varien_Event_Observer $observer)
	{
		$creditmemo = $observer->getEvent()->getCreditmemo();
		if ($creditmemo->getServicecost()) {
			$order = $creditmemo->getOrder();
			$order->setServicecostRefunded($creditmemo->getServicecost());
			$order->setBaseServicecostRefunded($creditmemo->getBaseServicecost());
			$order->setServicecostTaxRefunded($creditmemo->getServicecostTax());
			$order->setBaseServicecostTaxRefunded($creditmemo->getBaseServicecostTax());
		}
		return $this;
	}
}