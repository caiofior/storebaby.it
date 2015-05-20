<?php
/*#############################################################################
Project Name: NextScripts Pinterest AutoPoster
Project URL: http://www.nextscripts.com/pinterest-automated-posting/
Description: Automatically posts to your Pinterest profile
Author: NextScripts, Inc
Version: 2.15.69 (Feb, 27 2015)
Author URL: http://www.nextscripts.com
Copyright 2012-2015  Next Scripts, Inc
**** Please Note: This library is depreciated and will be no longer supported or updated after May, 20 2015. 
**** Please update to the Universal SNAP API - http://www.nextscripts.com/snap-api/
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
if (!function_exists("nxs_clFN")){ function nxs_clFN($fn){$sch = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}");
  return trim(preg_replace('/[\s-]+/', '-', str_replace($sch, '', $fn)), '.-_');    
}}
if (!function_exists("nxs_mkImgNm")){ function nxs_mkImgNm($fn, $cType){ $iex = array(".png", ".jpg", ".gif", ".jpeg"); $map = array('image/gif'=>'.gif','image/jpeg'=>'.jpg','image/png'=>'.png');
  $fn = str_replace($iex, '', $fn); if (isset($map[$cType])){return $fn.$map[$cType];} else return $fn.".jpg";    
}}
if (!function_exists("nxs_jsonFix")) { function nxs_jsonFix(&$item, &$key){ $item = (substr($item, -4)=='E+12')?(number_format($item, 0, '', '')):$item; }}

//================================Pinterest===========================================
//## Check current  Pinterest session
if (!function_exists("doConnectToPinterest")) {function doConnectToPinterest($email, $pass, $iidb=-1){ global $nxs_plurl, $nxs_gCookiesArr, $plgn_NS_SNAutoPoster;  
  if (!empty($nxs_gCookiesArr)) $ck = $nxs_gCookiesArr; else {  if ($iidb==-1 && !empty($_POST['ii'])) $iidb = $_POST['ii']; if ($iidb==-1 && !empty($_POST['nid'])) $iidb = $_POST['nid'];
    if ($iidb!=-1 && isset($plgn_NS_SNAutoPoster)) { $options = $plgn_NS_SNAutoPoster->nxs_options; if (isset($options['pn'][$iidb]['ck'])) $ck = maybe_unserialize($options['pn'][$iidb]['ck']); } else $ck = array();
  } $nt = new nxsAPI_PN(); $nt->debug = false; if (!empty($ck)) $nt->ck = $ck;  $loginErr = $nt->connect($email, $pass);  
  if (!$loginErr){ $nxs_gCookiesArr = $nt->ck; $nxs_gCookiesArr['chkPnt3'] = '1'; }
  return $loginErr;
}}
if (!function_exists("doCheckPinterest")) {function doCheckPinterest(){ global $nxs_gCookiesArr; 
  if (!empty($nxs_gCookiesArr) && empty($nxs_gCookiesArr['chkPnt3'])) { $nxs_gCookiesArr = array(); return "No"; }
  $nt = new nxsAPI_PN(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) { $nt->ck = $nxs_gCookiesArr; if (!empty($nt->ck['chkPnt3'])) unset($nt->ck['chkPnt3']); } return !$nt->check(); 
}}
if (!function_exists("doGetBoardsFromPinterest")) {function doGetBoardsFromPinterest(){ global $nxs_gCookiesArr;
  $nt = new nxsAPI_PN(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) {  if (!empty($nxs_gCookiesArr) && empty($nxs_gCookiesArr['chkPnt3'])) { $nxs_gCookiesArr = array(); } $nt->ck = $nxs_gCookiesArr;    
  if (!empty($nt->ck['chkPnt3'])) unset($nt->ck['chkPnt3']); } $boards = $nt->getBoards(); return $boards;    
}}
if (!function_exists("doPostToPinterest")) { function doPostToPinterest($msg, $imgURL, $lnk, $boardID, $title = '', $price='', $via=''){ global $nxs_gCookiesArr; 
  $nt = new nxsAPI_PN(); $nt->debug = false; if (!empty($nxs_gCookiesArr)) { $nt->ck = $nxs_gCookiesArr; if (!empty($nt->ck['chkPnt3'])) unset($nt->ck['chkPnt3']); } $ret = $nt->post($msg, $imgURL, $lnk, $boardID, $title, $price, $via); // prr($ret);
  if (is_array($ret) && !empty($ret['isPosted'])) return array("code"=>"OK", "post_id"=>$ret['postID'], "post_url"=>$ret['postURL']); else return $ret;  
}}
if (!class_exists('nxsAPI_PN')){class nxsAPI_PN{ var $ck = array(); var $tk=''; var $boards = ''; var $apVer=''; var $u=''; var $debug = false;
    function headers($ref, $org='', $type='GET', $aj=false){  $hdrsArr = array(); 
      $hdrsArr['Cache-Control']='max-age=0'; $hdrsArr['Connection']='keep-alive'; $hdrsArr['Referer']=$ref;
      $hdrsArr['User-Agent']='Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/30.0.1599.22 Safari/537.36'; 
      if($type=='JSON') $hdrsArr['Content-Type']='application/json;charset=UTF-8'; elseif($type=='POST') $hdrsArr['Content-Type']='application/x-www-form-urlencoded';
      if($aj===true) $hdrsArr['X-Requested-With']='XMLHttpRequest';  if ($org!='') $hdrsArr['Origin']=$org; 
      if ($type=='GET') $hdrsArr['Accept']='text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'; else $hdrsArr['Accept']='*/*';
      if (function_exists('gzdeflate')) $hdrsArr['Accept-Encoding']='deflate,sdch'; 
      $hdrsArr['Accept-Language']='en-US,en;q=0.8'; return $hdrsArr;         
    }
    function check($u=''){ $ck = $this->ck; if (!empty($ck) && is_array($ck)) { $hdrsArr = $this->headers('https://www.pinterest.com/settings/'); if ($this->debug) echo "[PN] Checking....;<br/>\r\n";
        $rep = nxs_remote_get('https://www.pinterest.com/settings/', array('headers' => $hdrsArr, 'timeout' => 45, 'httpversion' => '1.1', 'cookies' => $ck)); 
        if (is_nxs_error($rep)) return false; $ck = $rep['cookies']; $contents = $rep['body']; //if ($this->debug) prr($contents);
        $ret = stripos($contents, 'href="#accountBasics"')!==false; $usr = CutFromTo($contents, '"email": "', '"'); if ($ret & $this->debug) echo "[PN] Logged as:".$usr."<br/>\r\n"; 
        $apVer = trim(CutFromTo($contents,'"app_version": "', '"'));  $this->apVer = $apVer; 
        if (empty($u) || $u==$usr) return $ret; else return false;
      } else return false;
    }
    function connect($u,$p){ $badOut = 'Error: '; // $this->debug = true;
      //## Check if alrady IN
      if (!$this->check($u)){ if ($this->debug) echo "[PN] NO Saved Data; Logging in...<br/>\r\n";
        $hdrsArr = $this->headers('https://www.pinterest.com/login/'); $rep = nxs_remote_get('https://www.pinterest.com/login/', array('headers' => $hdrsArr, 'timeout' => 45, 'httpversion' => '1.1'));         
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -01-"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body']; prr($contents);  $apVer = trim(CutFromTo($contents,'"app_version": "', '"')); 
        $fldsTxt = 'data=%7B%22options%22%3A%7B%22username_or_email%22%3A%22'.urlencode($u).'%22%2C%22password%22%3A%22'.str_replace('%5C','%5C%5C',urlencode($p)).'%22%7D%2C%22context%22%3A%7B%22app_version%22%3A%22'.$apVer.
    '%22%7D%7D&source_url=%2Flogin%2F&module_path=App()%3ELoginPage()%3ELogin()%3EButton(class_name%3Dprimary%2C+text%3DLog+in%2C+type%3Dsubmit%2C+tagName%3Dbutton%2C+size%3Dlarge)';          
        foreach ($ck as $c) if ($c->name=='csrftoken') $xftkn = $c->value;
        //## ACTUAL LOGIN 
        $hdrsArr = $this->headers('https://www.pinterest.com/login/', 'https://www.pinterest.com', 'POST', true); $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-CSRFToken']=$xftkn;        
        $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $fldsTxt); // prr($advSet);
        $rep = nxs_remote_post('https://www.pinterest.com/resource/UserSessionResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02-"; return $badOut; } 
        if (!empty($rep['body'])) { $contents = $rep['body']; $resp = json_decode($contents, true); } else { $badOut = print_r($rep, true)." - ERROR -03-"; return $badOut; }
          if (is_array($resp) && empty($resp['resource_response']['error'])) { $ck = $rep['cookies'];  foreach ($ck as $ci=>$cc) $ck[$ci]->value = str_replace(' ','+', $cc->value);  
            $hdrsArr = $this->headers('https://www.pinterest.com/login'); $rep=nxs_remote_get('https://www.pinterest.com/', array('headers' => $hdrsArr, 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'httpversion' => '1.1')); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR -02.1-"; return $badOut; } 
            if (!empty($rep['cookies'])) foreach ($rep['cookies'] as $ccN) { $fdn = false; foreach ($ck as $ci=>$cc) if ($ccN->name == $cc->name) { $fdn = true; $ck[$ci] = $ccN;  } if (!$fdn) $ck[] = $ccN; }
            foreach ($ck as $ci=>$cc) $ck[$ci]->value = str_replace(' ','+', $cc->value); $this->tk = $xftkn; $this->ck = $ck;  $this->apVer = $apVer;           
            if ($this->debug) echo "[PN] You are IN;<br/>\r\n"; return false; // echo "You are IN";                                       
          } elseif (is_array($resp) && isset($resp['resource_response']['error'])) return "ERROR -04-: ".$resp['resource_response']['error']['http_status']." | ".$resp['resource_response']['error']['message'];
          elseif (stripos($contents, 'CSRF verification failed')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
            return "CSRF verification failed - Please contact NextScripts Support | Pinterest Message:".$retText;
          } elseif (stripos($contents, 'IP because of suspicious activity')!==false) return 'Pinterest blocked logins from this IP because of suspicious activity'; 
          elseif (stripos($contents, 've detected a bot!')!==false) return 'Pinterest has your IP ('.CutFromTo($contents, 'ess: <b>','<').') blocked. Please <a target="_blank" class="link" href="//help.pinterest.com/entries/22914692">Contact Pinterest</a> and ask them to unblock your IP. ';
          elseif (stripos($contents, 'bot running on your network')!==false) return 'Pinterest has your IP ('.CutFromTo($contents, 'Your IP is:','<').') blocked. Please <a target="_blank" class="link" href="//help.pinterest.com/entries/22914692">Contact Pinterest</a> and ask them to unblock your IP. '; 
          else return 'Pinterest login failed. Unknown Error. Please contact support.';           
          return 'Pinterest login failed. Unknown Error #2. Please contact support.'; 
      } else { if ($this->debug) echo "[PN] Saved Data is OK;<br/>\r\n"; return false; }
    }
    function getBoardsOLD() { if (!$this->check()){ if ($this->debug) echo "[PN] NO Saved Data;<br/>\r\n"; return 'Not logged IN';} $boards = ''; $ck = $this->ck; $apVer = $this->apVer; $brdsArr = array();
        $iu = 'http://memory.loc.gov/award/ndfa/ndfahult/c200/c240r.jpg'; $su = '/pin/find/?url='.urlencode($iu); 
        $hdrsArr = $this->headers('http://www.pinterest.com/pin/find/?url='.urlencode($iu),'','JSON', true); $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer;
        $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';
        $dt = '{"options":{},"context":{},"module":{"name":"PinCreate","options":{"image_url":"'.$iu.'","action":"create","method":"scraped","link":"'.$iu.'","transparent_modal":false}},"append":false,"error_strategy":0}';
        $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck);
        $rep = nxs_remote_get('http://www.pinterest.com/resource/NoopResource/get/?source_url='.urlencode($su).'&data='.urlencode($dt), $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body'];   $k = json_decode($contents, true);         
        if (!empty($k['module']['tree']) && !empty($k['module']['tree']['children'][0]) && !empty($k['module']['tree']['children'][0]['children'])) $brdsA = $k['module']['tree']['children'][0]['children'];
          foreach ($brdsA as $ab) { if (!empty($ab) && !empty($ab['data']['all_boards'])) { $ba = $ab['data']['all_boards']; 
            foreach ($ba as $kh) { $boards .= '<option value="'.$kh['id'].'">'.$kh['name'].'</option>'; $brdsArr[] = array('id'=>$kh['id'], 'n'=>$kh['name']); } $this->boards = $brdsArr; return $boards; 
          } $khtml = CutFromTo($k['module']['html'], "boardPickerInnerWrapper", "</ul>"); $khA = explode('<li', $khtml);
        }
        foreach ($khA as $kh) if (stripos($kh, 'data-id')!==false) { $bid = CutFromTo($kh, 'data-id="', '"'); $bname = trim(CutFromTo($kh, '</div>', '</li>'));
          if (isset($bid)) { $boards .= '<option value="'.$bid.'">'.trim($bname).'</option>'; $brdsArr[] = array('id'=>$bid, 'n'=>trim($bname)); }
        } $this->boards = $brdsArr; return $boards;  
    }
    function getBoards() { if (!$this->check()){ if ($this->debug) echo "[PN] NO Saved Data;<br/>\r\n"; return 'Not logged IN';} $boards = ''; $ck = $this->ck; $apVer = $this->apVer; $brdsArr = array();
        $iu = 'http://memory.loc.gov/award/ndfa/ndfahult/c200/c240r.jpg'; $su = '/pin/find/?url='.urlencode($iu); $iuu = urlencode($iu); $hdrsArr = $this->headers('http://www.pinterest.com/','','JSON', true);         
        $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-Pinterest-AppState']='active'; $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';                
        $brdURL = 'https://www.pinterest.com/resource/BoardPickerBoardsResource/get/?source_url=%2Fpin%2Ffind%2F%3Furl%'.$iuu.'&data=%7B%22options%22%3A%7B%22filter%22%3A%22all%22%2C%22field_set_key%22%3A%22board_picker%22%7D%2C%22context%22%3A%7B%7D%7D&module_path=App()%3EImagesFeedPage(resource%3DFindPinImagesResource(url%'.$iuu.'))%3EGrid()%3EGridItems()%3EPinnable()%3EShowModalButton(module%3DPinCreate)';$advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck); $rep = nxs_remote_get($brdURL, $advSet);
        if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; } $ck = $rep['cookies']; $contents = $rep['body'];   $k = json_decode($contents, true);         
        if (!empty($k['resource_data_cache'])) { $brdsA = $k['resource_data_cache'];
          foreach ($brdsA as $ab) if (!empty($ab) && !empty($ab['data']['all_boards'])) { $ba = $ab['data']['all_boards']; 
            foreach ($ba as $kh) { $boards .= '<option value="'.$kh['id'].'">'.$kh['name'].'</option>'; $brdsArr[] = array('id'=>$kh['id'], 'n'=>$kh['name']); } $this->boards = $brdsArr; return $boards; 
          } 
        } return getBoardsOLD(); //## Remove it in couple months
    }
    function post($msg, $imgURL, $lnk, $boardID, $title = '', $price='', $via=''){ 
      $tk = $this->tk; $ck = $this->ck; $apVer = $this->apVer; if ($this->debug) echo "[PN] Posting to ...".$boardID."<br/>\r\n";      
      foreach ($ck as $c) if ( is_object($c) && $c->name=='csrftoken') $tk = $c->value; $msg = strip_tags($msg); $msg = substr($msg, 0, 480); $tgs = ''; $this->tk = $tk;
      if ($msg=='') $msg = '&nbsp;';  if (trim($boardID)=='') return "Board is not Set";  if (trim($imgURL)=='') return "Image is not Set";   $msg = str_ireplace(array("\r\n", "\n", "\r"), " ", $msg); 
      $msg = strip_tags($msg); if (function_exists('nxs_decodeEntitiesFull')) $msg = nxs_decodeEntitiesFull($msg, ENT_QUOTES); 
      $mgsOut = urlencode($msg); $mgsOut = str_ireplace(array('%28', '%29', '%27', '%21', '%22', '%09'), array("(", ")", "'", "!", "%5C%22", '%5Ct'), $mgsOut);     
      $fldsTxt = 'source_url=%2Fpin%2Ffind%2F%3Furl%3D'.urlencode(urlencode($lnk)).'&data=%7B%22options%22%3A%7B%22board_id%22%3A%22'.$boardID.'%22%2C%22description%22%3A%22'.$mgsOut.'%22%2C%22link%22%3A%22'.urlencode($lnk).'%22%2C%22share_twitter%22%3Afalse%2C%22image_url%22%3A%22'.urlencode($imgURL).'%22%2C%22method%22%3A%22scraped%22%7D%2C%22context%22%3A%7B%7D%7D';
      $hdrsArr = $this->headers('https://www.pinterest.com/resource/PinResource/create/ ', 'https://www.pinterest.com', 'POST', true);       
      $hdrsArr['X-NEW-APP']='1'; $hdrsArr['X-APP-VERSION']=$apVer; $hdrsArr['X-CSRFToken']=$tk; $hdrsArr['X-Pinterest-AppState']='active';  $hdrsArr['Accept'] = 'application/json, text/javascript, */*; q=0.01';      
      $advSet = array('headers' => $hdrsArr, 'httpversion' => '1.1', 'timeout' => 45, 'redirection' => 0, 'cookies' => $ck, 'body' => $fldsTxt); 
      $rep = nxs_remote_post('https://www.pinterest.com/resource/PinResource/create/', $advSet); if (is_nxs_error($rep)) {  $badOut = print_r($rep, true)." - ERROR"; return $badOut; }       
      $contents = $rep['body']; $resp = json_decode($contents, true); //  prr($advSet);  prr($resp);   prr($fldsTxt); // prr($contents);    
      if (is_array($resp)) {
        if (isset($resp['resource_response']) && isset($resp['resource_response']['error']) && $resp['resource_response']['error']!='' ) return print_r($resp['resource_response']['error'], true); 
        elseif (isset($resp['resource_response']) && isset($resp['resource_response']['data']) && $resp['resource_response']['data']['id']!=''){ // gor JSON
          if (isset($resp['resource_response']) && isset($resp['resource_response']['error']) && $resp['resource_response']['error']!='') return print_r($resp['resource_response']['error'], true);
          else return array("isPosted"=>"1", "postID"=>$resp['resource_response']['data']['id'], 'pDate'=>date('Y-m-d H:i:s'), "postURL"=>"http://www.pinterest.com/pin/".$resp['resource_response']['data']['id']);
        }    
      }elseif (stripos($contents, 'blocked this')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "Pinterest ERROR: 'The Source is blocked'. Please see https://support.pinterest.com/entries/21436306-why-is-my-pin-or-site-blocked-for-spam-or-inappropriate-content/ for more info | Pinterest Message:".$retText;
      }  
      elseif (stripos($contents, 'image you tried to pin is too small')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "Image you tried to pin is too small | Pinterest Message:".$retText;
      }  
      elseif (stripos($contents, 'CSRF verification failed')!==false) { $retText = trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
        return "CSRF verification failed - Please contact NextScripts Support | Pinterest Message:".$retText;
      }
      elseif (stripos($contents, 'Oops')!==false && stripos($contents, '<body>')!==false ) return 'Pinterest ERROR MESSAGE : '.trim(str_replace(array("\r\n", "\r", "\n"), " | ", strip_tags(CutFromTo($contents, '</head>', '</body>'))));
      else return "Somethig is Wrong - Pinterest Returned Error 502";         
    }    
}} 
?>