<?php
class RelateAction extends CommonAction {

	public function getTest()
	{
		set_time_limit(0);
		$name = "EvidenceBotAll";
		$model = M($name);
		if(empty($model))
		{
			die("data error!");
		}
		$sql = "select IP,controlNum from EvidenceBotAllIP where controlNum>9 order by controlNum desc;";
		$list = $model->query($sql);
		if($list === false)
		{
			echo $model->getLastSql();
			die("数据错误!");
		}


		$f = fopen("src.txt","r");
		$i=0;
		echo "<table border=1 cellspacing=1 cellspacing=0 style='width:15000px;'>";
		echo "<tr><td></td>";
		for($k=1;$k<count($list);$k++)
		{
			echo "<td>".long2ip($list[$k]['IP']).":".$list[$k]['controlNum']."</td>";
		}
		echo "</tr>";
		while(!feof($f))
		{

			$line = fgets($f);
			$pos1 = strpos($line,"bots");
			if($pos1 === 0)
			{
				//连续读
				echo "<td style='width:250px;'>";
				echo $line."<br />";
				$line = fgets($f);
				echo $line."<br />";
				$line = fgets($f);
				echo $line."<br />";
				echo "</td>";

			}
			else if(strpos($line,"IP") ===0)
			{
				if($i>0) echo "</tr>";
				echo "<tr>";
				echo "<td style='width:250px;'>".long2ip($list[$i]['IP']).":".$list[$i]['controlNum']."</td>";
				for($k=0;$k<$i;$k++)
				{
					echo "<td>&nbsp;</td>";
				}

				$i++;
				//if($i>5) break;
			}
			else {
				continue;
			}
		}
		echo "</tr></table>";
		fclose($f);
	}
	//获取不同控制器是否有相同的IP及活动
	public function getControlSameIP()
	{
		$sameIPFlag = true;//是否输出公共IP

		set_time_limit(0);
		$name = "EvidenceBotAll";
		$model = M($name);
		if(empty($model))
		{
			die("data error!");
		}
		$sql = "select IP,controlNum from EvidenceBotAllIP where controlNum>9 order by controlNum desc;";
		$list = $model->query($sql);
		if($list === false)
		{
			echo $model->getLastSql();
			die("数据错误!");
		}

		//for($i=0;$i<count($list);$i++)
		//{
		//echo long2ip($list[$i]['IP']).":".$list[$i]['controlNum']."<br/>";
		//}


		$name = date("h-i-s").".txt";
		for($i=0;$i<count($list);$i++)
		{
			$sql = "select distinct(controlledIP) as IP from  EvidenceBotAll where controlIP=".$list[$i]['IP'];
			//	echo $model->getLastSql();
			//	echo "<hr>";
			$IPListSrc = $model->query($sql);
			if($IPListSrc === false)
			{
				die("data error!");
			}
			for($k=0;$k<count($IPListSrc);$k++)
			{
				$temp = $IPListSrc[$k]["IP"];
				unset($IPListSrc[$k]["IP"]);
				$IPListSrc[$k] = $temp;
			}
			//echo "num:".count($IPListSrc);
			//echo "<br >";
			for($j=$i+1;$j<count($list);$j++)
			{
				if($j!=3) continue;
				$list[$i][$j]['ip']=long2ip($list[$j]['IP']);
				$sql = "select distinct(controlledIP) as IP from  EvidenceBotAll where controlIP=".$list[$j]['IP'];
				$IPListDst = $model->query($sql);
				//echo $model->getLastSql();
				//echo "<br />";
				if($IPListDst === false)
				{
					die("data error!");
				}
				for($k=0;$k<count($IPListDst);$k++)
				{
					$temp = $IPListDst[$k]["IP"];
					unset($IPListDst[$k]["IP"]);
					$IPListDst[$k] = $temp;
				}
				$count = 0;
				//				for($k=0;$k<count($IPListDst);$k++)
				//				{
				//					for($m=0;$m<count($IPListSrc);$m++)
				//					{
				//						if($IPListDst[$k] == $IPListSrc[$m])
				//						{
				//							$count++;
				//							break;
				//						}
				//					}
				//				}
				//				$list[$i][$j]['same'] = $count;
				$same = array_intersect($IPListSrc,$IPListDst);

				//print_r($same);

				$list[$i][$j]['same']=0;
				$list[$i][$j]['UAHTS']=0;
				$list[$i][$j]['UAHTD']=0;
				//	fileWriteArray($same);
				if(count($same)>0)
				{
					$list[$i][$j]['same']=count($same);
					//UAHT,ThreatSrcIP
					$srcStr = "";
					$dstStr = "";
					foreach($same as $m)
					{
						if(!$m) continue;
						$sql = "select count(*) as c from EvidenceNBOSUAHT where ThreatSrcIP=".$m;
						$res = $model->query($sql);
						//fileWrite($model->getLastSql());
						if($res !== false && $res[0]['c'] > 0)
						{
							$list[$i][$j]['UAHTS'] += 1;

							if(sameIPFlag)
							{
								$sql = "select * from EvidenceNBOSUAHT where ThreatSrcIP=".$m;
								$res = $model->query($sql);
								for($k=0;$k<count($res);$k++)
								{
									if($res[$k]['ThreatSrcPort'] == -1) $res[$k]['ThreatSrcPort'] = '*';
									if($res[$k]['ThreatDstPort'] == -1) $res[$k]['ThreatDstPort'] = '*';
									$srcStr .="源/源端口(交集IP)：".long2ip($res[$k]['ThreatSrcIP'])."/".$res[$k]['ThreatSrcPort'];
									$srcStr .="宿/宿端口:".long2ip($res[$k]['ThreatDstIP'])."/".$res[$k]['ThreatDstPort'];
									$srcStr .="\r\n";
								}
									
							}
						}

						$sql = "select count(*) as c from EvidenceNBOSUAHT where ThreatDstIP=".$m;
						$res = $model->query($sql);
						if($res !== false && $res[0]['c'] > 0)
						{
							$list[$i][$j]['UAHTD'] += 1;

							if(sameIPFlag)
							{
								$sql = "select * from EvidenceNBOSUAHT where ThreatDstIP=".$m;
								$res = $model->query($sql);
								for($k=0;$k<count($res);$k++)
								{
									if($res[$k]['ThreatSrcPort'] == -1) $res[$k]['ThreatSrcPort'] = '*';
									if($res[$k]['ThreatDstPort'] == -1) $res[$k]['ThreatDstPort'] = '*';
									$dstStr .="源/源端口：".long2ip($res[$k]['ThreatSrcIP'])."/".$res[$k]['ThreatSrcPort'];
									$dstStr .="宿/宿端口(交集IP):".long2ip($res[$k]['ThreatDstIP'])."/".$res[$k]['ThreatDstPort'];
									$dstStr .="\r\n";
								}
									
							}

						}


					}

					//输出
					if(sameIPFlag)
					{
						fileWrite($srcStr,$name);
						fileWrite($dstStr,$name);
					}
					//UAHT,ThreatDstIP
				}

				unset($same);
				unset($IPListDst);
				//	break;
			}
			unset($IPListSrc);

			//写入文件
			$str = "\r\nIP:".long2ip($list[$i]["IP"]);
			for($j=$i+1;$j<count($list);$j++)
			{
				$str .="\r\n比较控制器IP:".$list[$i][$j]["ip"];
				$str .= "\r\nbots相同个数:".$list[$i][$j]["same"];
				$str .= "\r\n未授权流量相同个数（源）:".$list[$i][$j]["UAHTS"];
				$str .= "\r\n未授权流量相同个数（宿）:".$list[$i][$j]["UAHTD"];
			}
			//fwrite($name, $str);
			fileWrite($str,$name);

			break;
		}//end $i

		echo "over";
	}
	//统计各个控制器的不同类型
	public function getBotRule()
	{
		$name = "EvidenceBotAll";
		$model = M($name);
		if(empty($model))
		{
			die("data error!");
		}
		$sql = "select IP from EvidenceBotAllIP where controlNum>9;";
		$list = $model->query($sql);
		if($list === false)
		{
			echo $model->getLastSql();
			die("数据错误!");
		}
		for($i=0;$i<count($list);$i++)
		{
			$sql  = "select distinct(Rule) as Rule from EvidenceBotAll where controlIP=".$list[$i]['IP'];
			$clist = $model->query($sql);
			if($clist === false)
			{
				die($model->getLastSql());
			}

			echo "控制器:".long2ip($list[$i]['IP']);
			echo "<br />类型：";
			for($k=0;$k<count($clist);$k++)
			{
				if($k!=0) echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo $clist[$k]['Rule']."<br />";
			}
		}
	}

