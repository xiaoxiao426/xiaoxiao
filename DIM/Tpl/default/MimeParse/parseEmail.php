<?php
$__script = dirname(__FILE__)."/test_message_decoder.php";
$mbox_file = 'php://stdin';

define('__TEST',1);

$fp=fopen($mbox_file,'r');
$result='';
while(!feof($fp)){
    $result.=fgets($fp,128);
}
fclose($fp);
$mail_file = "/var/www/html/DIM_CHAIRS/DIM/Tpl/default/MimeParse/mail.txt";
$fp=fopen($mail_file,'w');
if($fp){
	fwrite($fp,$result);
	fclose($fp);
}
else 
{
	die("fail to open the file\r\n");
}
if(!file_exists($__script))
{
	die("\n".'Test script '.$__script.' does not exist.'."\n".str_repeat('_',80)."\n");
}

$__test_options = array('parameters'=>array('File'=>$mail_file));
ob_start();
require($__script);
$output=ob_get_contents();
ob_end_clean();
$generated=dirname(__FILE__)."/mbox.txt";
if(!($file = fopen($generated, 'wb')))
{
	die('Could not create the generated output file '.$generated."\n");
}
if(!fputs($file, $output) || !fclose($file))
{
	die('Could not save the generated output to the file '.$generated."\n");
}
echo date("Y-m-d H:i:s")."OK\r\n";
?>