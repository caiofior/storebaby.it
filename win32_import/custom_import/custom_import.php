<?php
require_once 'abstract.php';

class Mage_Custom_Import extends Mage_Shell_Abstract {
/**
 * Gets all categories 
 */
const GET_ALL_CATEGORIES='
    SELECT `value`,`entity_id` FROM `catalog_category_entity_varchar` 
    WHERE attribute_id = 35 GROUP BY entity_id
    ';
/**
 * Gets all eav data
 */
const GET_EAV_CODES='
    SELECT `attribute_code`,`attribute_id`,`backend_type` FROM `eav_attribute`
    WHERE `entity_type_id` = %d;
    ';
/**
 * Loads e category entity
 */
const LOAD_CATEGORY = '
    SELECT `catalog_category_entity_varchar`.`entity_id` FROM `catalog_category_entity_varchar` 
    LEFT JOIN `catalog_category_entity` ON `catalog_category_entity_varchar`.`entity_id`=`catalog_category_entity`.`entity_id`
    WHERE
    `catalog_category_entity_varchar`.`attribute_id`=35 AND 
    `catalog_category_entity_varchar`.`value`="%s" AND 
    `catalog_category_entity`.`parent_id`=%d
    ';
/**
 * Gets category position 
 */
const GET_CAT_POSITION='
    SELECT MAX(position)+1 FROM `catalog_category_entity`
    WHERE `parent_id`=%d
    ';
/**
 * Creates a new category
 */
const CREATE_CATEGORY='
INSERT INTO `catalog_category_entity`
(`entity_id`,
`entity_type_id`,
`attribute_set_id`,
`parent_id`,
`created_at`,
`updated_at`,
`path`,
`position`,
`level`,
`children_count`)
VALUES
(
NULL,
3,
3,
%d,
NOW(),
NOW(),
"",
%d,
%d,
0
)
    ';
/**
 * Updates category path after ccreation
 */
const UPDATE_CATEGORY_PATH='
UPDATE `catalog_category_entity`
SET `path`="%s"
WHERE `entity_id`=%d
    ';
/**
 * Gets category path 
 */
const LOAD_CATEGORY_PATH='
SELECT `path` FROM `catalog_category_entity`
WHERE `entity_id`=%d
';
/**
 * Updates an attribute
 */
const UPDATE_ATTRIBUTE='
INSERT IGNORE INTO `%s`
(`value_id`,
`entity_type_id`,
`attribute_id`,
`store_id`,
`entity_id`,
`value`)
VALUES
(
NULL,
%d,
%d,
0,
%d,
"%s"
)
';
/**
 * Sets an attribute
 */
const SET_ATTRIBUTE='
REPLACE INTO `%s`
(`value_id`,
`entity_type_id`,
`attribute_id`,
`store_id`,
`entity_id`,
`value`)
VALUES
(
NULL,
%d,
%d,
0,
%d,
"%s"
)
';
/**
 * Sets an immage to gallery
 */
const DEL_IMG='
DELETE FROM `%s`
WHERE `entity_id`=%d
';
/**
 * Sets an immage to gallery
 */
const SET_IMG='
REPLACE INTO `%s`
(`value_id`,
`attribute_id`,
`entity_id`,
`value`)
VALUES
(
NULL,
%d,
%d,
"%s"
)
';
/**
 * Loads e product
 */
const LOAD_PRODUCT = '
    SELECT `entity_id` FROM `catalog_product_entity`
    WHERE `sku`="%s"';
/**
 * Creates a product
 */
const CREATE_PRODUCT='
    INSERT INTO `catalog_product_entity`
(`entity_id`,
`entity_type_id`,
`attribute_set_id`,
`type_id`,
`sku`,
`has_options`,
`required_options`,
`created_at`,
`updated_at`)
VALUES
(
NULL,
4,
4,
"simple",
"%s",
0,
0,
NOW(),
NOW()
);
';
/**
 * Resets product category
 */
const RESET_PRODUCT_CATEGORY='
DELETE FROM `catalog_category_product`
WHERE `product_id`=%d
';
/**
 * Adds a product to a category
 */
const ADD_PRODUCT_TO_CATEGORY='
REPLACE INTO `catalog_category_product`
(`category_id`,
`product_id`,
`position`)
VALUES
(
%d,
%d,
0
);
    ';
/**
 * Updates image attribute
 */
const IMAGE_ATTRIBUTES='
REPLACE INTO `%s`
SET
`value_id` = %d,
`store_id` = 0,
`label` = "%s",
`position` = 0,
`disabled` = 0
';
/**
 *Adds a product stock 
 */
const ADD_CATALOG_INVENTORY_STOCK='
REPLACE INTO `cataloginventory_stock_item`
SET
`item_id` = NULL,
`product_id` = %d,
`stock_id` = 1,
`qty` = 10,
`min_qty` = 0,
`use_config_min_qty` = 1,
`is_qty_decimal` = 0,
`backorders` = 0,
`use_config_backorders` = 1,
`min_sale_qty` = 1,
`use_config_min_sale_qty` = 1,
`max_sale_qty` = 0,
`use_config_max_sale_qty` = 1,
`is_in_stock` = 1,
`low_stock_date` = NULL,
`notify_stock_qty` = NULL,
`use_config_notify_stock_qty` = 1,
`manage_stock` = 0,
`use_config_manage_stock` = 1,
`stock_status_changed_auto` = 0,
`use_config_qty_increments` = 1,
`qty_increments` = 0,
`use_config_enable_qty_inc` = 1,
`enable_qty_increments` = 0
    ';
/**
 * Adds a product stok status
 */
const ADD_CATALOG_INVENTORY_STATUS='
REPLACE INTO `cataloginventory_stock_status`
SET
`product_id` = %d,
`website_id` = 1,
`stock_id` = 1,
`qty` = 10,
`stock_status` = 1
    ';
/**
 * Adds product to a website
 */
const PRODUCT_WEBSITE='
REPLACE INTO `catalog_product_website`
SET
`product_id` = %d,
`website_id` = 1
';
/**
 * Collection of eav attributes
 */
private $eav=array();
/**
 * Read connection
 */
private $read_connection;
/**
 * Write connection
 */
private $write_connection;
/**
 * Product data
 */
private $product_data=array();
/**
 *Import fileds 
 * @var array
 */
private $import_fields= array(
    'argomento',
    'autore',
    'titolo',
    'sottotitolo',
    'descrizione',
    'testo',
    'prezzo intero',
    'sconto %',
    'metà prezzo',
    'codice'
);
/**
 *Current opened tag
 * @var type 
 */
private $column_association=array(
  'codice'=>'sku',
  'sottotitolo'=>'short_description',
  'testo'=>'description',
  'product_thumb_image'=>'thumbnail',
  'product_full_image'=>'image',
  'product_weight'=>'weight',
  'titolo'=>'name',
  'autore'=>'author',
  'prezzo intero'=>'price',
  'metà prezzo'=>'special_price',
  'descrizione'=>'edition'
);
/**
 * Image dir 
 */
private $img_dir;
/**
 * Root cat id 
 */
private $root_cat_id;
/**
 * Roor cat path
 */
private $root_cat_path;
/***
 * Initialize eav collection
 */
private function init_eav() {
    $core_resource = Mage::getSingleton('core/resource');
    $this->read_connection	= $core_resource->getConnection('read');
    $this->write_connection	= $core_resource->getConnection('write');
    $this->eav[3]=$this->read_connection->fetchAssoc(sprintf(self::GET_EAV_CODES,3));
    $this->eav[4]=$this->read_connection->fetchAssoc(sprintf(self::GET_EAV_CODES,4));    
}
/**
 * Sets attribute value
 */
private function set_attribute($entity_id,$attribute_name,$id,$value) {
    $table_name = 'category';
    if ($entity_id==4)
        $table_name = 'product';
    $sql = sprintf(self::SET_ATTRIBUTE,'catalog_'.$table_name.'_entity_'.$this->eav[$entity_id][$attribute_name]['backend_type'],$entity_id,$this->eav[$entity_id][$attribute_name]['attribute_id'],$id,$value);
    $this->write_connection->query($sql);
}
/**
 *Updates attribute value
 * @param type $entity_id
 * @param type $attribute_name
 * @param type $id
 * @param type $value 
 */
private function update_attribute($entity_id,$attribute_name,$id,$value) {
    $table_name = 'category';
    if ($entity_id==4)
        $table_name = 'product';
    $sql = sprintf(self::UPDATE_ATTRIBUTE,'catalog_'.$table_name.'_entity_'.$this->eav[$entity_id][$attribute_name]['backend_type'],$entity_id,$this->eav[$entity_id][$attribute_name]['attribute_id'],$id,$value);
    $this->write_connection->query($sql,null);
}
/**
 * Main run
 */
public function run() {
$this->init_eav();
// Input file
$input_file = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.$GLOBALS['argv'][1]);
if(!is_file($input_file)) {
    echo 'Unable to find input file'.$input_file;
    exit;
}

// Image archive dir
$img_archive=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR.'convert');
// Image media dir
$img_dir=realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product');

