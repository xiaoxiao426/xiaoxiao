<?php
class AdminAction extends CommonAction {

	function upload($uploadId="file",$type="text/plain")
	{
		if(($_FILES[$uploadId]["type"]))
		{
			if($_FILES[$uploadId]["type"] != $type){
				return -1;
			}
			if ($_FILES[$uploadId]["error"] > 0)
			{
				return -1;
			}
			else
			{
				//检查文价夹是否存在
				$filePath =  dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/MalWeb';
				if(!is_dir($filePath))
				{
					if(mkdir($filePath,0777))
					{

					}
					else {
						return -1;
					}
				}

				$fileName = $filePath ."/URL.txt";
				if (file_exists($fileName))
				{
					//如果存在先删除
					unlink($fileName);
				}
				if(move_uploaded_file($_FILES[$uploadId]["tmp_name"],$fileName))
				{
					return $returnName;
				}
				else
				{
					return -1;
				}
			}
		}
		else {
			return -1;
		}
	}



	//去掉重复行和空行,末尾加一行
	public function removeFileEmpty($fileName)
	{
		$filePath = $fileName;
		$fileArr = file($filePath);
		for($i=0;$i<count($fileArr);$i++)//去掉换行，最后一行多一个换行
		{
			$fileArr[$i] = str_replace("\n", "", $fileArr[$i]);
		}
		asort($fileArr);//排序
		$newFile = "";
		foreach(array_unique($fileArr) as $fa){
			if(!empty($fa))
			{
				if((substr(trim($fa),0,8))!='Revision')
				{
					$newFile.=trim($fa,' ')."\n";
				}
			}
		}

		$fp = @fopen($filePath,"w"); //以写的方式打开文件
		@fputs($fp, $newFile);
		@fclose($fp);
		return 1;
	}

	//malweb管理
	public function malWebUrlUpdate()
	{
		//写config文件
		$filePath =   dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/';
		if(!file_exists($filePath."MalWeb"))
		{
			if(mkdir($filePath."MalWeb",0777))
			{
				//fileWrite("创建成功");
			}
			else {
				$this->error("文件创建错误!");
			}
		}

		$fileName = $filePath."MalWeb/config.txt";
		$denity = $_POST['density'];
		$cycle = $_POST['cycle'];
		//fileWrite($fileName);
		$file = fopen($fileName, "w");
		if(!$file)
		{
			$this->error("文件打开错误!");
		}
		fwrite($file, $denity."\n".$cycle."\n\n");
		fclose($file);

		//处理上传文件
		if(!empty($_FILES) && !empty($_FILES["file"]["type"])) {//如果有文件上传
			// 上传附件并保存信息到数据库
			$flag = $this->upload("file","text/plain");
			if($flag == -1)
			{
				$this->error("文件上传错误!");
			}
		}

		//写URL文件
		$url = $_POST['url'];
		//	fileWrite($url);
		if(strlen($url) > 0)//如果该字段有内容
		{
			$fileName = $filePath."MalWeb/URL.txt";
			$file = fopen($fileName, "a+");
			if(!$file)
			{
				$this->error("文件写错误!");
			}
			fwrite($file, $url);
			fclose($file);
			$this->removeFileEmpty($filePath."MalWeb/URL.txt");

		}
		$this->redirect("/Admin/malWebUrl");
	}

	//统计结果
	public function malRes()
	{
		$model=M("CrawlLog");
		$map = Array();
		if( !empty ( $model ))
		{
			$this->_malList ( $model, $map );
		}

		$this->display();
	}

	protected function _malList($model, $map, $sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = 'EndTime';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		//$count = $model->count ( 'id' );
		$count = $model->where ( $map )->count ( 'id' );
		if ($count > 0) {
			import ( "@.ORG.Page" );
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = 30;
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据
			$voList = $model->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();

			//echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			//分页显示
			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
			$this->assign ( 'list', $voList );
			$this->assign ( 'sort', $sort );
			$this->assign ( 'order', $order );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
		}
		Cookie::set ( '_currentUrl_', __SELF__ );
		return;
	}


