<?php
if (isset($argv) && is_array($argv) && $argv[1]=='sleep') {
  sleep(rand(1,60)*60);
}
set_include_path(get_include_path().':'.__DIR__.'/../magmi/inc');
require __DIR__.'/../magmi/engines/magmi_productimportengine.php';
require __DIR__.'/../magmi/plugins/inc/magmi_generalimport_plugin.php';
require __DIR__.'/../magmi/plugins/extra/general/socialnotify/socialnotify.php';

$snp = new SocialNotifyPlugin('SocialNotifyPlugin');
$engine = new Magmi_ProductImportEngine();
$engine->initialize();
$engine->connectToMagento();
$snp->pluginInit($engine,array(
  'class' =>'SocialNotifyPlugin',
  'dir' => __DIR__.'/../magmi/plugins/extra/general/socialnotify',
  'file' =>'socialnotify.php'
));
$snp->afterImport();