	//统计botnet时间
	public function botTimePic()
	{
		set_time_limit(0);
		$name = "EvidenceBotAll";
		$model = M($name);
		if(empty($model))
		{
			die("data error!");
		}
			

		$i = 0;
		$begin=0;
		$count = 10;
		$start = $i*$count+$begin;
		$sql = "select IP from EvidenceBotAllIP  order by controlNum desc limit $start,$count;";
		$list = $model->query($sql);
		$this->assign("ipList",$list);

		$ip = $_REQUEST['ip'];
		if(isset($ip))
		{
			$sql = "select count(distinct(Rule)) as Rule from EvidenceBotAll where controlIP=".$ip;
			$this->assign("ip",$ip);
			$res = $model->query($sql);
			$this->assign("res",$res[0]['Rule']);
		}
		else {

			$this->assign("res",0);
		}
		print_r($ip);
		echo "<hr>";
		print_r($res);


		$this->display();
	}
	public function botTimeLine()
	{
		set_time_limit(0);
		$name = "EvidenceBotAll";
		$model = M($name);
		if(empty($model))
		{
			die("data error!");
		}
			

		$i = 0;
		$begin=0;
		$count = 10;
		$start = $i*$count+$begin;
		$sql = "select IP from EvidenceBotAllIP  order by controlNum desc limit $start,$count;";
		$list = $model->query($sql);
		echo $model->getLastSql();
		if(count($list) == 0)
		{
			break;
		}

		$listArray= array();
		$typeArray = array();
		for($k=0;$k<count($list);$k++)
		{
			unset($listArray);
			unset($typeArray);
			$sql = "select distinct(Rule) as Rule from EvidenceBotAll where controlIP=".$list[$k]['IP'];
			$typeArray = $model->query($sql);

			//echo $model->getLastSql();
			//echo "<hr>";
			if($typeArray === false)
			{
				die($model->query($sql));
			}
			if(count($typeArray)>0)
			{
				//break;
			}
			else {
				break;
			}
			//echo "<hr>";
			//print_r($typeArray);
			//获取时间,IP+模式
			for($m=0;$m<count($typeArray);$m++)
			{
				$sql = "select controlledIP,TimeFirst,TimeLast from EvidenceBotAll where Rule='".$typeArray[$m]['Rule']."' and controlIP=".$list[$k]['IP']." order by TimeFirst asc";
				//$sql = "select count(distinct(TimeLast)) as lastNum from EvidenceBotAll where controlIP=".$list[$k]['cIP'];
				//$sql = "select controlledIP,TimeLast from EvidenceBotAll where Rule='".$typeArray[$m]['Rule']."' and controlIP=".$list[$k]['cIP']." order by TimeLast asc";
				$tlist = $model->query($sql);
				if($tlist === false)
				{
					die($model->getLastSql());
				}
				//$listArray[$m]= $list;
				if(count($tlist) < 15) continue;

				$str = "<categories>";
				$min = strtotime("2020-12-30 12:12:12");
				$max = 0;
				for($j=0;$j<count($tlist);$j++)
				{
					if($j%20==0)
					{
						$str .="<category name='".($j+1)."' showName='1'/>";
					}
					else {
						$str .="<category name='".($j+1)."' showName='0'/>";
					}

					$tlist[$j]['TimeFirst'] = strtotime($tlist[$j]['TimeFirst']);
					$tlist[$j]['TimeLast'] = strtotime($tlist[$j]['TimeLast']);

					if($min > $tlist[$j]['TimeFirst'])
					{
						$min =  $tlist[$j]['TimeFirst'];
					}


					//					if($min > $tlist[$j]['TimeLast'])
					//					{
					//						$min =  $tlist[$j]['TimeLast'];
					//					}

					if($max < $tlist[$j]['TimeFirst'])
					{
						$max =  $tlist[$j]['TimeFirst'];
					}


					//					if($max < $tlist[$j]['TimeLast'])
					//					{
					//						$max =  $tlist[$j]['TimeLast'];
					//					}

				}
				$str .="</categories>";
				$max = $max -$min;
				$str = "\xEF\xBB\xBF<?xml version='1.0' encoding='UTF-8'?>\r\n<graph caption='".long2ip($list[$k]['IP'])."' xAxisName='时间' subcaption='(".$typeArray[$m]['Rule'].")'
				hovercapbg='FFECAA' hovercapborder='F47E00' formatNumberScale='0'
				 decimalPrecision='0' showvalues='0' numdivlines='3' baseFontSize='10' 
				 numVdivlines='0' yaxisminvalue='0' yaxismaxvalue='$max'  bgColor='EAF3FB' rotateNames='1'>\r\n".$str;
				//写文件

				$color = getTypeColor(1);
				$str .="<dataset seriesName='开始时间' color='$color' anchorBorderColor='$color' anchorBgColor='$color'>";
				for($j=0;$j<count($tlist);$j++)
				{
					$temp = $tlist[$j]['TimeFirst']-$min;
					$str .="<set value='$temp' />";
				}
				$str .="</dataset>";

				//				$color = getTypeColor(5);
				//				$str .="<dataset seriesName='上次活动时间' color='$color' anchorBorderColor='$color' anchorBgColor='$color'>";
				//				for($j=0;$j<count($tlist);$j++)
				//				{
				//					$temp = $tlist[$j]['TimeLast']-$min;
				//					$str .="<set value='$temp' />";
				//				}
				//				$str .="</dataset>";
				$str .="</graph>";
				//	$str .="<category name='".date("Y/m/d",$begin+$space*$j)."' showName='1'/>";
				//输出到xml
				$fileName = "../Public/chart/data/".long2ip($list[$k]['IP'])."-f-".$m.".xml";
				xmlWrite($str,$fileName,'w');
			}
		}

		echo "结束！";
	}

