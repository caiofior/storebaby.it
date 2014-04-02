<?php
/*#############################################################################
Project Name: NextScripts Google+ AutoPoster
Project URL: http://www.nextscripts.com/google-plus-automated-posting
Description: Automatically posts to your Google+ profile and/or Google+ page.
Author: NextScripts, Inc
Version: 2.14.5 (Mar 27, 2014)
Author URL: http://www.nextscripts.com
Copyright 2012-2013  Next Scripts, Inc
#############################################################################*/
require_once "nxs-http.php"; 
if (!function_exists('prr')){ function prr($str) { echo "<pre>"; print_r($str); echo "</pre>\r\n"; }}        
//## Code - General Functions
if (!function_exists("CutFromTo")) {function CutFromTo($string, $from, $to) {$fstart = stripos($string, $from); $tmp = substr($string,$fstart+strlen($from));$flen = stripos($tmp, $to);  return substr($tmp,0, $flen); }}
if (!function_exists("getUqID")) {function getUqID() {return mt_rand(0, 9999999);}}
if (!function_exists("build_http_query")) {function build_http_query( $query ){ $query_array = array(); foreach( $query as $key => $key_value ){ $query_array[] = $key . '=' . urlencode( $key_value );} return implode( '&', $query_array );}}
if (!function_exists("rndString")) {function rndString($lngth){$str='';$chars="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";$size=strlen($chars);for($i=0;$i<$lngth;$i++){$str .= $chars[rand(0,$size-1)];} return $str;}}
if (!function_exists("prcGSON")) {function prcGSON($gson){ $json = substr($gson, 5); $json = str_replace(',{',',{"',$json); $json = str_replace(':[','":[',$json); $json = str_replace(',{""',',{"',$json); $json = str_replace('"":[','":[',$json); 
  $json = str_replace('[,','["",',$json); $json = str_replace(',,',',"",',$json); $json = str_replace(',,',',"",',$json); return $json; 
}}
if (!function_exists("nxsCheckSSLCurl")){function nxsCheckSSLCurl($url){
  $ch = curl_init($url); $headers = array(); $headers[] = 'Accept: text/html, application/xhtml+xml, */*'; $headers[] = 'Cache-Control: no-cache';
  $headers[] = 'Connection: Keep-Alive'; $headers[] = 'Accept-Language: en-us';  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)"); 
  $content = curl_exec($ch); $err = curl_errno($ch); $errmsg = curl_error($ch); if ($err!=0) return array('errNo'=>$err, 'errMsg'=>$errmsg); else return false;
}}
if (!function_exists("cookArrToStr")){function cookArrToStr($cArr){ $cs = ''; if (!is_array($cArr)) return ''; foreach ($cArr as $cName=>$cVal){ $cs .= $cName.'='.$cVal.'; '; } return $cs; }}
if (!function_exists("getCurlPageMC")){function getCurlPageMC($ch, $ref='', $ctOnly=false, $fields='', $dbg=false, $advSettings='') { $ccURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
  if ($dbg) echo '<br/><b style="font-size:16px;color:green;">#### START CURL:'.$ccURL.'</b><br/>'; 
  static $curl_loops = 0; static $curl_max_loops = 20; global $nxs_gCookiesArr, $nxs_gCookiesArrBD; $cookies =  cookArrToStr($nxs_gCookiesArr); if ($dbg) { echo '<br/><b style="color:#005800;">## Request Cookies:</b><br/>'; prr($cookies);}
  if ($curl_loops++ >= $curl_max_loops){ $curl_loops = 0; return false; }
  $headers = array(); $headers[] = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; $headers[] = 'Cache-Control: no-cache';
  $headers[] = 'Connection: Keep-Alive';  $headers[] = 'Accept-Language: en-US,en;q=0.8';// $headers[] = 'Accept-Encoding: gzip, deflate';   
  
  if (isset($advSettings['Content-Type'])) $headers[] = 'Content-Type: '.$advSettings['Content-Type']; else 
    if ($fields!='') { if((stripos($ccURL, 'www.blogger.com/blogger_rpc')!==false)) $headers[] = 'Content-Type: application/javascript; charset=UTF-8'; else $headers[] = 'Content-Type: application/x-www-form-urlencoded;charset=utf-8';}  
  if (stripos($ccURL, 'www.blogger.com/blogger_rpc')!==false) {$headers[] = 'X-GWT-Permutation: 0408F3763409DF91729BBA5B25869425';
    $headers[] = 'X-GWT-Module-Base: https://www.blogger.com/static/v1/gwt/';    
  }
  if (isset($advSettings['liXMLHttpRequest'])) $headers[] = 'X-Requested-With: XMLHttpRequest';
  if (isset($advSettings['Origin'])) $headers[] = 'Origin: '.$advSettings['Origin'];    
  if (stripos($ccURL, 'blogger.com')!==false && (isset($advSettings['cdomain']) &&  $advSettings['cdomain']=='google.com') ) $advSettings['cdomain']='blogger.com';
  if(isset($advSettings['noSSLSec'])){curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0); curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); } 
  if(isset($advSettings['proxy']) && $advSettings['proxy']['host']!='' && $advSettings['proxy']['port']!==''){
    if ($dbg) { echo '<br/><b style="color:#005800;">## Using Proxy:</b><br/>'; /*prr($advSettings); */}
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
    curl_setopt( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP ); curl_setopt( $ch, CURLOPT_PROXY, $advSettings['proxy']['host'] ); curl_setopt( $ch, CURLOPT_PROXYPORT, $advSettings['proxy']['port'] );
    if ( isset($advSettings['proxy']['up']) && $advSettings['proxy']['up']!='' ) { curl_setopt( $ch, CURLOPT_PROXYAUTH, CURLAUTH_ANY ); curl_setopt( $ch, CURLOPT_PROXYUSERPWD, $advSettings['proxy']['up'] );}
  }
  if(isset($advSettings['headers'])){$headers = array_merge($headers, $advSettings['headers']);}  // prr($advSettings);
  curl_setopt($ch, CURLOPT_HEADER, true);     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_COOKIE, $cookies); curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // prr($headers);
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);  if (is_string($ref) && $ref!='') curl_setopt($ch, CURLOPT_REFERER, $ref); 
  if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) { curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); } 
  curl_setopt($ch, CURLOPT_USERAGENT, (( isset( $advSettings['UA']) && $advSettings['UA']!='')?$advSettings['UA']:"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.44 Safari/537.36")); 
  if ($fields!=''){ curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $fields); } else { curl_setopt($ch, CURLOPT_POST, false); curl_setopt($ch, CURLOPT_POSTFIELDS, '');  curl_setopt($ch, CURLOPT_HTTPGET, true); } 
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  $content = curl_exec($ch); //prr($content);  
  $errmsg = curl_error($ch);  if (isset($errmsg) && stripos($errmsg, 'SSL')!==false) { curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  $content = curl_exec($ch); }
  if (strpos($content, "\n\n")!=false && strpos($content, "\n\n")<100)  $content = substr_replace($content, "\n", strpos($content,"\n\n"), strlen("\n\n"));    
  if (strpos($content, "\r\n\r\n")!=false && strpos($content, "\r\n\r\n")<100) $content = substr_replace($content, "\r\n", strpos($content,"\r\n\r\n"), strlen("\r\n\r\n"));
  $ndel = strpos($content, "\n\n"); $rndel = strpos($content, "\r\n\r\n"); if ($ndel==false) $ndel = 1000000; if ($rndel==false) $rndel = 1000000; $rrDel = $rndel<$ndel?"\r\n\r\n":"\n\n";   
  @list($header, $content) = explode($rrDel, $content, 2);
  if ($ctOnly!==true) { $nsheader = curl_getinfo($ch); $err = curl_errno($ch); $errmsg = curl_error($ch); $nsheader['errno'] = $err;  $nsheader['errmsg'] = $errmsg;  $nsheader['headers'] = $header; $nsheader['content'] = $content; }
  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); $headers = curl_getinfo($ch); if ($dbg) { echo '<br/><b style="color:#005800;">## Headers:</b><br/>';  prr($headers); prr($header);} 
  if (empty($headers['request_header'])) $headers['request_header'] = 'Host: None'."\n";
  $results = array(); preg_match_all('|Host: (.*)\n|U', $headers['request_header'], $results); $ckDomain = str_replace('.', '_', $results[1][0]);  $ckDomain = str_replace("\r", "", $ckDomain); $ckDomain = str_replace("\n", "", $ckDomain);
  if ($dbg) { echo '<br/><b style="color:#005800;">## Domain:</b><br/>'; prr($ckDomain); } 
  
  $results = array(); $cookies = '';  preg_match_all('|Set-Cookie: (.*);|U', $header, $results); $carTmp = $results[1]; //$nxs_gCookiesArr = array_merge($nxs_gCookiesArr, $ret['cookies']); 
  preg_match_all('/Set-Cookie: (.*)\b/', $header, $xck); $xck = $xck[1]; if ($dbg) { echo "Full Resp Cookies"; prr($xck); echo "Plain Resp Cookies"; prr($carTmp); }
  //$clCook = array();
  if (isset($advSettings['cdomain']) &&  $advSettings['cdomain']!=''){
      foreach ($carTmp as $iii=>$cTmp) if (stripos($xck[$iii],'Domain=')===false || stripos($xck[$iii],'Domain=.'.$advSettings['cdomain'].';')!==false){ $ttt = explode('=',$cTmp,2); $nxs_gCookiesArr[$ttt[0]]=$ttt[1];  }
  } else { foreach ($carTmp as $cTmp){ $ttt = explode('=',$cTmp,2); $nxs_gCookiesArr[$ttt[0]]=$ttt[1];}}   
  foreach ($carTmp as $cTmp){ $ttt = explode('=',$cTmp,2); $nxs_gCookiesArrBD[$ckDomain][$ttt[0]]=$ttt[1]; }  
  if ($dbg) { echo '<br/><b style="color:#005800;">## Common/Response Cookies:</b><br/>'; prr($nxs_gCookiesArr); echo "\r\n\r\n<br/>".$ckDomain."\r\n\r\n"; prr($nxs_gCookiesArrBD); }
  if ($dbg && $http_code == 200){  $contentH = htmlentities($content);    prr($contentH);  } $rURL = '';
  
  if ($http_code == 200 && stripos($content, 'http-equiv="refresh" content="0; url=&#39;')!==false ) {
    $http_code=301; $rURL = CutFromTo($content, 'http-equiv="refresh" content="0; url=&#39;','&#39;"'); 
    if (stripos($rURL, 'blogger.com')===false) $nxs_gCookiesArr = array(); 
  } 
  elseif ($http_code == 200 && stripos($content, 'location.replace')!==false ) {$http_code=301; $rURL = CutFromTo($content, 'location.replace("','"'); }// echo "~~~~~~~~~~~~~~~~~~~~~~".$rURL."|".$http_code;
  if ($http_code == 301 || $http_code == 302 || $http_code == 303){  
    if ($rURL!='') { $rURL = str_replace('\x3d','=',$rURL); $rURL = str_replace('\x26','&',$rURL);
      $url = @parse_url($rURL); } else { $matches = array(); preg_match('/Location:(.*?)\n/', $header, $matches); $url = @parse_url(trim(array_pop($matches))); } $rURL = ''; //echo "#######"; prr($url);
    if (!$url){ $curl_loops = 0; return ($ctOnly===true)?$content:$nsheader;}
    $last_urlX = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); $last_url = @parse_url($last_urlX);
    if (!isset($url['scheme'])) $url['scheme'] = $last_url['scheme'];  if (!isset($url['host'])) $url['host'] = $last_url['host'];  if (!$url['path']) $url['path'] = $last_url['path']; if (!isset($url['query'])) $url['query'] = '';
    $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:''); curl_setopt($ch, CURLOPT_URL, $new_url);
    if ($dbg) echo '<br/><b style="color:#005800;">Redirecting to:</b>'.$new_url."<br/>"; return getCurlPageMC($ch, $last_urlX, $ctOnly, '', $dbg, $advSettings); 
  } else { $curl_loops=0; return ($ctOnly===true)?$content:$nsheader;}
}}
if (!function_exists("getCurlPageX")){function getCurlPageX($url, $ref='', $ctOnly=false, $fields='', $dbg=false, $advSettings='') { if ($dbg) echo '<br/><b style="font-size:16px;color:green;">#### GSTART URL:'.$url.'</b><br/>'; 
  $ch = curl_init($url); $contents = getCurlPageMC($ch, $ref, $ctOnly, $fields, $dbg, $advSettings); curl_close($ch); return $contents;
}}
if (!function_exists("nxs_clFN")){ function nxs_clFN($fn){$sch = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
  return trim(preg_replace('/[\s-]+/', '-', str_replace($sch, '', $fn)), '.-_');    
}}
if (!function_exists("nxs_mkImgNm")){ function nxs_mkImgNm($fn, $cType){ $iex = array(".png", ".jpg", ".gif", ".jpeg"); $map = array('image/gif'=>'.gif','image/jpeg'=>'.jpg','image/png'=>'.png');
  $fn = str_replace($iex, '', $fn); if (isset($map[$cType])){return $fn.$map[$cType];} else return $fn.".jpg";    
}}
//## Google
// Back Version 1.x Compatibility
if (!function_exists("doConnectToGooglePlus")) {function doConnectToGooglePlus($connectID, $email, $pass){ return doConnectToGooglePlus2($email, $pass);}}
if (!function_exists("doGetGoogleUrlInfo")) {function doGetGoogleUrlInfo($connectID, $url){ return doGetGoogleUrlInfo2($url);}}
if (!function_exists("doPostToGooglePlus")) {function doPostToGooglePlus($connectID, $msg, $lnk='', $pageID=''){ return doPostToGooglePlus2($msg, $lnk, $pageID);}}
// Back Version 2.x Compatibility
if (!function_exists("doConnectToGooglePlus2")) {function doConnectToGooglePlus2($email, $pass, $srv='GP', $iidb=0){ global $nxs_plurl, $nxs_gCookiesArr, $plgn_NS_SNAutoPoster;   
  if (isset($plgn_NS_SNAutoPoster)) { $options = $plgn_NS_SNAutoPoster->nxs_options; if (isset($options['li'][$iidb]['ck'])) $ck = maybe_unserialize($options['li'][$iidb]['ck']); } else $ck = array();
  $li = new nxsAPI_GP(); $li->debug = false; if (!empty($ck)) $li->ck = $ck;  $loginErr = $li->connect($email, $pass, $srv);  $nxs_gCookiesArr = $li->ck; 
  if (isset($plgn_NS_SNAutoPoster) && !empty($options)) { if (!$loginErr){ $options['li'][$iidb]['ck'] = $li->ck; 
    if(is_array($options)) { update_option('NS_SNAutoPoster', $options); $plgn_NS_SNAutoPoster->nxs_options = $options; }}
  } return $loginErr;
}}
if (!function_exists("doGetGoogleUrlInfo2")) {function doGetGoogleUrlInfo2($url){ global $nxs_gCookiesArr; 
  $nt = new nxsAPI_GP(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) $nt->ck = $nxs_gCookiesArr; return $nt->urlInfo($url);      
}}
if (!function_exists("doGetCCatsFromGooglePlus")) {function doGetCCatsFromGooglePlus($commPageID){ global $nxs_gCookiesArr; 
  $nt = new nxsAPI_GP(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) $nt->ck = $nxs_gCookiesArr; return $nt->getCCatsGP($commPageID);      
}}
if (!function_exists("doPostToGooglePlus2")) {function doPostToGooglePlus2($msg, $lnk='', $pageID='', $commPageID='', $commPageCatID=''){ global $nxs_gCookiesArr;  
  $nt = new nxsAPI_GP(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) $nt->ck = $nxs_gCookiesArr; return $nt->postGP($msg, $lnk, $pageID, $commPageID, $commPageCatID);    
}}

