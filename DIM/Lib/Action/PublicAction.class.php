<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

class PublicAction extends Action {

	public function CasesShadowUpdate()
	{
		set_time_limit(0);
		$model = M("UnitConcernIP");
		$time = date("Y-m-d H:i:s",time()- 3600);
		$list = $model->where("CreateTime>'$time'")->select();
		for($i=0;$i<count($list);$i++)
		{
			$ip = $list[$i]['IP'];
			$sql = "update Cases set ConcernFlag=1 where Subject='".long2ip($ip)."';";
			$res = $model->query($sql);
			$sql = "select CaseID from CaseSummery where IP=$ip";
			$res = $model->query($sql);
			if($res !== false && count($res) > 0)
			{
				$caseID = $res[0]['CaseID'];
				$sql = "update Cases set ConcernFlag=1 where CaseId='$caseID';";
				$res = $model->query($sql);
			}
		}
		unset($model);
		$model = M("CasesShadow");
		$sql = "delete from CasesShadow;";
		$res = $model->query($sql);
		$sql = "insert into CasesShadow select * from Cases where Cases.EvidenceNum>1 and Cases.CaseType=0;";
		$res = $model->query($sql);		
	//	$sql = "update Cases set CaseRule=1 where Cases.EvidenceNum>1";
	//	$sql = "update Cases set CaseRule='NBOS汇报DDOS' where TID=300005 and CaseId like 'NJo-%'";
	}
	
