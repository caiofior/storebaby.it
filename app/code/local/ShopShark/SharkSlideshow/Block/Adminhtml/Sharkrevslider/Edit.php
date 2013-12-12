<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'sharkslideshow';
        $this->_controller = 'adminhtml_sharkrevslider';
        
        $this->_updateButton('save', 'label', Mage::helper('sharkslideshow')->__('Save Slide'));
        $this->_updateButton('delete', 'label', Mage::helper('sharkslideshow')->__('Delete Slide'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $script =
<<<EOD
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }

			jQuery('#add_caption_action').click(function(){
			
				jQuery(function($){			
					var css_class = 'caption';
					css_class += ' ' + $('#incoming_animation').val();
					if ( $('#outgoing_animation').val() != '' ) css_class += ' ' + $('#outgoing_animation').val();
					if ( $('#color_class').val() != '' ) css_class += ' ' + $('#color_class').val();
	
					var params = '';
					if ( $('#data_x').val() != '' ) params += ' data-x="' + $('#data_x').val() + '"';
					if ( $('#data_y').val() != '' ) params += ' data-y="' + $('#data_y').val() + '"';
					if ( $('#data_speed').val() != '' ) params += ' data-speed="' + $('#data_speed').val() + '"';
					if ( $('#data_start_after').val() != '' ) params += ' data-start="' + $('#data_start_after').val() + '"';
					if ( $('#easing').val() != '' ) params += ' data-easing="' + $('#easing').val() + '"';
					if ( $('#data_end_speed').val() != '' ) params += ' data-endspeed="' + $('#data_end_speed').val() + '"';
					if ( $('#data_end').val() != '' ) params += ' data-end="' + $('#data_end').val() + '"';
					if ( $('#end_easing').val() != '' ) params += ' data-endeasing="' + $('#end_easing').val() + '"';
	
					$('#text').val( $('#text').val() + "\r\n\r\n" + '<div class="'+ css_class +'" '+params+'>'+ $('#data_text').val() +'</div>' );
				});	
			});
EOD;

        $this->_formScripts[] = str_replace(array("\r\n", "\r", "\n"), "", $script );
    }
	
	protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->addJs('shopshark/jquery-1.8.3.min.js');
		$this->getLayout()->getBlock('head')->addJs('shopshark/jquery.noconflict.js');
		return parent::_prepareLayout();
    }

    public function getHeaderText()
    {
        if( Mage::registry('sharkrevslider_data') && Mage::registry('sharkrevslider_data')->getId() ) {
            return Mage::helper('sharkslideshow')->__("Edit Slide");
        } else {
            return Mage::helper('sharkslideshow')->__('Add Slide');
        }
    }
}