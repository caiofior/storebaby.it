<?php
define('ABSPATH',__DIR__.'/wp/');
if (!isset($_SERVER))
   $_SERVER=array();
if (!key_exists('SERVER_NAME',$_SERVER))
   $_SERVER['SERVER_NAME']='';
require "wp/formatting.php";
require "wp/functions.php";
require "wp/plugin.php";
require "NextScripts_APIs/postToGooglePlus.php";
require "twitteroauth-master/twitteroauth/twitteroauth.php";

class SocialNotifyPlugin extends Magmi_GeneralImportPlugin
{

	
public function getPluginInfo()
	{
		return array(
            "name" => "Social notify of new records",
            "author" => "caiofior",
            "version" => "0.1"
            );
	}
	
	public function getPluginParams($params)
	{
		$pp=array();
		foreach($params as $k=>$v)
		{
			if(preg_match("/^SOCIAL:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}
		return $pp;
	}
	public function afterImport()
	{
            if (!function_exists('PHPMailerAutoload'))
               require __DIR__.DIRECTORY_SEPARATOR.'phpmailer'.DIRECTORY_SEPARATOR.'PHPMailerAutoload.php';
            $config = array();
            foreach($this->selectAll(
                    'SELECT `path`,`value` FROM `core_config_data`
                     WHERE `path` LIKE "%/lesti_smtp/%" OR `path` LIKE "%/ident_general/%" OR `path` = "web/unsecure/base_url"') as $value) {
                $config [$value['path']]=$value['value'];
            }
            $imageDir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'media'.DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR.'product'.DIRECTORY_SEPARATOR;
            
            $imageDir = realpath($imageDir);
            $products = $this->selectAll(
                '   SELECT `catalog_product_entity`.`entity_id` ,
                    `catalog_product_entity`.`sku` ,
                    (SELECT `value` FROM `catalog_product_entity_varchar` WHERE
                        `catalog_product_entity_varchar`.`entity_id`=`catalog_product_entity`.`entity_id` AND
                        `catalog_product_entity_varchar`.`attribute_id`= 
                            (SELECT `attribute_id` FROM `eav_attribute` WHERE
                            `attribute_code`="name" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_product"
                                )
                            )
                    LIMIT 1
                    ) as name,
                    (SELECT `value` FROM `catalog_product_entity_varchar` WHERE
                        `catalog_product_entity_varchar`.`entity_id`=`catalog_product_entity`.`entity_id` AND
                        `catalog_product_entity_varchar`.`attribute_id`= 
                            (SELECT `attribute_id` FROM `eav_attribute` WHERE
                            `attribute_code`="url_key" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_product"
                                )
                            )
                    LIMIT 1
                    ) as url_key ,
                    (SELECT `value` FROM `catalog_product_entity_varchar` WHERE
                        `catalog_product_entity_varchar`.`entity_id`=`catalog_product_entity`.`entity_id` AND
                        `catalog_product_entity_varchar`.`attribute_id`= 
                            (SELECT `attribute_id` FROM `eav_attribute` WHERE
                            `attribute_code`="image" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_product"
                                )
                            )
                    LIMIT 1
                    ) as image ,
                    (SELECT `request_path` FROM `core_url_rewrite`
                        WHERE `id_path` LIKE "product/%" AND `product_id` = `catalog_product_entity`.`entity_id`
                        ORDER BY `category_id` ASC LIMIT 1
                    ) as url_path
                    FROM `catalog_product_entity` 
                    LEFT JOIN `catalog_product_entity_int` ON 
                    `catalog_product_entity_int`.`entity_id`=`catalog_product_entity`.`entity_id` AND 
                    `catalog_product_entity_int`.`attribute_id`= (SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code`="shared_on_social_networks")
                    WHERE `catalog_product_entity_int`.`value` IS NULL OR `catalog_product_entity_int`.`value` != 1
                    HAVING `url_path` <> ""
                    ORDER BY `catalog_product_entity`.`updated_at` DESC 
                LIMIT '.$this->getParam("SOCIAL:topost","10"));

                  $consumerKey = 'WDnbLyP6L94K46hYMF9ourq1W';
                  $consumerSecret = '5M1QUbc2U8p7y9FHE6RXmTQuITlPW1v5EI0mUbARnLLjGwKL0G';
                  $OAuthToken = '2287014847-QcQTqdhaun1U8X6djQgqk6xoDhwnmgpQXuqlOSo';
                  $OAuthSecret = 'WaDHHycx1M9nUOufgQxseT0AzhAPCxnKN5fHwYMQeziTV';

                  // Full path to twitterOAuth.php (change OAuth to your own path)


                  // create new instance
                  $tweet = new TwitterOAuth(
                          $this->getParam("SOCIAL:twitterkey",""),
                          $this->getParam("SOCIAL:twittersecret",""),
                          $this->getParam("SOCIAL:twitterotoken",""),
                          $this->getParam("SOCIAL:twitterosecret","")
                        );

            foreach ($products as $product) {
                if (!is_file($imageDir.$product['image']))
                        continue;

                 $loginError = doConnectToGooglePlus2($this->getParam("SOCIAL:gemail",""), $this->getParam("SOCIAL:gpassword",""));
                 if (!$loginError) {
                  // Image URL
                  $lnk = array('img'=>$config['web/unsecure/base_url'].'/media/catalog/product/'.$product['image']);
                  doPostToGooglePlus2($product['name']. ' '.$config['web/unsecure/base_url'].'index.php/'.$product['url_path'], $lnk, $this->getParam("SOCIAL:gpage",""));
                  } 
                  else 
                      $this->log($config['web/unsecure/base_url'].'index.php/'.$product['url_path']." not sent succesfully ". $loginError,"info");
                  
                  // Your Message
                  $message = substr($product['name'],10,100). ' '.$config['web/unsecure/base_url'].'index.php/'.$product['url_path'];

                  // Send tweet 
                  $tweet->post('statuses/update', array('status' => $message));
                  
                

                $mail = new PHPMailer;
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
                $mail->addAddress($this->getParam("SOCIAL:facebook",""));

                $mail->WordWrap = 50;
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(false);
                            $url_path = '';
                $mail->Subject = $product['name']. ' '.$config['web/unsecure/base_url'].'index.php/'.$product['url_path'];
                $mail->Body = ' ';
                $mail->addAttachment($imageDir.$product['image']);
                if($mail->send()) {
                    $this->log($config['web/unsecure/base_url'].'index.php/'.$product['url_path']." sent succesfully","info");
                    $this->exec_stmt('REPLACE INTO `catalog_product_entity_int` SET `value`=1 ,
                        `catalog_product_entity_int`.`attribute_id`= (SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code`="shared_on_social_networks"),
                        `catalog_product_entity_int`.`entity_id` = '.$product['entity_id'].'
                         ');
                } else {
                    $this->log($config['web/unsecure/base_url'].'index.php/'.$product['url_path']." not sent succesfully ". $mail->ErrorInfo,"info");
                }
            }
	}

}