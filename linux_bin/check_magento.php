<?php
$import_csv_mtime = 0;
$import_csv_file = '../public_html/var/import/import.csv';
if (is_file($import_csv_file))
 $import_csv_mtime = filemtime($import_csv_file);

$import_state_mtime = 0;
$import_state_file = '../public_html/magmi/state/progress.txt';
if (is_file($import_state_file)) {
    $import_state_mtime = filemtime($import_state_file);
}
$magento_config_file = '../public_html/app/etc/local.xml';
$lastIndexdateTime = null;
if(is_file($magento_config_file)) {
    $simpleXml = simplexml_load_file($magento_config_file);
    $db = new mysqli (
        $simpleXml->global->resources->default_setup->connection->host,
        $simpleXml->global->resources->default_setup->connection->username,
        $simpleXml->global->resources->default_setup->connection->password,
        $simpleXml->global->resources->default_setup->connection->dbname
    );
    try{
    $dateTime = $db->query('SELECT MIN(ended_at) FROM index_process')->fetch_row();
    $dateTime = new DateTime(array_shift($dateTime));
    $lastIndexdateTime = (int)$dateTime->format('U')+60*60;
    } catch (\Exception $e) {
        $lastIndexdateTime = null;        
    }
}
if (
        $import_state_mtime < $import_csv_mtime
    ) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://magmi.storebaby.it/web/magmi_run.php?profile=default&mode=create&engine=magmi_productimportengine:Magmi_ProductImportEngine&logfile=progress.txt');
            curl_setopt($ch, CURLOPT_HEADER,false);
            $header = array( 'Authorization: Basic ' . base64_encode('joachim:alfaalfa56'));
            curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3600);
            curl_setopt($ch,CURLOPT_TIMEOUT,3600);
                
            curl_exec($ch);
            curl_close($ch);
} else if (
    !is_null($lastIndexdateTime) &&
    $lastIndexdateTime < $import_csv_mtime    
) {
    $basePath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public_html');
    $commands = array(
       "sh -c \"sleep 600; /usr/local/bin/php $basePath/shell/pricerule.php\"",
       "sh -c \"sleep 600; /usr/local/bin/php $basePath/shell/indexer.php --reindexall\"",
       "sh -c \"sleep 600; /usr/local/bin/php $basePath/shell/turpentine.php\"",
       "find $basePath/media/catalog/product/cache/ -type f -mtime +30 -delete",
       "find $basePath/var/report/ -type f  -mtime +2 -delete"
    );
    foreach ($commands as $command) {
       pclose(popen("$command 2>&1 > /dev/null &","r"));
    }
}