	//malweb管理
	public function malWebUrl()
	{
		$filePath =   dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/';
		//读取数据
		if(!file_exists($filePath."MalWeb"))
		{
			if(mkdir($filePath."MalWeb",0777))
			{
				//fileWrite("创建成功");
			}
			else {
				$this->error("数据错误!");
			}
		}

		if(!file_exists($filePath."MalWeb"))
		{
			$this->error("数据错误!");
		}
		$fileName = $filePath."MalWeb/config.txt";
		$denity = "";
		$cycle = "";
		if(file_exists($fileName))//存在
		{
			$file = fopen($fileName, "r");
			if(!$file)
			{
				$this->error("数据错误!");
			}
			//不为空
			if(!feof($file))
			{
				$denity = fgets($file);
			}
			if(!feof($file))
			{
				$cycle = fgets($file);
			}
			fclose($file);
		}
		$this->assign("denity",$denity);
		$this->assign("cycle",$cycle);
		if(file_exists($filePath."MalWeb/URL.txt"))
		{
			$this->assign("dflag",1);

			//读取内容
			$file = fopen($filePath."MalWeb/URL.txt", "r");
			if(!$file)
			{
				$this->error("数据错误!");
			}
			$content = fread($file, filesize($filePath));
			fclose($file);
			$this->assign("urlContent",$content);
		}

		$this->display();
	}

	//管理员下载malweb文件
	public function download()
	{
		$fileName = "URL.txt";
		$realName = "URL.txt";


		$filePath = dirname(dirname(dirname(dirname(__FILE__))))."/Public/Uploads/MalWeb/".$fileName;

		Header("Content-type:application/octet-stream");
		Header("Accept-Ranges: bytes");
		$realName = iconv("UTF-8","GBK",$realName);
		Header("Content-Disposition:attachment;filename=".$realName);
		$filePath = iconv("UTF-8","GBK",$filePath);//包含中文字符时需要转换
		//fileWrite($filePath);
		//$filePath = iconv("UTF-8","GBK",$filePath);

		if(file_exists($filePath) )//判断文件是否存在并打开
		{
			$file=fopen($filePath,"r");
			if($file)
			{
				//	fileWrite(fread($file,filesize($filePath))."\r\ntestsss");
				echo fread($file,filesize($filePath));
				fclose($file);
			}
			else {
				fileWrite("can not read file!");
			}

		}
		else
		{
			fileWrite("file not exists");
		}
	}
	//分配
	public function update()
	{
		$name = "Cases";
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		if($model->StartTime)
		{
			$timeCount = strtotime($model->StartTime)+3600*$model->DueTime;
			$model->DueTime =date("Y-m-d H:i:s",$timeCount);
		}
		$model->ProgressState = "open";
		// 更新数据
		$list=$model->save ();
		fileWrite($model->getLastSql());
		if (false !== $list) {
			//成功提示
			$this->redirect('Admin/index');
			//	$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			//	$this->success ('编辑成功!');
		} else {
			//错误提示
			$this->error ('编辑失败!');
		}
	}

	// 框架首页
	public function index() {

		$name = "Cases";
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$map = Array();
		$map['DeleteFlag'] = 0;
		$map['CaseType'] = 0;

		//查找各个单位
		$sql = "select * from Unit";
		$unitList = $model->query($sql);
		if($unitList === false)
		{
			$this->error("数据错误!");
		}
		//统计状态SELECT COUNT(*) AS tp_count,Unit FROM `Cases` where ProgressState='open' group by Unit;
		$sql = "SELECT COUNT(*) AS tp_count,Unit,ProgressState FROM `Cases`,Unit where `Cases`.Unit=`Unit`.UnitName and `Cases`.DeleteFlag=0  group by Unit,ProgressState";
		$sql = "select * from UnitProgressStateStat;";

		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误!");
		}
		for($i=0;$i<count($unitList);$i++)
		{
			$unitList[$i]['open'] = 0;
			$unitList[$i]['resolved'] = 0;
			for($j=0;$j<count($res);$j++)
			{
				if($unitList[$i]['UnitName'] == $res[$j]['Unit'])
				{
					if($res[$j]['ProgressState'] == 'open') $unitList[$i]['open'] = $res[$j]['tp_count'];
					if($res[$j]['ProgressState'] == 'resolved') $unitList[$i]['resolved'] = $res[$j]['tp_count'];
				}
			}
		}

		unset($res);


