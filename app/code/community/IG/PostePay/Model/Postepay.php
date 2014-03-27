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

class IG_PostePay_Model_Postepay extends Mage_Payment_Model_Method_Abstract
{
	protected $_code						= 'ig_postepay';
	protected $_paymentMethod				= 'Postepay';
	protected $_store_config				= 'payment/ig_postepay';
	protected $_formBlockType				= 'ig_postepay/form';
	protected $_infoBlockType				= 'ig_postepay/info';

	protected $_canAuthorize				= true;
	protected $_canUseInternal				= true;
	protected $_canUseCheckout				= true;
	protected $_canUseForMultishipping		= true;

	/*protected $_isGateway					= false;
	protected $_canCapture					= false;
	protected $_canCapturePartial			= false;
	protected $_canRefund					= false;
	protected $_canVoid						= true;
	protected $_canUseInternal				= true;
	protected $_canUseCheckout				= true;
	protected $_canUseForMultishipping		= true;*/

	public function getConfig($key)
	{
		return Mage::getStoreConfig($this->_store_config.'/'.$key);
	}
}
