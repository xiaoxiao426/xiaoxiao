<?php
class IndexAction extends CommonAction {

	public function test()
	{
		$this->display();
	}

	//僵尸网络统计
	public function myBotT()
	{
		set_time_limit(0);
		$model = M("EvidenceBot");
		$srcModel = M("EvidenceBotnetIP");

		$begin = 0;
		$count = 400;
		$i = 0;
		while(1)
		{
			$begin = $i*$count;
			$sql = "select * from EvidenceBotnetIP order by TimeLast desc limit $begin,$count;";
			$res = $srcModel->query($sql);
			if($res === false)
			{
				die("here die!");
			}
			if(count($res) == 0)
			{
				break;
			}
			$data = array();
			for($j=0;$j<count($res);$j++)
			{
				unset($data);
				$data['AnalyzerId'] = $res[$j]['AnalyzerId'];
				$data['ControlIP'] = long2ip($res[$j]['ControlIP']);
				$data['controlPort'] = $res[$j]['controlPort'];
				$data['Rule'] = $res[$j]['Rule'];
				$data['ConnNum'] = $res[$j]['ConnNum'];
				$data['DetectRuleID'] = $res[$j]['DetectRuleID'];
				$data['TimeLast'] = $res[$j]['TimeLast'];
				$secondRes = $model->add($data);
				if($secondRes === false)
				{
					echo $model->getLastSql();
					echo "<hr />";
				}
			}
			$i++;
		}
		echo "success";
	}
	//测试分页
	public function test4()
	{
		try{
			//在non-wsdl方式中option location系必须提供的,而服务端的location是选择性的，可以不提供
			$soap = new SoapClient(null,array('location'=>"http://127.0.0.1/CHAIRS_SOAP/SOAP/index.php/Public/MyService",'uri'=>'http://127.0.0.1/CHAIRS_SOAP/SOAP/index.php/Public/MyService'));
			$result = $soap->GetInfo();
			if(is_array($result))
			{
				for($i=0;$i<count($result);$i++)
				{
					print_r($result[$i]);
				}
			}
		}catch(SoapFault $e){
			echo $e->getMessage();
		}catch(Exception $e){
			echo $e->getMessage();
		}
		$this->display();
	}


	//测试分页
	public function test3()
	{
		try{
			//在non-wsdl方式中option location系必须提供的,而服务端的location是选择性的，可以不提供
			$soap = new SoapClient(null,array('location'=>"http://101.4.106.102/CHAIRS_SOAP/SOAP/index.php/Public/MyService",'uri'=>'http://101.4.106.102/CHAIRS_SOAP/SOAP/index.php/Public/MyService'));
			echo "befor <hr />";
			$result = $soap->GetInfo();
				
			if(is_array($result))
			{
				for($i=0;$i<count($result);$i++)
				{
					print_r($result[$i]);
					echo "<hr />";
				}
			}
		}catch(SoapFault $e){
			echo $e->getMessage();
		}catch(Exception $e){
			echo $e->getMessage();
		}
		$this->display();
	}