		$sql = "SELECT COUNT(*) AS tp_count,Unit,GroupID,TID FROM `Cases`,Unit where `Cases`.Unit=`Unit`.UnitName and `Cases`.DeleteFlag=0  group by Unit,GroupID,TID";
		$sql = "select * from UnitThreatGroupStat;";
		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误!");
		}

		//$gid = 1; $tid = 700004;back_door //网站后门
		//$gid = 1;	$tid = 700003;webshell //恶意网页
		//$gid = 2;	$tid = 700700;broken_host //被攻破主机
		//$gid = 2;	$tid = 600002;in_botnet //僵尸网络控制服务器（内部）
		//$gid = 2;	$tid = 600003;botnet_net //僵尸网络主机
		//$gid = 3;	$tid = 300002;ddos DDOS攻击
		//$gid = 4;	$tid = 600002;out_botnet //僵尸网络控制服务器（外部）
		//$gid = 4;$tid = 700700; potential_threat 潜在安全威胁

		for($i=0;$i<count($unitList);$i++)
		{
			$unitList[$i]['back_door'] = 0;
			$unitList[$i]['webshell'] = 0;
			$unitList[$i]['broken_host'] = 0;
			$unitList[$i]['in_botnet'] = 0;
			$unitList[$i]['botnet_net'] = 0;
			$unitList[$i]['ddos'] = 0;
			$unitList[$i]['out_botnet'] = 0;
			$unitList[$i]['potential_threat'] = 0;
				
			for($j=0;$j<count($res);$j++)
			{
				if($unitList[$i]['UnitName'] == $res[$j]['Unit'])
				{
					$tid = $res[$j]['TID'];
					$num = $res[$j]['tp_count'];
					switch($res[$j]['GroupID'])
					{
						case 1:
							if($tid == 700004)$unitList[$i]['back_door'] = $num;
							else if($tid == 700003)$unitList[$i]['webshell'] = $num;
							break;
						case 2:
							if($tid == 700700)$unitList[$i]['broken_host'] = $num;
							else if($tid == 600002)$unitList[$i]['in_botnet'] = $num;
							else if($tid == 600003)$unitList[$i]['botnet_net'] = $num;
							break;
						case 3:
							if($tid == 300002)$unitList[$i]['ddos'] = $num;
							break;
						case 4:
							if($tid == 600002)$unitList[$i]['out_botnet'] = $num;
							else if($tid == 700700)$unitList[$i]['potential_threat'] = $num;
							break;
						default:
							break;
					}//end of switch
				}
			}//end for
		}
		$this->assign("vo",$unitList);
		unset($res);
		$this->display();
	}

	// 框架首页
	public function index1() {
		$name = "Cases";
		$map = Array();
		$map['DeleteFlag'] = 0;
		$map['ProgressState'] = "new";
		$map['Cases.CaseType'] = 0;
		$model = M($name);

		if(!isset($_SESSION['loginUserName']))
		{
			$this->error("请重新登录!");
		}

		if( !empty ( $model ))
		{
			$this->_list ( $model, $map );
		}
		$this->display();
	}

	protected function _list($model, $map, $sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = 'StartTime';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		$count = $model->where ( $map )->count ( 'id' );
		if ($count > 0) {
			import ( "@.ORG.Page" );
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = 30;
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据
			$join = "left join `ThreatType` on `ThreatType`.`TID`= `Cases`.`TID`";
			$filed = "`Cases`.*,`ThreatType`.`Name`";
			$voList = $model->where($map)->join($join)->field($filed)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();

			//echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			//分页显示
			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
			$this->assign ( 'list', $voList );
			$this->assign ( 'sort', $sort );
			$this->assign ( 'order', $order );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
		}
		Cookie::set ( '_currentUrl_', __SELF__ );
		return;
	}

	//性能报告
	public function performanceReport()
	{
		$this->display();
	}
	//更新错误日志备注
	public function updateErrorLog()
	{
		$id=$_REQUEST['id'];
		$notes = $_REQUEST['notes'];

		if(empty($id) || empty($notes))
		{
			$this->AjaxReturn("","",0,"");
		}
		$ErrorLogs = new Model("ErrorLogs");
		$str = $this->getConnetManString();//获取配置字符串
		//	print_r($str);
		$list=$ErrorLogs->db(1, $str);
		$sql = "UPDATE ErrorLogs SET notes = '".$notes."' WHERE id = $id";
		$res = $ErrorLogs->query($sql);
		if($res === false)
		{
			$this->AjaxReturn("","",0,"");
		}
		else
		{
			$this->AjaxReturn("","",1,"");
		}
	}
	//故障信息细节查看
	public function viewLogInfo()
	{
		$ErrorLogs = new Model("ErrorLogs");
		$str = $this->getConnetManString();//获取配置字符串
		//	print_r($str);
		$list=$ErrorLogs->db(1, $str);
		$id = $_REQUEST ["id"];
		if(empty($ErrorLogs) || empty($id))
		{
			$this->error("数据查询错误!");
			return;
		}
		$sql = "SELECT id,code,level,description,starttime,stoptime,occurnum,bin(solution+0) as solution,result,alerttype,notes FROM ErrorLogs WHERE id=$id";
		$vo = $ErrorLogs->query($sql );
		if($vo[0]['level']==1)
		{
			$vo[0]['level'] = "低故障";
		}
		else if($vo[0]['level']==2)
		{
			$vo[0]['level'] = "中故障";
		}else if($vo[0]['level']==3)
		{
			$vo[0]['level'] = "高故障";
		}

		$solution = $vo[0]['solution'];
		$solution = sprintf("%05d",$solution);

		$var1 = substr($solution, 4,1);
		$var2 =  substr($solution, 3,1);
		$var3 =  substr($solution, 2,1);
		$var4 =  substr($solution, 1,1);
		$var5 =  substr($solution, 0,1);

		//echo $var1.$var2.$var3.$var4.$var5;
		if($var1 == 0 && $var2 == 0 && $var3 == 0 && $var4 == 0 && $var5 == 0)
		{
			$vo[0]['order'] = false;
			$vo[0]['extraTitle'] = "<h3 style=\"font-color:red\">该故障还未得到及时的处理！</h3>";
		}
		else
		{
			$vo[0]['order'] = true;
			$vo[0]['extraTitle'] = "<h3>该故障的处理日志</h3>";
			$allSolution="";
			if ($var1 == 1)
			{
				// 说明使用了“写故障日志”方案
				$allSolution .= "写故障日志  ";
			}
			if ($var2 == 1)
			{
				// 说明使用了“网页报警”方案
				$allSolution .= "网页报警  ";
			}
			if ($var3 == 1)
			{
				// 说明使用了“邮件报警”方案
				$allSolution .= "邮件报警  ";
			}
			if ($var4 == 1)
			{
				// 说明使用了“SMS报警”方案
				$allSolution.="SMS报警  ";
			}
			if ($var5 == 1)
			{
				// 说明使用了“重启进程”方案
				$allSolution .= "重启进程  ";
			}
			$vo[0]['allSolution'] = $allSolution;

			if ($vo[0]['result'])
			{
				$vo[0]['handleresult'] = "成功";//处理结果
			} else
			{
				$vo[0]['handleresult'] = "失败";
			}
			//echo "solution".$allSolution;
			//echo "result".$handleresult;
		}
		$alert= $vo[0]['alerttype'];//警报发送方式
		if($alert == 0)
		{
			$vo[0]['alerttype'] = "不发送";
		}
		else if($alert == 1)
		{
			$vo[0]['alerttype'] = "短信";
		}
		else if($alert == 2)
		{
			$vo[0]['alerttype'] = "邮件";
		}
		else if($alert == 3)
		{
			$vo[0]['alerttype'] = "网页";
		}

		$this->assign ( 'vo', $vo[0] );
		$this->display ();
	}
	//故障报告
	public function errorReport()
	{
		$ErrorLogs = new Model("ErrorLogs");
		$str = $this->getConnetManString();//获取配置字符串
		//	print_r($str);
		$list=$ErrorLogs->db(1, $str);

		$today_arr = getdate(time());
		$today_year = $today_arr['year'];
		$today_mon = $today_arr['mon'];
		$today_day = $today_arr['mday'];
		$start = date("Y-m-d H:i:s", mktime(0,0,0,$today_mon,$today_day-1,$today_year));
		$end = date("Y-m-d H:i:s");
		$query = "SELECT id,description,starttime,stoptime,solution+0,notes FROM ErrorLogs WHERE starttime >= '$start' and starttime <= '$end'";
		$list = $ErrorLogs->query($query);
		//	print_r($ErrorLogs->getLastSql());
		if($list === false)
		{
			$this->error("数据查询错误!");
			return;
		}
		$this->assign("errorLogs",$list);
		$this->display();
	}
	public function allocate()
	{
		$id = $_REQUEST ["id"];
		//获取类型
		$model = M("ThreatType");
		$list = $model->select();
		$this->assign("threatType",$list);


		//获取姓名
		$userModel = M("Users");
		if(!empty($userModel))
		{
			$userList = $userModel->field("id,name")->select();
			$this->assign("userList",$userList);
		}

		$name = "Ticket";
		$ticketModel = M($name);
		if(!empty($model))
		{
			//获取附加档案
			$join = "left join `Attachment` on `Attachment`.TicketId=`Ticket`.id";
			$field = "Attachment.id,Attachment.FileName,Attachment.SaveName";
			$list = $ticketModel->join($join)->field($field)->where("CaseId=$id AND `Attachment`.Type=0")->select();

			$this->assign("attList",$list);
		}
		$name = "Cases";
		$model = M ( $name );

		if(!empty($model))
		{
			$field = "`Cases`.*,`Users`.`name`";
			$join = "left join `Users` on `Users`.id=`Cases`.CaseResponsor";
			$vo = $model->where("`Cases`.id=$id")->field($field)->join($join)->select();

			$this->assign ( 'vo', $vo[0] );
		}
		$this->display();
	}
	public function changeDb()
	{
		$oldCaseModel = M("Cases");
		$oldTicketModel = M("Ticket");
		$oldEvidenceModel = M("Evidence");
			
		$newCaseModel = new Model("Cases");
		$list=$newCaseModel->db(1, "mysql://root:0000@127.0.0.1:3306/CHAIRS_NEW");
		//$list = $newCaseModel->select();
		//print_r($list);
		//echo "<hr>";
		$newTicketModel = new Model("Ticket");
		$list=$newTicketModel->db(1, "mysql://root:0000@127.0.0.1:3306/CHAIRS_NEW");
		//$list = $newTicketModel->select();
		//print_r($list);
		echo "<hr>";
		$newEvidenceModel = new Model("Evidence");
		$list=$newEvidenceModel->db(1, "mysql://root:0000@127.0.0.1:3306/CHAIRS_NEW");
		$list = $newEvidenceModel->select();
		print_r($list);
		/*$sql = "select * from Transactions";
		 echo "<hr>";
		 $Model2->
		 $list = $Model2->query($sql);
		 print_r($list);*/
			

		if(empty($oldCaseModel)|| empty($oldTicketModel) || empty($oldEvidenceModel))
		{
			$this->error("空模型");
			return;
		}
		/*$sql = "delete from `Cases`;alter table `Cases` auto_increment=1;";
		 $sql .= "delete from `Ticket`;alter table `Ticket` auto_increment=1;";
		 $sql .= "delete from `Evidence`;alter table `Evidence` auto_increment=1;";
		 //    	$res = $oldCaseModel->query($sql);
		 $res1 = $newCaseModel->query($sql);
		 echo $newCaseModel->getLastSql();
		 if($res1 === false)
		 {
		 $this->error("false");
		 return;
		 }
		 die();*/
		$list = $oldCaseModel->select();
		echo $oldCaseModel->getLastSql();
		echo count($list);
		$i = 0;
		$totalCount = 0;
		$newCaseModel->startTrans();
		$newEvidenceModel->startTrans();
		$newTicketModel->startTrans();
		for($i=0;$i<count($list);$i++)
		{
			$caseId = "2012";
			$caseId .= sprintf("%06d",$i+1);
			//case
			$newCaseModel->CaseId = $caseId;
			$newCaseModel->Subject = $list[$i]['Subject'];
			if($list[$i]['ThreatType'] == 12 || $list[$i]['ThreatType'] == 13
			|| $list[$i]['ThreatType'] == 14)
			{
				$newCaseModel->ThreatClassId = 9;
			}
			else {
				$newCaseModel->ThreatClassId = 0;
			}

			$newCaseModel->TID = $list[$i]['TID'];
			$newCaseModel->ThreatSrcInfo = $list[$i]['ThreatSourceInfo'];
			$newCaseModel->ThreatSrcSummary = "";
			$newCaseModel->ThreatTargetInfo = $list[$i]['ThreatDestInfo'];
			$newCaseModel->ThreatTargetSummary = "";
			$newCaseModel->Cause = "";
			$newCaseModel->Phenomenon = $list[$i]['Phenomenon'];
			$newCaseModel->Note = $list[$i]['Note'];
			$newCaseModel->Severity = $list[$i]['Severity'];
			$newCaseModel->Reliability = $list[$i]['Reliability'];
			$newCaseModel->StartTime = $list[$i]['StartTime'];
			$newCaseModel->LastTime = $list[$i]['LastTime'];
			//    		$newCaseModel->ActionTime = $list[$i]['Subject'];
			$newCaseModel->DueTime = $list[$i]['DueTime'];

			//    		$newCaseModel->ResolveTime = $list[$i]['Subject'];
			$newCaseModel->ProgressState = $list[$i]['ProgressState'];
			$newCaseModel->CaseResponsor = $list[$i]['CaseResponsor'];
			$newCaseModel->EvidenceNum = $list[$i]['EvidenceNum'];
			$newCaseModel->EmailNum = $list[$i]['EmailNum'];

			$newCaseModel->UpdateFlag = $list[$i]['Flag'];
			$newCaseModel->DeleteFlag = 0;

			$res = $newCaseModel->add();
			$lastId = $newCaseModel->getLastInsID();
			$threatId = $list[$i]['ThreatType'];
			$sql = "UPDATE `Cases` SET TID=$threatId WHERE id=$lastId";
			$res = $newCaseModel->query($sql);
			if($res === false)
			{
				$newCaseModel->rollback();
				$newEvidenceModel->rollback();
				$newTicketModel->rollback();
				return;
			}
			$map = array();
			$map['CaseID'] = $list[$i]['id'];
			//证据表
			$evidenceList = $oldEvidenceModel->where($map)->select();
			$totalCount += count($evidenceList);
			//echo $oldEvidenceModel->getLastSql()."<hr>";
			$j = 0;

			for($j=0;$j<count($evidenceList);$j++)
			{
				$newEvidenceModel->CaseId = $caseId;
				$newEvidenceModel->AnalyzerId = $evidenceList[$j]['AnalyzerID'];
				$newEvidenceModel->SeqNum = $evidenceList[$j]['SeqNum'];
				$newEvidenceModel->EvidenceType = $evidenceList[$j]['EvidenceType'];
				$newEvidenceModel->TimeFirst = $evidenceList[$j]['TimeFirst'];
				$newEvidenceModel->TimeLast = $evidenceList[$j]['TimeLast'];
				$newEvidenceModel->Confidence = $evidenceList[$j]['Confidence'];
				$newEvidenceModel->Content = $evidenceList[$j]['Content'];
				$newEvidenceModel->DstIP = 1;
				//print_r($newEvidenceModel);
				//					echo $newEvidenceModel->CaseId;
					
				$res = $newEvidenceModel->add();
				$lastId = $newEvidenceModel->getLastInsID();
				$sql = "UPDATE `Evidence` SET CaseId=$caseId WHERE id=$lastId";
				//				$newEvidenceModel->where("id=$lastId")->setField("CaseId",$caseId);
				$newEvidenceModel->query($sql);
				if($res === false)
				{
					$newCaseModel->rollback();
					$newEvidenceModel->rollback();
					$newTicketModel->rollback();
					return;
				}
			}//end for

			//ticket表
			$ticketList = $oldTicketModel->where($map)->select();
			$j = 0;
			for($j=0;$j<count($ticketList);$j++)
			{
				$newTicketModel->CaseId = $caseId;
				$newTicketModel->Type = $ticketList[$j]['Type'];
				$newTicketModel->Receiver = $ticketList[$j]['Receiver'];
				$newTicketModel->Cc = $ticketList[$j]['Cc'];
				$newTicketModel->Subject = $ticketList[$j]['Subject'];
				$newTicketModel->Content = $ticketList[$j]['Content'];
				$newTicketModel->ReceiverAddress = $ticketList[$j]['ReceiverAddress'];
				$newTicketModel->ReceiverPhoneNumber = $ticketList[$j]['ReceiverPhoneNumber'];
				$newTicketModel->Creator = $ticketList[$j]['Creator'];
				$newTicketModel->CreateTime = $ticketList[$j]['CreateTime'];
					
				$res = $newTicketModel->add();
				if($res === false)
				{
					$newCaseModel->rollback();
					$newEvidenceModel->rollback();
					$newTicketModel->rollback();
					return;
				}
			}//end for
		}

		$newCaseModel->commit();
		$newEvidenceModel->commit();
		$newTicketModel->commit();
		echo $totalCount;
		//$this->success("success");
	}
}
?>