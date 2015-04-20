<?php
/**
 * @version   1.0 12.06.2013
 * @author    ShopShark http://www.shopshark.com <mail@shopshark.com>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('sharkslideshow/revolution_slides')}`;
CREATE TABLE `{$this->getTable('sharkslideshow/revolution_slides')}` (
  `slide_id` int(11) unsigned NOT NULL auto_increment,
  `transition` text NOT NULL default '',
  `masterspeed` text NOT NULL default '',
  `slotamount` text NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `thumb` varchar(255) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `text` text NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `sort_order` smallint(6) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`slide_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$this->getTable('sharkslideshow/revolution_slides')}` (`slide_id`, `transition`, `masterspeed`, `slotamount`, `link`, `thumb`, `image`, `text`, `status`, `sort_order`, `created_time`, `update_time`) VALUES
	(1, 'boxslide', '100', '1', '', '', 'shopshark/revslider/MILANO-BNR-04-BG.png', '', 1, 1, '2013-01-05 16:16:16', '2013-01-05 16:16:16'),
	(2, 'slidedown', '100', '1', '', '', 'shopshark/revslider/banner-yellow_1.png', '', 1, 2, '2013-01-05 16:17:06', '2013-01-05 16:17:06'),
	(3, 'fade', '300', '1', '', '', 'shopshark/revslider/MILANO-BNR-02-BG.png', '', 1, 3, '2013-01-05 16:18:06', '2013-01-05 16:18:06'),
	(4, 'slidedown', '100', '7', '', '', 'shopshark/revslider/white_bg.png', '', 1, 4, '2013-01-05 16:21:20', '2013-01-05 16:21:20');
");

/**
 * Drop 'slides_store' table
 */
$conn = $installer->getConnection();
$conn->dropTable($installer->getTable('sharkslideshow/revolution_slides_store'));

/**
 * Create table for stores
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('sharkslideshow/revolution_slides_store'))
    ->addColumn('slide_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'nullable'  => false,
    'primary'   => true,
), 'Slide ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
), 'Store ID')
    ->addIndex($installer->getIdxName('sharkslideshow/revolution_slides_store', array('store_id')),
    array('store_id'))
    ->addForeignKey($installer->getFkName('sharkslideshow/revolution_slides_store', 'slide_id', 'sharkslideshow/revolution_slides', 'slide_id'),
    'slide_id', $installer->getTable('sharkslideshow/revolution_slides'), 'slide_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('sharkslideshow/revolution_slides_store', 'store_id', 'core/store', 'store_id'),
    'store_id', $installer->getTable('core/store'), 'store_id',
    Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Slide To Store Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Assign 'all store views' to existing slides
 */
$installer->run("INSERT INTO {$this->getTable('sharkslideshow/revolution_slides_store')} (`slide_id`, `store_id`) SELECT `slide_id`, 0 FROM {$this->getTable('sharkslideshow/revolution_slides')};");

$installer->endSetup();

/**
 * add slide data
 */
$data = array(
    1 => '<div class="caption fade"  data-x="0" data-y="0" data-speed="2000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-04-WOMAN.png"}}" alt="magento_themes-Milano"></div>
<div class="caption lfb"  data-x="860" data-y="0" data-speed="2000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-04-BLUE.png"}}" alt="magento_themes-Milano"></div>
<div class="caption lfb"  data-x="1090" data-y="0" data-speed="2000" data-start="2000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-04-GREEN.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sfl"  data-x="860" data-y="70" data-speed="1800" data-start="2500" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-04-strong.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sfb"  data-x="995" data-y="178" data-speed="1800" data-start="2500" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-04-finish.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sft"  data-x="1025" data-y="300" data-speed="1800" data-start="2500" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/text04.png"}}" alt="magento_themes-Milano"></div>
',
    '<div class="caption lfb"  data-x="-35" data-y="0" data-speed="1000" data-start="0" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-03-BG.png"}}" alt="magento_themes-Milano"></div>
<div class="caption fade"  data-x="-35" data-y="0" data-speed="1000" data-start="1400" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-03-BG-woman.png"}}" alt="magento_themes-Milano"></div>
<div class="caption fade"  data-x="720" data-y="153" data-speed="1000" data-start="2100" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-03-BG-UP.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sft"  data-x="575" data-y="90" data-speed="1000" data-start="2600" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-03-BG-Lighten.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sfl"  data-x="960" data-y="310" data-speed="1000" data-start="2900" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/text.png"}}" alt="magento_themes-Milano"></div>
',
    '<div class="caption fade"  data-x="0" data-y="0" data-speed="2000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-02-woman.png"}}" alt="magento_themes-Milano"></div>
<div class="caption lfb"  data-x="760" data-y="-100" data-speed="2000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-02-Blue-Colored.png"}}" alt="magento_themes-Milano"></div>
<div class="caption lfb"  data-x="890" data-y="0" data-speed="1800" data-start="2000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/MILANO-BNR-02-Green-Colored.png"}}" alt="magento_themes-Milano"></div>
<div class="caption fade"  data-x="810" data-y="70" data-speed="1800" data-start="2500" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/ed.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sft"  data-x="1055" data-y="205" data-speed="2000" data-start="3000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/ge.png"}}" alt="magento_themes-Milano"></div>
<div class="caption sfl"  data-x="747" data-y="33" data-speed="2000" data-start="3000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/pro.png"}}" alt="magento_themes-Milano"></div>
<div class="caption fade"  data-x="1220" data-y="70" data-speed="2000" data-start="3000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/default/text.png"}}" alt="magento_themes-Milano"></div>
',
    '<div class="caption lfb"  data-x="169" data-y="10" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/MILANOBNR-03-IMG1.jpg"}}" alt="magento_themes-Milano"></div>
<div class="caption lft"  data-x="453" data-y="10" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/MILANOBNR-03-TEXT.jpg"}}" alt="magento_themes-Milano"></div>
<div class="caption lfb"  data-x="737" data-y="10" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/MILANOBNR-03-IMG2.jpg"}}" alt="magento_themes-Milano"></div>
<div class="caption lft"  data-x="1022" data-y="10" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/MILANOBNR-03-IMG3.jpg"}}" alt="magento_themes-Milano"></div>
<div class="caption lfb"  data-x="1307" data-y="10" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/MILANOBNR-03-IMG4.jpg"}}" alt="magento_themes-Milano"></div>
<div class="caption lfr"  data-x="453" data-y="140" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/text01.png}}" alt="magento_themes-Milano"></div>
<div class="caption lfl"  data-x="453" data-y="160" data-speed="1000" data-start="1000" data-easing="easeOutExpo"  ><img src="{{media url="wysiwyg/milano/slideshow/fashion/text02.png}}" alt="magento_themes-Milano"></div>',
);

$model = Mage::getModel('sharkslideshow/sharkrevslider');
foreach ( $data as $k => $v ) {
    $model->load($k)
        ->setText($v)
        ->save();
}