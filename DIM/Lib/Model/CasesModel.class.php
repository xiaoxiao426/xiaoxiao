<?php
class CasesModel extends CommonModel
{
	public $_validate	=	array(
	array('Subject','require','主题必须'),
	);

	public function UpdateByCaseID()
	{

	}

	public function addMergeCaseInfo($caseInfo)
	{
		$data = $caseInfo;
		$caseId = $caseInfo['CaseId'];
		$oldCaseId = $caseId;
		unset($data['id']);
		$caseId = str_replace("NJ0-","NJM-", $caseId);//合併事件規則
		$data['CaseId'] = $caseId;
		$data['MergeType'] = 1;
		$res = $this->add($data);

		$sql = "update Evidence set MergeCaseID='$caseId' where CaseID='$oldCaseId'";
		$list = $this->query($sql);

		//ResponseTicket
		$sql = "update ResponseTicket set MergeCaseID='$caseId' where CaseId='$oldCaseId'";

		$list = $this->query($sql);

		//加入shadow
		$sql = "insert into CasesShadow select * from Cases where CaseId='$caseId';";
		$list = $this->query($sql);

		return $data;
	}

	public function myMergeCasesInfo(&$parentInfo,$childInfo,$flag=false)
	{
		if(empty($parentInfo)  || empty($childInfo))
		{
			fileWrite(__LINE__);
			return -1;
		}

		if($parentInfo['TcID'] != $childInfo['TcID'])//事件類型不同
		{
			//return 0;
		}

		//合并
		$ThreatSrcIPList = $parentInfo['ThreatSrcIPList'];//.$childInfo['ThreatSrcIPList'];
		$ThreatSrcDomainList = $parentInfo['ThreatSrcDomainList'];//.$childInfo['ThreatSrcDomainList'];
		$ThreatSrcSummary = $parentInfo['ThreatSrcSummary'];//.$childInfo['ThreatSrcSummary'];
		$ThreatDstIPList = $parentInfo['ThreatDstIPList'];//.$childInfo['ThreatDstIPList'];
		$ThreatDstDomainList = $parentInfo['ThreatDstDomainList'];//.$childInfo['ThreatDstDomainList'];
		$ThreatDstSummary = $parentInfo['ThreatDstSummary'];//.$childInfo['ThreatDstSummary'];
		$Subject = $parentInfo['Subject'];

		
		if(strpos($Subject, $childInfo['Subject']) === false)
		{
			$Subject .= "<br/>".$childInfo['Subject'];
			if(strlen($Subject) > 240)
			{
				$Subject =  $parentInfo['Subject'];
			}
		}


		$Cause = $parentInfo['Cause']."<br/>".$childInfo['Cause'];
		if(strlen($Cause)  > 1000)
		{
			$Cause = $parentInfo['Cause'];
		}
		$Phenomenon = $parentInfo['Phenomenon']."<br/>".$childInfo['Phenomenon'];
		if(strlen($Phenomenon)  > 1000)
		{
			$Phenomenon = $parentInfo['Phenomenon'];
		}

		$Note = $parentInfo['Note']."<br/>".$childInfo['Note'];
		$unit = $parentInfo['Unit'];

		if(strpos($unit, $childInfo['Unit']) === false)
		{
			$unit .= '<br/>'.$childInfo['Unit'];
			if(strlen($unit) > 120)
			{
				$unit =  $parentInfo['Unit'];
			}
		}



		if($childInfo['Severity'] > $parentInfo['Severity'])
		{
			$Severity = $childInfo['Severity'];
		}
		else
		{
			$Severity = $parentInfo['Severity'];
		}

		if($childInfo['Reliability'] > $parentInfo['Reliability'])
		{
			$Reliability = $childInfo['Reliability'];
		}
		else
		{
			$Reliability = $parentInfo['Reliability'];
		}

		if($childInfo['StartTime'] < $parentInfo['StartTime'])
		{
			$StartTime = $childInfo['StartTime'];
		}
		else
		{
			$StartTime = $parentInfo['StartTime'];
		}

		if($childInfo['LastTime'] > $parentInfo['LastTime'])
		{
			$LastTime = $childInfo['LastTime'];
		}
		else
		{
			$LastTime = $parentInfo['LastTime'];
		}

		if($childInfo['ActionTime'] > $parentInfo['ActionTime'])
		{
			$ActionTime = $childInfo['ActionTime'];
		}
		else
		{
			$ActionTime = $parentInfo['ActionTime'];
		}

		if($childInfo['DueTime'] > $parentInfo['DueTime'])
		{
			$DueTime = $childInfo['DueTime'];
		}
		else
		{
			$DueTime = $parentInfo['DueTime'];
		}

		if($childInfo['ResolveTime'] > $parentInfo['ResolveTime'])
		{
			$ResolveTime = $childInfo['ResolveTime'];
		}
		else
		{
			$ResolveTime = $parentInfo['ResolveTime'];
		}

		$ProgressState = "open";//强制打开为open
		if($childInfo['ResolveTime'] > $parentInfo['ResolveTime'])
		{
			$ResolveTime = $childInfo['ResolveTime'];
		}
		else
		{
			$ResolveTime = $parentInfo['ResolveTime'];
		}
		$EvidenceNum = $childInfo['EvidenceNum']+$parentInfo['EvidenceNum'];
		$EmailNum = $childInfo['EmailNum']+$parentInfo['EmailNum'];
		$ReserveValue1 = $childInfo['ReserveValue1']+$parentInfo['ReserveValue1'];
		$ReserveValue2 = $childInfo['ReserveValue2']+$parentInfo['ReserveValue2'];
		$ReserveValue3 = $childInfo['ReserveValue3']+$parentInfo['ReserveValue3'];
		$ReserveValue4 = $childInfo['ReserveValue4']+$parentInfo['ReserveValue4'];
			
		$ReserveValue5 = $parentInfo['ReserveValue5']."  ".$childInfo['ReserveValue5'];
		$ReserveValue6 = $parentInfo['ReserveValue6']."  ".$childInfo['ReserveValue6'];

		/*
		 $ReserveValue4
		 */
		$sql = "update Cases set TcID=-1,TID=-1,Subject='$Subject',ThreatSrcIPList='$ThreatSrcIPList',ThreatSrcDomainList='$ThreatSrcDomainList',
        ThreatSrcSummary='$ThreatSrcSummary',ThreatDstIPList='$ThreatDstIPList',
        ThreatDstDomainList='$ThreatDstDomainList',
        ThreatDstSummary='$ThreatDstSummary',Cause='$Cause',Unit='$unit',
        Phenomenon='$Phenomenon',Note='$Note',";
		if($Severity) $sql .="Severity=$Severity,";
		if($Reliability) $sql .="Reliability=$Reliability,";
		if($StartTime)   $sql .="StartTime='$StartTime',";
		if($LastTime)   $sql .="LastTime='$LastTime',";
		if($ActionTime)   $sql .="ActionTime='$ActionTime',";
		if($DueTime)   $sql .="DueTime='$DueTime',";
		if($ResolveTime)   $sql .="ResolveTime='$ResolveTime',";

		$sql .="ProgressState='$ProgressState',";
		if($EvidenceNum) $sql .="EvidenceNum=$EvidenceNum,";
		if($EmailNum) $sql .="EmailNum=$EmailNum,";
		if($ReserveValue1) $sql .="ReserveValue1=$ReserveValue1,";
		if($ReserveValue2) $sql .="ReserveValue2=$ReserveValue2,";
		if($ReserveValue3) $sql .="ReserveValue3=$ReserveValue3,";
		if($ReserveValue4) $sql .="ReserveValue4=$ReserveValue4,";

		$parentInfo['Subject'] = $Subject;
		$parentInfo['ThreatSrcIPList'] = $ThreatSrcIPList;
		$parentInfo['ThreatSrcDomainList'] = $ThreatSrcDomainList;
		$parentInfo['ThreatSrcSummary'] = $ThreatSrcSummary;
		$parentInfo['ThreatDstIPList'] = $ThreatDstIPList;
		$parentInfo['ThreatDstDomainList'] = $ThreatDstDomainList;
		$parentInfo['ThreatDstSummary'] = $ThreatDstSummary;
		$parentInfo['Cause'] = $Cause;
		$parentInfo['Unit'] = $unit;
		$parentInfo['Phenomenon'] = $Phenomenon;
		$parentInfo['Note'] = $Note;

		$parentInfo['Severity'] = $Severity;
		$parentInfo['Reliability'] = $Reliability;
		$parentInfo['StartTime'] = $StartTime;
		$parentInfo['LastTime'] = $LastTime;
		$parentInfo['ActionTime'] = $ActionTime;
		$parentInfo['DueTime'] = $DueTime;
		$parentInfo['ResolveTime'] = $ResolveTime;
		$parentInfo['ProgressState'] = $ProgressState;
		$parentInfo['EvidenceNum'] = $EvidenceNum;
		$parentInfo['EmailNum'] = $EmailNum;
		$parentInfo['ReserveValue1'] = $ReserveValue1;
		$parentInfo['ReserveValue2'] = $ReserveValue2;
		$parentInfo['ReserveValue3'] = $ReserveValue3;
		$parentInfo['ReserveValue4'] = $ReserveValue4;
		$parentInfo['ReserveValue5'] = $ReserveValue5;
		$parentInfo['ReserveValue6'] = $ReserveValue6;

		$sql .="ReserveValue5='$ReserveValue5',ReserveValue6='$ReserveValue6',MergeType=1
        where CaseId='".$parentInfo['CaseId']."'";	

		$list = $this->query($sql);

		if($list === false)
		{
			fileWrite($this->getLastSql());
			return __LINE__;
		}

		$pcId = $parentInfo['CaseId'];
		$ccId = $childInfo['CaseId'];
		//证据表
		$sql = "update Evidence set MergeCaseID='$pcId' where CaseID='$ccId'";
		$list = $this->query($sql);
		if($list === false)
		{
			$this->rollback();
			return __LINE__;
		}

		//ResponseTicket
		$sql = "update ResponseTicket set MergeCaseID='$pcId' where CaseId='$ccId'";
		$list = $this->query($sql);
		if($list === false)
		{
			$this->rollback();
			return __LINE__;
		}

		//加入shadow
		$sql = "delete from  CasesShadow where CaseId='$pcId';";
		$list = $this->query($sql);
		$sql = "insert into CasesShadow select * from Cases where CaseId='$pcId';";
		$list = $this->query($sql);

		return 1;
	}
}

?>