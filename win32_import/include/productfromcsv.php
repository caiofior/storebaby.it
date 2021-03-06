<?php
/**
* Reads csv stream and creates a product
*/
class ProductFromCsv {

    /**
* Config paramethers
* @var array
*/
    private $config = array();

    /**
* Mastro CSV Handle
* @var resource
*/
    private $mastroCsvHandle;
    /**
* Mastro Utf8 CSV Handle
* @var resource
*/
    private $mastroUtf8CsvHandle;
    /**
* Magento CSV File
* @var string
*/
    private $magentoCsvFilname;
    /**
* Magento CSV Handle
* @var resource
*/
    private $magentoCsvHandler;
        /**
* Magento Excluded CSV File
* @var string
*/
    private $magentoExcludedCsvFilname;
    /**
* Magento Excluded CSV Handle
* @var resource
*/
    private $magentoExcludedCsvHandler;

    /**
* Last position
* @var int
*/
    private $mastroCsvLastpos;

    /**
* Start datetime
* @var int
*/
    private $startTime;

    /**
* Association with categories
* @var array
*/
    protected $categories = array();
    /**
*Association with weights
* @var array
*/
    private $weigths = array();
    /**
* FTP reference
* @var resource
*/
    private $ftp;

    /**
* Firt ftp start
* @var bool
*/
    private $initialFtp = true;

    /**
* Database with parsed image names, real name and size to detect modified images
* @var SQLite3
*/
    private $imageDb;

    /**
* Log activity
* @var String
*/
    private $log;
    /**
* Lock file
* @var string
*/
    private $lock;
    /**
     * Shop codes
     */
    private $shopCodes=array('retail','ebimbo','storebaby');
    /**
     * Custom prices from csv
     */
    private $customPricesFromCsv=array();
    /**
     * Name of the custom prices file
     */
    private $customPricesCsvFilname;
    /**
     * Handler of the custom prices file
     */
    private $customPricesCsvHandler;

    /**
* Parses config file
* @param type $sconfig_file
* @throws Exception
*/
    public function __construct($sconfig_file) {
        if (ini_get('date.timezone') == '')
            ini_set('date.timezone', 'Europe/Rome');
        $this->startTime = microtime(true);
        $this->log = ' Started at: ' . strftime('%Y-%m-%d %H:%M:%S') . PHP_EOL;
        if (!is_file($sconfig_file))
            throw new Exception('config file not exists ' . $sconfig_file, 1312100827);
        $this->config = parse_ini_file($sconfig_file);
        if (!is_array($this->config))
            throw new Exception('config file is wrong ' . $sconfig_file, 1312061659);
        $dbFile = getcwd() . DIRECTORY_SEPARATOR . 'log';
        if (!is_dir($dbFile))
            mkdir($dbFile);
        $this->lock = $dbFile . DIRECTORY_SEPARATOR . 'lock';
        if (
                is_file($this->lock) &&
                filemtime($this->lock)-time() > 3600 * 24 * 7
            )
                unlink ($this->lock);
        touch($this->lock);
        $dbFile .= DIRECTORY_SEPARATOR . 'mastro';
        echo 'Setting up database' . PHP_EOL;
        $this->setUpDb($dbFile);
        echo 'Fixing database' . PHP_EOL;
        if (@$this->imageDb->exec('UPDATE product SET expire_date = DATETIME(\'now\') WHERE expire_date=\'\' OR expire_date IS NULL;') === false) {
            unlink($dbFile);
            $this->setUpDb($dbFile);
        }
        foreach($this->shopCodes as $shopCode) {
           $customPriceFile = getcwd() . DIRECTORY_SEPARATOR . 'custom_price' . DIRECTORY_SEPARATOR. $shopCode. '.csv';
           if (!is_file($customPriceFile)) {
              continue;
           }
           echo 'Found custom file for shop ' . $shopCode . PHP_EOL;
           $customPriceHandler = fopen($customPriceFile,'r');
           $header = fgetcsv($customPriceHandler,0,',','"',"\\");
           $header = array_map('trim',$header);
           if (
            !in_array('EAN13', $header) ||
            !in_array('VENDITA', $header)
           ) {
               echo 'Missing EAN13 or VENDITA column in custom price file for ' . $shopCode . PHP_EOL;
               continue;              
           }
           $this->customPricesFromCsv[$shopCode]=array();
           while($data = fgetcsv($customPriceHandler,0,',','"',"\\")) {              
              $this->customPricesFromCsv[$shopCode][$data[array_search('EAN13',$header)]]=array(
                  'special_price'=>(float)str_replace(',','.',str_replace(array('.','"'),'',$data[array_search('VENDITA',$header)])),
                  'use_cupon'=>($data[array_search('CUPON',$header)]==1?1:0)
              );
           }
           array_filter($this->customPricesFromCsv[$shopCode]);
        }
    }
    /**
     * Sets up the db
     */
    private function setUpDb($dbFile) {
        $this->imageDb = new SQLite3($dbFile);
        echo 'Setting up database' . PHP_EOL;
        $this->imageDb->exec('CREATE TABLE IF NOT EXISTS product (
ean13 TEXT PRIMARY KEY ON CONFLICT REPLACE,
code TEXT,
descrizione TEXT,
vendita NUMERIC,
image TEXT,
size NUMERIC,
md5 TEXT,
description_md5 TEXT,
modify_date NUMERIC,
create_date NUMERIC,
expire_date NUMERIC,
corrupted NUMERIC
);');
    }

