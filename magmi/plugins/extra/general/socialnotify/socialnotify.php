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
      $product_stmt = $this->exec_stmt('
         SELECT
         `catalog_product_entity`.`entity_id` ,
         `catalog_product_entity`.`sku`
         FROM `catalog_product_entity` 
         LEFT JOIN `catalog_product_entity_int` ON 
         `catalog_product_entity_int`.`entity_id`=`catalog_product_entity`.`entity_id` AND 
         `catalog_product_entity_int`.`attribute_id`= '.$shared_on_social_networks_id.'
         WHERE `catalog_product_entity_int`.`value` IS NULL OR `catalog_product_entity_int`.`value` != 1
         ORDER BY `catalog_product_entity`.`updated_at` DESC
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
            foreach($gogglePages as $gogglePage) {
               $nt = new nxsAPI_GP();
               $loginError = $nt->connect($this->getParam("SOCIAL:gemail", ""), $this->getParam("SOCIAL:gpassword", ""));
               // Image URL
               $lnk = array('img'=>$config['web/unsecure/base_url'] . '/media/catalog/product/' . $product['image']);
               $nt->postGP($product['name'] .' '. $tags . ' ' . $config['web/unsecure/base_url'] . 'index.php/' . $product['url_path'], $lnk, $gogglePage);
               if ($loginError != '')
                  $this->log($config['web/unsecure/base_url'] . 'index.php/' . $product['url_path'] . " not sent succesfully " . $loginError, "info");
            }
         }
         // Your Message
         $message = substr($product['name'], 10, 100) . ' ' . $config['web/unsecure/base_url'] . 'index.php/' . $product['url_path']. ' ' . $tags;

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
         $mail = new PHPMailer;
         foreach ($facebookPages as $facebookPage) {

            if ($config['system/lesti_smtp/enable'] == 1) {

               $mail->isSMTP();
               $mail->Host = $config['system/lesti_smtp/host'];
               if ($config['system/lesti_smtp/username'] != '' && $config['system/lesti_smtp/password'] != '')
                  $mail->SMTPAuth = true;
               $mail->Username = $config['system/lesti_smtp/username'];
               $mail->Password = $config['system/lesti_smtp/password'];
               if ($config['system/lesti_smtp/ssl'] != '')
                  $mail->SMTPSecure = $config['system/lesti_smtp/ssl'];
            }

            $mail->From = $config['trans_email/ident_general/email'];
            $mail->FromName = $config['trans_email/ident_general/name'];
            $mail->addAddress($facebookPage);

            $mail->WordWrap = 50;
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false);
            $url_path = '';
            $mail->Subject = $product['name'] .' ' . $tags . ' ' . $config['web/unsecure/base_url'] . 'index.php/' . $product['url_path'];
            $mail->Body = ' ';
            $mail->addAttachment($imageDir . $product['image']);        
            if ($mail->send()) {
               $this->log($config['web/unsecure/base_url'] . 'index.php/' . $product['url_path'] . " sent succesfully", "info");
               $this->exec_stmt('REPLACE INTO `catalog_product_entity_int` SET `value`=1 ,
                           `catalog_product_entity_int`.`attribute_id`= '.$shared_on_social_networks_id.',
                           `catalog_product_entity_int`.`entity_id` = ' . $product['entity_id'] . '
                            ');
            } else {
               $this->log($config['web/unsecure/base_url'] . 'index.php/' . $product['url_path'] . " not sent succesfully " . $mail->ErrorInfo, "info");
            }
         }
      }
      $product_stmt->closeCursor();
   }
}
