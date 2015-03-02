<?php
class ShopShark_ThemeConfig_Block_Activate extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
    		
		$_script = "<script type=\"text/javascript\">
		//<![CDATA[
			function showMessage(txt, type) {
				var html = '<ul class=\"messages\"><li class=\"'+type+'-msg\"><ul><li>' + txt + '</li></ul></li></ul>';
				$('messages').update(html);
			}
			function check() {
				new Ajax.Request('".$this->getAjaxCheckUrl()."', {
					method:     'get',
					 onSuccess: function(transport){
						if (transport.responseText){
							var response = transport.responseText.evalJSON();
							showMessage(response.text, response.type);
						}
					}
				 });
			}
		//]]>
		</script>";
		return $_script.$this->getButtonHtml();

    }

    /**
     * Return ajax url for button
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return Mage::helper('adminhtml')->getUrl('themeconfig/activate/activate');
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
            'id'        => 'themeconfig_button',
            'label'     => $this->helper('adminhtml')->__('Activate the template'),
            'onclick'   => 'javascript:check(); return false;'
        ));

        return $button->toHtml();
    }
}