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
        'categories'=>null,
        'sku'=>null,
        're_skus'=>null,
        'xre_skus'=>null,
        'cs_skus'=>null,
        'xcs_skus'=>null,
        'us_skus'=>null,
        'xus_skus'=>null,
        'has_options'=>'0',
        'name'=>null,
        'manufacturer'=>null,
        'meta_title'=>null,
        'meta_description'=>null,
        'image'=>null,
        'small_image'=>null,
        'thumbnail'=>null,
        'url_key'=>null,
        'url_path'=>null,
        'custom_design'=>null,
        'page_layout'=>'No layout updates',
        'options_container'=>'Block after Info Column',
        'image_label'=>null,
        'small_image_label'=>null,
        'thumbnail_label'=>null,
        'country_of_manufacture'=>null,
        'msrp_enabled'=>'Use config',
        'msrp_display_actual_price_type'=>'Use config',
        'gift_message_available'=>'No',
        'weight'=>null,
        'price'=>null,
        'special_price'=>null,
        'msrp'=>null,
        'status'=>'Enabled',
        'is_recurring'=>'No',
        'visibility'=>'Catalog, Search',
        'enable_googlecheckout'=>'Yes',
        'tax_class_id'=>'Taxable Goods',
        'description'=>null,
        'short_description'=>null,
        'meta_keyword'=>null,
        'custom_layout_update'=>null,
        'special_from_date'=>null,
        'special_to_date'=>null,
        'shared_on_social_networks'=>'__MAGMI_IGNORE__',
        'news_from_date'=>'__MAGMI_IGNORE__',
        'news_to_date'=>'__MAGMI_IGNORE__',
        'custom_design_from'=>null,
        'custom_design_to'=>null,
        'qty'=>null,
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
        'low_stock_date'=>null,
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
        'product_name'=>null,
        'store_id'=>'0',
        'product_type_id'=>'simple',
        'product_status_changed'=>null,
        'product_changed_websites'=>null

    );
    /**
     * Creates reference to PrductFromCsv
     * @param MastroProduct $mastroProduct
     */
    public function __construct(MastroProduct $mastroProduct) {
        foreach($mastroProduct->getHeaders() as $header)
            self::$headers['MASTRO_'.$header]=null;
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
            if (preg_match('/^[\-0-9 \.]+$/', $value))
                $data[$key]=$value;
            else
                $data[$key]='"'.addslashes($value).'"';
               
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
            $headers[$key]='"'.addslashes($field).'"';
        }
        return implode(',',$headers);
    }    
}

