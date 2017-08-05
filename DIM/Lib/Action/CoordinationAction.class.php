<?php
class CoordinationAction extends CommonAction
{
	public function add(){
		if(!isset($_REQUEST['id']))
		{
			$this->error("数据错误!");
		}
		$id = $_REQUEST['id'];

		$name = "Cases";
		$model= M($name);
		if(!empty($model))
		{
			$field = "`Cases`.*,`Users`.`name`";
			$join = "left join `Users` on `Users`.id=`Cases`.CaseResponsor";
			$vo = $model->where("`Cases`.id=$id")->field($field)->join($join)->select();
			$this->assign ( 'vo', $vo[0] );
		}
		else {
			$this->error("数据错误!");
		}

		$userList = $this->getContactUser($_SESSION[C('USER_AUTH_KEY')]);
		if($userList == -1)
		{
			$this->error("联系人信息错误!");
		}
		$this->assign("userList",$userList);
		//针对response的响应
		$this->assign("rid",$_REQUEST['rid']);

		$this->display();
	}

	//获取联系人
	public function getContactUser($uid)
	{
		if(!isset($uid))
		{
			return -1;
		}

		if (isset ( $_SESSION ['ContactUsersList'] )) {
			return $_SESSION ['ContactUsersList'];
		}

		$model = M("ContactUsers");
		$sql = "select ContactUsers.id,ContactUsers.emailAddress from ContactUsers,UsersRelationship
		where UsersRelationship.contact_id = ContactUsers.id
		and UsersRelationship.user_id=$uid";
		$list = $model->query($sql);

		if($list === false)
		{
			return -1;
		}
		//fileWrite($model->getLastSql());
		$_SESSION ['ContactUsersList'] = $list;
		return $list;
	}

	public function details(){
		$name = "Ticket";
		$model = M ( $name );
		$id = $_REQUEST ["id"];
		if(!empty($model))
		{
			$vo = $model->where("`Ticket`.id=$id")->select();

			$this->assign ( 'vo', $vo[0] );
		}
		$this->display();
	}

	//联系人信息
	public function cuGetInfoBy($field,$val)
	{
		if(!isset($field) || !isset($val))
		{
			return -1;
		}

		$model = M("ContactUsers");
		if(empty($model))
		{
			return -1;
		}

		$sql = "select * from ContactUsers where $field='$val';";
		$list = $model->query($sql);
		if($list === false)
		{
			return -1;
		}

		unset($model);
		return $list[0];
	}

