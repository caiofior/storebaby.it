<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{

		$model = Mage::registry('sharkslideshow_sharkrevslider');

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('sharkslideshow_form', array(
			'legend' => Mage::helper('sharkslideshow')->__('Revolution Slide information'),
			'class' => 'fieldset-wide'
		));

		$fieldset->addField('text', 'textarea', array(
			'label'     => Mage::helper('sharkslideshow')->__('Slide Content'),
			'required'  => false,
			'name'      => 'text'
		));
		
		$add_caption_form = 

<<<EOD
			<div>
            <table id="add_caption" cellspacing="10" cellpadding="0">
            <tr>
                <td width="150" align="right">Caption text</td>
                <td>
                    <input type="text" id="data_text" style="width:310px" />
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Color class</td>
                <td width="250">
                    <select id="color_class">
                        <option value="">- none -</option>

                        <option value="shark_large_caption_light">shark_large_caption_light</option>
                        <option value="shark_large_caption_medium">shark_large_caption_medium</option>
                        <option value="shark_large_caption_bold">shark_large_caption_bold</option>

                        <option value="shark_caption_light">shark_caption_light</option>
                        <option value="shark_caption_medium">shark_caption_medium</option>
                        <option value="shark_caption_bold">shark_caption_bold</option>

                        <option value="shark_caption_underline_light">shark_caption_underline_light</option>
                        <option value="shark_caption_underline_medium">shark_caption_underline_medium</option>
                        <option value="shark_caption_underline_bold">shark_caption_underline_bold</option>

                        <option value="shark_small_caption_light">shark_small_caption_light</option>
                        <option value="shark_small_caption_medium">shark_small_caption_medium</option>
                        <option value="shark_small_caption_bold">shark_small_caption_bold</option>

                        <option value="shark_small_text_light">shark_small_text_light</option>
                        <option value="shark_small_text_medium">shark_small_text_medium</option>
                        <option value="shark_small_text_bold">shark_small_text_bold</option>

                        <option value="shark_medium_text_light">shark_medium_text_light</option>
                        <option value="shark_medium_text_medium">shark_medium_text_medium</option>
                        <option value="shark_medium_text_bold">shark_medium_text_bold</option>

                        <option value="shark_white_bg_light">shark_white_bg_light</option>
                        <option value="shark_white_bg_medium">shark_white_bg_medium</option>
                        <option value="shark_white_bg_bold">shark_white_bg_bold</option>

                        <option value="big_white">big_white</option>
                        <option value="big_orange">big_orange</option>
                        <option value="medium_grey">medium_grey</option>
                        <option value="small_text">small_text</option>
                        <option value="medium_text">medium_text</option>
                        <option value="large_text">large_text</option>
                        <option value="very_large_text">very_large_text</option>
                        <option value="large_black_text">large_black_text</option>
                        <option value="very_large_black_text">very_large_black_text</option>
                        <option value="very_big_black">very_big_black</option>
                        <option value="big_black">big_black</option>
                        <option value="bold_red_text">bold_red_text</option>
                        <option value="bold_brown_text">bold_brown_text</option>
                        <option value="bold_green_text">bold_green_text</option>
                        <option value="very_big_white">very_big_white</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Incoming Animations</td>
                <td width="250">
                    <select id="incoming_animation">
                        <option value="randomrotate">Fade in, Rotate from a Random position and Degree</option>
                        <option value="sft">Short from Top</option>
                        <option value="sfb">Short from Bottom</option>
                        <option value="sfr">Short from Right</option>
                        <option value="sfl">Short from Left</option>
                        <option value="lft">Long from Top</option>
                        <option value="lfb">Long from Bottom</option>
                        <option value="lfr">Long from Right</option>
                        <option value="lfl">Long from Left</option>
                        <option value="fade">Fading</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Outgoing Animations</td>
                <td width="250">
                    <select id="outgoing_animation">
                        <option value="">- none -</option>
                        <option value="randomrotate">Fade in, Rotate from a Random position and Degree</option>
                        <option value="sft">Short from Top</option>
                        <option value="sfb">Short from Bottom</option>
                        <option value="sfr">Short from Right</option>
                        <option value="sfl">Short from Left</option>
                        <option value="lft">Long from Top</option>
                        <option value="lfb">Long from Bottom</option>
                        <option value="lfr">Long from Right</option>
                        <option value="lfl">Long from Left</option>
                        <option value="fade">Fading</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">X position</td>
                <td>
                    <input type="text" id="data_x" />
                    <br/> <small>horizontal position in the standard (via startwidth option defined) screen size (other screen sizes will be calculated)</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Y position</td>
                <td>
                    <input type="text" id="data_y" />
                    <br/> <small>vertical position in the standard (via startheight option defined) screen size (other screen sizes will be calculated)</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Start time</td>
                <td>
                    <input type="text" id="data_start_after" />
                    <br/> <small>how many milliseconds should this caption start to show</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Hide time</td>
                <td>
                    <input type="text" id="data_end" />
                    <br/> <small>after how many milliseconds should this caption leave the stage (should be bigger than data-start+data-speed !</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Show animation Speed</td>
                <td>
                    <input type="text" id="data_speed" />
                    <br/> <small>duration of the animation in milliseconds</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Hide animation Speed</td>
                <td>
                    <input type="text" id="data_end_speed" />
                    <br/> <small>duration of the animation when caption leaves the stage in milliseconds</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">Start Easing</td>
                <td>
                    <select id="easing">
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInQuad">easeInQuad</option>
                        <option value="easeOutQuad">easeOutQuad</option>
                        <option value="easeInOutQuad">easeInOutQuad</option>
                        <option value="easeInCubic">easeInCubic</option>
                        <option value="easeOutCubic">easeOutCubic</option>
                        <option value="easeInOutCubic">easeInOutCubic</option>
                        <option value="easeInQuart">easeInQuart</option>
                        <option value="easeOutQuart">easeOutQuart</option>
                        <option value="easeInOutQuart">easeInOutQuart</option>
                        <option value="easeInQuint">easeInQuint</option>
                        <option value="easeOutQuint">easeOutQuint</option>
                        <option value="easeInOutQuint">easeInOutQuint</option>
                        <option value="easeInSine">easeInSine</option>
                        <option value="easeOutSine">easeOutSine</option>
                        <option value="easeInOutSine">easeInOutSine</option>
                        <option value="easeInExpo">easeInExpo</option>
                        <option value="easeOutExpo">easeOutExpo</option>
                        <option value="easeInOutExpo">easeInOutExpo</option>
                        <option value="easeInCirc">easeInCirc</option>
                        <option value="easeOutCirc">easeOutCirc</option>
                        <option value="easeInOutCirc">easeInOutCirc</option>
                        <option value="easeInElastic">easeInElastic</option>
                        <option value="easeOutElastic">easeOutElastic</option>
                        <option value="easeInOutElastic">easeInOutElastic</option>
                        <option value="easeInBack">easeInBack</option>
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInOutBack">easeInOutBack</option>
                        <option value="easeInBounce">easeInBounce</option>
                        <option value="easeOutBounce">easeOutBounce</option>
                        <option value="easeInOutBounce">easeInOutBounce</option>
                    </select>
                    <br/> <small>special easing effect of the animation</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right">End Easing </td>
                <td>
                    <select id="end_easing">
                        <option value="">- none -</option>
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInQuad">easeInQuad</option>
                        <option value="easeOutQuad">easeOutQuad</option>
                        <option value="easeInOutQuad">easeInOutQuad</option>
                        <option value="easeInCubic">easeInCubic</option>
                        <option value="easeOutCubic">easeOutCubic</option>
                        <option value="easeInOutCubic">easeInOutCubic</option>
                        <option value="easeInQuart">easeInQuart</option>
                        <option value="easeOutQuart">easeOutQuart</option>
                        <option value="easeInOutQuart">easeInOutQuart</option>
                        <option value="easeInQuint">easeInQuint</option>
                        <option value="easeOutQuint">easeOutQuint</option>
                        <option value="easeInOutQuint">easeInOutQuint</option>
                        <option value="easeInSine">easeInSine</option>
                        <option value="easeOutSine">easeOutSine</option>
                        <option value="easeInOutSine">easeInOutSine</option>
                        <option value="easeInExpo">easeInExpo</option>
                        <option value="easeOutExpo">easeOutExpo</option>
                        <option value="easeInOutExpo">easeInOutExpo</option>
                        <option value="easeInCirc">easeInCirc</option>
                        <option value="easeOutCirc">easeOutCirc</option>
                        <option value="easeInOutCirc">easeInOutCirc</option>
                        <option value="easeInElastic">easeInElastic</option>
                        <option value="easeOutElastic">easeOutElastic</option>
                        <option value="easeInOutElastic">easeInOutElastic</option>
                        <option value="easeInBack">easeInBack</option>
                        <option value="easeOutBack">easeOutBack</option>
                        <option value="easeInOutBack">easeInOutBack</option>
                        <option value="easeInBounce">easeInBounce</option>
                        <option value="easeOutBounce">easeOutBounce</option>
                        <option value="easeInOutBounce">easeInOutBounce</option>
                    </select>
                    <br/><small>special easing effect of the animation</small>
                </td>
            </tr>
            <tr>
                <td width="150" align="right"></td>
                <td>
                    <input type="button" id="add_caption_action" value="Add caption" />
                </td>
            </tr>
            </table>
            </div>
EOD;
		
		$fieldset->addField('add-caption', 'note', array(
				'label' => Mage::helper('sharkslideshow')->__('Add Caption'),
				'class' => 'add-caption',
				'after_element_html' => $add_caption_form
		));
		
		/*
		$fieldset->addField('body', 'editor', array(
          'name'      => 'body',
          'label'     => Mage::helper('sharkslideshow')->__('Content'),
          'title'     => Mage::helper('sharkslideshow')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => true, 'add_widgets' => true, 'add_images' => true, 'files_browser_window_url'=>$this->getBaseUrl().'admin/cms_wysiwyg_images/index/')),
          'wysiwyg'   => false,
          'required'  => false,
      ));
	   */

		if (Mage::getSingleton('adminhtml/session')->getSharkrevsliderData()) {
			$form->setValues(Mage::getSingleton('adminhtml/session')->getSharkrevsliderData());
			Mage::getSingleton('adminhtml/session')->getSharkrevsliderData(null);
		} elseif (Mage::registry('sharkrevslider_data')) {
			$form->setValues(Mage::registry('sharkrevslider_data')->getData());
		}
		return parent::_prepareForm();
	}
}