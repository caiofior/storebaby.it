<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@idealiagroup.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category   IG
 * @package    IG_PostePay
 * @copyright  Copyright (c) 2012 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Riccardo Tempesta <tempesta@idealiagroup.com>
*/

class IG_PostePay_Block_Form extends Mage_Payment_Block_Form
{
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('ig_postepay/form.phtml');
	}

	public function getPaymentImageSrc()
	{
		if (file_exists(Mage::getDesign()->getSkinBaseDir().'/images/ig_postepay/logo.png'))
			return $this->getSkinUrl('images/ig_postepay/logo.png');

		if (file_exists(Mage::getDesign()->getSkinBaseDir().'/images/ig_postepay/logo.gif'))
			return $this->getSkinUrl('images/ig_postepay/logo.gif');

		return false;
	}
}