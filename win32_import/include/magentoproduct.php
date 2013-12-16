<?php
/**
 * Magento Product class
 */
class MagentoProduct {
    /**
     * Reference to product from csv
     * @var ProductFromCsv
     */
    private $productFromCsv;
    /**
     * Product data
     * @var array
     */
    private $data = array();
    /**
     * Headres of the csv
     * @var array
     */
    private static $headers = array(
        'store'=>'admin',
        'websites'=>'Base',
        'attribute_set'=>'Default',
        'type'=>'simple',
        'category_ids',
        'sku',
        'has_options'=>'0',
        'name',
        'meta_title',
        'meta_description',
        'image',
        'small_image',
        'thumbnail',
        'url_key',
        'url_path',
        'custom_design',
        'page_layout'=>'No layout updates',
        'options_container'=>'Block after Info Column',
        'image_label',
        'small_image_label',
        'thumbnail_label',
        'country_of_manufacture',
        'msrp_enabled'=>'Use config',
        'msrp_display_actual_price_type'=>'Use config',
        'gift_message_available'=>'No',
        'weight',
        'price',
        'special_price',
        'msrp',
        'status'=>'Enabled',
        'is_recurring'=>'No',
        'visibility'=>'Catalog, Search',
        'enable_googlecheckout'=>'Yes',
        'tax_class_id'=>'Taxable Goods',
        'description',
        'short_description',
        'meta_keyword',
        'custom_layout_update',
        'special_from_date',
        'special_to_date',
        'news_from_date',
        'news_to_date',
        'custom_design_from',
        'custom_design_to',
        'qty',
        'min_qty'=>'0',
        'use_config_min_qty'=>'1',
        'is_qty_decimal'=>'0',
        'backorders'=>'0',
        'use_config_backorders'=>'1',
        'min_sale_qty'=>'1',
        'use_config_min_sale_qty'=>'1',
        'max_sale_qty'=>'0',
        'use_config_max_sale_qty'=>'1',
        'is_in_stock'=>'1',
        'low_stock_date',
        'notify_stock_qty'=>'0',
        'use_config_notify_stock_qty'=>'1',
        'manage_stock'=>'0',
        'use_config_manage_stock'=>'1',
        'stock_status_changed_auto'=>'0',
        'use_config_qty_increments'=>'1',
        'qty_increments'=>'0',
        'use_config_enable_qty_inc'=>'1',
        'enable_qty_increments'=>'0',
        'is_decimal_divided'=>'0',
        'stock_status_changed_automatically'=>'0',
        'use_config_enable_qty_increments'=>'1',
        'product_name',
        'store_id'=>'0',
        'product_type_id'=>'simple',
        'product_status_changed',
        'product_changed_websites'

    );
    /**
     * Creates reference to PrductFromCsv
     * @param MastroProduct $mastroProduct
     */
    public function __construct(MastroProduct $mastroProduct) {
        foreach($mastroProduct->getHeaders() as $header)
            self::$headers['ZZ_'.$header]=null;
        $this->productFromCsv = $mastroProduct->getProductFromCsv();
    }
    /**
     * Sets product data
     * @param string $key
     * @param string $value
     */
    public function setData($key, $value) {
        if (key_exists($key, $this->data))
            $this->data[$key]=$value;
    }
    /**
     * Gets product data
     * @param string $key
     * @return string|null
     */
    public function getData($key) {
        if (key_exists($key, $this->data))
            return $this->data[$key];
    }
    /**
     * Empties product data
     */
    public function emptyData() {
        $this->data=self::$headers;
    }
    /**
     * Creates a csv row
     * @return string
     */
    public function getCsvRow() {
        $data = $this->data;
        foreach ($data as $key => $value) {
            if (preg_match('/[^\-0-9 ,\.]/', $value))
                $data[$key]='"'.str_replace (',','.',$value).'"';
        }
        return implode(',',$data);
    }
    /**
     * Creates a csv headers
     * @return string
     */
    public function getCsvHeaders() {
        $headers = array_keys(self::$headers);
        foreach ($headers as $key=>$field) {
            if (preg_match('/[^\-0-9 ,\.]/', $field))
                $headers[$key]='"'.str_replace (',','.',$field).'"';
        }
        return implode(',',$headers);
    }    
}

