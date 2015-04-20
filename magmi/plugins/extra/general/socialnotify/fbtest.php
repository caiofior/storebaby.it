<?php
require "wp/formatting.php";
require "wp/functions.php";
require "wp/plugin.php";
require "NextScripts_APIs/postToGooglePlus.php";
require "facebook-php-sdk/autoload.php";

$config = array();
$config['appId'] = '614363568708508';
$config['secret'] = '79c8c0b73df09d94fb7837d86e4dab5b';
$config['fileUpload'] = false; // optional

$fb = new Facebook($config);
 
// define your POST parameters (replace with your own values)
$params = array(
  "access_token" => "YOUR_ACCESS_TOKEN", // see: https://developers.facebook.com/docs/facebook-login/access-tokens/
  "message" => "Here is a blog post about auto posting on Facebook using PHP #php #facebook",
  "link" => "http://www.pontikis.net/blog/auto_post_on_facebook_with_php",
  "picture" => "http://i.imgur.com/lHkOsiH.png",
  "name" => "How to Auto Post on Facebook with PHP",
  "caption" => "www.pontikis.net",
  "description" => "Automatically post on Facebook with PHP using Facebook PHP SDK. How to create a Facebook app. Obtain and extend Facebook access tokens. Cron automation."
);
$ret = $fb->api('/620666404637761/feed', 'POST', $params);