<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Edit_Tab_Image extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{

		$model = Mage::registry('sharkslideshow_sharkrevslider');

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('sharkslideshow_form', array('legend' => Mage::helper('sharkslideshow')->__('Main Image And Transition Settings')));

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
		
		$fieldset->addField('image', 'file', array(
			'label' => Mage::helper('sharkslideshow')->__('Upload Image'),
			'required' => false,
			'name' => 'image',
			'note' => Mage::helper('sharkslideshow')->__('Valid extensions are .jpg, .jpeg, .gif and .png'),
		));
		
		$fieldset->addField('transition', 'select', array(
			'label' => Mage::helper('sharkslideshow')->__('Transition'),
			'name' => 'transition',
			'values' => array(
				array(
					'value' => 'boxslide',
					'label' => Mage::helper('sharkslideshow')->__('boxslide'),
				),
				array(
					'value' => 'boxfade',
					'label' => Mage::helper('sharkslideshow')->__('boxfade'),
				),
				array(
					'value' => 'slotzoom-horizontal',
					'label' => Mage::helper('sharkslideshow')->__('slotzoom-horizontal'),
				),
				array(
					'value' => 'slotslide-horizontal',
					'label' => Mage::helper('sharkslideshow')->__('slotslide-horizontal'),
				),
				array(
					'value' => 'slotfade-horizontal',
					'label' => Mage::helper('sharkslideshow')->__('slotfade-horizontal'),
				),
				array(
					'value' => 'slotzoom-vertical',
					'label' => Mage::helper('sharkslideshow')->__('slotzoom-vertical'),
				),
				array(
					'value' => 'slotslide-vertical',
					'label' => Mage::helper('sharkslideshow')->__('slotslide-vertical'),
				),
				array(
					'value' => 'slotfade-vertical',
					'label' => Mage::helper('sharkslideshow')->__('slotfade-vertical'),
				),
				array(
					'value' => 'curtain-1',
					'label' => Mage::helper('sharkslideshow')->__('curtain-1'),
				),
				array(
					'value' => 'curtain-2',
					'label' => Mage::helper('sharkslideshow')->__('curtain-2'),
				),
				array(
					'value' => 'curtain-3',
					'label' => Mage::helper('sharkslideshow')->__('curtain-3'),
				),
				array(
					'value' => 'slideleft',
					'label' => Mage::helper('sharkslideshow')->__('slideleft'),
				),
				array(
					'value' => 'slideright',
					'label' => Mage::helper('sharkslideshow')->__('slideright'),
				),
				array(
					'value' => 'slideup',
					'label' => Mage::helper('sharkslideshow')->__('slideup'),
				),
				array(
					'value' => 'slidedown',
					'label' => Mage::helper('sharkslideshow')->__('slidedown'),
				),
				array(
					'value' => 'fade',
					'label' => Mage::helper('sharkslideshow')->__('fade'),
				),
				array(
					'value' => 'random',
					'label' => Mage::helper('sharkslideshow')->__('random'),
				),
				array(
					'value' => 'slidehorizontal',
					'label' => Mage::helper('sharkslideshow')->__('slidehorizontal'),
				),
				array(
					'value' => 'slidevertical',
					'label' => Mage::helper('sharkslideshow')->__('slidevertical'),
				),
				array(
					'value' => 'papercut',
					'label' => Mage::helper('sharkslideshow')->__('papercut'),
				),
				array(
					'value' => 'flyin',
					'label' => Mage::helper('sharkslideshow')->__('flyin'),
				),
				array(
					'value' => 'turnoff',
					'label' => Mage::helper('sharkslideshow')->__('turnoff'),
				),
				array(
					'value' => 'cube',
					'label' => Mage::helper('sharkslideshow')->__('cube'),
				),
				array(
					'value' => '3dcurtain-vertical',
					'label' => Mage::helper('sharkslideshow')->__('3dcurtain-vertical'),
				),
				array(
					'value' => '3dcurtain-horizontal',
					'label' => Mage::helper('sharkslideshow')->__('3dcurtain-horizontal'),
				),
			),
			'note' => 'The appearance transition of this slide',
		));

		$fieldset->addField('masterspeed', 'text', array(
			'label' => Mage::helper('sharkslideshow')->__('Transition Speed'),
			'required' => false,
			'name' => 'masterspeed',
			'class' => 'validate-number',
			'note' => 'Set the speed of the slide transition in miliseconds. Default 300, min:100 max:2000.'
		));
		$fieldset->addField('slotamount', 'text', array(
			'label' => Mage::helper('sharkslideshow')->__('Slotamount'),
			'required' => false,
			'name' => 'slotamount',
			'class' => 'validate-number',
			'note' => 'The number of slots or boxes the slide is divided into. If you use boxfade, over 7 slots can be too slow.'
		));
		$fieldset->addField('link', 'text', array(
			'label' => Mage::helper('sharkslideshow')->__('Slide Link'),
			'required' => false,
			'name' => 'link',
		));

		$out = '';
		if (!empty($data['thumb'])) {
			$url = Mage::getBaseUrl('media') . $data['thumb'];
			$out = '<br/><center><a href="' . $url . '" target="_blank" id="imageurl">';
			$out .= "<img src=" . $url . " width='150px' />";
			$out .= '</a></center>';
		}

		$fieldset->addField('thumb', 'file', array(
			'label' => Mage::helper('sharkslideshow')->__('Slide thumb'),
			'required' => false,
			'name' => 'thumb',
			'note' => 'An Alternative Source for thumbs. If not defined a copy of the background image will be used in resized form. ' . $out,
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