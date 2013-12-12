<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Model_Cat extends Mage_Core_Model_Abstract {
    const NOROUTE_PAGE_ID = 'no-route';

    protected function _construct() {
        $this->_init('blog/cat');
    }

    public function load($id, $field=null) {
        return parent::load($id, $field);
    }

    public function noRoutePage() {
        $this->setData($this->load(self::NOROUTE_PAGE_ID, $this->getIdFieldName()));
        return $this;
    }

    public function getShortContent() {
        $content = $this->getData('short_content');
        if (Mage::getStoreConfig(ShopShark_Blog_Helper_Config::XML_BLOG_PARSE_CMS)) {
            $processor = Mage::getModel('core/email_template_filter');
            $content = $processor->filter($content);
        }
        return $content;
    }

    public function getPostContent() {
        $content = $this->getData('post_content');
        if (Mage::getStoreConfig(ShopShark_Blog_Helper_Config::XML_BLOG_PARSE_CMS)) {
            $processor = Mage::getModel('core/email_template_filter');
            $content = $processor->filter($content);
        }
        return $content;
    }
	
	public function getPostImage($width = false, $height = false) {
        		
		$img = $this->getData('post_image');
		
		if(empty($img)) return false;
		
		$imgDir = dirname($img);
		$imgFile = basename($img);		
		
		if(!$width) return $img;
		
		//if(!$height) $height = $width;
		
		$imageUrl = Mage::getBaseDir('media').DS.$img;
        if (!is_file($imageUrl) )
              return false;

        $imageResized = Mage::getBaseDir('media').DS.$imgDir."/resized/".$width."x".$height.DS.$imgFile;
        if(file_exists($imageResized)) return $imgDir."/resized/".$width."x".$height.DS.$imgFile;

        $imageObj = new Varien_Image($imageUrl);
        $imageObj->constrainOnly(TRUE);
        $imageObj->keepAspectRatio(TRUE);
        $imageObj->keepFrame(FALSE);
        $imageObj->quality(100);
        $imageObj->resize($width, $height);
        $imageObj->save($imageResized);
        
        return $imgDir."/resized/".$width."x".$height.DS.$imgFile;

    }

}
