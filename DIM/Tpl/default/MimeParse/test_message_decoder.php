<?php
/*
 * test_message_decoder.php
 *
 * @(#) $Header: /opt2/ena/metal/mimeparser/test_message_decoder.php,v 1.13 2012/04/11 09:28:19 mlemos Exp $
 *
 */

require_once('rfc822_addresses.php');
require_once('mime_parser.php');

$message_file='/var/www/html/DIM_CHAIRS/DIM/Tpl/default/MimeParse/mail.txt';
$mime=new mime_parser_class;

/*
 * Set to 0 for parsing a single message file
 * Set to 1 for parsing multiple messages in a single file in the mbox format
 */
$mime->mbox = 0;

/*
 * Set to 0 for not decoding the message bodies
 */
$mime->decode_bodies = 1;

/*
 * Set to 0 to make syntax errors make the decoding fail
 */
$mime->ignore_syntax_errors = 1;

/*
 * Set to 0 to avoid keeping track of the lines of the message data
 */
$mime->track_lines = 1;

/*
 * Set to 1 to make message parts be saved with original file names
 * when the SaveBody parameter is used.
 */
$mime->use_part_file_names = 0;

/*
 * Set this variable with entries that define MIME types not yet
 * recognized by the Analyze class function.
 */
$mime->custom_mime_types = array(
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>array(
			'Type' => 'ms-word',
			'Description' => 'Word processing document in Microsoft Office OpenXML format'
			)
			);

			$parameters=array(
		'File'=>$message_file,

			/* Read a message from a string instead of a file */
			/* 'Data'=>'My message data string',              */

			/* Save the message body parts to a directory     */
			/* 'SaveBody'=>'/tmp',                            */
			/* Do not retrieve or save message body parts     */
		'SkipBody'=>1,
			);

			/*
			 * The following lines are for testing purposes.
			 * Remove these lines when adapting this example to real applications.
			 */
			if(defined('__TEST'))
			{
				if(IsSet($__test_options['parameters']))
				$parameters=$__test_options['parameters'];
				if(IsSet($__test_options['mbox']))
				$mime->mbox=$__test_options['mbox'];
				if(IsSet($__test_options['decode_bodies']))
				$mime->decode_bodies=$__test_options['decode_bodies'];
				if(IsSet($__test_options['use_part_file_names']))
				$mime->use_part_file_names=$__test_options['use_part_file_names'];
			}

			if(!$mime->Decode($parameters, $decoded))
			{
				echo 'MIME message decoding error: '.$mime->error.' at position '.$mime->error_position;
				if($mime->track_lines
				&& $mime->GetPositionLine($mime->error_position, $line, $column))
				echo ' line '.$line.' column '.$column;
				echo "\n";
			}
			else
			{
				echo 'MIME message decoding successful.'."\n";
				//	echo (count($decoded)==1 ? '1 message was found.' : count($decoded).' messages were found.'),"\n";

				for($message = 0; $message < count($decoded); $message++)
				{
					//	echo 'Message ',($message+1),':',"\n";
					//	echo "\r\n-----testsss----------";
					$Cc = "";
					$mimiMess = $decoded[$message];
					//	echo "\r\n----------From&to----------------------".count($decoded)."\r\n";
					$from = $mimiMess["ExtractedAddresses"]["from:"][0]["address"];
					//echo "\r\nFrom:".$mimiMess["ExtractedAddresses"]["from:"][0]["address"];
						
					$toArray = $mimiMess["ExtractedAddresses"]["to:"];
					for($i=0;$i<count($toArray);$i++)
					{
						$to = $toArray[$i]["address"];
						//echo "\r\nto:".$toArray[$i]["address"]."\r\n";
					}
					if(isset($mimiMess["ExtractedAddresses"]["cc:"]))
					{
						$CcArray = $mimiMess["ExtractedAddresses"]["cc:"];
						for($i=0;$i<count($CcArray)-1;$i++)
						{
							$Cc .= $CcArray[$i]["address"].",";
						}
						$Cc .= $CcArray[$i]["address"];
					}
						
					//echo "\r\n---subject------------\r\n";
					//获取subject
					if(isset($mimiMess["DecodedHeaders"]["subject:"]))
					{
						$subject = $mimiMess["DecodedHeaders"]["subject:"][0][0]["Value"];
						//echo $mimiMess["DecodedHeaders"]["subject:"][0][0]["Value"];
					}
					else {
						$subject = $mimiMess["Headers"]["subject:"];
						//echo "\r\nsubject:".$mimiMess["Headers"]["subject:"];
					}

					$partArray = $mimiMess["Parts"];
					$body = "";
					if(isset($mimiMess["Body"]))
					{
						$body = $mimiMess["Body"];
					}
					$attachList = Array();
					$tempBody = "";
					$tempBody  = decodePartsArray($partArray,$attachList);
					if(strlen($body) <= 0 && strlen($tempBody) > 0)
					{
						$body = $tempBody;
					}
					require '/var/www/html/DIM_CHAIRS/DIM/Tpl/default/MimeParse/ticket.php';
					$temp = insertTicket($from,$to,$Cc,$subject,$body,"EmailRec",$attachList);
					if(!$temp)
					{
						echo "邮件解析失败!";
					}
					//function insertTicket($from,$to,$Cc,$subject,$body,$type="EmailRec",&$attchList='')

				}
				for($warning = 0, Reset($mime->warnings); $warning < count($mime->warnings); Next($mime->warnings), $warning++)
				{
					$w = Key($mime->warnings);
					echo 'Warning: ', $mime->warnings[$w], ' at position ', $w;
					if($mime->track_lines
					&& $mime->GetPositionLine($w, $line, $column))
					echo ' line '.$line.' column '.$column;
					echo "\n";
				}
			}

			function decodePartsArray($parts,&$attachList)
			{
				$mailBody = "";
				for($i=0;$i<count($parts);$i++)
				{
					if(isset($parts[$i]['FileDisposition']) && $parts[$i]['FileDisposition']=="attachment")
					{//附件
						$attachList[sizeof($attachList)] = $parts[$i];
					}
					else if(isset($parts[$i]['Headers']["content-type:"]))
					{
						//邮件正文，判断包含text/plain
						if(strpos($parts[$i]['Headers']["content-type:"],"text/plain")!==false)
						{
							$mailBody = $parts[$i]["Body"];
						}
						else if(strpos($parts[$i]['Headers']["content-type:"],"multipart/alternative")!==false)
						{
							$tempPart = $parts[$i]['Parts'];
							$mailBody= decodeParts($tempPart,$attachList);
						}
					}
				}
				return $mailBody;
			}

			//decode part for body and attachment
			function decodeParts($parts)
			{
				$mailBody = "";
				for($i=0;$i<count($parts);$i++)
				{
					if(isset($parts[$i]['FileDisposition']) && $parts[$i]['FileDisposition']=="attachment")
					{//附件
						$name = isset($parts[$i]['FileName'])?$parts[$i]['FileName']:"unknow".date("y-m-d");
						$fp = fopen($name, "w");
						fwrite($fp, $parts[$i]['Body']);
						fclose($fp);
						echo "\r\nattach:".$parts[$i]['FileName'];
					}
					else if(isset($parts[$i]['Headers']["content-type:"]))
					{
						//邮件正文，判断包含text/plain
						if(strpos($parts[$i]['Headers']["content-type:"],"text/plain")!==false)
						{
							echo "\r\nbody:".$parts[$i]["Body"];
							$mailBody = $parts[$i]["Body"];
						}
						else if(strpos($parts[$i]['Headers']["content-type:"],"multipart/alternative")!==false)
						{
							$tempPart = $parts[$i]['Parts'];
							$mailBody= decodeParts($tempPart);
						}
					}
				}
				return $mailBody;
			}
			?>