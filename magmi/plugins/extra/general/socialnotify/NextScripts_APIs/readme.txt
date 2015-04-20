=== NextScripts: API Libraries ===
(c) NextScripts.com

Google+
Demo and Examples: http://www.nextscripts.com/google-plus-automated-posting/

Examples:
1. Post simple message to your Google Plus Stream:
<?php        
  require "postToGooglePlus.php";
  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $msg = 'Post this to Google Plus!'; 
  $pageID = '104935301886129712427'; 
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> postGP($msg);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['post_url'])) 
    echo '<a target="_blank" href="'.$result['post_url'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>"; 
?>

2. Post message with attached URL to your Google Plus Stream:
<?php        
  require "postToGooglePlus.php";
  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $msg = 'Post this to Google Plus!'; 
  $pageID = '104935301886129712427'; 
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> postGP($msg, $lnk, $pageID);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['post_url'])) 
    echo '<a target="_blank" href="'.$result['post_url'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>"; 
?>

3. Import RSS to Google Plus!
<?php        
  require "postToGooglePlus.php";
  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $url = 'http://www.YorWebSite.com/rss.xml';
  
  $nt = new nxsAPI_GP();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
  { 
    $doc = new DOMdocument();
    $doc->load($url);
    $rss_array = array();
    $items = array();
    $tag = 'item';

    foreach($doc->getElementsByTagName($tag) AS $node) 
    {    
      $link = $node->getElementsByTagName('link')->item(0)->nodeValue;
      $title = $node->getElementsByTagName('title')->item(0)->nodeValue;
      $msg = $title."\r\n";
      $msg .= $link."\r\n";
      $msg .= $node->getElementsByTagName('description')->item(0)->nodeValue;
      $result[] = $nt -> postGP($msg, $lnk, $pageID);
    }
  } else echo $loginError; 

  echo "<pre>".print_r($result, true)."</pre>"; 
?>

Requirements

PHP5, cURL with OpenSSL, WordPress if you would like to use plugin.


Version history

Version 2.15.22 – June 10, 2014
   – [FIX] – Support for updated interface
Version 2.15.0 – June 20, 2014
   – [NEW] – More efficient nxsAPI_GP class
Version 2.6.0 – Mar 12, 2013
- [Improvement] – YouTube Support.
- [Improvement] – New interface support.
Version 2.0.9 – Sept 06, 2012
- [Improvement] – Better SSL handling.
Version 2.0.8 – Sept 06, 2012
- [BugFix] – Fixed “quotes” characters. {Broken by Google}
Version 2.0.7 – Aug 31, 2012
- [BugFix] – Fixed publishing of new lines in messages
Version 2.0.2 – Aug 16, 2012
- [BugFix] – Update to the latest Google+ release
Version 2.0.1 – May 18, 2012
- [Change] – Cookie files are mo longer required
Version 1.2.1 – Feb 28, 2012
- [BugFix] – Incorrect line break handling.
- [WP Plugin] – New Setting – Optional message to Announce Post.
Version 1.2.0 – Feb 27, 2012
- [Added] Ability to post/attach linked URLs- [Added] Ability to post/attach linked images
- [WP Plugin] Changed Settings Screen
Version 1.0.1 – Feb 20, 2012
- [BugFix] – Correct login – Incorrect page access.
Version 1.0.0 – Feb 01, 2012
- Initial Release
Blogger
Blogger support is included since version 2.0 You can use doConnectToBlogger() and doPostToBlogger() functions.



Pinterest
Demo and Examples: http://www.nextscripts.com/google-plus-automated-posting/

Examples:
1. Pin an image to your Pinterest board:
<?php        
  require "postToPinterest.php";
  
  $email = 'YourEmail@gmail.com'; 
  $pass = 'YourPassword';
  $msg = 'Post this to Pinterest!'; 
  $imgURL = 'http://www.YourWebsiteURL.com/link/to/your/image.jpg'; 
  $link = 'http://www.YourWebsiteURL.com/page'; 
  $boardID = '104935301886129712427'; 
  
  $nt = new nxsAPI_PN();
  $loginError = $nt->connect($email, $pass);     
  if (!$loginError)
    {
      $result = $nt -> post($msg, $imgURL, $link, $boardID);
    } 
  else echo $loginError; 
  
  if (!empty($result) && is_array($result) && !empty($result['post_url'])) 
    echo '<a target="_blank" href="'.$result['post_url'].'">New Post</a>'; 
  else 
    echo "<pre>".print_r($result, true)."</pre>";
?>
Requirements

PHP5, cURL with OpenSSL, WordPress if you would like to use plugin.

Version history
Version 2.15.28 – June 12, 2014
   – [FIX] – Support for updated Pinterest interface
Version 2.15.0 – June 20, 2014
   – [NEW] – More efficient nxsAPI_PN class
Version 2.9.15 – may 20, 2014
   – [FIX] – Support for updated Pinterest interface
Version 2.8.0 – July 10, 2013
   – [Improvement] – New Pinterest Design for posts
   – [Change] – OLD Pinterest Design is not longer supported
Version 2.7.3 – Jun 28, 2013
   – [Improvement] – New Pinterest Design Support
   – [Improvement] – Stability Improvements
Version 1.1.0 – Jun 28, 2012
   – [Improvement] – Switched to new generation of automated logins. No more “cookie” files required and it can work with open_basedir set.
Version 1.0.1 – Jun 22, 2012
   – [BugFix] – Some Small Bug Fixes.
Version 1.0.0 – Jun 20, 2012
   – Initial Release

== Changelog ==

