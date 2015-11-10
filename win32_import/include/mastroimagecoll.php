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
     * Array of remote image file names
     */
    private static $remoteImagenames = array();
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
        'CONVERT_COMMAND_THUMBNAIL'=>'small',
    );
    /**
     *Convert Command
     * @var string 
     */
    private $convertCommand;
    /**
     * Base name path
     * @var string
     */
    private $fileName;
    /**
     * File name
     * @var array
     */
    private $mastroFile;
    /**
     *Magento Path
     * @var string
     */
    private $magentoPath;
    /**
     * Magento Url
     * @var string
     */
    private $magentoUrl;
    /**
     * Images sub dirs
     * @var array
     */
    private $imagesSubDirs;
    /**
     * Image sufix
     * @var String
     */
    private $suffix;
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
        $this->mastroFile = $this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR') . DIRECTORY_SEPARATOR . $this->fileName['filename'];
        $this->magentoFileName = getcwd() . DIRECTORY_SEPARATOR . 'magento_images';
        if (!is_dir($this->magentoFileName))
            mkdir($this->magentoFileName);
        $this->magentoFileName .= DIRECTORY_SEPARATOR;
    }
    /**
     * Strips strange characters from name files
     * @param string $fileName
     * @return boolean
     */
    private function parseFilename ($fileName) {
        preg_match('/(.+?)(\.[^\.]+$|$)/', $fileName,$fileParts);
        if (!key_exists(1, $fileParts))
                return false;
        switch (PHP_OS) {
            case 'WINNT' :
                return str_replace(' ','_',strtolower(iconv('WINDOWS-1252', 'ASCII//TRANSLIT',trim($fileParts[1]))));
            default:
                return str_replace(' ','_',strtolower(iconv('UTF-8', 'ASCII//TRANSLIT',trim($fileParts[1]))));
        }
    }
    /**
     * Gets the file name from the name present in mastro csv
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
     */
    public function saveData() {
        $parsedFilename = $this->fileName['parsedFilename'];
        $data = $this->mastroProduct->getData();
        unset($data['qty']);             
        $fileName = self::$imageNames[$parsedFilename];
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('INSERT OR IGNORE INTO product (code,ean13,modify_date,create_date) VALUES (
            \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\',
            \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\',
            DATETIME(\'now\'),
            DATETIME(\'now\')
            ); ');
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET 
            descrizione=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['DESCRIZIONE']).'\',
            vendita=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['VENDITA']).'\',
            image=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($fileName).'\',
            image_remote=\''.$this->magentoPath.'\',
            size=\''.filesize($this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$fileName).'\',
            md5=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString(md5(serialize($data))).'\',
            expire_date=NULL,
            modify_date=DATETIME(\'now\')
            WHERE ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'
            ; ');
        if(key_exists('corrupted',$data) && $data['corrupted']==1)
            $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET 
            corrupted=\'1\'
            WHERE ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'
            ; ');
      
        $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET create_date=DATETIME(\'now\') WHERE ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\' AND (create_date=\'\' OR create_date IS NULL)');
    }
    /**
     * Checks if a file name exists and has been modified
     * @param string $fileName
     * @return bool
     */
    public function checkFile ($fileName) {
        $parsedFilename = $this->parseFilename($fileName);
        $size = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT size FROM product WHERE code =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\'');
        $corrupted = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT corrupted FROM product WHERE code =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($parsedFilename).'\'');
        return (
                is_file($this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$fileName) && 
                filesize($this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$fileName) != $size &&
                $corrupted != 1
                );
        
    }
    /**
     * Deletes the image, used usually on C99
     */
    public function deleteImage($eanCode) {
        $image = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT image FROM product
            WHERE ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($eanCode).'\'
            ; ');
        $imageRemote = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT image_remote FROM product
            WHERE ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($eanCode).'\'
            ; ');
        $image = substr($image, 0,1).DIRECTORY_SEPARATOR.substr($image, 2,1).DIRECTORY_SEPARATOR.$image;
        if (is_file($this->magentoFileName.$imageRemote)) {
            $this->deleteFtp($imageRemote);
            unlink($this->magentoFileName.$imageRemote);
        }
        if (is_file($this->magentoFileName.$image)) {
            $this->deleteFtp($image);
            unlink($this->magentoFileName.$image);
        }
    }
    /**
     * Resizes the image
     * @param string $fileName
     * @param string $convertCommand
     * @return string
     */
    public function resizeImage($fileName,$convertCommand) {
        $this->fileName = $this->getFileName($fileName);
        $this->convertCommand = $convertCommand;
        $this->createImagePath($this->fileName['filename']);
        if (
                $this->fileName['filename'] != '' &&
                (
                    $this->checkFile($this->fileName['filename']) ||
                    !is_file($this->magentoFileName . $this->magentoPath)
                ) &&
                is_string($this->mastroProduct->getProductFromCsv()->getConfig($this->convertCommand)) &&
                is_string($this->mastroProduct->getProductFromCsv()->getConfig(self::$mainImageConvert))

        ) {
            $this->imageConvert();
            if ($this->uploadFtp()) {
                $this->saveData();
            }
        }
        if (
                !is_file($this->magentoFileName . $this->magentoPath) ||
                $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT strftime(\'%s\',modify_date) FROM product WHERE code =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($this->fileName['parsedFilename']).'\'') == ''

           )
           $this->magentoUrl =  '';

        return $this->magentoUrl;
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
     * Returns if record has been modified
     * @param string $data
     * @return bool
     */
    public function getModifiedDescription($data,$fileName) {
        $descriptiveData = array();
        $descriptiveData['DESCRIZIONE']=$data['DESCRIZIONE'];
        $descriptiveData['TESTO']=$data['TESTO'];
        $descriptiveData['FILENAME']=$fileName;
        $fileSize = '';
        $mastroFile = $this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR') . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($mastroFile))
            $fileSize = filesize ($mastroFile);
        $descriptiveData['FILESIZE']=$fileSize;
        $descriptiveDataHash = md5(serialize($descriptiveData));
        $modified = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT description_md5 FROM product WHERE ean13 =\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'') != $descriptiveDataHash;
        if ($modified == true)
            $this->mastroProduct->getProductFromCsv()->getImageDb()->exec('UPDATE product SET 
                description_md5=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($descriptiveDataHash).'\'
                WHERE ean13 = \''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\'; ');
        return $modified;
        
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
    /**
     * Composes the image path, if image name is already present prepends EAN code
     */
    private function createImagePath() {
        $this->magentoPath = '';
        $this->magentoUrl = '+';
        
        $destinationFilename = $this->fileName['parsedFilename'];
        
        $mastroData = $this->mastroProduct->getData();
        if (array_key_exists('DESCRIZIONE', $mastroData)) {
            $destinationFilename = preg_replace('/[^[:alnum:]]/','_',strtolower(iconv('WINDOWS-1252', 'ASCII//TRANSLIT',trim($mastroData['DESCRIZIONE']))));
            if (
                    array_key_exists($destinationFilename, self::$remoteImagenames) &&
                    self::$remoteImagenames[$destinationFilename]!=$mastroData['FOTO_ARTICOLO']
            ) {
                $destinationFilename = preg_replace('/[^[:alnum:]]/','_',strtolower(iconv('WINDOWS-1252', 'ASCII//TRANSLIT',trim($mastroData['EAN13'])))).'_'.$destinationFilename;
            } 
            self::$remoteImagenames[$destinationFilename]=$mastroData['FOTO_ARTICOLO'];  
        }
        
        if (strlen($destinationFilename) > 2) {
            $imagesSubDir = substr($destinationFilename, 0, 1);
            $this->magentoPath = $imagesSubDir;
            $this->magentoUrl .= '/' . $imagesSubDir;
            if (!is_dir($this->magentoFileName . $this->magentoPath))
                mkdir($this->magentoFileName . $this->magentoPath);
            $imagesSubDir=substr($destinationFilename, 1, 1);
            $this->magentoPath .= DIRECTORY_SEPARATOR . $imagesSubDir;
            $this->magentoUrl .= '/' . $imagesSubDir;
            if (!is_dir($this->magentoFileName . $this->magentoPath))
                mkdir($this->magentoFileName . $this->magentoPath);
            $this->magentoPath .= DIRECTORY_SEPARATOR;
            $this->magentoUrl .= '/';
        }
        $this->suffix = '';
        if (key_exists($this->convertCommand, self::$imageDirs))
                $this->suffix = '_'.self::$imageDirs[$convertCommand];
        $this->magentoPath .= $destinationFilename . $this->suffix . '.jpeg';
        $this->magentoUrl .= $destinationFilename . $this->suffix . '.jpeg';
    }
    /**
     * 
     * Convertts the image
     */
    private function imageConvert() {
        $status = '';
        echo 'Converting image '.$this->fileName['filename'].' to '.$this->magentoPath.PHP_EOL;
        $data = $this->mastroProduct->getData();
        $imageRemote = $this->mastroProduct->getProductFromCsv()->getImageDb()->querySingle('SELECT image_remote FROM product
            WHERE ean13=\''.$this->mastroProduct->getProductFromCsv()->getImageDb()->escapeString($data['EAN13']).'\' ; ');
        $mastroImageFile = $this->mastroProduct->getProductFromCsv()->getConfig('BMP_DIR').DIRECTORY_SEPARATOR.$this->fileName['filename'];
        if (
                $imageRemote != ($this->magentoFileName . $this->magentoPath) &&
                is_file($this->magentoFileName.$imageRemote)
            ) {
            $this->deleteImage($data['EAN13']);
        }
        if (!is_file($mastroImageFile)) {
            echo 'Manca file di input '.$mastroImageFile.PHP_EOL;
        }
        switch (PHP_OS) {
            case 'WINNT' :
                $timeout = 20;
                $command = '"'.__DIR__.'\..\lib\imagick\convert.exe" ' . sprintf($this->mastroProduct->getProductFromCsv()->getConfig($this->convertCommand), $mastroImageFile, $this->magentoFileName . $this->magentoPath). ' 2>&1 ';
                $status = exec($command);
            break;
            default :
                $timeout = 20;
                $command = 'timeout -s SIGKILL '. $timeout . ' convert ' . sprintf($this->mastroProduct->getProductFromCsv()->getConfig($this->convertCommand), $mastroImageFile, $this->magentoFileName . $this->magentoPath). ' 2>&1 ';
                $status = exec($command);

                break;
        }

        if (strlen($status) > 0 ) {
            $this->mastroProduct->getProductFromCsv()->appendToLog('Error on image:'.$this->mastroFile.' '.$status);
        }
        if (
                !is_file($this->magentoFileName . $this->magentoPath) ||
                filesize($this->magentoFileName . $this->magentoPath) == 0
        ) {
            $this->magentoUrl='';
            if (is_file($this->magentoFileName . $this->magentoPath))
                unlink($this->magentoFileName . $this->magentoPath);
        }
    }
    /**
     * Uploads the image
     */
    private function uploadFtp() {
        $size = 0;
        $count = 0;
        $fileSize = null;
        if (is_file($this->magentoFileName . $this->magentoPath))
            $fileSize = filesize($this->magentoFileName . $this->magentoPath);
        do {
            $ftp = $this->mastroProduct->getProductFromCsv()->getFtp();
        if (
                $this->magentoUrl != '' &&
                is_resource($ftp) &&
                $this->mastroProduct->getProductFromCsv()->getConfig(self::$mainImageConvert) !== false &&
                is_file($this->magentoFileName . $this->magentoPath)
                ) {
            ftp_chdir($ftp, $this->mastroProduct->getProductFromCsv()->getConfig('FTP_BASE_DIR'));
            $imagesSubDirs = $this->getRemoteSubdirs($this->magentoPath);    
            foreach ($imagesSubDirs as $dir) {
                $fileList = ftp_nlist($ftp,'.');
                if (!in_array($dir, $fileList)) {
                    ftp_mkdir($ftp,$dir);
                }
                ftp_chdir($ftp,$dir);

            }
            $fileList = ftp_nlist($ftp,'.');
            if (
                    !in_array($this->fileName['parsedFilename'] .$this->suffix . '.jpeg', $fileList) ||
                     ftp_size ($ftp,$this->fileName['parsedFilename'] . $this->suffix . '.jpeg') != $fileSize
                ) {
                    echo 'Uploading image '. $this->magentoPath.PHP_EOL;
                    $fileBaseName = pathinfo($this->magentoPath,  PATHINFO_BASENAME);
                    ftp_put($ftp, $fileBaseName, $this->magentoFileName . $this->magentoPath,  FTP_BINARY);
            }
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
                   in_array($this->fileName['parsedFilename'] .$this->suffix . '.jpeg', $fileList)

                ) {
                    $size = ftp_size ($ftp,$this->fileName['parsedFilename'] . $this->suffix . '.jpeg') != filesize($this->magentoFileName . $this->magentoPath);
            }
            ftp_chdir($ftp, $this->mastroProduct->getProductFromCsv()->getConfig('FTP_BASE_DIR'));
        }
        $count++;
        } while (($size == $fileSize || $fileSize == null) && $count < 10);
        $updated = true;
        if ($count >=10) {
            $this->mastroProduct->getProductFromCsv()->appendToLog('Error on image:'.$this->mastroFile);
            $updated = false;
        }
        return $updated;
    }
    /**
     * Deletes the image trought FTP
     */
    private function deleteFtp($imageRemote) {
        $size = 0;
        $count = 0;
        $deleted = false;
        do {
            $ftp = $this->mastroProduct->getProductFromCsv()->getFtp();
        if (
                is_resource($ftp) &&
                $this->mastroProduct->getProductFromCsv()->getConfig(self::$mainImageConvert) !== false
                ) {
            ftp_chdir($ftp, $this->mastroProduct->getProductFromCsv()->getConfig('FTP_BASE_DIR'));
            $imagesSubDirs = $this->getRemoteSubdirs($imageRemote);
            foreach ($imagesSubDirs as $dir) {
                $fileList = ftp_nlist($ftp,'.');
                if (!in_array($dir, $fileList)) {
                    ftp_mkdir($ftp,$dir);
                }
                ftp_chdir($ftp,$dir);
            }
            $fileList = ftp_nlist($ftp,'.');
            $fileBaseName = pathinfo($imageRemote,  PATHINFO_BASENAME);
            if (
                    !in_array($fileBaseName, $fileList)
                ) {
                    echo 'Deleted image '.$fileBaseName.PHP_EOL;
                    ftp_delete($ftp,$fileBaseName);
            }
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
                   in_array($fileBaseName, $fileList)

                ) {
                    $size = ftp_size ($ftp,$fileBaseName) != filesize($this->magentoFileName .$imageRemote);
            }
            ftp_chdir($ftp, $this->mastroProduct->getProductFromCsv()->getConfig('FTP_BASE_DIR'));
        }
        $count++;
        } while ($deleted == true && $count < 10);
        $updated = true;
        if ($count >=10) {
            $this->mastroProduct->getProductFromCsv()->appendToLog('Error on image delete :'.$fileBaseName);
            $updated = false;
        }
        return $updated;
    }
    /**
     * get RemoteSubdirs
     * @param string $imageRemote
     * @return array
     */
    private function getRemoteSubdirs($imageRemote) {
        $imagesSubDirs = array('media','import');
        $dirname = pathinfo($imageRemote, PATHINFO_DIRNAME);
        $imagesSubDirs =array_merge($imagesSubDirs,explode(DIRECTORY_SEPARATOR, $dirname));
        return $imagesSubDirs;
    }
}