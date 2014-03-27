<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_ThemeConfig_Block_Adminhtml_System_Config_Form_Field_Color extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
	
	protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('shopshark/jquery-1.8.3.min.js');
		$this->getLayout()->getBlock('head')->addJs('shopshark/jquery.noconflict.js');
		$this->getLayout()->getBlock('head')->addJs('shopshark/jquery.mColorPicker.min.js');
		return parent::_prepareLayout();
    }
	
	/**
     * Override field method to add js
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {

        // Get the default HTML for this option
        $html = parent::_getElementHtml($element);

        if ( !Mage::registry('mColorPicker') ) {
            $html .= '
                <script type="text/javascript">
					jQuery.fn.mColorPicker.init.replace = false;
					jQuery.fn.mColorPicker.init.enhancedSwatches = false;
					jQuery.fn.mColorPicker.init.allowTransparency = true;
					jQuery.fn.mColorPicker.init.showLogo = false;
					jQuery.fn.mColorPicker.defaults.imageFolder = "'.$this->getJsUrl('shopshark/mColorPicker/').'";
                </script>
                ';
            Mage::register('mColorPicker', 1);
        }
		$html .= '
        <script type="text/javascript">
			jQuery(function($){
				$("#'.$element->getHtmlId().'").width("200px").attr("data-hex", true).mColorPicker();
			});
        </script>
        ';
        return $html;
    }
}