<?php
define('ABSPATH',__DIR__.'/wp/');
if (!isset($_SERVER))
   $_SERVER = array();
if (!key_exists('SERVER_NAME', $_SERVER))
   $_SERVER['SERVER_NAME'] = '';
require "wp/formatting.php";
require "wp/functions.php";
require "wp/plugin.php";
require "NextScripts_APIs/postToGooglePlus.php";
require "twitteroauth-master/twitteroauth/twitteroauth.php";
require "wp/class-IXR.php";
require "wp/mimetype.php";
require_once __DIR__ . '/Facebook/autoload.php';




class SocialNotifyPlugin extends Magmi_GeneralImportPlugin {

   public function getPluginInfo() {
      return array(
          "name" => "Social notify of new records",
          "author" => "caiofior",
          "version" => "0.1"
      );
   }

   public function getPluginParams($params) {
      $pp = array();
      foreach ($params as $k => $v) {
         if (preg_match("/^SOCIAL:.*$/", $k)) {
            $pp[$k] = $v;
         }
      }
      return $pp;
   }

   public function afterImport() {
      if (!function_exists('PHPMailerAutoload'))
         require __DIR__ . DIRECTORY_SEPARATOR . 'phpmailer' . DIRECTORY_SEPARATOR . 'PHPMailerAutoload.php';
      $config = array();
      foreach ($this->selectAll(
              'SELECT `path`,`value` FROM `core_config_data`
                     WHERE `scope` = "default" AND ( `path` LIKE "%/lesti_smtp/%" OR `path` LIKE "%/ident_general/%" OR `path` = "web/unsecure/base_url")') as $value) {
         $config [$value['path']] = $value['value'];
      }
      $secondPath = $this->selectone('SELECT `value` FROM `core_config_data` WHERE `scope_id` = 2 AND `path` = "web/unsecure/base_url"',null,'value');
      $imageDir = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
              '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .
              'media' . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR;

      $imageDir = realpath($imageDir);
      $name_id = $this->selectone('
         SELECT `attribute_id` FROM `eav_attribute` WHERE
         `attribute_code`="name" AND `entity_type_id`= 
             (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
             `entity_type_code`="catalog_product"
             )
      ',null,'attribute_id');
      //$name = 71;
      $description_id = $this->selectone('
         SELECT `attribute_id` FROM `eav_attribute` WHERE
         `attribute_code`="description" AND `entity_type_id`= 
             (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
             `entity_type_code`="catalog_product"
             )
      ',null,'attribute_id');
      //$description = 72;
      $url_key_id = $this->selectone('
      SELECT `attribute_id` FROM `eav_attribute` WHERE
         `attribute_code`="url_key" AND `entity_type_id`= 
             (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
             `entity_type_code`="catalog_product"
             )
      ',null,'attribute_id');
      //$url_key = 97;
      $image_id = $this->selectone('
      SELECT `attribute_id` FROM `eav_attribute` WHERE
      `attribute_code`="image" AND `entity_type_id`= 
          (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
          `entity_type_code`="catalog_product"
          )
      ',null,'attribute_id');
      //$image = 85;
      $catalog_category_id = $this->selectone('
      SELECT `attribute_id` FROM `eav_attribute` WHERE
      `attribute_code`="name" AND `entity_type_id`= 
      (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
         `entity_type_code`="catalog_category"
      )
      ',null,'attribute_id');
      //$catalog_category = 41;
      $shared_on_social_networks_id = $this->selectone('
            SELECT `attribute_id` FROM `eav_attribute` WHERE
            `attribute_code`="shared_on_social_networks"
      ',null,'attribute_id');
      //$shared_on_social_networks = 143;
      $news_from_date = $this->selectone('
            SELECT `attribute_id` FROM `eav_attribute` WHERE
            `attribute_code`="news_from_date"
      ',null,'attribute_id');
      //$shared_on_social_networks = 93;
      $product_stmt = $this->exec_stmt('
         SELECT
         `catalog_product_entity`.`entity_id` ,
         `catalog_product_entity`.`sku`
         FROM `catalog_product_entity` 
         LEFT JOIN `catalog_product_entity_int` ON 
         `catalog_product_entity_int`.`entity_id`=`catalog_product_entity`.`entity_id` AND 
         `catalog_product_entity_int`.`attribute_id`= '.$shared_on_social_networks_id.'
         LEFT JOIN `catalog_product_entity_datetime` ON 
         `catalog_product_entity_datetime`.`entity_id`=`catalog_product_entity`.`entity_id` AND 
         `catalog_product_entity_datetime`.`attribute_id`= '.$news_from_date.'
         WHERE 
           ( `catalog_product_entity_int`.`value` IS NULL OR `catalog_product_entity_int`.`value` != 1 ) AND  
           ( `catalog_product_entity_datetime`.`value` IS NOT NULL OR `catalog_product_entity_datetime`.`value` != "" )
         GROUP BY `catalog_product_entity`.`entity_id`
         ORDER BY `catalog_product_entity_datetime`.`value` DESC
         ',null,false);
      // Full path to twitterOAuth.php (change OAuth to your own path)
      // create new instance
      $tweet = new TwitterOAuth(
              $this->getParam("SOCIAL:twitterkey", ""), $this->getParam("SOCIAL:twittersecret", ""), $this->getParam("SOCIAL:twitterotoken", ""), $this->getParam("SOCIAL:twitterosecret", "")
      );
      $productCount = 0;
      $facebookPages = explode(':',$this->getParam("SOCIAL:facebook", ""));
      $gogglePages = explode(':',$this->getParam("SOCIAL:gpage", ""));
      while ($productCount < $this->getParam("SOCIAL:topost", "10") && $product = $product_stmt->fetch(PDO::FETCH_ASSOC) ) {
         $product['image']= $this->selectone('
               SELECT `value` FROM `catalog_product_entity_varchar` WHERE
                  `catalog_product_entity_varchar`.`entity_id`='.$product['entity_id'].' AND
                  `catalog_product_entity_varchar`.`attribute_id`= '.$image_id.'
               LIMIT 1
         ',null,'value');
         $product['url_path'] = $this->selectone('
               SELECT `request_path` FROM `core_url_rewrite`
               WHERE `id_path` LIKE "product/%" AND `product_id` ='.$product['entity_id'].'
               ORDER BY `category_id` ASC LIMIT 1
         ',null,'request_path');
         if (
                 $product['url_path'] == '' ||
                 !is_file($imageDir . $product['image'])
            ) {
            continue;
         }
         $product['name']= $this->selectone('
               SELECT `value` FROM `catalog_product_entity_varchar` WHERE
                     `catalog_product_entity_varchar`.`entity_id`='.$product['entity_id'].' AND
                     `catalog_product_entity_varchar`.`attribute_id`= '.$name_id.'  
               LIMIT 1
         ',null,'value');
         $product['description']= $this->selectone('
               SELECT `value` FROM `catalog_product_entity_text` WHERE
                     `catalog_product_entity_text`.`entity_id`='.$product['entity_id'].' AND
                     `catalog_product_entity_text`.`attribute_id`= '.$description_id.'
               LIMIT 1
         ',null,'value');
         $product['url_key']= $this->selectone('
               SELECT `value` FROM `catalog_product_entity_varchar` WHERE
                     `catalog_product_entity_varchar`.`entity_id`='.$product['entity_id'].' AND
                     `catalog_product_entity_varchar`.`attribute_id`= '.$url_key_id.'
               LIMIT 1
         ',null,'value');
         $product['category_names'] = $this->selectone('
               SELECT GROUP_CONCAT( DISTINCT `value` SEPARATOR ", ") as value
               FROM `catalog_category_entity_varchar` WHERE
               `entity_id` IN (SELECT `category_id` FROM `catalog_category_product` WHERE `product_id` = '.$product['entity_id'].')
               AND `catalog_category_entity_varchar`.`attribute_id`= '.$catalog_category_id.'                       
         ',null,'value');
         
         $productCount ++;
         $tags = preg_replace('/^[^,]*, /', '', $product['category_names']);
         $tags = strtolower(', '.$tags);
         $tags = str_replace(' ','_',$tags);
         $tags = trim(str_replace(',_',' #',$tags));
         if (
                 $this->getParam("SOCIAL:gemail", "") != '' &&
                 $this->getParam("SOCIAL:gpassword", "") != '' &&
                 $this->getParam("SOCIAL:gpage", "") != ''
         ) {
            foreach($gogglePages as $key=>$gogglePage) {
	       $baseUrl = $config['web/unsecure/base_url'];
	       if ($key>0 && $secondPath != '')
		   $baseUrl = $secondPath;
               $nt = new nxsAPI_GP();
               $loginError = $nt->connect($this->getParam("SOCIAL:gemail", ""), $this->getParam("SOCIAL:gpassword", ""));
               // Image URL
               $lnk = array('img'=>$baseUrl. '/media/catalog/product/' . $product['image']);
               $nt->postGP($product['name'] .' '. $tags . ' ' . $baseUrl . 'index.php/' . $product['url_path'], $lnk, $gogglePage);
               if ($loginError === false)
                  $this->log($baseUrl . 'index.php/' . $product['url_path'] . " not sent succesfully " . $loginError, "info");
            }
         }
         // Your Message
         $message = substr($product['name'], 0, 100) . ' ' . $config['web/unsecure/base_url'] . 'index.php/' . $product['url_path']. ' ' . $tags;

         if (
                 $this->getParam("SOCIAL:twitterkey", "") != '' &&
                 $this->getParam("SOCIAL:twittersecret", "") != '' &&
                 $this->getParam("SOCIAL:twitterotoken", "") != '' &&
                 $this->getParam("SOCIAL:twitterosecret", "") != ''
         ) {
            // Send tweet 
            $tweet->post('statuses/update', array('status' => $message));
         }
         if (
                 $this->getParam("SOCIAL:wpurl", "") != '' &&
                 $this->getParam("SOCIAL:wpusername") != '' &&
                 $this->getParam("SOCIAL:wppassword", "") != ''
         ) {
            $client = new IXR_Client($this->getParam("SOCIAL:wpurl", ""));
	    $description = substr($product['description'],0,100);
	    $description .= '<!--more-->';
            $description .= substr($product['description'],100);
            $description .= '<br/><a href="' . $config['web/unsecure/base_url'] . 'index.php/' . $product['url_path'] . '">' . $product['name'] . '</a>';
            $content = array(
                'post_status' => 'draft',
                'post_type' => 'post',
                'post_title' => $product['name'],
                'post_content' => $description,
                'terms' => array('category' => array($this->getParam("SOCIAL:wpcategory_id", "")))
            );
            $params = array(0, $this->getParam("SOCIAL:wpusername", ""), $this->getParam("SOCIAL:wppassword", ""), $content);
            $client->query('wp.newPost', $params);
            $post_id = $client->getResponse();
            
            $command = 'convert "' .$imageDir . $product['image']. '" -resize 604x270\\>  -resample 72 /tmp/storebaby.jpg 2>&1 ';
            exec($command);
            
            $content = array(
                'name' => basename($product['image']),
                'type' => mime_content_type(basename($product['image'])),
                'bits' => new IXR_Base64(file_get_contents('/tmp/storebaby.jpg')),
                true
            );
            $client->query('metaWeblog.newMediaObject', 1, $this->getParam("SOCIAL:wpusername"), $this->getParam("SOCIAL:wppassword"), $content);
            $media = $client->getResponse();
            $content = array(
                'post_status' => 'publish',
                'mt_keywords' => preg_replace('/^[^,]*, /', '', $product['category_names']),
                'wp_post_thumbnail' => $media['id']
            );
            $client->query('metaWeblog.editPost', $post_id, $this->getParam("SOCIAL:wpusername"), $this->getParam("SOCIAL:wppassword"), $content, true);
         }
         $fbConfigFile = __DIR__.'/fbConf.php';
         if (is_file($fbConfigFile)) {
             require $fbConfigFile;
             $baseUrl = $config['web/unsecure/base_url'];
             $fb = new Facebook\Facebook(array(
                        'app_id' => $fbConfig['appId'],
                        'app_secret' => $fbConfig['appSecret'],
                        'default_graph_version' => 'v2.3'
             ));
             foreach($fbConfig['pages'] as $pageId=>$pageToken) {
                   $fb->setDefaultAccessToken($pageToken);
               $linkData = [
		  'link' =>  $baseUrl . 'index.php/' . $product['url_path'],
                  'name' =>  $product['name'] .' ' . $tags,
 		  'message' =>  $product['name'] .' ' . $tags,
                  'picture' => $baseUrl. '/media/catalog/product/' . $product['image']
		  ];
		  try {
		  // Returns a `Facebook\FacebookResponse` object
                  $fb->post('/'. substr($pageId,1,99) .'/feed', $linkData,$pageToken);
                  } catch(Facebook\Exceptions\FacebookResponseException $e) {
                    echo 'Graph returned an error: ' . $e->getMessage();
                  } catch(Facebook\Exceptions\FacebookSDKException $e) {
                    echo 'Facebook SDK returned an error: ' . $e->getMessage();
                  }
            }
	    $this->exec_stmt('REPLACE INTO `catalog_product_entity_int` SET `value`=1 ,
            	`catalog_product_entity_int`.`attribute_id`= '.$shared_on_social_networks_id.',
                `catalog_product_entity_int`.`entity_id` = ' . $product['entity_id'] . '
            ');
         }
         unlink('/tmp/storebaby.jpg');
      }
      $product_stmt->closeCursor();
   }
}