	public function botCombineIP()
	{
		set_time_limit(0);
		$name = "EvidenceBotAll";
		$model = M($name);
		$addModel = M('EvidenceBotAllIP');
		if(empty($model))
		{
			die("data error!");
		}
			

		$i = 0;
		while(1)
		{
			$count = 20;
			$start = $i*$count;
			$i++;
			$sql = "select distinct(controlIP) as cIP from EvidenceBotAll limit $start,$count;";
			//$sql = $sql . " limit $start,$count";
			$list = $model->query($sql);
			//echo $model->getLastSql();
			if(count($list) == 0)
			{
				break;
			}
			$addArray= array();
			for($k=0;$k<count($list);$k++)
			{
				$addArray[$k]['IP'] = $list[$k]['cIP'];
				$sql = "select count(distinct(controlledIP)) as cdIP from EvidenceBotAll where controlIP=".$list[$k]['cIP'];
				$res = $model->query($sql);
				if($res === false)
				{
					die("overs");
				}
				if(count($res)>0)
				{
					$addArray[$k]['controlNum'] = $res[0]['cdIP'];
				}
				else {
					$addArray[$k]['controlNum'] = 0;
				}

				$sql = "select count(distinct(Rule)) as Rule from EvidenceBotAll where controlIP=".$list[$k]['cIP'];
				$res = $model->query($sql);
				if($res === false)
				{
					die("overs");
				}
				if(count($res)>0)
				{
					$addArray[$k]['ruleNum'] = $res[0]['Rule'];
				}
				else {
					$addArray[$k]['ruleNum'] = 0;
				}

				$sql = "select count(distinct(TimeFirst)) as TimeFirst from EvidenceBotAll where controlIP=".$list[$k]['cIP'];
				$res = $model->query($sql);
				if($res === false)
				{
					die("overs");
				}
				if(count($res)>0)
				{
					$addArray[$k]['startNum'] = $res[0]['TimeFirst'];
				}
				else {
					$addArray[$k]['startNum'] = 0;
				}

				$sql = "select count(distinct(TimeLast)) as lastNum from EvidenceBotAll where controlIP=".$list[$k]['cIP'];
				$res = $model->query($sql);
				if($res === false)
				{
					die("overs");
				}
				if(count($res)>0)
				{
					$addArray[$k]['lastNum'] = $res[0]['lastNum'];
				}
				else {
					$addArray[$k]['lastNum'] = 0;
				}

			}

			$res = $addModel->addAll($addArray);
			//echo "<hr>";
			//echo $addModel->getLastSql();
			///echo "<hr>";
			//	print_r($addArray);
			if($res === false)
			{
				die("insert error!");
			}
		}
		echo "<hr>insert over！";
	}
	//第一步，把僵尸合进去
	public function botCombineDate()
	{
		set_time_limit(0);
		$name = "EvidenceBotnetAct";
		$model = M($name);
		$addModel = M('EvidenceBotAll');
		if(empty($model) || empty($addModel))
		{
			die("data error!");
		}
			

		$i = 0;
		while(1)
		{
			$count = 200;
			$start = $i*$count;
			$i++;
			$sql = "select Evidence.*,b.ControlIP,b.controlPort,b.controlledIP,
					b.ControlledPort,b.ConnNum,b.Rule,b.AttachFile
					from Evidence,$name b
					where Evidence.EvidenceID=b.EvidenceID  limit $start,$count
					";
			//$sql = $sql . " limit $start,$count";
			$list = $model->query($sql);
			//echo $model->getLastSql();
			if(count($list) == 0)
			{
				break;
			}

			$res = $addModel->addAll($list);
			//	echo $addModel->getLastSql();
			if($res === false)
			{
				print_r($list);
				$addModel->getLastSql();
				die("插入错误!");
			}
			//die();
		}
		echo "插入结束！";
	}
	//获取用户
	public function getUser()
	{
		$model = M("Users");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$sql = "select * from Users where power=1;";//查找普通用户
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("user",$list);
	}