if (!function_exists("doConnectToBlogger")){function doConnectToBlogger($email, $pass){ return doConnectToGooglePlus2($email, $pass, 'BG'); }}
if (!function_exists("doPostToBlogger")) {function doPostToBlogger($blogID, $title, $msg, $tags=''){ global $nxs_gCookiesArr;  
  $nt = new nxsAPI_GP(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) $nt->ck = $nxs_gCookiesArr; return $nt->postBG($blogID, $title, $msg, $tags);    
}}
if (!function_exists("doPostToYouTube")) {function doPostToYouTube($msg, $ytUrl, $vURL = '', $ytGPPageID=''){ global $nxs_gCookiesArr;  
  $nt = new nxsAPI_GP(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) $nt->ck = $nxs_gCookiesArr; return $nt->postYT($msg, $ytUrl, $vURL, $ytGPPageID);    
}}
//================================GOOGLE===========================================
if (!class_exists('nxsAPI_GP')){ class nxsAPI_GP{ var $ck = array(); var $debug = false;
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array(); 
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.22 Safari/537.36'; 
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded'; 
        elseif($type=='JS') $hdrsArr['Content-Type']='application/javascript; charset=UTF-8'; elseif($type=='PUT') $hdrsArr['Content-Type']='application/octet-stream';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org; 
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='gzip,deflate,sdch'; 
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;         
    }
    function check(){ $ck = $this->ck;  if (!empty($ck) && is_array($ck)) { } return false; }
    function connect($u,$p,$srv='GP'){ $sslverify = true; if ($this->debug) echo "[".$srv."] L to: ".$srv."<br/>\r\n";
        $err = nxsCheckSSLCurl('https://www.google.com'); if ($err!==false && $err['errNo']=='60') $sslverify = false;  
        if ($srv == 'GP') $lpURL = 'https://accounts.google.com/ServiceLogin?service=oz&continue=https://plus.google.com/?gpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&hl=en-US'; 
        if ($srv == 'YT') $lpURL = 'https://accounts.google.com/ServiceLogin?service=oz&checkedDomains=youtube&checkConnection=youtube%3A271%3A1%2Cyoutube%3A69%3A1&continue=https://www.youtube.com/&hl=en-US';   
        if ($srv == 'BG') $lpURL = 'https://accounts.google.com/ServiceLogin?service=blogger&passive=1209600&continue=https://www.blogger.com/home&followup=https://www.blogger.com/home&ltmpl=start';
        $hdrsArr = $this->headers('https://accounts.google.com/'); $rep = nxs_remote_get($lpURL, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'sslverify'=>$sslverify)); 
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents); 
        //## GET HIDDEN FIELDS
        $md = array(); $flds  = array();
        while (stripos($contents, '<input')!==false){ $inpField = trim(CutFromTo($contents,'<input', '>')); $name = trim(CutFromTo($inpField,'name="', '"'));
          if ( stripos($inpField, '"hidden"')!==false && $name!='' && !in_array($name, $md)) { $md[] = $name; $val = trim(CutFromTo($inpField,'value="', '"')); $flds[$name]= $val;}
          $contents = substr($contents, stripos($contents, '<input')+8);          
        } $flds['Email'] = $u; $flds['Passwd'] = $p;  $flds['signIn'] = 'Sign%20in'; $flds['PersistentCookie'] = 'yes'; $flds['rmShown'] = '1'; $flds['pstMsg'] = '1'; // $flds['bgresponse'] = $bg;
        //if ($srv == 'GP' || $srv == 'BG') $advSettings['cdomain']='google.com';
        //## ACTUAL LOGIN    
        $hdrsArr = $this->headers($lpURL, 'https://accounts.google.com', 'POST'); 
        $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $flds, 'sslverify'=>$sslverify);// prr($advSet);
        $rep = nxs_remote_post('https://accounts.google.com/ServiceLoginAuth', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies']; //prr($rep);
        $unlockCaptchaMsg = "Your Google+ account is locked for the new applications to connect. Please follow this instructions to unlock it: <a href='http://www.nextscripts.com/support-faq/#q21' target='_blank'>http://www.nextscripts.com/support-faq/#q21</a> - Question #2.1.";
        if ($rep['response']['code']=='200' && !empty($rep['body'])) { $rep['body'] = str_ireplace('\'CREATE_CHANNEL_DIALOG_TITLE_IDV_CHALLENGE\': "Verify your identity"', "", $rep['body']);
            if (stripos($rep['body'],'class="error-msg"')!==false) return strip_tags(CutFromTo(CutFromTo($rep['body'],'class="error-msg"','/span>'), '>', '<'));
            if (stripos($rep['body'],'class="captcha-box"')!==false || stripos($rep['body'],'is that really you')!==false || stripos($rep['body'],'Verify your identity')!==false) return $unlockCaptchaMsg;
        }
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location']) && stripos($rep['headers']['location'], 'ServiceLoginAuth')!==false) return 'Incorrect Username/Password ';
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location']) && stripos($rep['headers']['location'], 'LoginVerification')!==false) return $unlockCaptchaMsg;
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location']) && ( stripos($rep['headers']['location'], '/SmsAuth')!==false || stripos($rep['headers']['location'], '/SecondFactor')!==false)) return '<b style="color:#800000;">2-step verification is on.</b> <br/><br/> 2-step verification is not compatible with auto-posting. <br/><br/>Please see more here:<br/> <a href="http://www.nextscripts.com/blog/google-2-step-verification-and-auto-posting" target="_blank">Google+, 2-step verification and auto-posting</a><br/>';
        if ($rep['response']['code']=='302' && !empty($rep['headers']['location'])) { 
            if ($srv == 'BG') $rep['headers']['location'] = 'https://accounts.google.com/CheckCookie?checkedDomains=youtube&checkConnection=youtube%3A170%3A1&pstMsg=1&chtml=LoginDoneHtml&service=blogger&continue=https%3A%2F%2Fwww.blogger.com%2Fhome&gidl=CAA'; 
            if ($srv == 'YT') $rep['headers']['location'] = 'https://accounts.google.com/CheckCookie?hl=en-US&checkedDomains=youtube&checkConnection=youtube%3A271%3A1%2Cyoutube%3A69%3A1&pstMsg=1&chtml=LoginDoneHtml&service=oz&continue=https%3A%2F%2Fwww.youtube.com%2F&gidl=CAA';
            if ($srv == 'GP') $rep['headers']['location'] = 'https://accounts.google.com/CheckCookie?hl=en-US&checkedDomains=youtube&checkConnection=youtube%3A179%3A1&pstMsg=1&chtml=LoginDoneHtml&service=oz&continue=https%3A%2F%2Fplus.google.com%2F%3Fgpsrc%3Dogpy0%26tab%3DwX%26gpcaz%3Dc7578f19&gidl=CAA';           
          if ($this->debug) echo "[".$srv."] R to: ".$rep['headers']['location']."<br/>\r\n";  $hdrsArr = $this->headers($lpURL, 'https://accounts.google.com');
          $repLoc = $rep['headers']['location']; 
          $rep = nxs_remote_get($repLoc, array('headers' => $hdrsArr, 'redirection' => 0, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify));          
          if (!is_nxs_error($rep) && $srv == 'YT' && $rep['response']['code']=='302' && !empty($rep['headers']['location'])) { $repLoc = $rep['headers']['location'];             
            $rep = nxs_remote_get($repLoc, array('headers' => $hdrsArr, 'redirection' => 0, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); $ck = $rep['cookies'];                             
          } if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $contents = $rep['body']; $rep['body'] = '';          
          //## BG Auth redirect          
          if ($srv != 'GP' && stripos($contents, 'meta http-equiv="refresh"')!==false) {$rURL = htmlspecialchars_decode(CutFromTo($contents,';url=','"')); 
            if ($this->debug) echo "[".$srv."] R to: ".$rURL."<br/>\r\n";  $hdrsArr = $this->headers($repLoc); // prr($hdrsArr);
            $rep = nxs_remote_get($rURL, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'sslverify'=>$sslverify));  
            if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies'];  
          } $this->ck = $ck; return false;  
        } return 'Unexpected Error, Please contact support';  
    }
    
    function urlInfo($url){  $rnds = rndString(13); $url = urlencode($url); $sslverify = false; $ck = $this->ck; 
      $hdrsArr = $this->headers('https://plus.google.com/'); $rep = nxs_remote_get('https://plus.google.com/', array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
      if (is_nxs_error($rep)) return false; /* if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body']; $at = CutFromTo($contents, 'csi.gstatic.com/csi","', '",');     
      $spar='f.req=%5B%22'.$url.'%22%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Ctrue%5D&at='.$at."&";
      $gurl = 'https://plus.google.com/u/0/_/sharebox/linkpreview/?soc-app=1&cid=0&soc-platform=1&hl=en&rt=j'; $hdrsArr = $this->headers('https://plus.google.com/', 'https://plus.google.com', 'POST', true);
      $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $spar, 'sslverify'=>$sslverify);//  prr($advSet);    
      $rep = nxs_remote_post($gurl, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }  $contents = $rep['body']; 
      $json = prcGSON($contents);  $arr = json_decode($json, true); if (!is_array($arr)) return;
      if (!isset($arr[0]) || !is_array($arr[0])) return; if (!isset($arr[0][1]) || !is_array($arr[0][1])) return; if (!isset($arr[0][1][4]) || !is_array($arr[0][1][4])) return; if (!isset($arr[0][1][4][0]) || !is_array($arr[0][1][4][0])) return; 
      $out['link'] = $arr[0][1][4][0][1]; $out['title'] = $arr[0][1][4][0][3]; $out['domain'] = $arr[0][1][4][0][4];  $out['txt'] = $arr[0][1][4][0][7];   
      if (isset($arr[0][1][4][0][2]) && trim($arr[0][1][4][0][2])!='') $out['fav'] = $arr[0][1][4][0][2]; else $out['fav'] = 'https://s2.googleusercontent.com/s2/favicons?domain='.$out['domain'];  
      if (isset($arr[0][1][4][0][6][0])) { $out['img'] = $arr[0][1][4][0][6][0][8]; $out['imgType'] = $arr[0][1][4][0][6][0][1]; } else {
        if (isset($arr[0][1][2][1][24][3])) $out['imgType'] = $arr[0][1][2][1][24][3];
        if (isset($arr[0][1][2][1][41][0])) $out['img'] = $arr[0][1][2][1][41][0][1]; elseif (isset($arr[0][1][2][1][41][1])) $out['img'] = $arr[0][1][2][1][41][1][1];
      } $out['title'] = str_replace('&#39;',"'",$out['title']); $out['txt'] = str_replace('&#39;',"'",$out['txt']);   
      $out['txt'] = html_entity_decode($out['txt'], ENT_COMPAT, 'UTF-8');  $out['title'] = html_entity_decode($out['title'], ENT_COMPAT, 'UTF-8');   
      if (isset($arr[0][1][5][0]) && is_array($arr[0][1][5][0])){$out['arr'] = $arr[0][1][5][0];} return $out;
    }
    function getCCatsGP($commPageID){ $items = '';   $sslverify = false; $ck = $this->ck; 
      $hdrsArr = $this->headers('https://plus.google.com/'); $rep = nxs_remote_get('https://plus.google.com/communities/'.$commPageID, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body']; 
      $commPageID2 = '[["'.stripslashes(str_replace('\n', '', CutFromTo($contents, ',,[[["', "]\n]\n]"))); if (substr($commPageID2, -1)=='"') $commPageID2.="]]"; else $commPageID2.="]]]"; 
      $commPageID2 = str_replace('\u0026','&',$commPageID2); $commPageID2 = json_decode($commPageID2);   
      if (is_array($commPageID2)) foreach ($commPageID2 as $cpiItem) if (is_array($cpiItem)) { $val = $cpiItem[0]; $name = $cpiItem[1]; $items .= '<option value="'.$val.'">'.$name.'</option>'; }
      return $items;   
    }
    function postGP($msg, $lnk='', $pageID='', $commPageID='', $commPageCatID=''){ $rnds = rndString(13); $sslverify = false; $ck = $this->ck; $hdrsArr = $this->headers('');
      $pageID = trim($pageID); $commPageID = trim($commPageID); $ownerID = ''; $bigCode = '';  $isPostToPage = $pageID!=''; $isPostToComm = $commPageID!='';   
      if (function_exists('nxs_decodeEntitiesFull')) $msg = nxs_decodeEntitiesFull($msg); if (function_exists('nxs_html_to_utf8')) $msg = nxs_html_to_utf8($msg);
      $msg = str_replace('<br>', "_NXSZZNXS_5Cn", $msg); $msg = str_replace('<br/>', "_NXSZZNXS_5Cn", $msg); $msg = str_replace('<br />', "_NXSZZNXS_5Cn", $msg);     
      $msg = str_replace("\r\n", "\n", $msg); $msg = str_replace("\n\r", "\n", $msg); $msg = str_replace("\r", "\n", $msg); $msg = str_replace("\n", "_NXSZZNXS_5Cn", $msg);  $msg = str_replace('"', '\"', $msg); 
      $msg = urlencode(strip_tags($msg)); $msg = str_replace("_NXSZZNXS_5Cn", "%5Cn", $msg);  
      $msg = str_replace('+', '%20', $msg); $msg = str_replace('%0A%0A', '%20', $msg); $msg = str_replace('%0A', '', $msg); $msg = str_replace('%0D', '%5C', $msg);
      if (!empty($lnk) && !is_array($lnk)) $lnk = $this->urlInfo($lnk);
      if ($lnk=='') $lnk = array('img'=>'', 'link'=>'', 'fav'=>'', 'domain'=>'', 'title'=>'', 'txt'=>'');
      if (!isset($lnk['link']) && !empty($lnk['img'])) { $hdrsArr = $this->headers(''); $rep = nxs_remote_get($lnk['img'], array('headers' => $hdrsArr, 'httpversion' => '1.1', 'sslverify'=>$sslverify)); 
        if (is_nxs_error($rep)) $lnk['img']=''; elseif ($rep['response']['code']=='200' && !empty($rep['headers']['content-type']) && stripos($rep['headers']['content-type'],'text/html')===false) {    
           $imgdSize = $rep['headers']['content-length']; $urlParced = pathinfo($lnk['img']); $remImgURL = $lnk['img'];
           if ($imgdSize == '-1') $imgdSize = $rep['headers']['size_download']; $remImgURLFilename = nxs_mkImgNm(nxs_clFN($urlParced['basename']), $rep['headers']['content-type']);  $imgData = $rep['body'];        
        } else $lnk['img']=''; 
      }
      if (isset($lnk['img'])) $lnk['img'] = urlencode($lnk['img']); if (isset($lnk['link'])) $lnk['link'] = urlencode($lnk['link']); 
      if (isset($lnk['fav'])) $lnk['fav'] = urlencode($lnk['fav']); if (isset($lnk['domain'])) $lnk['domain'] = urlencode($lnk['domain']);      
      if (isset($lnk['title'])) { $lnk['title'] = (str_replace(Array("\n", "\r"), ' ', $lnk['title']));  $lnk['title'] = rawurlencode(addslashes($lnk['title'])); }    
      if (isset($lnk['txt'])) { $lnk['txt'] = (str_replace(Array("\n", "\r"), ' ', $lnk['txt'])); $lnk['txt'] = rawurlencode( addslashes($lnk['txt'])); }
      $refPage = 'https://plus.google.com/b/'.$pageID.'/'; $rndReqID = rand(1203718, 647379); $rndSpamID = rand(4, 52);
      if ($commPageID!='') { //## Posting to Community      
        if ($pageID!='') $pgIDT = 'u/0/b/'.$pageID.'/'; else $pgIDT = '';
        $gpp = 'https://plus.google.com/'.$pgIDT.'_/sharebox/post/?spam='.$rndSpamID.'&_reqid='.$rndReqID.'&rt=j';            
        $rep = nxs_remote_get('https://plus.google.com/communities/'.$commPageID, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR commPageID"; return $badOut; } /* if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body'];
        if (trim($commPageCatID)!='') $commPageID2 = $commPageCatID; else {$commPageID2 = CutFromTo($contents, "AF_initDataCallback({key: '60',", '</script>'); $commPageID2 = CutFromTo($commPageID2, ',,[[["', '"'); }
      } elseif ($pageID!='') { //## Posting to Page
        $gpp = 'https://plus.google.com/b/'.$pageID.'/_/sharebox/post/?spam='.$rndSpamID.'&_reqid='.$rndReqID.'&rt=j';    
        $rep = nxs_remote_get($refPage, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR pageID"; return $badOut; } /* if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body'];
      } else { //## Posting to Profile      
        $gpp = 'https://plus.google.com/u/0/_/sharebox/post/?spam='.$rndSpamID.'&soc-app=1&cid=0&soc-platform=1&hl=en&rt=j'; 
        $rep = nxs_remote_get('https://plus.google.com/', array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR Main Page"; return $badOut; } /* if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body'];
        $pageID = CutFromTo($contents, "key: '2'", "]"); /* $pageID = CutFromTo($pageID, 'https://plus.google.com/', '"'); */ $pageID = CutFromTo($pageID, 'data:["', '"');  $refPage = 'https://plus.google.com/'; 
        $refPage = 'https://plus.google.com/_/scs/apps-static/_/js/k=oz.home.en.JYkOx2--Oes.O';     
        //unset($nxs_gCookiesArr['GAPS']); unset($nxs_gCookiesArr['GALX']); unset($nxs_gCookiesArr['RMME']); unset($nxs_gCookiesArr['LSID']);  // We migh still need it ?????
      } // echo $lnk['txt'];         
      if ($rep['response']['code']=='400') return "Invalid Sharebox Page. Something is wrong, please contact support";
      if (stripos($contents,'csi.gstatic.com/csi","')!==false) $at = CutFromTo($contents, 'csi.gstatic.com/csi","', '",'); else {        
        $rep = nxs_remote_get('https://plus.google.com/', array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR CSI"; return $badOut; } /* if (!empty($rep['cookies'])) $ck = $rep['cookies']; */ $contents = $rep['body']; // prr($rep);
        if (stripos($contents,'csi.gstatic.com/csi","')!==false) $at = CutFromTo($contents, 'csi.gstatic.com/csi","', '",');  else return "Error (NXS): Lost Login info. Please contact support";
      } // prr($lnk);
      //## URL     
      if (!isset($lnk['txt'])) $lnk['txt'] = '';
      $txttxt = $lnk['txt'];  $txtStxt = str_replace('%5C', '%5C%5C%5C%5C%5C%5C%5C', $lnk['txt']);
      if ($isPostToComm) $proOrCommTxt = "%5B%22".$commPageID."%22%2C%22".$commPageID2."%22%5D%5D%2C%5B%5B%5Bnull%2Cnull%2Cnull%2C%5B%22".$commPageID."%22%5D%5D%5D"; else $proOrCommTxt = "%5D%2C%5B%5B%5Bnull%2Cnull%2C1%5D%5D%2Cnull";        
      if (isset($lnk['link']) && trim($lnk['link'])!='' && isset($lnk['arr']) ) { $urlInfo = urlencode(str_replace('\/', '/', str_replace('##-KXKZK-##', '\""', str_replace('""', 'null', str_replace('\""', '##-KXKZK-##', json_encode($lnk['arr']))))));
        $spar="f.req=%5B%22".$msg."%22%2C%22oz%3A".$pageID.".".$rnds.".2%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5D%22%2Cnull%2Cnull%2Ctrue%2C%5B%5D%2Cfalse%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2C".$urlInfo."%2Cnull%2C%5B".$proOrCommTxt."%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%22!".$bigCode."%22%2Cnull%2Cnull%2Cnull%2C%5B%5D%5D&at=".$at."&";
      }
      //## Video    
      elseif(isset($lnk['video']) && trim($lnk['video'])!='') { $vidCode = $lnk['video']; 
        if (trim($lnk['videoTitle'])=='') { $repV = nxs_remote_get("https://gdata.youtube.com/feeds/api/videos/".$vidCode."?v=2&alt=json", array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); 
          if (is_nxs_error($repV)) {  $badOut = print_r($repV, true)." - ERROR VID"; return $badOut; } $cvv = $repV['body']; $jsVT = json_decode($cvv, true);
          if(is_array($jsVT) && is_array($jsVT['entry']['title'])) $lnk['videoTitle'] = $jsVT['entry']['title']['$t'];  
          if(is_array($jsVT) && is_array($jsVT['entry']['media$group']) && is_array($jsVT['entry']['media$group']['media$description'])) $lnk['videoDesc'] = $jsVT['entry']['media$group']['media$description']['$t']; 
            else $lnk['videoDesc'] = $lnk['videoTitle'];   
          $lnk['videoDesc']  = str_replace(Array("\n", "\r"), ' ', $lnk['videoDesc']); $lnk['videoTitle']  = str_replace(Array("\n", "\r"), ' ', $lnk['videoTitle']);  
          $lnk['videoDesc'] = rawurlencode( addslashes( substr($lnk['videoDesc'], 0, 70)));  $lnk['videoTitle'] = rawurlencode( addslashes(substr($lnk['videoTitle'], 0, 70))); 
        }
      $spar="f.req=%5B%22".$msg."%22%2C%22oz%3A".$pageID.".".$rnds.".0%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5D%22%2Cnull%2Cnull%2Ctrue%2C%5B%5D%2Cfalse%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5B22%2C18%2C1%2C0%5D%2C%22http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D".$vidCode."%22%2Cnull%2Cnull%2Cnull%2C%7B%2226807910%22%3A%5B%22".str_replace('%5C', '%5C%5C%5C%5C%5C%5C%5C', $lnk['videoTitle'])."%22%2C%22".str_replace('%5C', '%5C%5C%5C%5C%5C%5C%5C', $lnk['videoDesc'])."%22%2C%22http%3A%2F%2Fwww.youtube.com%2Fwatch%3Fv%3D".$vidCode."%22%2C%22http%3A%2F%2Fwww.youtube.com%2Fv%2F".$vidCode."%3Fautohide%3D1%26version%3D3%22%2Cnull%2C%22http%3A%2F%2Fi4.ytimg.com%2Fvi%2F".$vidCode."%2Fhqdefault.jpg%22%2C%5B%22%2F%2Fimages1-focus-opensocial.googleusercontent.com%2Fgadgets%2Fproxy%3Furl%3Dhttp%3A%2F%2Fi4.ytimg.com%2Fvi%2F".$vidCode."%2Fhqdefault.jpg%26container%3Dfocus%26gadget%3Da%26rewriteMime%3Dimage%2F*%26refresh%3D31536000%26resize_w%3D497%22%2C497%2C279%2C1%2C1%2C1%2Cnull%2Cnull%2C%5B3%2C%22https%3A%2F%2Fimages2-focus-opensocial.googleusercontent.com%2Fgadgets%2Fproxy%3Furl%3Dhttp%3A%2F%2Fi4.ytimg.com%2Fvi%2F".$vidCode."%2Fhqdefault.jpg%26container%3Dfocus%26gadget%3Dhttps%3A%2F%2Fplus.google.com%26rewriteMime%3Dimage%2F*%26resize_h%3D800%26resize_w%3D800%26no_expand%3D1%22%5D%5D%2C%221280%22%2C%22720%22%2C1280%2C720%2C%22Flash%22%2C%22PT5M15S%22%2C%22http%3A%2F%2Fwww.youtube.com%2Fv%2F".$vidCode."%3Fautohide%3D1%26version%3D3%22%2C%5B%5Bnull%2Cnull%2Cnull%2C%22http%3A%2F%2Fwww.youtube.com%2Fuser%2FNightwishofficial%22%5D%5D%2Cnull%2Cnull%2C%22False%22%2C%22False%22%2C%22http%3A%2F%2Fi4.ytimg.com%2Fvi%2F".$vidCode."%2Fmqdefault.jpg%22%2C1%2Cnull%2Cnull%2C%5B%5D%2C%5B%5D%2C%5B%5D%2C%5B%5D%5D%7D%5D%2Cnull%2C%5B".$proOrCommTxt."%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%22!".$bigCode."%22%5D&at=".$at."&";
    
      //## Image
    } elseif(!empty($lnk['img']) && !empty($imgData)) { $pgAddFlds = '';
       //if($isPostToPage) $pgAddFlds = '{"inlined":{"name":"effective_id","content":"'.$pageID.'","contentType":"text/plain"}},{"inlined":{"name":"owner_name","content":"'.$pageID.'","contentType":"text/plain"}},'; else $pgAddFlds = '';
       if ($isPostToComm) $proOrCommTxt = "%5B%22".$commPageID."%22%2C%22".$commPageID2."%22%5D%5D%2C%5B%5B%5Bnull%2Cnull%2Cnull%2C%5B%22".$commPageID."%22%5D%5D%5D"; else $proOrCommTxt = "%5D%2C%5B%5B%5Bnull%2Cnull%2C1%5D%5D%2Cnull";        
       //if (!$isPostToComm) $pgAddFlds = '{"inlined":{"name":"effective_id","content":"'.$pageID.'","contentType":"text/plain"}},{"inlined":{"name":"owner_name","content":"'.$pageID.'","contentType":"text/plain"}},'; else $pgAddFlds = '';
       $iflds = '{"protocolVersion":"0.8","createSessionRequest":{"fields":[{"external":{"name":"file","filename":"'.$remImgURLFilename.'","put":{},"size":'.$imgdSize.'}},{"inlined":{"name":"use_upload_size_pref","content":"true","contentType":"text/plain"}},{"inlined":{"name":"batchid","content":"1389803229361","contentType":"text/plain"}},{"inlined":{"name":"client","content":"sharebox","contentType":"text/plain"}},{"inlined":{"name":"disable_asbe_notification","content":"true","contentType":"text/plain"}},{"inlined":{"name":"album_mode","content":"temporary","contentType":"text/plain"}},'.$pgAddFlds.'{"inlined":{"name":"album_abs_position","content":"0","contentType":"text/plain"}}]}}';
              
       $hdrsArr = $this->headers('', 'https://plus.google.com', 'POST', true); $hdrsArr['X-GUploader-Client-Info']='mechanism=scotty xhr resumable; clientVersion=58505203'; 
       $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $iflds, 'sslverify'=>$sslverify);// prr($advSet);
       $imgReqCnt = nxs_remote_post('https://plus.google.com/_/upload/photos/resumable?authuser=0', $advSet); if (is_nxs_error($imgReqCnt)) {  $badOut = print_r($imgReqCnt, true)." - ERROR IMG"; return $badOut; } //prr($imgReqCnt);
       $gUplURL = str_replace('\u0026', '&', CutFromTo($imgReqCnt['body'], 'putInfo":{"url":"', '"'));  $gUplID = CutFromTo($imgReqCnt['body'], 'upload_id":"', '"');      
       
       $hdrsArr = $this->headers('', 'https://plus.google.com', 'PUT', true); $hdrsArr['X-GUploader-No-308']='yes'; $hdrsArr['X-HTTP-Method-Override']='PUT'; 
       $hdrsArr['Expect']=''; $hdrsArr['Content-Type']='application/octet-stream'; 
       $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $imgData, 'sslverify'=>$sslverify);// prr($advSet);
       $imgUplCnt = nxs_remote_post($gUplURL, $advSet); if (is_nxs_error($imgUplCnt)) {  $badOut = print_r($imgUplCnt, true)." - ERROR IMG Upl"; return $badOut; } 
       $imgUplCnt = json_decode($imgUplCnt['body'], true);   if (empty($imgUplCnt)) return "Can't upload image: ".$remImgURL;
       if (is_array($imgUplCnt) && isset($imgUplCnt['errorMessage']) && is_array($imgUplCnt['errorMessage']) ) return "Error (500): ".print_r($imgUplCnt['errorMessage'], true);     
       $infoArray = $imgUplCnt['sessionStatus']['additionalInfo']['uploader_service.GoogleRupioAdditionalInfo']['completionInfo']['customerSpecificInfo'];     
       $albumID = $infoArray['albumid']; $photoid =  $infoArray['photoid']; // $albumID = "5969185467353784753";
       $imgUrl = urlencode($infoArray['url']); $imgTitie = $infoArray['title'];          
       $imgUrlX = str_ireplace('https:', '', $infoArray['url']); $imgUrlX = str_ireplace('//lh4.', '//lh3.', $imgUrlX); $imgUrlX = urlencode(str_ireplace('http:', '', $imgUrlX));
       $width = $infoArray['width']; $height = $infoArray['height']; $userID = $infoArray['username'];      
       $intID = $infoArray['albumPageUrl'];  $intID = str_replace('https://picasaweb.google.com/','', $intID);  $intID = str_replace($userID,'', $intID); $intID = str_replace('/','', $intID); // prr($infoArray);
       $spar="f.req=%5B%22".$msg."%22%2C%22oz%3A".$pageID.".".$rnds.".4%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5D%22%2Cnull%2Cnull%2Ctrue%2C%5B%5D%2Cfalse%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5B344%2C339%2C338%2C336%2C335%5D%2Cnull%2Cnull%2Cnull%2C%5B%7B%2239387941%22%3A%5Btrue%2Cfalse%5D%7D%5D%2Cnull%2Cnull%2C%7B%2240655821%22%3A%5B%22https%3A%2F%2Fplus.google.com%2Fphotos%2F".$userID."%2Falbums%2F".$albumID."%2F".$photoid."%22%2C%22".$imgUrlX."%22%2C%22".$imgTitie."%22%2C%22%22%2Cnull%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%22".$width."%22%2C%22".$height."%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%22".$userID."%22%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%22".$albumID."%22%2C%22".$photoid."%22%2C%22albumid%3D".$albumID."%26photoid%3D".$photoid."%22%2C1%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B%5D%5D%7D%5D%2Cnull%2C%5B".$proOrCommTxt."%5D%2Cnull%2Cnull%2C2%2Cnull%2Cnull%2Cnull%2C%22!".$bigCode."%22%2Cnull%2Cnull%2Cnull%2C%5B%22updates%22%5D%2C%5B%5Btrue%5D%5D%2Cnull%2C%5B%5D%5D&at=".$at."&";
    }
    //## Just Message    
    else $spar="f.req=%5B%22".$msg."%22%2C%22oz%3A".$pageID.".".$rnds.".6%22%2Cnull%2Cnull%2Cnull%2Cnull%2C%22%5B%5D%22%2Cnull%2Cnull%2Ctrue%2C%5B%5D%2Cfalse%2Cnull%2Cnull%2C%5B%5D%2Cnull%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cfalse%2Cfalse%2Cfalse%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2Cnull%2C%5B".$proOrCommTxt."%5D%2Cnull%2Cnull%2C2%2Cnull%2Cnull%2Cnull%2C%22!".$bigCode."%22%2Cnull%2Cnull%2Cnull%2C%5B%5D%2C%5B%5Btrue%5D%5D%2Cnull%2C%5B%5D%5D&at=".$at."&";    
    //## POST
    $spar = str_ireplace('+','%20',$spar); $spar = str_ireplace(':','%3A',$spar);  $hdrsArr = $this->headers($refPage, 'https://plus.google.com', 'POST', true); $hdrsArr['X-Same-Domain']='1'; 
    $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $spar, 'sslverify'=>$sslverify);
    $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR POST"; return $badOut; }  $contents = $rep['body']; // prr($advSet);    prr($rep);
        
    if ($rep['response']['code']=='403') return "Error: You are not authorized to publish to this page. Are you sure this is even a page? (".$pageID.")";
    if ($rep['response']['code']=='404') return "Error: Page you are posting is not found.<br/><br/> If you have entered your page ID as 117008619877691455570/117008619877691455570, please remove the second copy. It should be one number only - 117008619877691455570";
    if ($rep['response']['code']=='400') return "Error (400): Something is wrong, please contact support";
    if ($rep['response']['code']=='500') return "Error (500): Something is wrong, please contact support";
    if ($rep['response']['code']=='200') { $ret = $rep['body']; $remTxt = CutFromTo($ret,'"{\"','}"'); $ret = str_replace($remTxt, '', $ret); $ret = prcGSON($ret);  $ret = json_decode($ret, true); 
      $ret = $ret[0][1][1][0][0][21]; return array("code"=>"OK", "post_id"=>$ret, "post_url"=>'https://plus.google.com/'.$ret); 
    }
    return print_r($contents, true);         
    }
 
    function postBG($blogID, $title, $msg, $tags=''){ $sslverify = false; $rnds = rndString(35); $blogID = trim($blogID); $ck = $this->ck; 
      $gpp = "https://www.blogger.com/blogger.g?blogID=".$blogID; $refPage = "https://www.blogger.com/home";
      $hdrsArr = $this->headers($refPage); $rep = nxs_remote_get($gpp, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify));// prr($ck); prr($rep); die();
      if (is_nxs_error($rep)) return false; if (!empty($rep['cookies'])) $ck = $rep['cookies']; $contents = $rep['body']; if ( stripos($contents, 'Error 404')!==false) return "Error: Invalid Blog ID - Blog with ID ".$blogID." Not Found";
      $jjs = CutFromTo($contents, 'BloggerClientFlags=','_layoutOnLoadHandler'); $j69 = '';  //prr($jjs);  prr($contents); echo "\r\n"; echo "\r\n";    
      for ($i = 54; $i <= 99; $i++) { if ($j69=='' && strpos($jjs, $i.':"')!==false){ $j69 = CutFromTo($jjs, $i.':"','"'); if (strpos($j69, ':')===false || (strpos($j69, '/')!==false) || (strpos($j69, '\\')!==false)) $j69 = '';}}
      $gpp = "https://www.blogger.com/blogger_rpc?blogID=".$blogID; $refPage = "https://www.blogger.com/blogger.g?blogID=".$blogID;
      $spar = '{"method":"editPost","params":{"1":1,"2":"","3":"","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":1,"13":0,"14":{"6":""},"15":"en","16":0,"17":{"1":'.date("Y").',"2":'.date("n").',"3":'.date("j").',"4":'.date("G").',"5":'.date("i").'},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":1},"xsrf":"'.$j69.'"}';      
      $hdrsArr = $this->headers($refPage, 'https://www.blogger.com', 'JS', false); $hdrsArr['X-GWT-Module-Base']='https://www.blogger.com/static/v1/gwt/'; $hdrsArr['X-GWT-Permutation']='3A7F66CAC13C096CA61784226B8E58D1';      
      $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $spar, 'sslverify'=>$sslverify); // prr($advSet);    
      $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR BG"; return $badOut; }  $contents = $rep['body'];      
      $newpostID = CutFromTo($contents, '"result":[null,"', '"');  
      if ($tags!='') $pTags = '["'.$tags.'"]'; else $pTags = ''; $pTags = str_replace('!','',$pTags); $pTags = str_replace('.','',$pTags);
      if (class_exists('DOMDocument')) { $doc = new DOMDocument();  @$doc->loadXML("<QAZX>".$msg."</QAZX>"); $styles = $doc->getElementsByTagName('style');
        if ($styles->length>0) {  foreach ($styles as $style)  $style->nodeValue = str_ireplace("<br/>", "", $style->nodeValue);
          $msg = $doc->saveXML($doc->documentElement, LIBXML_NOEMPTYTAG); $msg = str_ireplace("<QAZX>", "", str_ireplace("</QAZX>", "", $msg)); 
        }
      } $msg = str_replace("'",'"',$msg); $msg = addslashes($msg); $msg = str_replace("\r\n","\n",$msg); $msg = str_replace("\n\r","\n",$msg); $msg = str_replace("\r","\n",$msg); $msg = str_replace("\n",'\n',$msg);  
      $title = strip_tags($title); $title = str_replace("'",'"',$title); $title = addslashes($title); $title = str_replace("\r\n","\n",$title); 
      $title = str_replace("\n\r","\n",$title); $title = str_replace("\r","\n",$title); $title = str_replace("\n",'\n',$title); //echo "~~~~~";  prr($title);
      $spar = '{"method":"editPost","params":{"1":1,"2":"'.$title.'","3":"'.$msg.'","4":"'.$newpostID.'","5":0,"6":0,"7":1,"8":3,"9":0,"10":2,"11":2,'.($pTags!=''?'"12":'.$pTags.',':'').'"13":0,"14":{"6":""},"15":"en","16":0,"17":{"1":'.date("Y").',"2":'.date("n").',"3":'.date("j").',"4":'.date("G").',"5":'.date("i").'},"20":0,"21":"","22":{"1":1,"2":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0,"10":"0"}},"23":1},"xsrf":"'.$j69.'"}';    
      
      $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $spar, 'sslverify'=>$sslverify);// prr($advSet);    
      $rep = nxs_remote_post($gpp, $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR BG2"; return $badOut; }  $contents = $rep['body'];
      
      $retJ = json_decode($contents, true); if (is_array($retJ) && !empty($retJ['result']) && is_array($retJ['result']) ) $postID = $retJ['result'][6]; else $postID = '';
      if ( stripos($contents, '"error":')!==false) { return "Error: ".print_r($contents, true); }
      if ($rep['response']['code']=='200') return array("code"=>"OK", "post_id"=>$postID, "post_url"=>$postID); else return print_r($contents, true);        
    }    
    function postYT($msg, $ytUrl, $vURL = '', $ytGPPageID='') { $ck = $this->ck; $sslverify = false; $ytUrl = str_ireplace('/feed','',$ytUrl); if (substr($ytUrl, -1)=='/') $ytUrl = substr($ytUrl, 0, -1); $ytUrl .= '/feed';   
      $hdrsArr = $this->headers('http://www.youtube.com/');
      if ($ytGPPageID!=''){ $pgURL = 'http://www.youtube.com/signin?authuser=0&action_handle_signin=true&pageid='.$ytGPPageID;      if ($this->debug) echo "[GP] G to: ".$ytGPPageID."<br/>\r\n";
        $rep = nxs_remote_get($pgURL, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true);// if (!empty($rep['cookies'])) $ck = $rep['cookies']; 
      } $rep = nxs_remote_get($ytUrl, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true);// if (!empty($rep['cookies'])) $ck = $rep['cookies'];
      $contents = $rep['body']; $gpPageMsg = "Either BAD YouTube USER/PASS or you are trying to post from the wrong account/page. Make sure you have Google+ page ID if your YouTube account belongs to the page.";
      $actFormCode = 'channel_ajax'; if (stripos($contents, 'action="/c4_feed_ajax?')!==false) $actFormCode = 'c4_feed_ajax';
      if (stripos($contents, 'action="/'.$actFormCode.'?')) $frmData = CutFromTo($contents, 'action="/'.$actFormCode.'?', '</form>'); else { 
        if (stripos($contents, 'property="og:url"')) {  $ytUrl = CutFromTo($contents, 'property="og:url" content="', '"').'/feed'; 
          $rep = nxs_remote_get($ytUrl, array('headers' => $hdrsArr, 'httpversion' => '1.1', 'cookies' => $ck, 'sslverify'=>$sslverify)); if (is_nxs_error($rep)) return "ERROR: ".print_r($rep, true); if (!empty($rep['cookies'])) $ck = $rep['cookies'];  $contents = $rep['body'];        
          if (stripos($contents, 'action="/'.$actFormCode.'?')) $frmData = CutFromTo($contents, 'action="/'.$actFormCode.'?', '</form>'); else return 'OG - Form not found. - '. $gpPageMsg;
        } else { $eMsg = "No Form/No OG - ". $gpPageMsg; return $eMsg; }
      }      
      $md = array(); $flds = array(); if ($vURL!='' && stripos($vURL, 'http')===false) $vURL = 'https://www.youtube.com/watch?v='.$vURL; $msg = strip_tags($msg); $msg = nsTrnc($msg, 500);
      while (stripos($frmData, '"hidden"')!==false){$frmData = substr($frmData, stripos($frmData, '"hidden"')+8); $name = trim(CutFromTo($frmData,'name="', '"'));
        if (!in_array($name, $md)) {$md[] = $name; $val = trim(CutFromTo($frmData,'value="', '"')); $flds[$name]= $val;}
      } $flds['message'] = $msg; $flds['video_url'] = $vURL; // prr($flds);
      
      $hdrsArr = $this->headers($ytGPPageID, 'http://www.youtube.com/', 'POST', true); $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $flds, 'sslverify'=>$sslverify);
      $rep = nxs_remote_post('https://www.youtube.com/'.$actFormCode.'?action_add_bulletin=1', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR YT"; return $badOut; }  $contents = $rep['body'];
              
      if ($rep['response']['code']=='200' && $contents = '{"code": "SUCCESS"}') return array("code"=>"OK", "post_id"=>''); else return $rep['response']['code']."|".$contents;     
    }              
}}

?>