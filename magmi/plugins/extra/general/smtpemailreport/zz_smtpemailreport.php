<?php
class SmtpEmailReportPlugin extends Magmi_GeneralImportPlugin
{

	
public function getPluginInfo()
	{
		return array(
            "name" => "Import Report Smtp Mail Notifier",
            "author" => "caiofior",
            "version" => "0.1"
            );
	}
	
	public function getPluginParams($params)
	{
		$pp=array();
		foreach($params as $k=>$v)
		{
			if(preg_match("/^EMAILREP:.*$/",$k))
			{
				$pp[$k]=$v;
			}
		}
		return $pp;
	}
	public function afterImport()
	{
            if (!function_exists('PHPMailerAutoload'))
               require __DIR__.DIRECTORY_SEPARATOR.'phpmailer'.DIRECTORY_SEPARATOR.'PHPMailerAutoload.php';
            $config = array();
            foreach($this->selectAll(
                    'SELECT `path`,`value` FROM `core_config_data`
                     WHERE  `scope` = "default" AND ( `path` LIKE "%/lesti_smtp/%" OR `path` LIKE "%/ident_general/%" OR `path` = "web/unsecure/base_url")') as $value) {
                $config [$value['path']]=$value['value'];
            }
            $mail = new PHPMailer;
            if ($config['system/lesti_smtp/enable'] == 1) {
                
                $mail->isSMTP();
                $mail->Host = $config['system/lesti_smtp/host'];
                if ($config['system/lesti_smtp/username'] != '' && $config['system/lesti_smtp/password'] != '')
                    $mail->SMTPAuth = true; 
                $mail->Username = $config['system/lesti_smtp/username'];
                $mail->Password = $config['system/lesti_smtp/password'];
                if ($config['system/lesti_smtp/ssl'] != '')
                    $mail->SMTPSecure = $config['system/lesti_smtp/ssl'];
            }

            $mail->From = $config['trans_email/ident_general/email'];
            $mail->FromName = $config['trans_email/ident_general/name'];
            $mail->addAddress($config['trans_email/ident_general/email']);

            $mail->WordWrap = 50;
            
            $content = 'Import log';
            ini_set('memory_limit', '256M');
            $dir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'var'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR;
            chmod($dir,0777);
            if(is_file($dir.'import.csv')) {
                $status = exec('gzip -c '.$dir.'import.csv > '.$dir.'last_import.csv.gz');
                if(is_file($dir.'last_import.csv.gz'))
                    $mail->addAttachment($dir.'last_import.csv.gz');
                else
                    $content .= 'Unable to create gzip file '.$status;
            }
            if(is_file($dir.'excluded.csv')) {
                $status = exec('gzip -c '.$dir.'excluded.csv > '.$dir.'last_excluded.csv.gz');
                if(is_file($dir.'last_excluded.csv.gz'))
                    $mail->addAttachment($dir.'last_excluded.csv.gz');
                else
                    $content .= 'Unable to create gzip file '.$status;
            }
            $backuplLink = '';
            $dir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'var'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR;
            chmod($dir,0777);
            if (is_dir($dir)) {
                $dirArray = scandir ($dir,SCANDIR_SORT_DESCENDING);
                foreach ($dirArray as $dirItem) {
                    if ($dirItem != '.'  && $dirItem != '..' && $dirItem != '.htaccess') {
                        $backuplLink =  ' ftp://storebaby.it/public_html/var/backups/'.$dirItem; 
                        continue;
                    }
                        
                }
                
            }
            $mail->isHTML(false);

            $mail->Subject = 'Importazione prodotti del '.strftime('%Y-%m-%d %H:%M:%S') ;
            
            if (key_exists('message', $_POST))
                    $content .=$_POST['message'].PHP_EOL;
            $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'state'.DIRECTORY_SEPARATOR.'progress.txt';
            if (is_file($file)) {
                chmod($file,0666);
                $content .=file_get_contents($file).PHP_EOL;
            }
            $mail->Body    = $content.$backuplLink.PHP_EOL;

            if(!$mail->send()) {
               $this->log('Message could not be sent. Mailer Error: ' . $mail->ErrorInfo);
            } else {
               $this->log('Mail sent');
            }
	}

}