$handle = fopen($input_file, 'r');
fseek($handle,0,SEEK_END);
$last = ftell($handle);
rewind($handle);
unlink('errors.txt');
$row = fgetcsv($handle, 1000, ',');
if ($row != false)
    $fields = $row;
$raw_categories = $this->read_connection->fetchPairs(self::GET_ALL_CATEGORIES);
$categories=array();
foreach($raw_categories as $key=>$value) 
    $categories[trim(strtolower($key))]=$value;
$this->root_cat_id = $this->read_connection->fetchOne(sprintf(self::LOAD_CATEGORY,'Il compralibro',1));
if ($this->root_cat_id == false) {
        $next_position = $this->read_connection->fetchOne(sprintf(self::GET_CAT_POSITION,1));
        //Create category
        $this->write_connection->query(sprintf(self::CREATE_CATEGORY,1,$next_position,1));
        //Gets its ID
        $this->root_cat_id=$this->write_connection->lastInsertId();
        // Updates the path
        $this->write_connection->query(sprintf(self::UPDATE_CATEGORY_PATH,'1/'.$this->root_cat_id,$this->root_cat_id));
        //Sets attributes
        $this->set_attribute(3, 'name', $this->root_cat_id,'Il compralibro');
        $this->set_attribute(3, 'url_key', $this->root_cat_id,'il-compralibro');
        $this->set_attribute(3, 'display_mode', $this->root_cat_id,'PRODUCTS');
        $this->set_attribute(3, 'is_active', $this->root_cat_id,1);
        $this->set_attribute(3, 'include_in_menu', $this->root_cat_id,1);
}
while (($raw = fgetcsv($handle, 1000, ',')) !== false) {
    $this->write_connection->query('START TRANSACTION');
    $raw = array_combine($this->import_fields,$raw);
    $product = array();
    foreach($this->column_association as $key=>$value)
        $product[$value]=$raw[$key];
    echo (ceil((ftell($handle)/$last)*100))." %\t".$product['sku']."\t".$product['name'];
    $product_id = $this->read_connection->fetchOne(sprintf(self::LOAD_PRODUCT,$product['sku']));
    if ($product_id == false) {
        //Creates a product
        $this->write_connection->query(sprintf(self::CREATE_PRODUCT,$product['sku']));
        $product_id=$this->write_connection->lastInsertId();
    }
    $category_names = preg_split('/,(?![0-9]{2})/',$raw['argomento']);
    array_walk($category_names, create_function('&$v,$k', '$v = trim(strtolower(iconv("UTF-8","ISO-8859-1//TRANSLIT",$v)));'));
    
    $this->write_connection->query(sprintf(self::RESET_PRODUCT_CATEGORY,$product_id));
    $this->write_connection->query(sprintf(self::ADD_PRODUCT_TO_CATEGORY,1,$product_id));
    $this->write_connection->query(sprintf(self::ADD_PRODUCT_TO_CATEGORY,$this->root_cat_id,$product_id));
    foreach($category_names as $category_name) {
        if ($category_name == '')  continue;
        if (!key_exists($category_name, $categories)) {
            $next_position = $this->read_connection->fetchOne(sprintf(self::GET_CAT_POSITION,$this->root_cat_id));
            //Create category
            $this->write_connection->query(sprintf(self::CREATE_CATEGORY,$this->root_cat_id,$next_position,2));
            //Gets its ID
            $cat_id=$this->write_connection->lastInsertId();
            // Updates the path
            $this->write_connection->query(sprintf(self::UPDATE_CATEGORY_PATH,'1/'.$this->root_cat_id.'/'.$cat_id,$cat_id));
            //Sets attributes
            $this->set_attribute(3, 'name', $cat_id,$category_name);
            $this->set_attribute(3, 'url_key', $cat_id,  str_replace(' ', '-', $category_name));
            $this->set_attribute(3, 'display_mode', $cat_id,'PRODUCTS');
            $this->set_attribute(3, 'is_active',$cat_id,1);
            $this->set_attribute(3, 'include_in_menu', $cat_id,1);
            $raw_categories = $this->read_connection->fetchPairs(self::GET_ALL_CATEGORIES);
            $categories=array();
            foreach($raw_categories as $key=>$value) 
                $categories[trim(strtolower($key))]=$value;
        }
        $cat_id = $categories[$category_name];
        $this->write_connection->query(sprintf(self::ADD_PRODUCT_TO_CATEGORY,$cat_id,$product_id));
    }
    try{
    $this->update_attribute(4, 'description', $product_id, addslashes($product['description']));
    } catch (Exception $e) {}
    try {
    $this->update_attribute(4, 'short_description', $product_id,  addslashes($product['short_description']));
    }catch (Exception $e) {}
    $this->update_attribute(4, 'name', $product_id,addslashes($product['name']));
    $this->set_attribute(4, 'url_key', $product_id,addslashes(str_replace(array(' '),array('-'),strtolower($product['name']))));
    $this->set_attribute(4, 'url_path', $product_id,addslashes(str_replace(array(' '),array('-'),strtolower($product['name'])).'.html'));
    $this->set_attribute(4, 'price', $product_id,  str_replace(',', '.', $product['price']));
    $this->set_attribute(4, 'special_price', $product_id,str_replace(',', '.', $product['special_price']));
    $this->set_attribute(4, 'status', $product_id,1);
    $this->set_attribute(4, 'visibility', $product_id,4);
    $this->set_attribute(4, 'tax_class_id', $product_id,2);
    $edition = explode(',',$product['edition']);
    while (sizeof($edition) > 3) {
        $edition[0].=' '.$edition[1];
        unset($edition[0]);
        $edition = array_values($edition);
    }
    if (sizeof($edition) < 3) {
        $copy_edition = array();
        foreach ($edition as $key=>$item) {
            $add_item = true;
            $posp = strpos($item,'pp.');
            if ($posp !== false && $posp > 3) {
                $add_item = false;
                $copy_edition[]=  trim(substr($item, 0, $posp));
                $item = trim(substr($item, $posp));
                $posc = strpos($item,'cm.');
                if ($posc === false || $posc < 3)
                    $copy_edition[]=$item;
            }
            
            $posc = strpos($item,'cm.');
            if ($posc !== false && $posc > 3) {
                $add_item = false;
                $copy_edition[]=  trim(substr($item, 0, $posc));
                $copy_edition[]=  trim(substr($item, $posc));
            }
            
            if ($add_item)
                $copy_edition[]=  $item;
        }
        $edition = $copy_edition;
    }
    if (sizeof($edition) != 3)
        file_put_contents ('errors.txt', $product['sku']."\t".$product_id."\t".$product['name'],FILE_APPEND);

    foreach($edition as $key=>$value) {
        switch($key) {
            case 0:
                $this->update_attribute(4, 'edition', $product_id,addslashes($value));
            break;
            case 1:
                $this->update_attribute(4, 'pages', $product_id,addslashes($value));
            break;
            case 2:
                $this->update_attribute(4, 'size', $product_id,addslashes($value));
            break;
        } 
    }
    $product['image']=$product['sku'].'.jpeg';
    $file = $img_archive.DIRECTORY_SEPARATOR.$product['image'];
    if (is_file($file)) {
        $dest = '/'.substr($product['image'], 0, 1);
        if (!is_dir($img_dir.$dest))
            mkdir($img_dir.$dest);
        $dest .= '/'.substr($product['image'], 1, 1);
            mkdir($img_dir.$dest);
        $dest .='/'.$product['image'];
            copy($file,$img_dir.$dest);
            //SEts image
            $this->write_connection->query(sprintf(self::SET_IMG,'catalog_product_entity_media_gallery',82,$product_id,$dest));
            $image_id=$this->write_connection->lastInsertId();
            //Adds attributes
            $this->write_connection->query(sprintf(self::IMAGE_ATTRIBUTES,'catalog_product_entity_media_gallery_value',$image_id,addslashes($product['name'])));
            //SEts image to predefined
            $this->set_attribute(4, 'image', $product_id,$dest);
            $this->set_attribute(4, 'small_image', $product_id,$dest);
            $this->set_attribute(4, 'thumbnail', $product_id,$dest);
    }
    // Adds a inventory stock to a product
    $this->write_connection->query(sprintf(self::ADD_CATALOG_INVENTORY_STOCK,$product_id));
    // Adds an inventory stock status to a prodcuct
    $this->write_connection->query(sprintf(self::ADD_CATALOG_INVENTORY_STATUS,$product_id));
    // Adds a producxt to a website
    $this->write_connection->query(sprintf(self::PRODUCT_WEBSITE,$product_id));

    $this->write_connection->query('COMMIT'); 
    echo "\t".(is_file($file)? '': 'NO IMAGE').PHP_EOL;
    
}
fclose($handle);


}
    
}

$shell = new Mage_Custom_Import();
$shell->run();
$_SERVER['argv'][1]='reindexall';
$_SERVER['argc']=2;
require('indexer.php');