    /**
    * Starts data import
    * @throws Exception
    */
    public function import() {
        $this->setuUpCsv();
        $row = '';
        $mastroProduct = new MastroProduct($this);
        $rowCount = 0;
        $byteCount = 0;
        while (($buffer = fgets($this->mastroCsvHandle, 4096)) !== false) {
            $byteCount += (strlen($buffer) + 2);
            $lastchar = ord(substr($buffer, strlen($buffer) - 1));

            while (
            $lastchar == 10 ||
            $lastchar == 13
            ) {
                $buffer = substr($buffer, 0, strlen($buffer) - 1);
                $lastchar = ord(substr($buffer, strlen($buffer) - 1));
            }
            if (is_resource($this->mastroUtf8CsvHandle))
                fwrite($this->mastroUtf8CsvHandle , iconv('WINDOWS-1252', 'UTF-8', $buffer ). PHP_EOL);
            if (strlen($row) > 0)
                $row .= '<br/>';
            $row .= $buffer;
            if (preg_match('/\*\*$/', $buffer)) {

                $row = iconv('WINDOWS-1252', 'UTF-8', $row);
                $mastroProduct->importFromCsvRow($row);
                $magentoProductArray = $mastroProduct->createMagentoProduct();
                foreach($magentoProductArray as  $magentoProduct) {
                  if ($magentoProduct instanceof MagentoProduct) {
                      if (ftell($this->magentoCsvHandler) == 0)
                          fwrite($this->magentoCsvHandler, "\xEF\xBB\xBF".$magentoProduct->getCsvHeaders() . PHP_EOL);
                      fwrite($this->magentoCsvHandler, $magentoProduct->getCsvRow() . PHP_EOL);
                  }
                }
                $rowCount++;
                $row = '';
                if ($rowCount / 100 == (int) ($rowCount / 100)) {
                    $microtime = microtime(true) - $this->startTime;
                    $rimaningTime = $microtime * $this->mastroCsvLastpos / $byteCount - $microtime;
                    $progress = 'Progress:' . intval($byteCount / $this->mastroCsvLastpos * 100 - 1) . " %\t";
                    $progress .= 'Products: ' . $rowCount . "\t";
                    $progress .= 'Remaning time: ' . intval($rimaningTime / 60 + 1) . "m\t";
                    $progress .= 'ETA:' . date('G:i:s', $this->startTime + $microtime + $rimaningTime) . PHP_EOL;
                    echo $progress;
                    file_put_contents($this->lock, $progress);
                    $this->setUpFtp();
                    if (is_resource($this->ftp)) {
                        ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
                        foreach (array('media','import') as $dir) {
                            $fileList = ftp_nlist($this->ftp,'.');
                            if (is_array($fileList) && !in_array($dir, $fileList)) {
                                ftp_mkdir($this->ftp,$dir);
                            }
                            ftp_chdir($this->ftp,$dir);
                            ftp_put($this->ftp, 'progress.txt', $this->lock, FTP_ASCII);
                        }
                    }
                }
            }
        }
        $this->uploadCsv();
        $this->downloadBackup();
        $this->execOnShutdown();
    }