	public $debug = true;
	public function import()
	{
		set_time_limit(0);
		$fileName = "unit.txt";
		$file = fopen($fileName,"r");
		$res= array();
		$str = "";
		$i = 1;
		$name="Users";
		$model = D ($name);
		$userData = array();
		while(!feof($file))
		{
			$line = fgets($file);
			$line = str_replace("\r\n","",$line);
			$line = str_replace("\n","",$line);
			$array = split(" ",$line);
			if(!isset($array) || count($array) < 1)
			{
				fileWrite(__LINE__.$line);
				continue;
			}
			$unit = $array[0];
			$sql = "select id from Unit where UnitName='".$unit."'";
			$res1 = $model->query($sql);
			if($res1 === false || count($res1) != 1)
			{
				fileWrite(__LINE__.$model->getLastSql());
				continue;
			}
			fileWrite(__LINE__);
			$id = $res1[0]['id'];
			$emailArray = split("@",$array[1]);
			if(count($emailArray) < 1)
			{
				fileWrite(__LINE__.$line);
				continue;
			}
			$userData['name'] = $emailArray[0];
			$userData['password'] = "88f411db3247e5b40c2ce490bcf1d6ae";
			$userData['power']  = 0;
			$userData['emailAddress']  = $array[1];
			$userData['realName'] = $emailArray[0];
			$res = $model->add($userData);

			if($res === false)
			{
				fileWrite(__LINE__.$model->getLastSql());
				continue;
			}
			fileWrite(__LINE__);
			$uid=$model->getLastInsID();
			$uname =$unit;
			$email = $array[1];
			$sql = "INSERT INTO `UnitUsersRelation` VALUES (NULL,$id,'$uname','$email',$uid,0);";
			$res = $model->query($sql);
			if($res === false)
			{
				fileWrite(__LINE__.$line);
				continue;
			}
			
			fileWrite(__LINE__);
			$sql = "update `Unit` set Email='$email',UserId=$uid where id=$id;";
			$res = $model->query($sql);
			if($res === false)
			{
				fileWrite(__LINE__.$line);
				continue;
			}
		}

		fclose($file);
	}
	// 检查用户是否登录
	public function send()
	{
		return;
		set_time_limit(0);
		$data['TID'] = 110001;

		$model = M("CaseSummery1");
		$end = 10000;
		$list = $model->limit("0,$end")->select();
		if($list === false)
		{
			die("hehe");
		}
		for($i=0;$i<count($list);$i++)
		{
			unset($list[$i]['id']);
			unset($list[$i]['CaseID']);
		}
		$data["values"] = $list;
		
		$urlcon = "http://chairs26.cert.edu.cn/DIM_CHAIRS/DIM/index.php/Public/CaseReportPHP";
		$data_string = json_encode($data);

		$ch = curl_init($urlcon);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',                                                                                
    'Content-Length: ' . strlen($data_string))                                                                       
		);

		$result = curl_exec($ch);
		print_r($result);
	}

	public function CaseReportPHP()
	{
		set_time_limit(0);
		//fileWriteArray($_POST);
		$array = file_get_contents("php://input");
		$array = json_decode($array,true);

		if(!isset($array['TID']) || !isset($array['values']) || count($array['values']) == 0)
		{
			$this->ajaxReturn("","wrong format",0);
		}
		fileWriteArray($array,"nbos.txt");
		$res = $this->CaseReport($array['TID'],$array['values']);
		if($res == 1)
		{
			$this->ajaxReturn("","success",1);
		}
		else {
			$this->ajaxReturn("","insert error",0);
		}
	}

	public function CaseReportAJAX()
	{
	//	$this->ajaxReturn("","调试中",0);
	//	return;
		set_time_limit(0);
		$tid = $_REQUEST['TID'];
		$values = $_REQUEST['values'];
		$array = json_decode($values,true);
		if(!isset($tid) || !isset($array) || count($array) ==0)
		{
			$this->ajaxReturn("","wrong format",0);
		}
		fileWrite($values,"nbos.txt");
		$res = $this->CaseReport($tid,$array);
		if($res == 1)
		{
			$this->ajaxReturn("","success",1);
		}
		else {
			$this->ajaxReturn("","insert error",0);
		}
	}

	private function CaseReport($tid,&$data)
	{
		set_time_limit(0);
		$model = D("CaseSummery");
		$tcid = $model->query("select TcId from ThreatType where TID=$tid");
		if($tcid === false || count($tcid) != 1)
		{
			return -1;
		}
		$tcid = $tcid[0]['TcId'];
		for($i=0;$i<count($data);$i++)
		{
			$data[$i]['TcID'] = $tcid;
			$data[$i]['TID'] = $tid;
		}
		$res = $model->myAddAll($data);
		if($res === -1)
		{
			return -1;
		}
		return 1;
	}
	public function getAllReadyTable()
	{
		set_time_limit(0);
		//管理员首页索引表格
		$model = M('UnitProgressStateStat');
		if(empty($model))
		{
			$this->error("数据错误1!");
		}
		$sql = "delete from UnitProgressStateStat;";
		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误2!");
		}
		$sql = "SELECT COUNT(*) AS tp_count,Unit,ProgressState FROM `Cases`,Unit where `Cases`.Unit=`Unit`.UnitName and `Cases`.DeleteFlag=0 group by Unit,ProgressState;";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误3!");
		}

		$res = $model->addAll($list);
		if($res === false)
		{
			$this->error("数据错误4!");
		}

		unset($model);

		$model = M('UnitThreatGroupStat');
		if(empty($model))
		{
			$this->error("数据错误1!");
		}
		$sql = "delete from UnitThreatGroupStat;";
		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误2!");
		}
		$sql = "SELECT COUNT(*) AS tp_count,Unit,GroupID,TID FROM `Cases`,Unit where `Cases`.Unit=`Unit`.UnitName and `Cases`.DeleteFlag=0  group by Unit,GroupID,TID";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误3!");
		}

		$res = $model->addAll($list);
		if($res === false)
		{
			$this->error("数据错误4!");
		}

		unset($model);


		$model = M("ThreatDstIPViewCount");
		if(empty($model))
		{
			$this->error("数据错误2!");
		}

		$sql = "delete from ThreatDstIPViewCount;";
		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误1!");
		}

		$sql = "delete from ThreatSrcIPViewCount;";
		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误1!");
		}

		$sql = "select sum(IPCnt) as threatCount,TID
				from ThreatDstIPView
				group by TID;";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误3!");
		}

		$res = $model->addAll($list);
		if($res === false)
		{
			$this->error("数据错误4!");
		}

		$srcModel = M("ThreatSrcIPViewCount");

		$sql = "select sum(IPCnt) as threatCount,TID
				from ThreatSrcIPView
				group by TID;";
		$list = $srcModel->query($sql);
		if($list === false)
		{
			$this->error("数据错误5!");
		}

		$res = $srcModel->addAll($list);
		if($res === false)
		{
			$this->error("数据错误6!".$srcModel->getDbError());
		}
	}

	public function MyService(){
		import('@.Webserver.Server');
	}
	public function sendMail()
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
		$host = "smtp.163.com";
		$mail_from ="";
		$mail_from_pwd = "";
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
			//return 0;
		}
		else {
			//return 1;
		}
		//$this->display();
	}

	protected function checkUser() {
		if(!isset($_SESSION[C('USER_AUTH_KEY')])) {
			$this->assign('jumpUrl','Public/login');
			$this->error('没有登录');
		}
	}

	//过滤僵尸网络
	public function filter()
	{
		set_time_limit(0);
		$model = M("Cases_ch");
		$addModel=M("Cases_js");
		$i = 0;
		$base = 0;
		while(1)
		{
			$count = 100;
			$begin = $i*$count+$base;
			$sql = "select  Subject  from  Cases_ch where TcID=6 limit $begin,$count ";
			$list = $model->query($sql);
			if($list === false)
			{
				echo "数据错误!<hr>";
				break;
			}
			if(count($list) < 1)
			{
				break;
			}
			for($j=0;$j<count($list);$j++)
			{
				$s = $list[$j]['Subject'];
				//if($res !== false && count($res)==1) continue;
				//if($list[$j]['id'] <= 437839) continue;
				$sql = "select * from Cases_ch  where `Subject` = '$s'";
				$res = $model->query($sql);
				if($res === false)
				{
					die("数据错误!");
				}
				for($k=0;$k<count($res);$k++)
				{
					$out=$addModel->add($res[$k]);
					if($out === false)
					{
						fileWrite($s.":".$addModel->getLastSql());
						echo "败!";
					}
					else
					{
						echo "功！";
					}
				}
			}
			$i++;
			//break;
		}
		echo "<hr>结束<hr>";
		$this->display();
	}
	public function getCH()
	{
		set_time_limit(0);
		$model = M("Cases");
		$addModel=M("Cases_ch");
		$i = 0;
		$base = 1300;
		while(1)
		{
			$count = 100;
			$begin = $i*$count+$base;
			$sql = "select  Subject  from  Cases group  by  Subject  having  count(Subject) > 1 limit $begin,$count ";
			$list = $model->query($sql);
			if($list === false)
			{
				echo "数据错误!<hr>";
				break;
			}
			if(count($list) < 1)
			{
				break;
			}
			for($j=0;$j<count($list);$j++)
			{
				$s = $list[$j]['Subject'];
				//if($res !== false && count($res)==1) continue;
				//if($list[$j]['id'] <= 437839) continue;
				$sql = "select * from Cases where `Subject` = '$s'";
				$res = $model->query($sql);
				if($res === false)
				{
					die("数据错误!");
				}
				for($k=0;$k<count($res);$k++)
				{
					$out=$addModel->add($res[$k]);
					if($out === false)
					{
						echo "败!";
					}
					else
					{
						echo "功！";
					}
				}
			}
			$i++;
			//break;
		}
		echo "<hr>结束<hr>";
		$this->display();
	}
	// 顶部页面
	public function top() {
		C('SHOW_RUN_TIME',false);			// 运行时间显示
		C('SHOW_PAGE_TRACE',false);
		$_SESSION['currpage'] = 2;
		$this->display();
	}
	// 尾部页面
	public function footer() {
		C('SHOW_RUN_TIME',false);			// 运行时间显示
		C('SHOW_PAGE_TRACE',false);
		$this->display();
	}
	// 菜单页面

	// 后台首页 查看系统信息
	public function main() {
		$info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式'=>php_sapi_name(),
            'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
            'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
            'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
		);
		$this->assign('info',$info);
		$this->display();
	}

	// 用户登录页面
	public function login() {

		if(!isset($_SESSION[C('USER_AUTH_KEY')]) || !isset($_SESSION["userPower"])) {
			if(isset($_SESSION['loginFalse']))
			{
				unset($_SESSION['loginFalse']);
				echo "<script>alert('请输入正确的用户名或密码!');</script>";
			}

			$this->display();
		}else{
			//	fileWrite($_SESSION["userPower"]);
			//	$this->redirect('Index/index');
			if($_SESSION["userPower"] == 2  || $_SESSION["userPower"] == 3)
			{
				$this->redirect('Admin/index');
			}
			else
			{
				$this->redirect('Index/index');
			}
		}
	}

	public function index()
	{
		//如果通过认证跳转到首页
		redirect(__APP__);
	}

	// 用户登出
	public function logout()
	{
		if(isset($_SESSION[C('USER_AUTH_KEY')])) {
			unset($_SESSION[C('USER_AUTH_KEY')]);
			unset($_SESSION);
			session_destroy();
			$this->assign("jumpUrl",__URL__.'/login/');
			$this->success('登出成功！');
		}else {
			$this->error('已经登出！');
		}
	}

	// 登录检测
	public function checkLogin() {
		if(empty($_POST['name'])) {
			$_SESSION['loginFalse']=1;
			$this->redirect('Public/login');
		}elseif (empty($_POST['password'])){
			$_SESSION['loginFalse']=1;
			$this->redirect('Public/login');
		}
		//生成认证条件
		$map            =   array();
		// 支持使用绑定帐号登录
		$map['name']	= $_POST['name'];

		//fileWriteArray($map);

		import ( '@.ORG.RBAC' );
		$authInfo = RBAC::authenticate($map);

		//fileWriteArray($authInfo);
		//使用用户名、密码和状态的方式进行认证
		if(false === $authInfo) {
			$_SESSION['loginFalse']=1;
			$this->redirect('Public/login');
		}else {

			if($authInfo['password'] != md5($_POST['password'])) {
				$_SESSION['loginFalse']=1;
				$this->redirect('Public/login');
			}
			$_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
			//	$_SESSION[C('USER_AUTH_KEY')] = 15;
			$_SESSION['email']	=	$authInfo['email'];
			$_SESSION['loginUserName'] =	$authInfo['name'];
			$_SESSION["userPower"] = $authInfo['power'];
			$_SESSION['administrator']		=	true;
			// 缓存访问权限
			// RBAC::saveAccessList();
			//$this->success('登录成功！');
			
			if($_SESSION["userPower"] == 2  || $_SESSION["userPower"] == 3)
			{
				$this->redirect('Admin/index');
			}
			else
			{
				$this->redirect('Index/index');
			}

		}
	}


	public function loginReturn($status,$info="")
	{

		$arr = array('status'=>$status,"info"=>$info);
		$result = json_encode($arr);
		$callback = $_GET['callback'];
		echo $callback."($result)";
		exit(0);
	}

	public function ajaxLogin() {
		if(empty($_REQUEST['name'])) {
			$_SESSION['loginFalse']=1;
			$this->loginReturn(0,"用户名不能为空");
		}elseif (empty($_REQUEST['password'])){
			$_SESSION['loginFalse']=1;
			$this->loginReturn(0,"密码不能为空");
		}
		//生成认证条件
		$map            =   array();
		// 支持使用绑定帐号登录
		$map['name']	= $_REQUEST['name'];

		import ( '@.ORG.RBAC' );
		$authInfo = RBAC::authenticate($map);

		//fileWriteArray($authInfo);
		//使用用户名、密码和状态的方式进行认证
		if(false === $authInfo) {
			$_SESSION['loginFalse']=1;
			$this->loginReturn(0,"用户名或密码不存在");
		}else {

			if($authInfo['password'] != md5($_REQUEST['password'])) {
				$_SESSION['loginFalse']=1;
				$this->loginReturn(0,"密码不正确");
			}
			$_SESSION[C('USER_AUTH_KEY')]	=	$authInfo['id'];
			//	$_SESSION[C('USER_AUTH_KEY')] = 15;
			$_SESSION['email']	=	$authInfo['email'];
			$_SESSION['loginUserName'] =	$authInfo['name'];
			$_SESSION["userPower"] = $authInfo['power'];
			$_SESSION['administrator']		=	true;
			// 缓存访问权限
			// RBAC::saveAccessList();
			//$this->success('登录成功！');
			if($_SESSION["userPower"] == 2  || $_SESSION["userPower"] == 3)
			{
				$this->loginReturn(1,"管理员");
			}
			else
			{
				$this->loginReturn(1,"普通用户");
			}

		}
	}
	// 更换密码
	public function changePwd()
	{
		$this->checkUser();
		//对表单提交处理进行处理或者增加非表单数据
		if(md5($_POST['verify'])	!= $_SESSION['verify']) {
			$this->error('验证码错误！');
		}
		$map	=	array();
		$map['password']= pwdHash($_POST['oldpassword']);
		if(isset($_POST['account'])) {
			$map['account']	 =	 $_POST['account'];
		}elseif(isset($_SESSION[C('USER_AUTH_KEY')])) {
			$map['id'] = $_SESSION[C('USER_AUTH_KEY')];
		}
		//检查用户
		$User    =   M("User");
		if(!$User->where($map)->field('id')->find()) {
			$this->error('旧密码不符或者用户名错误！');
		}else {
			$User->password	=	pwdHash($_POST['password']);
			$User->save();
			$this->success('密码修改成功！');
		}
	}
	public function profile() {
		$this->checkUser();
		$User	 =	 M("User");
		$vo	=	$User->getById($_SESSION[C('USER_AUTH_KEY')]);
		$this->assign('vo',$vo);
		$this->display();
	}
	public function verify()
	{
		$type	 =	 isset($_GET['type'])?$_GET['type']:'gif';
		import("@.ORG.Image");
		Image::buildImageVerify(4,1,$type);
	}
	// 修改资料
	public function change() {
		$this->checkUser();
		$User	 =	 D("User");
		if(!$User->create()) {
			$this->error($User->getError());
		}
		$result	=	$User->save();
		if(false !== $result) {
			$this->success('资料修改成功！');
		}else{
			$this->error('资料修改失败!');
		}
	}
}
?>