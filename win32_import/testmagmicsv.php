<?php
$f = fopen(__DIR__.'/magento_csv/t1.csv','r');
var_dump(fgetcsv($f));
die('HI');
require 'include/magmi_csvreader.php';
$magmiCsv = new Magmi_CSVReader();
$magmiCsv->initialize(__DIR__.'/magento_csv/last_import.csv');
$magmiCsv->openCsv();
$column_names = $magmiCsv->getColumnNames();
$c=0;
while (true) {
   $row = $magmiCsv->getNextRecord();
   if ($row == false) break;
   file_put_contents(__DIR__.'/log/'.$c++.'.txt',var_export($row,true));
}