<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_InnoCore
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Editable area grid container block
 * 
 * @category   Innoexts
 * @package    Innoexts_InnoCore
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Editable_Area_Container 
    extends Innoexts_InnoCore_Block_Adminhtml_Widget_Grid_Editable_Container 
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('innocore/widget/grid/editable/area/container.phtml');
    }
    /**
     * Retrieve regions in JSON format
     * 
     * @return string
     */
    public function getRegionsJson()
    {
        return $this->helper('directory')->getRegionJson();
    }
}