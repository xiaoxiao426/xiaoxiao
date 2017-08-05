<?php
class StatisticsAction extends CommonAction{
	public function index(){

	}


	public function getAllReady()
	{
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
	//获取TcID为1-8的各类事件前N的统计
	public function getTPON()
	{
		if(C("TPON_NUM_CASE"))
		{
			$n = C("TPON_NUM_CASE");
		}
		else
		{
			C("TPON_NUM_CASE",5);
			$n = 5;
		}
		$model = M("Cases");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$end = time();//当前时间
		$begin = $end-86400;//结束时间

		//DDoS
		$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TcID = 3 
				ORDER BY EvidenceNum desc,LastTime desc
				limit 0,$n
				 ";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("DDoS",$list);

		//网站后门
		$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TID = 600001 
				ORDER BY EvidenceNum desc,LastTime desc
				limit 0,$n
				 ";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("webshell",$list);

		//僵尸网络
		$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TID = 600002 
				ORDER BY EvidenceNum desc,LastTime desc
				limit 0,$n
				 ";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("botnet",$list);

		//挂马网站
		$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TID = 700003 
				ORDER BY EvidenceNum desc,LastTime desc
				limit 0,$n
				 ";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("trojan",$list);

	}
	public function safe_situation()
	{

		//---------------------------案件分类总体统计------------------------
		$end = time();//当前时间
		$begin = strtotime("-1 month");//开始时间
		$endDate = date('Y/m/d',$end);
		$beginDate = date('Y/m/d',$begin);
		$sql = "select sum(Cnt) as caseCount,GroupID from `CaseUnit`
		        where unix_timestamp(LastTime) > $begin			
				group by GroupID
				";
		$name = "CaseUnit";
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}

		//总数区分
		$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph showNames='1'  decimalPrecision='0' subcaption='($beginDate ~ $endDate)' caption='案件分类总体统计数据' bgColor='d4e6f6'>\r\n";
		$i = 0;
		for($i=0;$i<count($list);$i++)
		{
			if(!$list[$i]['GroupID'])
			{
				continue;
			}
			$typeName = get_case_unit_name($list[$i]['GroupID']);
			$number = $list[$i]['caseCount'];
			$color = getTypeColor($list[$i]['GroupID']);
			$str .= "<set color='$color' name='$typeName' value='$number' />\r\n";
		}
		$str .="</graph>";

		$fileName = "../Public/chart/data/Pie3D_type.xml";
		//	fileWrite($fileName.":".$str);
		xmlWrite($str,$fileName,'w');

		//------------------------------------威胁源统计--------------------
		$sql = "select sum(IPCnt) as threatCount,TID
				from ThreatSrcIPView
				group by TID";
		$sql = "select * from ThreatSrcIPViewCount;";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}

		//总数区分
		$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph showNames='1'  decimalPrecision='0' formatNumberScale='0' caption='案件威胁源统计数据' bgColor='d4e6f6'>\r\n";
		$i = 0;
		for($i=0;$i<count($list);$i++)
		{
			if(!$list[$i]['TID'])
			{
				continue;
			}
			$typeName = $this->get_gt_name($list[$i]['TID']);
			$number = $list[$i]['threatCount'];
			$color = getTypeColor($i);
			$str .= "<set color='$color' name='$typeName' value='$number' />\r\n";
		}
		$str .="</graph>";

		$fileName = "../Public/chart/data/Pie3D_src.xml";
		xmlWrite($str,$fileName,'w');

		//---------------------------------威胁宿统计------------------
		$sql = "select sum(IPCnt) as threatCount,TID
				from ThreatDstIPView
				group by TID";

