<?php

class Test {
    private $customPrice = array();
    public function initialize() {
        $customPriceFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'import'
                .DIRECTORY_SEPARATOR.'custom_prices.csv';
        if (is_file($customPriceFile)) {
            $customPriceHandler = fopen($customPriceFile,'r');
            $header = fgetcsv($customPriceHandler,0,',','"',"\\");
            $header = array_map(create_function('$val', 'return preg_replace("/[^[:alnum:]]/","",$val);'),$header);
            while($data = fgetcsv($customPriceHandler,0,',','"',"\\")) {              
              $this->customPrice[] = $data[array_search('EAN13',$header)];
            }
            $this->customPrice = array_unique($this->customPrice);
        }
    }
    public function processItemAfterId() {
        $customPriceFile = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'
                .DIRECTORY_SEPARATOR.'var'.DIRECTORY_SEPARATOR.'import'
                .DIRECTORY_SEPARATOR.'import.csv';
        $customPriceHandler = fopen($customPriceFile,'r');
        $header = fgetcsv($customPriceHandler,0,',','"',"\\");
        $header = array_map(create_function('$val', 'return preg_replace("/[^[:alnum:]]/","",$val);'),$header);
        while($data = fgetcsv($customPriceHandler,0,',','"',"\\")) {              
            $sku = $data[array_search('sku',$header)];
            $store = $data[array_search('store',$header)];
            if (
                    (
                        ($store != 'admin' && !in_array($sku,$this->customPrice))
                    )
                        ||
                    (
                        $store == 'admin' && in_array($sku,$this->customPrice)
                    )
               ) {
                 continue;
               }
               if ($sku == '8430488044944') {
                echo 'Store '.$store.PHP_EOL;
                echo 'Special price '.$sku.PHP_EOL;
               }
            
        }
        
    }
}
$test = new Test();
$test->initialize();
$test->processItemAfterId();