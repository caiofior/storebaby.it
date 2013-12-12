<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{

		$model = Mage::registry('sharkslideshow_sharkrevslider');

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('sharkslideshow_form', array('legend' => Mage::helper('sharkslideshow')->__('Slide Settings')));

		$data = array();
		$out = '';
		if (Mage::getSingleton('adminhtml/session')->getSharkrevsliderData()) {
			$data = Mage::getSingleton('adminhtml/session')->getSharkrevsliderData();
		} elseif (Mage::registry('sharkrevslider_data')) {
			$data = Mage::registry('sharkrevslider_data')->getData();
		}
		
		if (!empty($data['image'])) {
			$url = Mage::getBaseUrl('media') . $data['image'];
			$out = '<a href="' . $url . '" target="_blank" id="imageurl">';
			$out .= "<img src=" . $url . " width='280px' />";
			$out .= '</a>';

			$fieldset->addField('label', 'label', array(
				'label' => Mage::helper('sharkslideshow')->__('Preview'),
				'after_element_html' => $out,
			));
		
		}
		
		$fieldset->addField('status', 'select', array(
			'label' => Mage::helper('sharkslideshow')->__('Status'),
			'name' => 'status',
			'values' => array(
				array(
					'value' => 1,
					'label' => Mage::helper('sharkslideshow')->__('Enabled'),
				),
				array(
					'value' => 2,
					'label' => Mage::helper('sharkslideshow')->__('Disabled'),
				),
			),
		));

		$fieldset->addField('store_id', 'multiselect', array(
			'name' => 'stores[]',
			'label' => Mage::helper('sharkslideshow')->__('Store View'),
			'title' => Mage::helper('sharkslideshow')->__('Store View'),
			'required' => true,
			'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
		));

		$fieldset->addField('sort_order', 'text', array(
			'label' => Mage::helper('sharkslideshow')->__('Sort Order'),
			'required' => false,
			'class' => 'validate-number',
			'name' => 'sort_order',
		));

		if (Mage::getSingleton('adminhtml/session')->getSharkrevsliderData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getSharkrevsliderData());
			Mage::getSingleton('adminhtml/session')->getSharkrevsliderData(null);
		} elseif (Mage::registry('sharkrevslider_data')) {
			$form->setValues(Mage::registry('sharkrevslider_data')->getData());
		}
		return parent::_prepareForm();
	}
}