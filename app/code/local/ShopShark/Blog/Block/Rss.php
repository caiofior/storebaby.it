<?php
/**
 * ShopShark Blog Extension
 * @version   1.0 12.09.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_Blog_Block_Rss extends Mage_Rss_Block_Abstract {

    protected function _construct() {
        /*
         * setting cache to save the rss for 10 minutes
         */
        $this->setCacheKey('rss_catalog_category_'
                . Mage::app()->getStore()->getId() . '_'
                . $this->getRequest()->getParam('cid') . '_'
                . $this->getRequest()->getParam('sid')
        );
        $this->setCacheLifetime(600);
    }

    protected function _toHtml() {
        $rssObj = Mage::getModel('rss/rss');

        $route = Mage::helper('blog')->getRoute();

        $url = $this->getUrl($route);
        $title = Mage::getStoreConfig('blog/blog/title');
        $data = array('title' => $title,
            'description' => $title,
            'link' => $url,
            'charset' => 'UTF-8'
        );

        if (Mage::getStoreConfig('blog/rss/image') != "") {
            $data['image'] = $this->getSkinUrl(Mage::getStoreConfig('blog/rss/image'));
        }

        $rssObj->_addHeader($data);

        $collection = Mage::getModel('blog/blog')->getCollection()
                ->addPresentFilter()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->setOrder('created_time', 'desc');

        $identifier = $this->getRequest()->getParam('identifier');

        $tag = $this->getRequest()->getParam('tag');
        if ($tag) {
            $collection->addTagFilter(urldecode($tag));
        }


        if ($cat_id = Mage::getSingleton('blog/cat')->load($identifier)->getcatId()) {
            Mage::getSingleton('blog/status')->addCatFilterToCollection($collection, $cat_id);
        }


        Mage::getSingleton('blog/status')->addEnabledFilterToCollection($collection);

        $collection->setPageSize((int) Mage::getStoreConfig('blog/rss/posts'));
        $collection->setCurPage(1);



        if ($collection->getSize()) {

            $processor = Mage::helper('cms')->getBlockTemplateProcessor();

            foreach ($collection as $post) {

                $data = array(
                    'title' => $post->getTitle(),
                    'link' => $this->getUrl($route . "/" . $post->getIdentifier()),
                    'description' => $processor->filter($post->getPostContent()),
                    'lastUpdate' => strtotime($post->getCreatedTime()),
                );

                $rssObj->_addEntry($data);
            }
        }

        return $rssObj->createRssXml();
    }

}