	public function insert(){
		$name="Ticket";
		$model = D ($name);
		if (empty($model) || false === $model->create ()) {
			$this->error ( $model->getError () );
		}

		$model->Creator = $_SESSION[C('USER_AUTH_KEY')];
		$model->CreateTime = date("Y-m-d H:i:s");
		//保存当前数据对象
		$list=$model->add ();
		$ticketId = $model->getLastInsID();


		//插入ResponTicket
		$rModel = M("ResponseTicket");
		if(empty($rModel))
		{
			$this->error("数据错误!");
		}
		$rid = $_REQUEST['rid'];
		$info = $this->cuGetInfoBy("emailAddress",$model->ReceiverAddress);
		//fileWriteArray($info);
		if($info == -1)
		{
			$this->error("数据错误!");
		}
			
		if($rid)//更新
		{
			$list = $rModel->where('id='.$rid)->select();
			if($list === false || count($list) != 1)
			{
				$this->error("数据错误!");
			}
			$rModel->ResponseTime = date("Y-m-d H:i:s");
			$rModel->Responser = $_SESSION['loginUserName'];
			$rModel->id = $rid;

				
			if(strlen($list[0]['ResponseContent']) > 0)
			{
				$rModel->ResponseContent = $list[0]['ResponseContent']."<br /><a href='__ROOT__/DIM/index.php/Coordination/details/id/".$ticketId."'>向".$info['organization']."协查</a>";
			}
			else {
				$rModel->ResponseContent = "<a href='__ROOT__/DIM/index.php/Coordination/details/id/".$ticketId."'>向".$info['organization']."协查</a>";
			}

			$res = $rModel->save();
			//fileWriteArray($rModel);
			//fileWrite($rModel->getLastSql());
			if($res === false)
			{
				$this->error("响应更新失败!");
			}
		}
		else {//插入
			$rModel->CaseId = $model->CaseId;
			$rModel->Type = 0;
			$rModel->CreateTime= date("Y-m-d H:i:s");
			$rModel->Creator = $_SESSION[C('USER_AUTH_KEY')];
			$rModel->ResponseTime = date("Y-m-d H:i:s");
			$rModel->Responser = $_SESSION['loginUserName'];
			$rModel->ResponseContent = "<a href='__ROOT__/DIM/index.php/Coordination/details/id/".$ticketId."'>向".$info['organization']."协查</a>";

			$res = $rModel->add();
			//fileWriteArray($rModel);
			//fileWrite($rModel->getLastSql());
			if($res === false)
			{
				$this->error("响应更新失败!");
			}
		}
		$idAtt = $_REQUEST['uploadIds'];
		$ids = preg_split('/,/',$idAtt);
		$count = 0;

		$yId = "";
		$j = 0;
		for($i=0;$i<sizeof($ids)-1;$i++)
		{
			if($ids[$i])
			{
				$count++;
				$yId .= $ids[$i].",";
			}
		}
		if($ids[sizeof($ids)-1])
		{
			$count++;
		}

		//最后一个附件
		if(sizeof($ids) > 0)
		{
			$yId .= $ids[sizeof($ids)-1];
		}
		$attachList = Array();

		//	fileWrite($model->ReceiverAddress);
		//	fileWrite($yId);
		if($count > 0)
		{
			$attModel = M("Attachment");
			if(empty($attModel))
			{
				$this->error("插入失败!");
			}
			$condition = array ("id" => array ('in', $yId) );
			$list=$attModel->where ( $condition )->setField ( 'TicketId', $ticketId );
			$attachList = $attModel->where($condition)->select();
		}
		$model->Content .="<br />----------------------------------------------<br />
		回复有关此案件邮件时,请在邮件主题中包含:[#".$model->CaseId."]";
		$this->getCaseAttach($model->CaseId);
		$attachList[sizeof($attachList)]["FileName"] ="cases_".$model->CaseId."_attach.txt";
		$attachList[sizeof($attachList)]["SaveName"] ="cases_".$model->CaseId."_attach.txt";
		$this->sendMail($model->ReceiverAddress, $model->Subject, $model->Content,$model->Cc, $attachList);

		$id = $_REQUEST['baseId'];
		if ($list!==false) { //保存成功
			$this->redirect('/Incident/details/id/'.$id.'/Flag/0');
			//$this->assign ( 'jumpUrl', "__ROOT__//DIM/index.php/Incident/details/id/".$id );
			//	$this->success ('新增成功!');
		} else {
			//失败提示
			$this->error ('新增失败!');
		}
	}

	private function getCaseAttach($CaseID)
	{
		$model = M("Cases");
		if(empty($model))
		{
			$this->error("数据查询错误!");
		}
		$vo = $model->where("`Cases`.`CaseId`='$CaseID'")->select();
		if($vo === false)
		{
			$this->error("数据查询错误!");
		}
		$storeName = "cases_".$CaseID."_attach.txt";
		$savePath = dirname(dirname(dirname(dirname(__FILE__))))."/Public/Uploads/";
		$storeName = $savePath.$storeName;
		$fp = fopen($storeName, "w");
		if(!$fp)
		{
			$this->error("数据写入错误!".$storeName);
		}
		else
		{
			$str = "编号：".$vo[0]['CaseId']."\r\n";
			$str .= "主题：".$vo[0]['Subject']."\r\n";
			$str .= "威胁源描述：".$vo[0]['ThreatSrcInfo']."\t".$vo[0]['ThreatSrcSummary']."\r\n";
			$str .= "威胁宿描述：".$vo[0]['ThreatTargetInfo']."\t".$vo[0]['ThreatDstSummary']."\r\n";
			$str .= "判定原因：".$vo[0]['Phenomenon']."\r\n";
			fwrite($fp, $str);
			fclose($fp);
		}
	}

	public function sendMail($sendto_email,$subject,$body,$Cc,$attachList)
	{
		$sendto_email="lzzhu@njnet.edu.cn";
		$subject="test";
		$body="test test";
		$Cc = "";		
		error_reporting(E_STRICT);
		date_default_timezone_set("Asia/Shanghai");

		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Public/PHPMailer/classphpmailer.php';
		$mail = new PHPMailer(); //新建一个邮件发送类对象
		$mail->IsSMTP();                // send via SMTP
		$host = "carnation.njnet.edu.cn";
		$mail_from ="dim@njnet.edu.cn";
		$mail_from_pwd = "$#@!dim";
		//$sendto_name = "";
		//$subject = "test php mialer";
		//$body = "testbody";
		//
		$mail->Host =$host; // SMTP 邮件服务器地址，这里需要替换为发送邮件的邮箱所在的邮件服务器地址
		$mail->SMTPAuth = true;         // turn on SMTP authentication 邮件服务器验证开
		$mail->Username = $mail_from;   // SMTP服务器上此邮箱的用户名，有的只需要@前面的部分，有的需要全名。请替换为正确的邮箱用户名
		$mail->Password = $mail_from_pwd;        // SMTP服务器上该邮箱的密码，请替换为正确的密码
		$mail->From = $mail_from;    // SMTP服务器上发送此邮件的邮箱，请替换为正确的邮箱，$mail->Username 的值是对应的。
		//$mail->FromName =  ”网站用户”;  // 真实发件人的姓名等信息，这里根据需要填写
		$mail->CharSet = "utf-8";            // 这里指定字符集！
		$mail->Encoding = "base64";
		$mail->AddAddress($sendto_email,$sendto_name);  // 收件人邮箱和姓名
		if(strlen($Cc)>0)
		{
			$mail->AddAddress($Cc);   //添加Cc
		}

		//$mail->WordWrap = 50; // set word wrap
		for($i=0;$i<count($attachList);$i++)
		{
			$file = dirname(dirname(dirname(dirname(__FILE__))))."/Public/Uploads/".$attachList[$i]["SaveName"];
			$name = $attachList[$i]["FileName"];
			$mail->AddAttachment($file,$name); // 附件处理
		}
		//$mail->AddAttachment("test.txt"); // 附件处理
		//$mail->AddAttachment(“/tmp/image.jpg”, “new.jpg”);
		//$mail->IsHTML(true);  // send as HTML
		// 邮件主题
		$mail->Subject = $subject;

		// 邮件内容
		$mail->Body ="<html><head><meta http-equiv=”Content-Language” content=”zh-cn”>  <meta http-equiv=”Content-Type” content=”text/html; charset=utf-8″></head><body>$body</body></html>";
		$mail->AltBody ="text/html";
		if(!$mail->Send())
		{
			fileWrite("邮件错误信息: ".$mail->ErrorInfo);
			return 0;
		}
		else {
			return 1;
		}
	}
	public function upload() {
		//fileWrite("filePath:".APP_PATH."\tAPP_PUBLIC_PATH:".APP_PUBLIC_PATH."::moduleName::".MODULE_NAME);
		if(!empty($_FILES)) {//如果有文件上传
			// 上传附件并保存信息到数据库
			$this->_upload("Attachment");
		}
	}
}
?>