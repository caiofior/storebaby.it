<?php
class MultiSafepay_Msp_Model_Servicecost_Creditmemo_Total extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
	public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
	{
		$order = $creditmemo->getOrder();
		$amount = $order->getServicecostInvoiced() - $order->getServicecostRefunded();
		$baseamount = $order->getBaseServicecostInvoiced() - $order->getBaseServicecostRefunded();
		$taxamount = $order->getServicecostTaxInvoiced() - $order->getServicecostTaxRefunded();
		$basetaxamount = $order->getBaseServicecostTaxInvoiced() - $order->getBaseServicecostTaxRefunded();
		
		if ($baseamount > 0) {
			$creditmemo->setServicecost($amount);
			$creditmemo->setBaseServicecost($baseamount);
			$creditmemo->setServicecostTax($order->getServicecostTaxRefunded());
			$creditmemo->setBaseServicecostTax($order->getBaseServicecostTaxRefunded());
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount +  $taxamount - $order->getServicecostTax() );
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseamount +  $basetaxamount - $order->getServicecostTax());

		}
		return $this;
	}
}
