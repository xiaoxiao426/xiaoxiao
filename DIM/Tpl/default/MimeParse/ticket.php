<?php

//获取文件扩展名
function getExt($fileName)
{
	$pathinfo = pathinfo($fileName);
	if(isset($pathinfo['extension']))
	{
		return $pathinfo['extension'];
	}
	else {
		return "";
	}

}
//获取数据库连接
function getConn()
{
	$config = require dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/config.php";
	$DBHOST = $config['DB_HOST'].":".$config['DB_PORT'];
	$DBUSER = $config['DB_USER'];
	$DBPWD = $config['DB_PWD'];
	$DBNAME = $config['DB_NAME'];
	$conn = mysql_connect($DBHOST,$DBUSER,$DBPWD);
	if(!$conn)
	{
		die('数据库连接失败：'.mysql_error());
	}
	mysql_select_db($DBNAME) or die('no database can use');
	mysql_query("SET NAMES 'utf8'");
	mysql_query("SET CHARACTER_SET_CLIENT=utf8");
	mysql_query("SET CHARACTER_SET_RESULTS=utf8");

	return $conn;
}
/*正则解析CaseID*/
function getCaseId($subject)
{
	preg_match('/\[#\d{10}\]/', $subject,$caseId);
	if(sizeof($caseId) != 1)
	{
		return "";
	}

	$caseId = $caseId[0];
	$caseId = substr($caseId,2,10);
	return $caseId;
}
/*验证合法性，即不在用户表中的报告者的报告不做处理*/
function isLegal($from,&$con)
{
	$sql = "select `id` from `Users` where `emailAddress`='$from';";
	$res = mysql_query($sql);
	$num = mysql_num_rows($res);
	if($num > 0)
	{
		$rows = mysql_fetch_array($res);
		return $rows['id'];
	}
	else {
		return false;
	}
}
function insertTicket($from,$to,$Cc,$subject,$body,$type="EmailRec",&$attchList='')
{
	$con = getConn();

	if(!$con)
	{
		return  0;
	}
	$fromId=isLegal($from, $con);
	if(!$fromId)
	{
		echo "$from不在联系人\r\n";
		return 0;
	}

	$CreateTime = date("Y-m-d H:i:s");
	$caseId = getCaseId($subject);
	mysql_query('start transaction');
	mysql_query('SET autocommit=0');
	if(strlen($caseId) > 0)//判断为回复邮件
	{
		//
		$subject = iconv("GBK","utf-8",$subject);
		$body = iconv("GBK","utf-8",$body);
		//echo $body;
		//$body = iconv("ascii","GBK",$body);
		$sql = "INSERT INTO Ticket (`Type`,`Receiver`,`ReceiverAddress`,`CaseId`,`Cc`,`Subject`,`Content`,`Creator`,`CreateTime`)
			VALUES('$type','$to','$to','$caseId','$Cc','$subject','$body','$fromId','$CreateTime');";

		$res = mysql_query($sql);
		if(mysql_affected_rows() < 1)
		{
			mysql_query('rollback');
			mysql_close($con);
			return 0;
		}

		$ticketId = mysql_insert_id();		
		
		if(!$ticketId) {
			mysql_query('rollback');
			mysql_close($con);
			return 0;
		}
		$savePath = dirname(dirname(dirname(dirname(dirname(__FILE__)))))."/Public/Uploads/";
		//添加附件
		$i = 0;
		for($i<0;$i<count($attchList);$i++)
		{
			//先写文件
			//附件
			$name = isset($attchList[$i]['FileName'])?$attchList[$i]['FileName']:"unknow".date("y-m-d");
			$ext = getExt($name);
			$storeName = uniqid().".".$ext;
			$saveName = $savePath.$storeName;
			$fp = fopen($saveName, "w");
			if(!$fp)
			{
				mysql_query('rollback');
				mysql_close($con);
				return 0;
			}
			fwrite($fp, $attchList[$i]['Body']);
			fclose($fp);
			//echo $saveName."\r\n";
			//插入附件表
			$name = iconv("GBK","utf-8",$name);
			$sql = "INSERT INTO Attachment (`TicketId`,`FileName`,`SaveName`,`Flag`)
			VALUES('$ticketId','$name','$storeName','0');";
			$res = mysql_query($sql);
			if(mysql_affected_rows() < 1)
			{
				mysql_query('rollback');
				mysql_close($con);
				return 0;
			}
		}
	}
	else {//事件报告

	}
	mysql_query('commit');
	mysql_close($con);
	return 1;
}
?>