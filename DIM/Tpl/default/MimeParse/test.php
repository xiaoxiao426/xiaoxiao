<?php

/*
 * test.php
 *
 * @(#) $Id: test.php,v 1.9 2012/03/11 08:20:25 mlemos Exp $
 *
 */
$mbox_file='root';
$__tests=array(
		'mbox'=>array(
			'script'=>'test_message_decoder.php',
			'generatedfile'=>'mbox.txt',
			'options'=>array(
				'parameters'=>array(
					'File'=>$mbox_file,
),
			
)
),
);
define('__TEST',1);
//for($__different=$__test=$__checked=0, Reset($__tests); $__test<count($__tests); Next($__tests), $__test++)
//{
	$__name="mbox";
	$__script="test_message_decoder.php";
	if(!file_exists($__script))
	{
		echo "\n".'Test script '.$__script.' does not exist.'."\n".str_repeat('_',80)."\n";
		continue;
	}
	echo 'Test "'.$__name.'": ... ';
	flush();
	if(IsSet($__tests[$__name]['options']))
	{
		$__test_options=$__tests[$__name]['options'];
		print_r($__test_options);
	}
	else
	{
		$__test_options=array();
	}
	ob_start();
	require($__script);
	//die();
	$output=ob_get_contents();
	ob_end_clean();
	$generated=$__tests[$__name]['generatedfile'];
	if(!($file = fopen($generated, 'wb')))
	{
		die('Could not create the generated output file '.$generated."\n");
	}
	if(!fputs($file, $output) || !fclose($file))
	{
		die('Could not save the generated output to the file '.$generated."\n");
	}
//}


?>