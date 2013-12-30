<?php
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
			if(preg_match("/^EMAILREP:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}
		return $pp;
	}
	public function beforeImport()
	{
            if (!function_exists('PHPMailerAutoload'))
               require __DIR__.DIRECTORY_SEPARATOR.'phpmailer'.DIRECTORY_SEPARATOR.'PHPMailerAutoload.php';

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
                            `attribute_code`="url_path" AND `entity_type_id`= 
                                (SELECT `entity_type_id` FROM  `eav_entity_type` WHERE
                                `entity_type_code`="catalog_product"
                                )
                            )
                    LIMIT 1
                    ) as url_path ,
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
                    ) as image
                    FROM `catalog_product_entity` 
                    LEFT JOIN `catalog_product_entity_int` ON 
                    `catalog_product_entity_int`.`entity_id`=`catalog_product_entity`.`entity_id` AND 
                    `catalog_product_entity_int`.`attribute_id`= (SELECT `attribute_id` FROM `eav_attribute` WHERE `attribute_code`="shared_on_social_networks")
                    WHERE `catalog_product_entity_int`.`value` IS NULL OR `catalog_product_entity_int`.`value` != 1
                    ORDER BY `catalog_product_entity`.`updated_at` DESC 
                LIMIT 10');
            var_dump($products);
            die('HI');
	}

}