		$sql = "select * from ThreatDstIPViewCount;";

		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}

		//总数区分
		$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph showNames='1' formatNumberScale='0' decimalPrecision='0' caption='案件威胁源宿统计数据' bgColor='d4e6f6'>\r\n";
		$i = 0;
		for($i=0;$i<count($list);$i++)
		{
			if(!$list[$i]['TID'])
			{
				continue;
			}
			$typeName = $this->get_gt_name($list[$i]['TID']);
			$number = $list[$i]['threatCount'];
			$color = getTypeColor($i);
			$str .= "<set color='$color' name='$typeName' value='$number' />\r\n";
		}
		$str .="</graph>";

		$fileName = "../Public/chart/data/Pie3D_des.xml";
		xmlWrite($str,$fileName,'w');


		//-------------国内各运营商统计--------------
		$sumArray = Array();
		$sumArray[0]['name'] = "电信";
		$sumArray[1]['name'] = "移动";
		$sumArray[2]['name'] = "联通";
		$sumArray[3]['name'] = "网通";
		$sumArray[4]['name'] = "铁通";
		$sumArray[5]['name'] = "教育网";
		for($i=0;$i<count($sumArray);$i++)
		{
			$sumArray[$i]['SrcIP'] = 0;
			$sumArray[$i]['FirstTime'] = "";
			$sumArray[$i]['LastTime'] = "";
			$sumArray[$i]['DstIP'] = 0;
		}

		$sql = "SELECT *
		       from `ISPStat`
				where `ISP` in('电信','移动','联通','网通','铁通','教育网')
				";

		$list = $model->query($sql);

		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}
		for($i=0;$i<count($list);$i++)
		{
			for($j=0;$j<count($sumArray);$j++)
			{
				if($sumArray[$j]['name'] == $list[$i]['ISP'])
				{
					$sumArray[$j]['SrcIP'] = $list[$i]['srcCnt'];
					$sumArray[$j]['DstIP'] = $list[$i]['dstCnt'];
					$sumArray[$j]['FirstTime'] = $list[$i]['FirstTime'];
					$sumArray[$j]['LastTime'] = $list[$i]['LastTime'];
					break;
				}
			}
		}

		//铁通并进移动；联通并进网通
		$sumArray[5]['name'] = "CERNET";
		$sumArray[0]['rname'] = "电信";
		$sumArray[1]['rname'] = "移动,铁通";
		$sumArray[3]['rname'] = "网通,联通";
		$sumArray[5]['rname'] = "教育网";

		//铁通并进移动
		$sumArray[1]['SrcIP'] += $sumArray[4]['SrcIP'];
		$sumArray[1]['DstIP'] += $sumArray[4]['DstIP'];

		if(empty($sumArray[1]['FirstTime']) || ($sumArray[1]['FirstTime'] > $sumArray[4]['FirstTime'] && !empty($sumArray[4]['FirstTime'])))
		{
			$sumArray[1]['FirstTime'] = $sumArray[4]['FirstTime'];
		}
		if($sumArray[1]['LastTime'] < $sumArray[4]['LastTime'])
		{
			$sumArray[1]['LastTime'] = $sumArray[4]['LastTime'];
		}

		//联通并进网通
		$sumArray[3]['SrcIP'] += $sumArray[2]['SrcIP'];
		$sumArray[3]['DstIP'] += $sumArray[2]['DstIP'];

		if(empty($sumArray[3]['FirstTime']) || (!empty($sumArray[2]['FirstTime']) && $sumArray[3]['FirstTime'] > $sumArray[2]['FirstTime']))
		{
			$sumArray[3]['FirstTime'] = $sumArray[2]['FirstTime'];
		}
		if($sumArray[2]['LastTime'] && $sumArray[3]['LastTime'] < $sumArray[2]['LastTime'])
		{
			$sumArray[3]['LastTime'] = $sumArray[2]['LastTime'];
		}

		unset($sumArray[2]);//
		unset($sumArray[4]);//

		$this->assign("ISPStat",$sumArray);

		//-----------全球安全统计--------------------
		$sql = "select *
				from `CountryStat`
				where `Country` is not null AND `Country` !=''
				group by `Country`
				order by srcCnt desc
				limit 0,5;";

		$list = $model->query($sql);

		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}

		unset($dstList);
		$this->assign("globalStat",$list);

		$this->getWeekStat();
		$this->getMonthStat();
		$this->display();
	}


	//获取周统计
	private function getMonthStat()
	{
		//------------------------------统计各类案件状态--------------------
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$end = time();//当前时间
		$begin = strtotime("-1 month");//开始时间
		$endDate = date('Y/m/d',$end);
		$beginDate = date('Y/m/d',$begin);
		$begin = strtotime($beginDate);

		if(!isset($_SESSION['tcList']))
		{
			$sql = "select distinct TcID from Cases where DeleteFlag = 0 and TcID between 1 and 8";

			$tcList = $model->query($sql);
			if($tcList === false)
			{
				$this->error("数据错误!".$model->getLastSql());
			}
			$_SESSION['tcList'] = $tcList;
		}
		else {
			$tcList = $_SESSION['tcList'];
		}


		$space= 14400*2;
		$num = ($end-$begin)/$space;
		$sumArray = Array();
		//先统计TcID各种类型
		for($i=0;$i<count($tcList);$i++)
		{
			for($j=0;$j<$num;$j++)
			{
				$sumArray[$tcList[$i]['TcID']][$j]['time'] = $begin+$space*$j;
				$sumArray[$tcList[$i]['TcID']][$j]['count'] = 0;
			}
		}


		$sql = "select TcID,unix_timestamp(StartTime) as StartTime,ProgressState,unix_timestamp(ResolveTime) as ResolveTime,unix_timestamp(LastTime) as ActionTime
				from Cases
				where DeleteFlag = 0 and `Cases`.CaseType=0 and TcID between 1 and 8
				AND ((ProgressState='new' or ProgressState='open' or ProgressState='waiting')
				 OR( ProgressState='suspended' AND unix_timestamp(LastTime) > $begin) 
				 OR( ProgressState='resolved' AND unix_timestamp(ResolveTime) > $begin)
				 OR( ProgressState='abnormal' AND unix_timestamp(ResolveTime) > $begin))
				 ";
		$sql = "select TcID,unix_timestamp(StartTime) as StartTime,ProgressState,unix_timestamp(ResolveTime) as ResolveTime,unix_timestamp(LastTime) as ActionTime
				from Cases
				where DeleteFlag = 0 and `Cases`.CaseType=0 and TcID between 1 and 8
				AND ((ProgressState='open')				 
				 OR( ProgressState='resolved' AND unix_timestamp(ResolveTime) > $begin));";
		
		$sql = "select TcID,unix_timestamp(StartTime) as StartTime,ProgressState,unix_timestamp(ResolveTime) as ResolveTime,unix_timestamp(LastTime) as ActionTime
				from Cases
				where DeleteFlag = 0 and `Cases`.CaseType=0
				AND ((ProgressState='open')				 
				 OR( ProgressState='resolved' AND unix_timestamp(ResolveTime) > $begin));";

		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}

		//总数区分
		$str = "";
		$i = 0;

		for($i=0;$i<count($list);$i++)
		{
			if(!$list[$i]['TcID'])
			{
				continue;
			}
			$tcId = $list[$i]['TcID'];
			switch($list[$i]['ProgressState'])
			{
				case "new":
				case "open":
				case "waiting":
					$k = 0;
					for($k=0;$k<$num;$k++)
					{
						if($sumArray[$tcId][$k]['time'] > $list[$i]['StartTime'])
						{
							break;
						}
					}
					$m = 0;
					for($m=$k;$m<$num;$m++)
					{
						$sumArray[$tcId][$m]['count']++;
					}
					break;
				case "suspended":
					$k = 0;
					for($k=0;$k<$num;$k++)
					{
						if($sumArray[$tcId][$k]['time'] >= $list[$i]['StartTime']
						&& $sumArray[$tcId][$k]['time'] < $list[$i]['ActionTime'])
						{
							$sumArray[$tcId][$k]['count']++;
						}
					}
					break;
				case "resolved":
				case "abnormal":
					$k = 0;
					for($k=0;$k<$num;$k++)
					{
						if($sumArray[$tcId][$k]['time'] >= $list[$i]['StartTime']
						&& $sumArray[$tcId][$k]['time'] < $list[$i]['ResolveTime'])
						{
							$sumArray[$tcId][$k]['count']++;
						}
					}
					break;
				default:
					break;

			}

		}

		for($j=0;$j<$num;$j++)
		{
			if($j%12==0)
			{
				$str .="<category name='".date("Y/m/d",$begin+$space*$j)."' showName='1'/>";
			}
			else {
				$str .="<category name='".date("Y/m/d H:i",$begin+$space*$j)."' showName='0'/>";
			}
		}
		$str .= "</categories>";

		$maxCount = 0;
		for($i=0;$i<count($tcList);$i++)
		{
			$typeName = get_threat_class_type_name($tcList[$i]['TcID']);
			$color = getTypeColor($tcList[$i]['TcID']);
			$str .="<dataset seriesName='$typeName' color='$color' anchorBorderColor='$color' anchorBgColor='$color'>";

			for($j=0;$j<$num;$j++)
			{
				$count = $sumArray[$tcList[$i]['TcID']][$j]['count'];
				if($count > $maxCount)
				{
					$maxCount = $count;
				}
				$str .="<set value='$count' />";
			}
			$str .="</dataset>";
		}
		$str .="</graph>";

		$maxCount = $this->getMaxY($maxCount);
		$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph caption='安全趋势月统计' xAxisName='时间' subcaption='($beginDate ~ $endDate)'
				hovercapbg='FFECAA' hovercapborder='F47E00' formatNumberScale='0'
				 decimalPrecision='0' showvalues='0' numdivlines='3' baseFontSize='10' 
				 numVdivlines='0' yaxisminvalue='0' yaxismaxvalue='$maxCount'  bgColor='EAF3FB' rotateNames='1'>\r\n<categories>".$str;

		$fileName = "../Public/chart/data/msline_month.xml";
		xmlWrite($str,$fileName,'w');
	}
	//获取周统计
	private function getWeekStat()
	{
		//------------------------------统计各类案件状态--------------------
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$end = time();//当前时间
		$begin = $end-518400;//开始时间
		$endDate = date('Y/m/d',$end);
		$beginDate = date('Y/m/d',$begin);
		$begin = strtotime($beginDate);

		if(!isset($_SESSION['tcList']))
		{
			$sql = "select distinct TcID from Cases where DeleteFlag = 0 and TcID between 1 and 8";

			$tcList = $model->query($sql);
			if($tcList === false)
			{
				$this->error("数据错误!".$model->getLastSql());
			}
			$_SESSION['tcList'] = $tcList;
		}
		else {
			$tcList = $_SESSION['tcList'];
		}		

		$num = 42;
		$sumArray = Array();
		//先统计TcID各种类型
		for($i=0;$i<count($tcList);$i++)
		{
			for($j=0;$j<$num;$j++)
			{
				$sumArray[$tcList[$i]['TcID']][$j]['time'] = $begin+14400*$j;
				$sumArray[$tcList[$i]['TcID']][$j]['count'] = 0;
			}
		}


		$sql = "select TcID,unix_timestamp(StartTime) as StartTime,ProgressState,unix_timestamp(ResolveTime) as ResolveTime,unix_timestamp(LastTime) as ActionTime
				from Cases
				where DeleteFlag = 0 and `Cases`.CaseType=0 and TcID between 1 and 8
				AND ((ProgressState='new' or ProgressState='open' or ProgressState='waiting')
				 OR( ProgressState='suspended' AND unix_timestamp(LastTime) > $begin) 
				 OR( ProgressState='resolved' AND unix_timestamp(ResolveTime) > $begin)
				 OR( ProgressState='abnormal' AND unix_timestamp(ResolveTime) > $begin))
				 ";

		$sql = "select TcID,unix_timestamp(StartTime) as StartTime,ProgressState,unix_timestamp(ResolveTime) as ResolveTime,unix_timestamp(LastTime) as ActionTime
				from Cases
				where DeleteFlag = 0 and `Cases`.CaseType=0 and TcID between 1 and 8
				AND (ProgressState='open'		 
				 OR( ProgressState='resolved' AND unix_timestamp(ResolveTime) > $begin)
				 )
				 ";
		
		
		$sql = "select TcID,unix_timestamp(StartTime) as StartTime,ProgressState,unix_timestamp(ResolveTime) as ResolveTime,unix_timestamp(LastTime) as ActionTime
				from Cases
				where DeleteFlag = 0 and `Cases`.CaseType=0 
				AND (ProgressState='open'		 
				 OR( ProgressState='resolved' AND unix_timestamp(ResolveTime) > $begin)
				 )
				 ";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}

		//总数区分
		$str = "";
		$i = 0;

		for($i=0;$i<count($list);$i++)
		{
			if(!$list[$i]['TcID'])
			{
				continue;
			}
			$tcId = $list[$i]['TcID'];
			switch($list[$i]['ProgressState'])
			{
				case "new":
				case "open":
				case "waiting":
					$k = 0;
					for($k=0;$k<42;$k++)
					{
						if($sumArray[$tcId][$k]['time'] > $list[$i]['StartTime'])
						{
							break;
						}
					}
					$m = 0;
					for($m=$k;$m<42;$m++)
					{
						$sumArray[$tcId][$m]['count']++;
					}
					break;
				case "suspended":
					$k = 0;
					for($k=0;$k<42;$k++)
					{
						if($sumArray[$tcId][$k]['time'] >= $list[$i]['StartTime']
						&& $sumArray[$tcId][$k]['time'] < $list[$i]['ActionTime'])
						{
							$sumArray[$tcId][$k]['count']++;
						}
					}
					break;
				case "resolved":
				case "abnormal":
					$k = 0;
					for($k=0;$k<42;$k++)
					{
						if($sumArray[$tcId][$k]['time'] >= $list[$i]['StartTime']
						&& $sumArray[$tcId][$k]['time'] < $list[$i]['ResolveTime'])
						{
							$sumArray[$tcId][$k]['count']++;
						}
					}
					break;
				default:
					break;

			}
			/*	$typeName = get_threat_class_type_name($list[$i]['TcID']);
			 $number = $list[$i]['threatCount'];
			 $color = getTypeColor($list[$i]['TcID']);
			 $str .= "<set color='$color' name='$typeName' value='$number' />\r\n";*/
		}

		for($j=0;$j<42;$j++)
		{
			if($j%6==0)
			{
				$str .="<category name='".date("Y/m/d",$begin+14400*$j)."' showName='1'/>";
			}
			else {
				$str .="<category name='".date("Y/m/d H:i",$begin+14400*$j)."' showName='0'/>";
			}
		}
		$str .= "</categories>";

		$maxCount = 0;
		for($i=0;$i<count($tcList);$i++)
		{
			$typeName = get_threat_class_type_name($tcList[$i]['TcID']);
			$color = getTypeColor($tcList[$i]['TcID']);
			$str .="<dataset seriesName='$typeName' color='$color' anchorBorderColor='$color' anchorBgColor='$color'>";

			for($j=0;$j<42;$j++)
			{
				$count = $sumArray[$tcList[$i]['TcID']][$j]['count'];
				if($count > $maxCount)
				{
					$maxCount = $count;
				}
				$str .="<set value='$count' />";
			}
			$str .="</dataset>";
		}
		$str .="</graph>";

		$maxCount = $this->getMaxY($maxCount);
		$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph caption='安全趋势周统计' xAxisName='时间' subcaption='($beginDate ~ $endDate)'
				hovercapbg='FFECAA' hovercapborder='F47E00' formatNumberScale='0'
				 decimalPrecision='0' showvalues='0' numdivlines='3' baseFontSize='10' 
				 numVdivlines='0' yaxisminvalue='0' yaxismaxvalue='$maxCount'  bgColor='EAF3FB' rotateNames='1'>\r\n<categories>".$str;

		$fileName = "../Public/chart/data/msline_84.xml";
		xmlWrite($str,$fileName,'w');
	}


	public function IPCommon()
	{
		$name = $_REQUEST['fromName'];
		if(isset($_REQUEST['flag']) && $_REQUEST['flag'] == 1)
		{
			$country = $_REQUEST['Country'];
			$model = M($name);
			if(empty($model))
			{
				$this->error("数据错误!");
			}

			$sql = "SELECT *
				from `$name`
				where `$name`.`Country`='$country';";

			$list = $model->query($sql);
			if($list === false)
			{
				$this->error("数据错误".$model->getLastSql());
			}
		}
		else {
			$ispName = $_REQUEST['ISP'];
			$ispArray = explode(',',$ispName);
			$condition = "";
			for($i=0;$i<count($ispArray)-1;$i++)
			{
				$condition .= "'".$ispArray[$i]."',";
			}
			$condition .= "'".$ispArray[count($ispArray)-1]."'";
			$model = M($name);
			if(empty($model))
			{
				$this->error("数据错误!");
			}

			$sql = "SELECT *
				from `$name`
				where `$name`.`ISP` in($condition)";

			$list = $model->query($sql);
			if($list === false)
			{
				$this->error("数据错误".$model->getLastSql());
			}
		}
		$this->assign("vo",$list);
		$this->display();
		/*
		 //查找宿ip数
		 $sql = "SELECT count(distinct ThreatDstIP.IPAddress) as `DstIp`,`ISP`
		 from `ThreatDstIP`
		 where `ThreatDstIP`.`ISP` in('电信','移动','联通','网通','铁通','教育网')
		 group by `ISP`;";*/
	}

	private function get_gt_name($tid) {
		if (empty($tid)) {
			return '未分类';
		}

		$model = M ( "GroupTID" );
		$map = array();
		$map['TID'] = $tid;
		$list = $model->where($map)->select();
		if($list === false)
		{
			return "分类错误";
		}
		//fileWrite($model->getLastSql());
		if(count($list) == 0)
		{
			return get_threat_type_name($tid);
		}
		return $list[0]['Name'];
	}

	private function getMaxY($maxCount,$base=25)
	{
		if($maxCount%$base == 0)
		{
			return  $maxCount;
		}
		else {
			$tt = ($maxCount-$maxCount%$base)/$base;
			return ($tt+1)*$base;
		}
	}
	/*
	 * 2013-01-12修改前
	 *
	 public function safe_situation()
	 {
		$sql = "select count(TID) as threatCount,TID from `Cases`
		where TID is not null
		group by TID";
		$name = "Cases";
		$model = M($name);
		if(empty($model))
		{
		$this->error("数据错误!");
		}
		$list = $model->query($sql);
		if($list === false)
		{
		$this->error("数据错误!");
		}

		//总数区分
		$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph showNames='1'  decimalPrecision='0' caption='案件类别总体统计数据' bgColor='d4e6f6'>\r\n";
		$i = 0;
		for($i=0;$i<count($list);$i++)
		{
		if(!$list[$i]['TID'])
		{
		continue;
		}
		$typeName = get_threat_type_name($list[$i]['TID']);
		//			fileWrite($list[$i]['TID']."\t\t".$typeName);
		$number = $list[$i]['threatCount'];
		$color = getTypeColor($i%5);
		$str .= "<set color='$color' name='$typeName' value='$number' />\r\n";

		}
		$str .="</graph>";

		$fileName = "../Public/chart/data/Pie3D_type.xml";
		xmlWrite($str,$fileName,'w');
		$this->display();
		}
		*/
}
?>