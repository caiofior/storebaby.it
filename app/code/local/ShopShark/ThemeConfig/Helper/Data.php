<?php 
class ShopShark_ThemeConfig_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getMenuCss() {
		$_menutype = Mage::getStoreConfig('milanoconfig/menuoptions/menutype');
		if($_menutype == 1) return "css/menu1.css";
		else return "css/menu2.css";
	}
	
	public function getMenuJs() {
		$_menutype = Mage::getStoreConfig('milanoconfig/menuoptions/menutype');
		if($_menutype == 1) return "shopshark/jquery.megamenu.js";
		else return "shopshark/jquery.menu.js";
	}
	
	public function getColorSchemaCss() {
		$_color_schema = Mage::getStoreConfig('milanoconfig/generaloptions/color_schema');
		if($_color_schema == 1) return "css/color1.css";
		if($_color_schema == 2) return "css/color2.css";
		if($_color_schema == 3) return "css/color3.css";
		if($_color_schema == 4) return "css/color4.css";
		if($_color_schema == 5) return "css/color5.css";
		if($_color_schema == 6) return "css/color6.css";
		if($_color_schema == 7) return "css/color7.css";
		if($_color_schema == 8) return "css/color8.css";
		return "css/color1.css";
	}
	
	public function getDesignVariationCss() {
		$_design_variation = Mage::getStoreConfig('milanoconfig/generaloptions/design_variation');
		if($_design_variation == 1) return "css/design1.css";
		if($_design_variation == 2) return "css/design2.css";
		if($_design_variation == 3) return "css/design3.css";
		if($_design_variation == 4) return "css/design4.css";
		if($_design_variation == 5) return "css/design5.css";
		return false;
	}
}
?>