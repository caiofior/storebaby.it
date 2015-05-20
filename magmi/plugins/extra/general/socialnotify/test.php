<?php
require "wp/formatting.php";
require "wp/functions.php";
require "wp/plugin.php";
require "NextScripts_APIs/postToGooglePlus.php";
$nt = new nxsAPI_GP();
$loginError = $nt->connect('storebaby.italia@gmail.com', 'stbaby2014');
$lnk=array('img'=>'http://www.storebaby.it/media/catalog/product/0/0/00007331070000.jpeg');
//$lnk = array('img'=>'http://www.nextscripts.com/imgs/nextscripts.png'); 
var_dump($nt->postGP('Chicco Fiat 500 macchina elettronica - red 00007331070000  http://www.storebaby.it', $lnk, '113160380943238250605'));
var_dump($loginError);

//var_dump(doConnectToGooglePlus2('storebaby.italia@gmail.com', 'stbaby2014'));
//var_dump( doPostToGooglePlus2('Test', 'http://digilander.libero.it/caiofior/','113160380943238250605'));


