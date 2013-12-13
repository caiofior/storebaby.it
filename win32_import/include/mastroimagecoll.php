<?php
/**
 * Manages the immage colleciotn of Mastro
 * 
 */
class MastroImageColl {
    /**
     * Array of parsed and raw image files
     * @var array 
     */
    private static $imageNames = array();
    /**
     * Reference to mastro Product
     * @var MastroProduct
     */
    private $mastroProduct;
    /**
     * Main image convert
     * @var string
     */
    private static $mainImageConvert='CONVERT_COMMAND';
    /**
     *Directory of different images
     * @var array
     */
    private static $imageDirs =array(
        'CONVERT_COMMAND'=>'full',
        'CONVERT_COMMAND_THUMBNAIL'=>'thumbnail',
    );
    /**
     * Creatreslist of parsed name files and set up db for storing previus image sizes
     * @param MastroProduct $mastroProduct
     * @throws Exception
     */
    public function __construct(MastroProduct $mastroProduct) {
        $this->mastroProduct = $mastroProduct;
        $bmpDir = $this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR');
        $dirStream = opendir($bmpDir);
                
        if (!is_resource($dirStream))
            throw new Exception('Unable to open dir '.$bmpDir,1312051536);
    
        while (false !== ($fileName = readdir($dirStream))) { 
            self::$imageNames[$this->parseFilename($fileName)] = $fileName;
        
        }
    }
    /**
     * Stips stange characters from name files
     * @param string $fileName
     * @return boolean
     */
    private function parseFilename ($fileName) {
        preg_match('/(.+?)(\.[^\.]+$|$)/', $fileName,$fileParts);
        if (!key_exists(1, $fileParts))
                return false;
        return str_replace(' ','_',strtolower( iconv('UTF-8', 'ASCII//TRANSLIT',trim($fileParts[1]))));
    }
    /**
     * Gets the file name from athe name present in mastro csv
     * @param string $fileName
     * @return array
     */
    public function getFileName($fileName) {
        $parsedFilename = $this->parseFilename($fileName);
        if (!key_exists($parsedFilename,self::$imageNames))
                return array('parsedFilename' => $parsedFilename ,'filename'=>false);
        return array('parsedFilename' => $parsedFilename ,'filename' =>self::$imageNames[$parsedFilename]);
        
    }
    /**
     * Updates file size in db of a modified image
     * @param string $parsedFilename
     */
    public function saveData($parsedFilename,$data) {
        $fileName = self::$imageNames[$parsedFilename];
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('INSERT OR IGNORE INTO product (code,ean13,modify_date,create_date) VALUES (
            \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\',
            \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\',
            DATETIME(\'now\'),
            DATETIME(\'now\')
            ); ');
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET 
            ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\',
            descrizione=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['DESCRIZIONE']).'\',
            vendita=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['VENDITA']).'\',
            image=\''.$fileName.'\',
            size=\''.filesize($this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$fileName).'\',
            md5=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString(md5(serialize($data))).'\',
            expire_date=NULL,
            modify_date=DATETIME(\'now\')
            WHERE code = \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\'
            ; ');
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET create_date=DATETIME(\'now\') WHERE code = \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\' AND (create_date=\'\' OR create_date IS NULL)');
    }
    /**
     * Checks if a file name exists and has been modified
     * @param string $fileName
     * @return bool
     */
    public function checkFile ($fileName) {
        $parsedFilename = $this->parseFilename($fileName);
        $size = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT size FROM product WHERE code =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\'');
        return is_file($this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$fileName) && 
        filesize($this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$fileName) != $size;
        
    }
    /**
     * Resizes the image
     * @param string $fileName
     * @param string $convertCommand
     * @return string
     */
    public function resizeImage($fileName,$convertCommand) {
                $fileName = $this->getFileName($fileName);
                $mastroFile = $this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR') . DIRECTORY_SEPARATOR . $fileName['filename'];
                $magentoFileName = getcwd() . DIRECTORY_SEPARATOR . 'magento_images';
                if (!is_dir($magentoFileName))
                    mkdir($magentoFileName);
                $magentoFileName .= DIRECTORY_SEPARATOR;
                $imagesSubDirs = array('media','catalog','product');
                if (key_exists($convertCommand, self::$imageDirs)) {
                    $magentoFileName .= self::$imageDirs[$convertCommand];
                    if (!is_dir($magentoFileName))
                        mkdir($magentoFileName);
                    $magentoFileName .= DIRECTORY_SEPARATOR;
                    $imagesSubDirs[]=self::$imageDirs[$convertCommand];
                }
                $magentoPath = '';
                $magentoUrl = '';
                if (strlen($fileName['parsedFilename']) > 2) {
                    $imagesSubDir = substr($fileName['parsedFilename'], 0, 1);
                    $imagesSubDirs[]=$imagesSubDir;
                    $magentoPath = $imagesSubDir;
                    if (!is_dir($magentoFileName . $magentoPath))
                        mkdir($magentoFileName . $magentoPath);
                    $imagesSubDir=substr($fileName['parsedFilename'], 1, 1);
                    $imagesSubDirs[]=$imagesSubDir;
                    $magentoPath .= DIRECTORY_SEPARATOR . $imagesSubDir;
                    $magentoUrl .= '/' . $imagesSubDir;
                    if (!is_dir($magentoFileName . $magentoPath))
                        mkdir($magentoFileName . $magentoPath);
                    $magentoPath .= DIRECTORY_SEPARATOR;
                    $magentoUrl .= '/';
                }
                $magentoPath .= $fileName['parsedFilename'] . '.jpeg';
                $magentoUrl .= $fileName['parsedFilename'] . '.jpeg';
                $return = $magentoUrl;
                if (
                        $fileName['filename'] != '' &&
                        (
                            $this->checkFile($fileName['filename']) ||
                            !is_file($magentoFileName . $magentoPath)
                        ) &&
                        $this->mastroProduct->getProductFromCsv()->getConfig($convertCommand) !== false &&
                        $this->mastroProduct->getProductFromCsv()->getConfig(self::$mainImageConvert) !== false
                ) {
                    $command = 'convert ' . sprintf($this->mastroProduct->getProductFromCsv()->getConfig($convertCommand), $mastroFile, $magentoFileName . $magentoPath). ' 2>&1';
                    $status = '';
                    $commandHandle = proc_open($command,array(
                        0 => array('pipe', 'r'),
                        1 => array('pipe', 'w'),
                        2 => array('pipe', 'w')
                         ), $pipes);
                    if (is_resource($commandHandle)) {
                        $startProcTime = microtime(true);
                        while(microtime(true) < $startProcTime + 5)
                        {
                            $procStatus = proc_get_status($commandHandle);
                            $status .= stream_get_contents($pipes[1]);
                            if($procStatus['running'])
                                usleep(100);
                            else
                                return true;
                        }
                    }
                    else $status = 'error';
                    proc_terminate($commandHandle);
                    if (strlen($status) > 0 ) {
                        $this->mastroProduct->getProductFromCsv()->appendToLog('Error on image:'.$mastroFile.' '.$status);
                       
                    }
                    if (
                            !is_file($magentoFileName . $magentoPath) ||
                            filesize($magentoFileName . $magentoPath) == 0
                    ) {
                        $return =  '';
                        if (is_file($magentoFileName . $magentoPath))
                            unlink($magentoFileName . $magentoPath);
                    } else {
                        if ($convertCommand == self::$mainImageConvert)
                            $this->saveData($fileName['parsedFilename'],$this->mastroProduct->getData());
                    }
                }
                if (
                        !is_file($magentoFileName . $magentoPath) ||
                        $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT strftime(\'%s\',modify_date) FROM product WHERE code =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($fileName['parsedFilename']).'\'') == ''
                        
                   )
                    $return = '';
                $ftp = $this->mastroProduct->getProductFromCsv()->getFtp();
                if (
                        $return != '' &&
                        is_resource($ftp) &&
                        $this->mastroProduct->getProductFromCsv()->getConfig(self::$mainImageConvert) !== false &&
                        is_file($magentoFileName . $magentoPath)
                        ) {
                    ftp_chdir($ftp, $this->mastroProduct->getProductFromCsv()->getConfig('FTP_BASE_DIR'));
                    foreach ($imagesSubDirs as $dir) {
                        $fileList = ftp_nlist($ftp,'.');
                        if (!in_array($dir, $fileList)) {
                            ftp_mkdir($ftp,$dir);
                        }
                        ftp_chdir($ftp,$dir);
   
                    }
                    $fileList = ftp_nlist($ftp,'.');
                    if (
                            !in_array($fileName['parsedFilename'] . '.jpeg', $fileList) ||
                             ftp_size ($ftp,$fileName['parsedFilename'] . '.jpeg') != filesize($magentoFileName . $magentoPath)
                        ) {
                            ftp_put($ftp, $fileName['parsedFilename'] . '.jpeg', $magentoFileName . $magentoPath,  FTP_BINARY);
                    }
                    ftp_chdir($ftp, $this->mastroProduct->getProductFromCsv()->getConfig('FTP_BASE_DIR'));
                    

                }
                    
                return $return;
    }
    /**
     * Check if textual data has been modified
     * @param array $data
     * @return bool
     */
    public function getModifiedData($data) {
        if ( $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT md5 FROM product WHERE ean13 =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'') != md5(serialize($data)))
            $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET 
                descrizione=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['DESCRIZIONE']).'\',
                vendita=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['VENDITA']).'\',
                md5=\''.md5(serialize($data)).'\',
                modify_date=DATETIME(\'now\')
                WHERE ean13 = \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'; ');
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET modify_date=DATETIME(\'now\') WHERE ean13 = \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\' AND (modify_date=\'\' OR modify_date IS NULL)');
        return $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT strftime(\'%s\',modify_date) FROM product WHERE ean13 =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'');

    }
    /**
     * Get create date time
     * @param array $data
     * @return int
     */
    public function getCreationData ($data) {
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET create_date=DATETIME(\'now\') WHERE ean13 = \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\' AND (create_date=\'\' OR create_date IS NULL)');
        return $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT strftime(\'%s\',create_date) FROM product WHERE ean13 =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'');
    }
}

