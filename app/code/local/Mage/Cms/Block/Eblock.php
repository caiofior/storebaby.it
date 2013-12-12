
<?php
class Mage_Cms_Block_Eblock extends Mage_Core_Block_Template
{
    public function getEHtml()
    {
        $blockId = $this->getBlockId();
        $html = '';
        if ($blockId) {
            $block = Mage::getModel('cms/block')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($blockId);
            if ($block->getIsActive()) {
                /* @var $helper Mage_Cms_Helper_Data */
                $helper = Mage::helper('cms');
                $processor = $helper->getBlockTemplateProcessor();
                $html = $processor->filter($block->getContent());
            }
            return $html;
        }
        return false;
    }
    
    public function getETitle()
    {
        $blockId = $this->getBlockId();
        
        $title = Mage::getModel('cms/block')->setStoreId(Mage::app()->getStore()->getId())->load($blockId)->getTitle();
        
        return $title;
    }
}