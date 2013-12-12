<?php
/**
 * @version   1.0 06.10.2013
 * @author    ShopShark http://www.shopshark.net <info@shopshark.net>
 * @copyright Copyright (C) 2010 - 2013 ShopShark
 */

class ShopShark_SharkSlideshow_Block_Adminhtml_Sharkrevslider_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  	public function __construct()
  	{
    	parent::__construct();
      	$this->setId('sharkrevsliderGrid');
      	$this->setDefaultSort('sort_order');
      	$this->setDefaultDir('ASC');
      	$this->setSaveParametersInSession(true);
  	}

  	protected function _prepareCollection()
  	{
    	$collection = Mage::getModel('sharkslideshow/sharkrevslider')->getCollection();
    	$this->setCollection($collection);
    	return parent::_prepareCollection();
  	}

  	protected function _prepareColumns()
  	{
		$this->addColumn('sort_order', array(
			'header'    => Mage::helper('sharkslideshow')->__('Sort Order'),
			'align'     => 'right',
			'width'     => '70px',
			'index'     => 'sort_order',
		));
				
		$this->addColumn('image', array(
			'header'    => Mage::helper('sharkslideshow')->__('Image'),
			'align'     => 'center',
			'index'     => 'image',
			'renderer' 	=> 'sharkslideshow/adminhtml_sharkrevslider_grid_renderer_image'
		));
	
		$this->addColumn('link', array(
			'header'    => Mage::helper('sharkslideshow')->__('Link'),
			'align'     => 'left',
			'index'     => 'link',
		));
	
		if (!Mage::app()->isSingleStoreMode()) {
			$this->addColumn('store_id', array(
			'header'        => Mage::helper('sharkslideshow')->__('Store View'),
			'index'         => 'store_id',
			'type'          => 'store',
			'store_all'     => true,
			'store_view'    => true,
			'sortable'      => false,
			'filter_condition_callback' => array($this, '_filterStoreCondition'),
			));
		}
	
		$this->addColumn('status', array(
			'header'    => Mage::helper('sharkslideshow')->__('Status'),
			'align'     => 'left',
			'width'     => '80px',
			'index'     => 'status',
			'type'      => 'options',
			'options'   => array(
					1 	=> 'Enabled',
				  	2 	=> 'Disabled',
			),
		));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('sharkslideshow')->__('Action'),
                'width'     => '10',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('sharkslideshow')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

      	return parent::_prepareColumns();
	}

	protected function _afterLoadCollection()
  	{
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
  	}

  	protected function _filterStoreCondition($collection, $column)
  	{
    	if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addStoreFilter($value);
  	}

  	protected function _prepareMassaction()
  	{
        $this->setMassactionIdField('slide_id');
        $this->getMassactionBlock()->setFormFieldName('sharkrevslider');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('sharkslideshow')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('sharkslideshow')->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('sharkslideshow/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('sharkslideshow')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('sharkslideshow')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        return $this;
  	}

  	public function getRowUrl($row)
  	{
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  	}

}