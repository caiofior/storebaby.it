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
     * Magento CSV Handle
     * @var resource
     */
    private $magentoCsvHandle;
    /**
     * Last position
     * @var int
     */
    private $mastroCsvLastpos;
    /**
     * Satrt datetime
     * @var int
     */
    private $startTime;
    /**
     * Association with categories
     * @var array 
     */
    private $categories =array();
    /**
     * FTP reference
     * @var resource
     */
    private $ftp;
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
     * Parses config file
     * @param type $sconfig_file
     * @throws Exception
     */
    public function __construct($sconfig_file) {
        if(ini_get('date.timezone') == '')
            ini_set('date.timezone','Europe/Rome');
        $this->startTime = microtime(true);
        $this->log = ' Started at: '.strftime('%Y-%m-%d %H:%M:%S').PHP_EOL;
        if (!is_file($sconfig_file))
            throw new Exception('config file not exists '.$sconfig_file,1312100827);
        $this->config = parse_ini_file($sconfig_file);
        if (!is_array($this->config))
            throw new Exception('config file is wrong '.$sconfig_file,1312061659);
                $dbFile = getcwd().DIRECTORY_SEPARATOR.'log';
        if (!is_dir($dbFile))
            mkdir($dbFile);
        $dbFile .= DIRECTORY_SEPARATOR.'mastro';
        $this->imageDb = new SQLite3($dbFile);
        echo 'Setting up database'.PHP_EOL;
        $this->imageDb->exec('CREATE TABLE IF NOT EXISTS product (
code TEXT PRIMARY KEY,
ean13 TEXT UNIQUE ON CONFLICT REPLACE,
descrizione TEXT,
vendita NUMERIC,
image TEXT,
size NUMERIC,
md5 TEXT,
modify_date NUMERIC,
create_date NUMERIC,
expire_date NUMERIC,
corrupted NUMERIC
);');
         $this->imageDb->exec('CREATE TABLE IF NOT EXISTS related (
key TEXT PRIMARY KEY,
sku TEXT,
UNIQUE (key, sku) ON CONFLICT IGNORE
);');
        echo 'Fixing database'.PHP_EOL;
        $this->imageDb->exec('UPDATE product SET expire_date = DATETIME(\'now\') WHERE expire_date=\'\' OR expire_date IS NULL;');
        $this->imageDb->exec('DELETE FROM related;');
        register_shutdown_function(array($this, 'execOnShutdown'));
    }
    /**
     * Starts data import
     * @throws Exception
     */
    public function import() {
        
        
        if (key_exists('MASTRO_COMMAND',$this->config)) {
            echo 'Export command '.$this->config['MASTRO_COMMAND'].PHP_EOL;
            $timeout = 30;
            $commandHandle = proc_open($this->config['MASTRO_COMMAND'],array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
                 ), $pipes);
            if (is_resource($commandHandle)) {
                $startProcTime = microtime(true);
                $procStatus = proc_get_status($commandHandle);
                $ticks = 0;
                while(microtime(true) < $startProcTime + $timeout && $procStatus['running']) {
                    if ($ticks++ % 5000 == 0 && $ticks > 1)
                        echo 'Export in progress '.ceil($ticks/5000).PHP_EOL;
                    usleep(10);
                    $procStatus = proc_get_status($commandHandle);

                }
                if (microtime(true) > $startProcTime + $timeout) {
                    echo 'Export command executed '.PHP_EOL;
                    proc_terminate($commandHandle,9);
                    posix_kill($procStatus['pid'],9);
                }

                proc_close($commandHandle);
            }
        }
        $this->mastroCsvHandle = fopen($this->config['MASTRO_CSV_FILE'], 'r');
        if (key_exists('FTP_SERVER', $this->config)) {
            $this->ftp = ftp_connect($this->config['FTP_SERVER']);
            if (!is_resource($this->ftp))
                throw new Exception('Wrong FTP server server:'.$this->config['FTP_SERVER'],1312100839);
            if (key_exists('FTP_USER', $this->config)) {
                if (!ftp_login($this->ftp, $this->config['FTP_USER'], $this->config['FTP_PASSWORD']))
                    throw new Exception('Wrong FTP login user:'.$this->config['FTP_USER'].' password:'.$this->config['FTP_PASSWORD'],1312100835);
            }
            if (!key_exists('FTP_BASE_DIR', $this->config)) {
                 $this->config['FTP_BASE_DIR'] = '';
            }
            $this->config['FTP_BASE_DIR'] = ftp_pwd($this->ftp).DIRECTORY_SEPARATOR.$this->config['FTP_BASE_DIR'];
            ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
        }
        if (is_resource($this->ftp))
            echo 'FTP connection with  '.$this->config['FTP_SERVER'].PHP_EOL;
        if (key_exists('CONVERT_COMMAND',$this->config))
            echo 'Image conversion command   '.$this->config['CONVERT_COMMAND'].PHP_EOL;
        $magentoCsvFilname = getcwd().DIRECTORY_SEPARATOR.'magento_csv';
        if (!is_dir($magentoCsvFilname))
            mkdir($magentoCsvFilname);
        $magentoCsvFilname .= DIRECTORY_SEPARATOR.'import.csv';
        if (is_file($magentoCsvFilname))
            unlink($magentoCsvFilname);
        $this->magentoCsvHandle = fopen($magentoCsvFilname, 'w');
        
        $this->categories =json_decode(str_replace("'", '"', $this->config['CATEGORIES']),true);
        if (!is_array($this->categories))
            throw new Exception('Associative categories in config.ini are wrong',1312061657);
        if (!is_resource($this->mastroCsvHandle))
            throw new Exception('Unable to open mastro CSV file '.$this->config['MASTRO_CSV_FILE'],1312030807);
        $this->mastroCsvLastpos = filesize($this->config['MASTRO_CSV_FILE']);
        $row = '';
        $mastroProduct = new MastroProduct($this);
        $rowCount = 0;
        $byteCount = 0;
        fseek($this->mastroCsvHandle, 0);
        fseek($this->mastroCsvHandle, 0);
        while (($buffer = fgets($this->mastroCsvHandle, 4096)) !== false  ) {
            
            $byteCount += (strlen($buffer)+2);
            $lastchar = ord(substr($buffer,strlen($buffer)-1));
            
            while (
                    $lastchar==10 ||
                    $lastchar==13
                    ) {
                $buffer = substr($buffer,0,strlen($buffer)-1);
                $lastchar = ord(substr($buffer,strlen($buffer)-1));
            }

            if (strlen($row) > 0)
                $row .= '<br/>';
            $row .= $buffer;
            if (preg_match('/\*\*$/', $buffer)) {
                
                $row = iconv ('WINDOWS-1252','UTF-8',$row);
                $mastroProduct->importFromCsvRow($row);
                $magentoProduct = $mastroProduct->createMagentoProduct();
                
                if ($magentoProduct instanceof MagentoProduct) {
                    if (ftell($this->magentoCsvHandle) == 0)
                        fwrite($this->magentoCsvHandle, $magentoProduct->getCsvHeaders().PHP_EOL);
                    fwrite($this->magentoCsvHandle, $magentoProduct->getCsvRow().PHP_EOL);
                }
                
                $rowCount++;
                $row = '';
                if ($rowCount/100 == (int) ($rowCount/100)) {
                    
                    $microtime = microtime(true)-$this->startTime;
                    $rimaningTime = $microtime*$this->mastroCsvLastpos/$byteCount-$microtime;
                    echo 'Progress:'.intval($byteCount/$this->mastroCsvLastpos*100-1)." %\t";
                    echo 'Products: '.$rowCount."\t";
                    echo 'Remaning time: '.intval($rimaningTime/60+1)."m\t";
                    echo 'ETA:'.date('G:i:s',$this->startTime+$microtime+$rimaningTime).PHP_EOL; 
                }
            }
        }
        fclose($this->mastroCsvHandle);
        fclose($this->magentoCsvHandle);
        if (is_resource($this->ftp)) {
            ftp_chdir($this->ftp, $this->config['FTP_BASE_DIR']);
            $imagesSubDirs = array('var','import');
            foreach ($imagesSubDirs as $dir) {
                        $fileList = ftp_nlist($this->ftp,'.');
                        if (!in_array($dir, $fileList)) {
                            ftp_mkdir($this->ftp,$dir);
                        }
                        ftp_chdir($this->ftp,$dir);
   
            }
            $fileList = ftp_nlist($this->ftp,'.');
            if (in_array('import.csv', $fileList))
                ftp_delete ($this->ftp, 'import.csv');
            ftp_put($this->ftp, 'import.csv',$magentoCsvFilname,  FTP_ASCII);
            ftp_close($this->ftp);
        }
         if (key_exists('UPDATE_MAGENTO_URL', $this->config)) {
         
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->config['UPDATE_MAGENTO_URL'].'?'.  http_build_query(array('message'=> urlencode($this->log))));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);  
         }
    }
    /**
     * Gets config parameter
     * @param null|string $key
     * @return string|array
     */
     public function getConfig ($key=null) {
         if ($key == null)
            return $this->config;
         else if (key_exists($key,$this->config))
            return $this->config[$key];
         else
            return false; 
     }
     /**
      * Gets a Magento Category name from Mastro Category Name
      * @param string $mastroCode
      * @return string
      */
     public function getCategory ($mastroCode) {
         if (key_exists($mastroCode, $this->categories))
                 return $this->categories[$mastroCode];
         else return '';
     }
     /**
      * Returns Ftp resource
      * @return resource
      */
     public function getFtp() {
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
         $this->log .= $string.PHP_EOL;
     }
     /**
      * Called on export end
      */
     public function execOnShutdown() {
         $this->imageDb->exec('CREATE TABLE IF NOT EXISTS log (
datetime NUMERIC,
message TEXT
);');
         $error = error_get_last();
         if (is_array($error)){
            $this->log .= PHP_EOL."type\t" . $error["type"] . PHP_EOL;
            $this->log .= "message\t" . $error["message"] . PHP_EOL;
            $this->log .= "file\t" . $error["file"] . PHP_EOL;
            $this->log .= "line\t" . $error["line"] . PHP_EOL;
         }
         $this->imageDb->exec('INSERT INTO log (datetime,message) VALUES (
            DATETIME(\'now\'),
            \''.$this->imageDb->escapeString($this->log).'\'
            ); ');
         
         $this->imageDb->exec('DELETE FROM log WHERE datetime < DATETIME("now","-1 month");');
         $this->imageDb->close();
     }
}


