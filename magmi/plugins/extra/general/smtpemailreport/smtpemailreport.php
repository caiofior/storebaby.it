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
                     WHERE `path` LIKE "%/lesti_smtp/%" OR `path` LIKE "%/ident_general/%"') as $value) {
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
            $dir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'var'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR;
            chmod($dir,0777);
            if(is_file($dir.'import.csv')) {
                if(is_file($dir.'import.csv.gz'))
                    unlink($dir.'import.csv.gz');
                $status = exec('gzip -k '.$dir.'import.csv');
                if(is_file($dir.'import.csv.gz'))
                    $mail->addAttachment($dir.'import.csv.gz');
                else
                    $content .= 'Unable to create gzip file '.$status;
            }
            $mail->isHTML(false);

            $mail->Subject = 'Importazione prodotti del '.strftime('%Y-%m-%d %H:%M:%S') ;
            
            if (key_exists('message', $_POST))
                    $content .=$_POST['message'].PHP_EOL;
            $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    '..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
                    'state'.DIRECTORY_SEPARATOR.'progress.txt';
            if (is_file($file))
                $content .=file_get_contents($file).PHP_EOL;
            $mail->Body    = $content;

            if(!$mail->send()) {
               echo 'Message could not be sent.';
               echo 'Mailer Error: ' . $mail->ErrorInfo;
            }
	}

}