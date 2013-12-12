<?php
/**
 * SFC - Featured Catagories Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@storefrontconsulting.com so we can send you a copy immediately.
 *
 *
 * @package    SFC_FeaturedCategories
 * @copyright  (C)Copyright 2010 StoreFront Consulting, Inc (http://www.StoreFrontConsulting.com/)
 * @author     Adam Lundrigan
 */

class SFC_FeaturedCategories_Block_Display extends Mage_Core_Block_Template
{
	protected function getFeaturedCategories()
	{
		$Categories = array();
		$cats = split(",",Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId())->getAllChildren());
		$cats[] = Mage::app()->getStore()->getRootCategoryId();
		foreach ( $cats as $cat )
		{
			$Category = Mage::getModel('catalog/category')->load($cat);

			//BUG: "0" = true, "1" = false
			//echo $Category->getName() . " - C:" . $Category->getIsFeaturedCategory() . " - S:" . $Category->getIsFeaturedSubcat() .  "<br />";

			if ( $Category->getIsFeaturedCategory() )
			{
				$Categories[$Category->getId()]['Category'] = $Category;
			}
			if ( $Category->getIsFeaturedSubcat() )
			{
				// Should search all predecessor nodes for a featured category to nest under
				$ParentCategory = $Category->getParentCategory();
				while ( true )
				{
					if ( $ParentCategory->getIsFeaturedCategory() || $ParentCategory->getId() == 1 )
					{
						break;
					}
					$ParentCategory = $ParentCategory->getParentCategory();
				}
				$Categories[$ParentCategory->getId()]['Subcats'][$Category->getId()] = $Category;
			}
		}
		return $Categories;
	}
	
	protected function calcCategoryBlockHeight()
	{
		$Categories = getFeaturedCategories();
		
		// Iterate and check the longest 
	}
}