	public function bot()
	{
		$this->getClassType();

		$name = "Cases_js";
		$this->getUnit($name);
		$map = Array();
		$map = $this->_search ($name);


		//主题
		if(isset($map['Subject']))
		{
			$this->assign("Subject",$map['Subject']);
			$map['Subject'] = array('like',"%".$map['Subject']."%");
		}

		//单位
		if(isset($map['Unit']))
		{
			$this->assign("curUnit",$map['Unit']);
		}
		//分类
		if($map['TcID'])
		{
			$this->assign("curTCID",$map['TcID']);
			$this->getType($map['TcID']);
			//$map['Cases_ch.TcID'] = $map['TcID'];
			unset($map['TcID']);
		}
		else {
			$this->assign("curTCID",-1);
		}
		//类型
		if($map['TID'])
		{
			$this->assign("curTID",$map['TID']);
			//$map['Cases_ch.TID'] = $map['TID'];
			unset($map['TID']);
		}
		else {
			$this->assign("curTID",-1);
		}

		$model = M($name);

		if( !empty ( $model ))
		{
			$this->_list ( $model, $map ,"Subject");
		}
		//fileWrite("in incident:".$model->getLastSql());
		//echo $model->getLastSql();
		$this->display();
	}
	// 框架首页
	public function index() {
		$this->getClassType();

		$name = "Cases_ch";
		$map = Array();
		$map = $this->_search ($name);


		//主题
		if(isset($map['Subject']))
		{
			$this->assign("Subject",$map['Subject']);
			$map['Subject'] = array('like',"%".$map['Subject']."%");
		}

		//分类
		if($map['TcID'])
		{
			$this->assign("curTCID",$map['TcID']);
			$this->getType($map['TcID']);
			$map['Cases_ch.TcID'] = $map['TcID'];
			unset($map['TcID']);
		}
		else {
			$this->assign("curTCID",-1);
		}
		//类型
		if($map['TID'])
		{
			$this->assign("curTID",$map['TID']);
			$map['Cases_ch.TID'] = $map['TID'];
			unset($map['TID']);
		}
		else {
			$this->assign("curTID",-1);
		}

		$model = M($name);

		if( !empty ( $model ))
		{
			$this->_list ( $model, $map ,"Subject");
		}
		//fileWrite("in incident:".$model->getLastSql());
		//echo $model->getLastSql();
		$this->display();
	}