    /**
* Gets config parameter
* @param null|string $key
* @return string|array
*/
    public function getConfig($key = null) {
        if ($key == null)
            return $this->config;
        else if (key_exists($key, $this->config))
            return $this->config[$key];
        else
            return false;
    }

    /**
* Gets a Magento Category name from Mastro Category Name
* @param string $mastroCode
* @return string
*/
    public function getCategory($mastroCode) {
        if (key_exists($mastroCode, $this->categories))
            return $this->categories[$mastroCode];
        else
            return false;
    }
    
     /**
* Gets a weioght from Mastro Category Name
* @param string $mastroCode
* @return string
*/
    public function getWeight($mastroCode) {
        if (key_exists($mastroCode, $this->weigths))
            return $this->weigths[$mastroCode];
        else
            return false;
    }

    /**
* Returns Ftp resource
* @return resource
*/
    public function getFtp() {
        $this->setUpFtp();
        return $this->ftp;
    }

    /**
* Return Sqlite image db reference
* @return SQLite3
*/
    public function getImageDb() {
        return $this->imageDb;
    }

    /**
* Appends a row to lof message
* @param string $string
*/
    public function appendToLog($string) {
       if (strlen($this->log) < 500)
         $this->log .= $string . PHP_EOL;
    }

    /**
* Called on export end
*/
    public function execOnShutdown() {
        $this->log .= ' Ended at: ' . strftime('%Y-%m-%d %H:%M:%S') . PHP_EOL;
        if (key_exists('CANCEL_MAGENTO_URL', $this->config)) {
            echo 'Call to magento cancel url ' . PHP_EOL;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['CANCEL_MAGENTO_URL']);
            if (key_exists('UPDATE_MAGENTO_CREDENTIALS', $this->config)) {
                curl_setopt($ch, CURLOPT_HEADER,false);
                $header = array( 'Authorization: Basic ' . base64_encode($this->config['UPDATE_MAGENTO_CREDENTIALS']));

                
            }
	    curl_setopt($ch,CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3600);
            curl_setopt($ch,CURLOPT_TIMEOUT,3600);
            curl_exec($ch);
            curl_close($ch);
        }
        if (key_exists('UPDATE_MAGENTO_URL', $this->config)) {
            $this->log .= ' Magmi call start at: ' . strftime('%Y-%m-%d %H:%M:%S') . PHP_EOL;
            echo 'Call to magento update url ' . PHP_EOL;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['UPDATE_MAGENTO_URL']);
            curl_setopt($ch,CURLOPT_POST, true);
            $data = array('message' => $this->log);
            if (key_exists('UPDATE_MAGENTO_CREDENTIALS', $this->config)) {
                curl_setopt($ch, CURLOPT_HEADER,false);
                $header = array( 'Authorization: Basic ' . base64_encode($this->config['UPDATE_MAGENTO_CREDENTIALS']));
                curl_setopt($ch,CURLOPT_HTTPHEADER, $header);

                
            }
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3600);
            curl_setopt($ch,CURLOPT_TIMEOUT,3600);
            curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($data));
            curl_exec($ch);
            curl_close($ch);
            $this->log .= ' Magmi call end at: ' . strftime('%Y-%m-%d %H:%M:%S') . PHP_EOL;
        }
        $dbFile = getcwd() . DIRECTORY_SEPARATOR . 'log';
        $dbFile .= DIRECTORY_SEPARATOR . 'mastro';
        $imageDb = new SQLite3($dbFile);
        $imageDb->exec('CREATE TABLE IF NOT EXISTS log (
datetime NUMERIC,
message TEXT
);');
        $error = error_get_last();
        if (is_array($error)) {
            $this->log .= PHP_EOL . "type\t" . $error["type"] . PHP_EOL;
            $this->log .= "message\t" . $error["message"] . PHP_EOL;
            $this->log .= "file\t" . $error["file"] . PHP_EOL;
            $this->log .= "line\t" . $error["line"] . PHP_EOL;
        }
        $imageDb->exec('INSERT INTO log (datetime,message) VALUES (
DATETIME(\'now\'),
\'' . $this->imageDb->escapeString($this->log) . '\'
); ');

        $imageDb->exec('DELETE FROM log WHERE datetime < DATETIME("now","-1 month");');
        $imageDb->close();
        
        
        unlink($this->lock);
    }

    /**
* Test ftp status
* @throws Exception
*/
    public function setUpFtp() {
        if (!key_exists('FTP_SERVER', $this->config))
            return;
        if (is_resource($this->ftp) && !is_array(ftp_nlist($this->ftp, '.'))) {
            ftp_close($this->ftp);
            $this->ftp = null;
            echo 'FTP reconnect'.PHP_EOL;
        }
        $count = 0;
        while (!is_resource($this->ftp) && $count < 5) {
            $count++;
            $this->ftp = ftp_connect($this->config['FTP_SERVER']);
            if (!is_resource($this->ftp)) {
                $this->appendToLog(strftime('%Y-%m-%d %H:%M:%S').' FTP server wrong or down '.$this->config['FTP_SERVER'].PHP_EOL);
                echo 'FTP server wrong or down '.$this->config['FTP_SERVER'].PHP_EOL;
                sleep(5);
                continue;
            }
            if (key_exists('FTP_USER', $this->config)) {
                if (!ftp_login($this->ftp, $this->config['FTP_USER'], $this->config['FTP_PASSWORD'])){
                    $this->appendToLog(strftime('%Y-%m-%d %H:%M:%S').' Wrong login to FTP server '.$this->config['FTP_SERVER'].' user:'.$this->config['FTP_USER'].' password:'.$this->config['FTP_PASSWORD'].PHP_EOL);
                    echo 'Wrong login to FTP server '.$this->config['FTP_SERVER'].' user:'.$this->config['FTP_USER'].' password:'.$this->config['FTP_PASSWORD'].PHP_EOL;
                    sleep(5);
                    continue;
            }
            }
            if ($this->initialFtp) {
                if (!key_exists('FTP_BASE_DIR', $this->config)) {
                    $this->config['FTP_BASE_DIR'] = '';
                }
                $this->config['FTP_BASE_DIR'] = ftp_pwd($this->ftp) . '/' . $this->config['FTP_BASE_DIR'];
            }
            $this->initialFtp = false;
            ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
        }
    }
    /**
* Uploads CSV Data
*/
    private function uploadCsv (){
        fclose($this->mastroCsvHandle);
        fclose($this->magentoCsvHandler);
        fclose($this->magentoExcludedCsvHandler);
        fclose($this->customPricesCsvHandler);
        if (is_resource($this->mastroUtf8CsvHandle))
            fclose($this->mastroUtf8CsvHandle);
        if (is_resource($this->ftp)) {
         
        $this->uploadSingleFile($this->magentoCsvFilname);
        $this->uploadSingleFile($this->magentoExcludedCsvFilname);
        $this->uploadSingleFile($this->customPricesCsvFilname);
      }
    }
    /**
     * Upload single file
     * @param $filePath file path
     */
    private function uploadSingleFile($filePath) {
        $fileName = basename($filePath);
        $size = 0;
        $count = 0;
        $fileSize = null;
        if (is_file($filePath))
            $fileSize = filesize($filePath);
        do {
        $this->setUpFtp();
        
            ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
            $imagesSubDirs = array('var', 'import');
            foreach ($imagesSubDirs as $dir) {
                $fileList = ftp_nlist($this->ftp, '.');
                if (!is_array($fileList) || !in_array($dir, $fileList)) {
                    ftp_mkdir($this->ftp, $dir);
                }
                ftp_chdir($this->ftp, $dir);
            }
            $fileList = ftp_nlist($this->ftp, '.');
                        
            if (is_array($fileList) && in_array($fileName, $fileList))
                ftp_delete($this->ftp, $fileName);
            echo 'Uploading '.$fileName.PHP_EOL;
            ftp_put($this->ftp, $fileName, $filePath, FTP_ASCII);

            ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
            $imagesSubDirs = array('var', 'import');
            foreach ($imagesSubDirs as $dir) {
                $fileList = ftp_nlist($this->ftp, '.');
                if (!is_array($fileList) || !in_array($dir, $fileList)) {
                    ftp_mkdir($this->ftp, $dir);
                }
                ftp_chdir($this->ftp, $dir);
            }
            $fileList = ftp_nlist($this->ftp, '.');
            if (is_array($fileList) && in_array($fileName, $fileList))
                $size = ftp_size ($this->ftp, $fileName);
            ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);            
            $count++;
            
        } while (($size == $fileSize || $fileSize == null) && $count < 10);
        if ($count >=10)
                $this->appendToLog('Error on uploading '.$fileName);
    }

    /**
    * Downloads the backup
    */
    private function downloadBackup (){
        if (is_resource($this->ftp)) {
            echo 'Downloading backups'.PHP_EOL;
        $count = 0;
            $fileList =array();
            $backupDir = getcwd() . DIRECTORY_SEPARATOR . 'backups';
            if (!is_dir($backupDir))
                mkdir ($backupDir);
            $fileList = array();
            while(sizeof($fileList) == 0 && $count <5) {
                $this->setUpFtp();
                ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
                $imagesSubDirs = array('var', 'backups');
                foreach ($imagesSubDirs as $dir) {
                    $fileList = ftp_nlist($this->ftp, '.');
                    if (!is_array($fileList) || !in_array($dir, $fileList)) {
                        ftp_mkdir($this->ftp, $dir);
                    }
                    ftp_chdir($this->ftp, $dir);
                }
                $fileList = ftp_nlist($this->ftp, '.');
                $count++;
            }
            $count=0;
            foreach($fileList as $file) {
                $filesize = null;
                if (is_file($backupDir.DIRECTORY_SEPARATOR.$file))
                    $filesize = filesize($backupDir.DIRECTORY_SEPARATOR.$file);
                if ($count > 5 || $filesize > 0) continue;
                $iCount = 0;
                do {
                    
                    
                if (
                        $file != '.' &&
                        $file != '..' && (
                            $filesize == null ||
                            filesize($backupDir.DIRECTORY_SEPARATOR.$file) == 0
                       )
                    ) {
                        echo 'Downloading backup '.$file.PHP_EOL;
                        ftp_get($this->ftp, $backupDir.DIRECTORY_SEPARATOR.$file, $file, FTP_BINARY);
                        if (is_file($backupDir.DIRECTORY_SEPARATOR.$file))
                            $filesize =filesize($backupDir.DIRECTORY_SEPARATOR.$file);
                    }
                    $iCount++;
                }
                while (is_null($filesize) && $iCount <5);
                $count++;
            }
            echo 'Downloading backups end'.PHP_EOL;
            ftp_close($this->ftp);
            
            $dir = opendir($backupDir);
            $list = array();
            while($file = readdir($dir)){
                if ($file != '.' and $file != '..'){
                    $ctime = filemtime($backupDir .DIRECTORY_SEPARATOR. $file) . ',' . $file;
                    $list[$ctime.$file] = $file;
                }
            }
            closedir($dir);
            krsort($list);
            $c=0;
            foreach ($list as $file) {
                if ($c++ < 5) {
                    continue;
                }
                unlink($backupDir.DIRECTORY_SEPARATOR.$file);
            }             
        }
    }
    /**
     * Gets the custom prices based on an SKU
     * @param string $sku
     * @return array
     */
    public function getCustomPrices ($sku) {
       $customPrices =array();
       foreach($this->shopCodes as $shop) {
          if (array_key_exists($shop,$this->customPricesFromCsv) && array_key_exists($sku, $this->customPricesFromCsv[$shop])) {
               $customPrices[$shop]=$this->customPricesFromCsv[$shop][$sku];     
          }
       } 
       return $customPrices;
    }
     /**
     * Gets a reference to the custom price handler
     */
    public function getMagentoExcludedCsvHandler() {
       if (ftell($this->magentoExcludedCsvHandler) == 0) {
           $tempMastro = new MastroProduct($this);
           fwrite($this->magentoExcludedCsvHandler, "\xEF\xBB\xBF".'"EAN","reason","info",' . implode(',',$tempMastro->getHeaders()) . PHP_EOL);
       }
       return $this->magentoExcludedCsvHandler;
    }
    /**
     * Gets a reference to the custom price handler
     */
    public function getCustomPricesHandler() {
       if (ftell($this->customPricesCsvHandler) == 0) {
           fwrite($this->customPricesCsvHandler, "\xEF\xBB\xBF".'"EAN","shop","price","cupon"'. PHP_EOL);
       }
       return $this->customPricesCsvHandler;
    }
    /**
     * Sets up the output csv
     * @throws Exception
     */
    private function setuUpCsv () {
        if (key_exists('MASTRO_COMMAND', $this->config)) {
            echo 'Export command ' . $this->config['MASTRO_COMMAND'] . PHP_EOL;
            echo exec($this->config['MASTRO_COMMAND']) . PHP_EOL;
        }
        if (key_exists('MASTRO_UTF8_FILE', $this->config)) {
            $this->mastroUtf8CsvHandle = fopen($this->config['MASTRO_UTF8_FILE'], 'w');
        }
        $this->mastroCsvHandle = fopen($this->config['MASTRO_CSV_FILE'], 'r');
        $this->setUpFtp();
        if (is_resource($this->ftp))
            echo 'FTP connection with ' . $this->config['FTP_SERVER'] . PHP_EOL;
        if (key_exists('CONVERT_COMMAND', $this->config))
            echo 'Image conversion command ' . $this->config['CONVERT_COMMAND'] . PHP_EOL;
        
        $this->magentoCsvFilname = getcwd() . DIRECTORY_SEPARATOR . 'magento_csv';
        if (!is_dir($this->magentoCsvFilname))
            mkdir($this->magentoCsvFilname);
        $this->magentoCsvFilname .= DIRECTORY_SEPARATOR . 'import.csv';
        if (is_file($this->magentoCsvFilname))
            unlink($this->magentoCsvFilname);
        $this->magentoCsvHandler = fopen($this->magentoCsvFilname, 'w');
        
        $this->magentoExcludedCsvFilname = getcwd() . DIRECTORY_SEPARATOR . 'magento_csv' . DIRECTORY_SEPARATOR . 'excluded.csv';
        if (is_file($this->magentoExcludedCsvFilname))
            unlink($this->magentoExcludedCsvFilname);
        $this->magentoExcludedCsvHandler = fopen($this->magentoExcludedCsvFilname, 'w');
        
        $this->customPricesCsvFilname = getcwd() . DIRECTORY_SEPARATOR . 'magento_csv' . DIRECTORY_SEPARATOR . 'custom_prices.csv';
        if (is_file($this->customPricesCsvFilname))
            unlink($this->customPricesCsvFilname);
        $this->customPricesCsvHandler = fopen($this->customPricesCsvFilname, 'w');

        $this->categories = json_decode(str_replace("'", '"', $this->config['CATEGORIES']), true);
        if (!is_array($this->categories))
            throw new Exception('Associative categories in config.ini are wrong', 1312061657);
        $this->weigths = json_decode(str_replace("'", '"', $this->config['WEIGHTS']), true);
        if (!is_array($this->weigths))
            throw new Exception('Associative weights in config.ini are wrong', 1312061658);
        if (!is_resource($this->mastroCsvHandle))
            throw new Exception('Unable to open mastro CSV file ' . $this->config['MASTRO_CSV_FILE'], 1312030807);
        $this->mastroCsvLastpos = filesize($this->config['MASTRO_CSV_FILE']);
        echo 'Create CSV file ' . PHP_EOL;
        fseek($this->mastroCsvHandle, 0);
    }
}