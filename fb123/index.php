<?php
$baseDir = __DIR__.'/../magmi/plugins/extra/general/socialnotify/';
require $baseDir.'/Facebook/Facebook.php';
require $baseDir.'/Facebook/FacebookApp.php';
require $baseDir.'/Facebook/FacebookClient.php';
require $baseDir.'/Facebook/FacebookRequest.php';
require $baseDir.'/Facebook/HttpClients/FacebookCurl.php';
require $baseDir.'/Facebook/HttpClients/FacebookHttpClientInterface.php';
require $baseDir.'/Facebook/HttpClients/FacebookCurlHttpClient.php';
require $baseDir.'/Facebook/Authentication/AccessToken.php';
require $baseDir.'/Facebook/FacebookRequest.php';
require $baseDir.'/Facebook/Url/FacebookUrlManipulator.php';
require $baseDir.'/Facebook/Http/RequestBodyInterface.php';
require $baseDir.'/Facebook/Http/RequestBodyUrlEncoded.php';
require $baseDir.'/Facebook/Http/GraphRawResponse.php';
require $baseDir.'/Facebook/FacebookResponse.php';
require $baseDir.'/Facebook/GraphNodes/GraphObjectFactory.php';
require $baseDir.'/Facebook/GraphNodes/Collection.php';
require $baseDir.'/Facebook/GraphNodes/GraphObject.php';
require $baseDir.'/Facebook/GraphNodes/GraphList.php';
require $baseDir.'/Facebook/Exceptions/FacebookSDKException.php';
require $baseDir.'/Facebook/Exceptions/FacebookResponseException.php';
require $baseDir.'/Facebook/Exceptions/FacebookAuthorizationException.php';

$appId = '768074056624586';
$appSecret='214351f71348c3fb0f092d05fffdc8bb';
$config = array(
  'appId'=>$appId,
  'appSecret'=>$appSecret,
  'pages'=>array()
);
if (array_key_exists('authResponse', $_REQUEST)) {
    $fb = new Facebook\Facebook(array(
            'app_id' => $appId,
            'app_secret' => $appSecret,
            'default_graph_version' => 'v2.3',
            'default_access_token' =>$_REQUEST['authResponse']['accessToken']
   ));
   $userProfile = $fb->get('/me')->getGraphUser()->asArray();
   $config['userId']=$userProfile['id'];
   $pages = $fb->get('/me/accounts')->getGraphList();
   foreach ($pages as $page) {
     $page = $page->asArray();
     echo 'Name:<br/>';     
     echo $page['name'].'<br/>';
     echo 'Id:<br/>';     
     echo $page['id'].'<br/>';
     echo 'Access token:<br/>';     
     echo $page['access_token'].'<br/>';
     if ($page['id'] == '620666404637761' || $page['id'] == '1474517576133620') {
     $linkData = [
        'grant_type' => 'fb_exchange_token',
        'client_id' => $appId,
        'client_secret' => $appSecret,
        'fb_exchange_token' => $page['access_token']
     ];
     $response = $fb->post('/oauth/access_token',$linkData)->getGraphObject()->asArray();
     $config['pages']['p'.(string)$page['id']]=$response['access_token'];

	
     
     /**
		  $linkData = [
		  'link' => 'http://www.example.com',
		  'message' => 'User provided message',
		  ];
		  try {
		  // Returns a `Facebook\FacebookResponse` object
		  $response = $fb->post('/'. $page['id'] .'/feed', $linkData, $page['access_token']);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
	**/


     }
   }
   file_put_contents($baseDir.'/fbConf.php','<?php'.PHP_EOL.'$fbConfig='.var_export($config,true).';');
}
?><!doctype html>
<html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Facebook Test Page</title>
</head>
<body>
        <div id="fb-root"></div>
        <fb:login-button scope="public_profile,email,manage_pages,publish_actions,offline_access,read_stream,publish_stream,publish_pages,status_update " onlogin="checkLoginState();"></fb:login-button>
        <div id="fbstatus"></div>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script>
            (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/it_IT/all.js#xfbml=1&appId=<?php echo $appId; ?>";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
    function statusChangeCallback(response) {
    $("#fbstatus").empty();
    $.ajax("#", {
        async:false,
        cache: true,
        method:"post",
        data:response,
        success: function (data) {
            $("#fbstatus").html(data);
        },
        error: function (jqXHR, textStatus,errorThrown) {
            console.error(textStatus+" "+errorThrown);
         }
    });  
  }
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
        </script>
</body>
</html>
