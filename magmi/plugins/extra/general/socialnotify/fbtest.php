<?php 
ini_set('display_errors',1);
require __DIR__."/wp/formatting.php";
require __DIR__."/wp/functions.php";
require __DIR__."/wp/plugin.php";
require __DIR__."/NextScripts_APIs/postToGooglePlus.php";
require __DIR__.'/Facebook/Facebook.php';
require __DIR__.'/Facebook/FacebookApp.php';
require __DIR__.'/Facebook/FacebookClient.php';
require __DIR__.'/Facebook/HttpClients/FacebookCurl.php';
require __DIR__.'/Facebook/HttpClients/FacebookHttpClientInterface.php';
require __DIR__.'/Facebook/HttpClients/FacebookCurlHttpClient.php';
require __DIR__.'/Facebook/Authentication/AccessToken.php';
require __DIR__.'/Facebook/FacebookRequest.php';
require __DIR__.'/Facebook/Url/FacebookUrlManipulator.php';
require __DIR__.'/Facebook/Http/RequestBodyInterface.php';
require __DIR__.'/Facebook/Http/RequestBodyUrlEncoded.php';
require __DIR__.'/Facebook/Http/RequestBodyMultipart.php';
require __DIR__.'/Facebook/Http/GraphRawResponse.php';
require __DIR__.'/Facebook/FacebookResponse.php';
require __DIR__.'/Facebook/GraphNodes/GraphObjectFactory.php';
require __DIR__.'/Facebook/GraphNodes/Collection.php';
require __DIR__.'/Facebook/GraphNodes/GraphObject.php';
require __DIR__.'/Facebook/GraphNodes/GraphList.php';
require __DIR__.'/Facebook/Exceptions/FacebookSDKException.php';
require __DIR__.'/Facebook/Exceptions/FacebookResponseException.php';
require __DIR__.'/Facebook/Exceptions/FacebookAuthorizationException.php';
require __DIR__.'/Facebook/Exceptions/FacebookAuthenticationException.php';

$fbConfig = array();
$fbConfig['appId'] = '768074056624586';
$fbConfig['appSecret'] = '214351f71348c3fb0f092d05fffdc8bb';
$fbConfig['access_token'] = 'CAAK6jy1OacoBAEiU02CYZCw13uTLTwF7zfQ38fvNFfTLsFV1gzZC2roXuYRnH6wJTBZCyEOQz7kWvY8tgCehtukTFHH8fis1ZCEw3MtOC6OvgOd0P6o3MrI6IZC9ZC5ZB2T3eLeYKo3d7eY5HIXqFcpawpYEhbpb7piIDYJCcWCBUxQyXA0KVlRZCdDS6sZCPx8AZD';


$fb = new \Facebook\Facebook(array(
                        'app_id' => $fbConfig['appId'],
                        'app_secret' => $fbConfig['appSecret'],
                        'default_graph_version' => 'v2.3'
             ));
// define your POST parameters (replace with your own values)
$params = array(
  "message" => "Here is a blog post about auto posting on Facebook using PHP #php #facebook",
  "link" => "http://www.pontikis.net/blog/auto_post_on_facebook_with_php",
  "picture" => "http://i.imgur.com/lHkOsiH.png",
  "name" => "How to Auto Post on Facebook with PHP",
  "caption" => "www.pontikis.net",
  "description" => "Automatically post on Facebook with PHP using Facebook PHP SDK. How to create a Facebook app. Obtain and extend Facebook access tokens. Cron automation."
);
$ret = $fb->post('/620666404637761/feed', $params,$fbConfig['access_token']);