	protected function _list($model, $map, $sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = 'Subject';
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
			//$join = "left join `ThreatType` on `ThreatType`.`TID`= `Cases`.`TID`";
			$filed = "*";
			$voList = $model->where($map)->field($filed)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select( );
			//echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					//	$key=str_replace("Cases.", "", $key);
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
				else {
					$val = str_replace("%", "", $val[1]);
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			//print_r($map);
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

	//根据分类id获取案件类型表,Ajax,Json返回
	public function getTypeById()
	{
		if(empty($_REQUEST['id']))
		{
			$this->AjaxReturn("","",0,"");
			return;
		}
		$id = $_REQUEST['id'];
		$model = M("ThreatType");
		$list = $model->where("`TcId`=$id")->select();
		$this->ajaxReturn($list);
	}

	public function getUnit($name="Cases")
	{
		$model = M($name);
		$sql = "select distinct(Unit) as Unit from $name ";
		$list = $model->query($sql);
		$this->assign("UnitList",$list);
	}
	//获取案件类型表
	public function getType($id)
	{
		$model = M("ThreatType");
		if($id)
		{
			$list = $model->where("`TcId`=$id")->select();
		}
		else {
			$list = $model->select();
		}
		$this->assign("threatType",$list);
	}
	//获取案件分类表
	public function getClassType()
	{
		$model = M("ThreatClassType");
		$list = $model->select();
		$this->assign("threatClassType",$list);

	}



}
?>
