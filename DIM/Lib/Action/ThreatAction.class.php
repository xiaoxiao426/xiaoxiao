<?php
class ThreatAction extends CommonAction
{
	
//组织TID总和
	public function UnitTIDSum()
	{
		$gid=$_REQUEST['gid'];
		if(!isset($gid))
		{
			$this->error("数据错误!");
		}

		$model=M("CaseUnit");
		if(empty($model))
		{
			$this->error("数据错误!");
		}

		$sql="select distinct TID from CaseUnit where GroupID=$gid";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("gid",$gid);
		$this->assign("tids",$list);
		$this->display();
	}
	
	public function getCommonData($tpl)
	{
		$class = getTcIdConfig();
		$model = M("Cases");
		if(empty($model))
		{
			$this->error("数据错误!");
			return;
		}
		for($j=0;$j<count($class);$j++)
		{
			if($class[$j]['name'] == $tpl)
			{
				$tcId = $class[$j]['TcID'];
				break;
			}
		}//class
		$gid = $_REQUEST['gid'];
		if(!isset($gid))
		{
			$this->error("Threat group id error!");
		}
		$sql = "";
		switch($tpl)
		{
			case 'spam':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "SELECT `Cases`.Subject,`ThreatSrc`.*,`Cases`.StartTime,`Cases`.LastTime
				FROM `Cases`,CaseSrcDstRelation,ThreatSrc
				where CaseSrcDstRelation.CaseID=`Cases`.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and `Cases`.TcID=$tcId";
				break;
			case 'phishing':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`ThreatSrc`.`DomainCnt`,`ThreatDst`.`IPCnt` as dIPCnt
						FROM Cases,CaseSrcDstRelation,ThreatSrc,ThreatDst
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and ThreatDst.EntityID = CaseSrcDstRelation.DstID and Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				break;
			case 'virus'://恶意代码传播
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`ThreatDst`.`IPCnt`
						FROM Cases,CaseSrcDstRelation,ThreatSrc,ThreatDst
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and ThreatDst.EntityID = CaseSrcDstRelation.DstID and Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				break;
			case 'DDoS':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.ReserveValue5,`Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,
				`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`Cases`.ReserveValue1,`Cases`.ReserveValue2,`Cases`.ReserveValue3,`Cases`.ReserveValue4
						FROM Cases,CaseSrcDstRelation,ThreatSrc
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				break;
			case 'scan':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.ReserveValue5,`Cases`.ReserveValue1,`Cases`.id,`Cases`.CaseID,
				`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,
				`Cases`.EvidenceNum
						FROM Cases,CaseSrcDstRelation,ThreatSrc
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				break;
			case 'invasion':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.ReserveValue1,
				`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`Cases`.ReserveValue2
						FROM Cases,CaseSrcDstRelation,ThreatSrc 
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				break;
			case 'platform':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`, `ThreatSrc`.`DomainCnt`,`ThreatDst`.`IPCnt` as dIPCnt
						FROM Cases,CaseSrcDstRelation,ThreatSrc,ThreatDst
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and ThreatDst.EntityID = CaseSrcDstRelation.DstID and Cases.DeleteFlag = 0 AND `Cases`.TcID=$tcId";
				break;
			case 'infomation':
				break;
			case 'illegalService':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TcID = $tcId";
				$sql = "select `Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.EvidenceNum,`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`ThreatSrc`.`DomainCnt`,`ThreatDst`.`IPCnt` ad dIPCnt
						FROM Cases,CaseSrcDstRelation,ThreatSrc,ThreatDst
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and ThreatDst.EntityID = CaseSrcDstRelation.DstID and Cases.DeleteFlag = 0 AND Cases.TcID = 8";
				break;
			case 'webshell':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND TID = 600001";
				$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TID = 600001";
				break;
			case 'botnet':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND TID = 600002";
				$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TID = 600002";
				break;
			case 'trojan':
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND TID = 700003";
				$sql = "select id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime
				from Cases
				where DeleteFlag = 0 and  TID = 700003 ";
				break;
			case 'UahPort':
				$tId = 700700;
				$sql1 ="select count(*) as count FROM Cases
						where Cases.DeleteFlag = 0 AND Cases.TID = $tId";
				$sql = "select `Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.ReserveValue1,
				`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`Cases`.ReserveValue2
						FROM Cases,CaseSrcDstRelation,ThreatSrc 
						where CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and Cases.DeleteFlag = 0 AND Cases.TID = $tId";
				break;
			default:
				break;
		}

		//echo $sql;
		if(strlen($sql) > 0)
		{
			//group id 以及 CaseType
			$sql1 .=" and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
			$sql .=" and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
			/*暂时单列异常流量*/
			if($tcId == 7)
			{
				$sql .="  order by `Cases`.ReserveValue1 desc,`Cases`.LastTime desc";
			}
			else {
				$sql .="  order by `Cases`.LastTime desc";
			}

			//获取总数
			$list = $model->query($sql1);

			if($list === false)
			{
				$this->error("查询失败!");
			}
			$count = $list[0]['count'];

			import ( "@.ORG.Page" );
			$listRows = 50;
			$p = new Page ( $count, $listRows );
			$sql .= " limit ".$p->firstRow . ',' . $p->listRows;
			$list = $model->query($sql);

			$page = $p->show ();
			$this->assign ( "page", $page );
		}
		//	echo $sql."<hr>";
		//	echo $model->getLastSql();
		//	echo "<hr>";
		$this->assign($tpl,$list);
		$this->assign("tpl",$tpl);
	}
	//
	public function index()
	{
		$gid = $_REQUEST['gid'];
		$tid = $_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		if(!isset($gid) || !isset($tid))
		{
			$this->error("Threat group id error!");
		}
		
		$unit=urldecode($unit);

		$this->assign("gid",$gid);
		$this->assign("tid",$tid);
		$this->assign("unit",$unit);
		//$this->getCommonData($tpl);
		$this->display();
	}
}
?>