	public function test1()
	{
		$model=M("Cases");
		//取得满足条件的记录数
		$count = $model->count ( 'id' );
		if ($count > 0) {
			import ( "@.ORG.Page" );
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

			$voList = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
			//echo $model->getlastsql();

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
		$this->display();
	}

	public function test2()
	{
		$model=M("Cases");
		//取得满足条件的记录数
		$count = $model->count ( 'id' );
		if ($count > 0) {
			import ( "@.ORG.Page" );
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

			$voList = $model->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
			//echo $model->getlastsql();

			//分页显示
			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			//模板赋值显示
			$this->assign ( 'vo', $voList );
			$this->assign ( 'sort', $sort );
			$this->assign ( 'order', $order );
			$this->assign ( 'sortImg', $sortImg );
			$this->assign ( 'sortType', $sortAlt );
			$this->assign ( "page", $page );
		}
		Cookie::set ( '_currentUrl_', __SELF__ );
		$this->display();
	}

	public function _before_index()
	{
		if($_SESSION["userPower"] == 2  || $_SESSION["userPower"] == 3)
		{
			$this->redirect('/Admin/index');
		}
	}


	// 框架首页
	public function indexStat() {
		//	C ( 'SHOW_RUN_TIME', false ); // 运行时间显示
		//C ( 'SHOW_PAGE_TRACE', false );
		$name = "Cases";
		$model = M($name);
		$map = Array();


		if( empty ( $model ))
		{
			echo "错误<br/>";
		}

		$count = $model->count ( 'id' );
		echo "Total case Num:".$count."\r\n";

		$sql = "SELECT count(distinct Unit) AS unum,GroupID,sum(Cnt) as num FROM `CaseUnit` GROUP BY GroupID;";
		$list = $model->query($sql);
		print_r($list);

	}


	// 框架首页
	public function indexSum()
	{
		$name = "Cases";
		$model = M($name);
		$map = Array();
		if(!isset($_SESSION['loginUserName']))
		{
			$this->error("请重新登录!");
		}

		if( empty ( $model ))
		{
			$this->display();
			return;
		}

		$map = Array();
		$map['DeleteFlag'] = 0;//全部案件数
		$map['CaseType'] = 0;//案件统计，对应类型0
		//	$map['CaseResponsor'] = $_SESSION[C('USER_AUTH_KEY')];
		$uname = $this->getUnitNameByUid($_SESSION[C('USER_AUTH_KEY')]);
		$unitStr = "";
		if($uname == -1)
		{
			$this->error("单位错误!");
		}
		if(is_array($uname))
		{
			for($k=0;$k<count($uname)-1;$k++)
			{
				$unitStr .= "'".$uname[$k]['UnitName']."'".',';
			}
			if(count($uname)>0)
			{
				$unitStr .= "'".$uname[$k]['UnitName']."'";
			}
		}
		//$map['Unit'] = $uname;//按单位来处理
		$map['_string'] = "`ProgressState` = 'open' and Unit in ($unitStr)";
	
		$count = $model->where ( $map )->count ( 'id' );
		$this->assign("totalCase",$count);

		
		$map['ProgressState'] = "open";//待定性件数
		$count = $model->where ( $map )->count ( 'id' );
		$this->assign("openCase",$count);
		$map['ProgressState'] = "resolved";//跟踪性件数
		$count = $model->where ( $map )->count ( 'id' );
		$this->assign("resolvedCase",$count);

		//事件数
		unset($map['ProgressState']);//跟踪性件数
		unset($map['CaseType']);//事件统计所有类型CaseType
		unset($map['CaseResponsor']);//事件统计所有类型CaseType
		$count = $model->where ( $map )->count ( 'id' );
		$this->assign("caseNum",$count);

		$count = 0;
		$list = Array();
		$this->getSummaryInfo();

		/*//当前用户需处理的事件//
		unset($map);
		$map['DeleteFlag'] = 0;//全部案件数
		//$map['CaseResponsor'] = $_SESSION[C('USER_AUTH_KEY')];
		//$map['Unit'] = $uname;//按单位来处理
		$map['CaseType'] = 0;//案件
		//$map['ProgressState'] = "open";//waiting
		//	$map['_string'] = "`ProgressState` = 'open' or `ProgressState` = 'waiting'";
		$map['_string'] = "`ProgressState` = 'open'  and Unit in ($unitStr)";
		$join = "left join `ThreatType` on `ThreatType`.`TID`= `Cases`.`TID`";
		$filed = "`Cases`.*";
		//$list = $model->where($map)->field($filed)->limit("0,10")->order("StartTime desc")->select();
		$list = $model->where($map)->field($filed)->order("StartTime desc")->select();
		//echo $model->getLastSql();
		//fileWrite($model->getLastSql());
		$total = $model->where($map)->count ( 'id' );
		$this->assign("vo",$list);
		$this->assign("total",$total);*/
		$this->display();
	}

	public function index() {

		$this->redirect("/Incident/index");
		return;

	}

	//获得总体信息
	public function getSummaryInfo()
	{
		$caseModel = M("Cases");

		//内部威胁
		$phishing = array();//钓鱼网站
		$virus=array();//恶意代码
		$backdoor=array();//后门
		$DDoS=array();

		if(empty($caseModel))
		{
			$this->error("数据错误!");
		}
		$res = array();
		$sql = "SELECT count(distinct Unit) AS unum,GroupID,sum(Cnt) as num FROM `CaseUnit` GROUP BY GroupID;";
		$list = $caseModel->query($sql);
		$allId = "";

		if($list === false)
		{
			$this->error("数据错误!");
		}

		//获取各类事件数量
		for($i=0;$i<count($list);$i++)
		{
			switch($list[$i]['GroupID'])
			{
				case 1:
					$phishing['case'] = $list[$i]['num'];
					$phishing['unum'] = $list[$i]['unum'];
					break;
				case 2:
					$backdoor['case'] = $list[$i]['num'];
					$backdoor['unum'] = $list[$i]['unum'];
					break;
				case 3:
					$DDoS['case'] = $list[$i]['num'];
					$DDoS['unum'] = $list[$i]['unum'];
					break;
				case 4:
					$virus['case'] = $list[$i]['num'];
					$virus['unum'] = $list[$i]['unum'];
					break;
				default:
					break;
			}
		}//list

		/*
		 //group=1
		 $sql = "SELECT sum(SrcCnt) as SrcCnt,sum(DstCnt) as DstCnt FROM `Cases` WHERE GroupID=1 and `DeleteFlag`=0 and (`CaseType` = 1 or `CaseType` = 0);";
		 $list = $caseModel->query($sql);

		 if($list === false)
		 {
			$this->error("数据错误1!".$caseModel->getLastSql());
			}

			$phishing['src'] = $list[0]['SrcCnt'];
			$phishing['target'] = $list[0]['DstCnt'];

			////group=2
			$sql = "SELECT sum(SrcCnt) as SrcCnt,sum(DstCnt) as DstCnt FROM `Cases` WHERE GroupID=2 and `DeleteFlag`=0 and (`CaseType` = 1 or `CaseType` = 0);";
			$list = $caseModel->query($sql);

			if($list === false)
			{
			$this->error("数据错误2!");
			}

			$backdoor['src'] = $list[0]['SrcCnt'];
			$backdoor['target'] = $list[0]['DstCnt'];

			////group=3
			$sql = "SELECT sum(SrcCnt) as SrcCnt,sum(DstCnt) as DstCnt FROM `Cases` WHERE GroupID=3 and `DeleteFlag`=0 and (`CaseType` = 1 or `CaseType` = 0);";
			$list = $caseModel->query($sql);

			if($list === false)
			{
			$this->error("数据错误!");
			}

			$DDoS['src'] = $list[0]['SrcCnt'];
			$DDoS['target'] = $list[0]['DstCnt'];

			////group=4
			$sql = "SELECT sum(SrcCnt) as SrcCnt,sum(DstCnt) as DstCnt FROM `Cases` WHERE GroupID=3 and `DeleteFlag`=0 and (`CaseType` = 1 or `CaseType` = 0);";
			$list = $caseModel->query($sql);

			if($list === false)
			{
			$this->error("数据错误!");
			}

			$virus['src'] = $list[0]['SrcCnt'];
			$virus['target'] = $list[0]['DstCnt'];
			*/
		$this->assign("phishing",$phishing);
		$this->assign("backdoor",$backdoor);
		$this->assign("virus",$virus);
		$this->assign("DDoS",$DDoS);
	}

	public function PHPExcelExport()
	{
		ini_set('max_execution_time',1000000);

		$dir = "/var/www/html/DIM_CHAIRS/Public/Uploads";
		$list = scandir($dir);

		$xls_row = 2;
		$count = 0;
		$fileName = "dim_upload.txt";
		$file1 = fopen($fileName,"a+");
		//	fwrite($file, date("Y:m:d H:i:s  ").$str."\r\n");

		$str = "";
		$max = 0;
		$maxFileName = "";
		$totalSize = 0;
		foreach($list as $file){//遍历

			if($file!="." && $file!="..")
			{
				$fileSize = filesize($dir."/".$file);
				if($fileSize > $max)
				{
					$max = $fileSize;
					$maxFileName = $file;
				}

				$totalSize += $fileSize;
				$a=filectime($dir."/".$file);
				$datetime = date("Y-m-d H:i:s",$a);
				$str .=$file." ".$fileSize." ".$datetime."\n";
				$count++;
			}
			if($count % 10000 == 9999)
			{
				fwrite($file1,$str);
				$str = "";
			}

		}
		fwrite($file1,$str);
		fclose($file1);
		echo "total size:$totalSize"."\n";
		echo "max size:".$max."\n"."file name: $maxFileName"."\n";
	}

	function fileWrite($str,$fileName="test.txt",$pattern="a+")
	{
		$file = fopen($fileName,$pattern);
		fwrite($file, date("Y:m:d H:i:s  ").$str."\r\n");
		fclose($file);
	}

}
?>