<?php        
  require "postToGooglePlus.php";

  $url = 'http://www.rueckfahrkameras.de/rss_news_cat01.php'; // FEED URL
  $email = 'your@gmail.com';                                  // Google Email
  $pass = '123#test@123';                                     // Google Password
  $okv = "gprssimp-x00001";                                   // OKV TAG - !!!!! Use Uniqe value for each feed  !!!!!
  
  $pageID = '109888164682746252347';                          // Google+ Page ID. Leave empty for Profile
  $isOutput = true;                                           // true if you would like to see output. false if you need it to be silent
  $commonGuid = "http://www.rueckfahrkameras.de/Kamera-Monitorsyteme/";  //  Common Parg for RSS GUID. This will save some OKV space.
  
  //#######################################################################################################################################
  
  function nsFindImgs($txt) { 
    $txt = str_replace("'",'"',$txt); $output = preg_match_all( '/src="([^"]*)"/', $txt, $matches ); if ($output === false){return false;}
    foreach ($matches[1] as $match) { if (!preg_match('/^https?:\/\//', $match ) ) $match = site_url( '/' ) . ltrim( $match, '/' ); $postImgs[] = $match;}  return $postImgs;
  }  
  function nxs_saveToOKV($okv, $val){$okvVal = getCurlPageX('http://api.openkeyval.org/'.$okv, '', true, 'data='.urlencode($val)); return $okvVal;}
  function nxs_loadFromOKV($okv){ $okvVal = getCurlPageX('http://api.openkeyval.org/'.$okv, '', true); $okvValE = json_decode($okvVal, true);
    if (is_array($okvValE) && isset($okvValE['error'])) return false; else return $okvVal;
  }
  
  //$ret = nxs_saveToOKV($okv, ''); //##### If you ever need to reset/reimport
  
  $postedItemsList = nxs_loadFromOKV($okv); prr($postedItemsList);
  if ($postedItemsList!='') $postedItemsList = explode("\n",$postedItemsList); else $postedItemsList = array();  prr($postedItemsList);
  
 
  global $nxs_gCookiesArr;
  $loginError = doConnectToGooglePlus2($email, $pass); 
  if (!$loginError)
  { 
    if ($isOutput) echo "### Logged In - No Problems<br/>";
    $doc = new DOMdocument();
    $doc->load($url);
    $rss_array = array();
    $items = array();
    $tag = 'item';
    //## Reversing RSS
    foreach($doc->getElementsByTagName($tag) as $node) 
    {    
        $rss_array[] = $node;
    }
    $rss_array = array_reverse($rss_array);
    //## Importing
    foreach($rss_array as $node) 
    {    
      $postDate = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;  
      $title = $node->getElementsByTagName('title')->item(0)->nodeValue;      
      $guid = $node->getElementsByTagName('guid')->item(0)->nodeValue;  
      $guid = str_ireplace($commonGuid, "", $guid);
      
      if ($isOutput) echo "### Found Item: ".$title."<br/>";       
      if (in_array($guid, $postedItemsList)) { if ($isOutput) echo "&nbsp;&nbsp;&nbsp;&nbsp;### ".$guid." ******* Skipped<br/>"; continue; }      
      
      $link = $node->getElementsByTagName('link')->item(0)->nodeValue;      
      $msg = $node->getElementsByTagName('description')->item(0)->nodeValue;
      $msg = str_ireplace('<br />','___NN_BRGG____',$msg);    
      $msg = str_ireplace("&deg;","&#176;",$msg); $msg = str_ireplace("&uuml;","&#220;",$msg);
      $msg = str_ireplace("&szlig;","&#223;",$msg); $msg = str_ireplace("&nbsp;","&#160;",$msg);
      $msg = str_ireplace("\n","",$msg);
      $msg = str_ireplace("\r","",$msg);
      $msg = str_ireplace("  "," ",$msg);
      $msg = str_ireplace("  "," ",$msg);
      $imgURL = nsFindImgs($msg); 
      if (is_array($imgURL)) $imgURL = $imgURL[0];
      $msg = utf8_encode(html_entity_decode(trim(strip_tags($msg))));
      $msg = str_ireplace('___NN_BRGG____', '<br/>', $msg);
      $lnk = doGetGoogleUrlInfo2($link); $lnk['img'] = $imgURL; 
      
      
      doPostToGooglePlus2($msg, $lnk, $pageID);
      if ($isOutput) echo "&nbsp;&nbsp;&nbsp;&nbsp;### Imported: ".$title."<br/>";
      $postedItemsList[] = $guid;        
      sleep(rand(2, 10)); // Sleep some random time - Just a precaution.
    }
    $postedItemsList = implode("\n",$postedItemsList);
    $ret = nxs_saveToOKV($okv, $postedItemsList);  //die();
  } else echo $loginError; 
?>