<?php
class EvidenceModel extends CommonModel {

	/*
	 * $caseId:事件ID
	 * $mergeType:事件是否为合成类型
	 * $etype:返回类型
	 * */
	public function getEvidence($caseId,$mergeType,$e_type=0)
	{
		$return = array();

		if($mergeType == 0)
		{
			$nameTable = "CaseID";
		}
		else {
			$nameTable = "MergeCaseID";
			$countSql = "select count(*) as count from Evidence where MergeCaseID='$caseId'";
			$res = $this->query($countSql);
			
			if($res === false)
			{
				return -1;
			}
			$return['totalCount'] = $res[0]['count'];
			
			$sql = "select * from Evidence where MergeCaseID='$caseId' order by TimeLast desc limit 0,10;";
			$list = $this->query($sql);
			//fileWrite($this->getDbError());
			$return['evidenceList'] = $list;
			$return['evidenceType'][0]['EvidenceType'] = -10;
			$return['EvidenceType'] = -1;
			$return['CaseId'] = $caseId;
			return $return;			
		}
		

		//查找相关证据
		//首先查找EvidenceType
		$sql = "select distinct `EvidenceType` as EvidenceType from `Evidence` where `$nameTable`='$caseId'";
		$res = $this->query($sql);

		for($k=0;$k<count($res);$k++)
		{
			if(!$res[$k]['EvidenceType'])
			{
				unset($res[$k]);
			}
		}
		if($res === false)
		{
			//$this->error("数据查询错误!");
			return -1;
		}
		if($e_type==1)
		{

		}
		else {
			//$this->assign("evidenceType",$res);
			$return['evidenceType'] = $res;
		}

		$map = Array();
		$map['CaseId'] = $caseId;
		$join = "";
		$vo = Array();
		$field = "";
		$evidenceName = "";//供页面误报用

		if($e_type==1)
		{
			$limit = " limit 0,50";
			$returnArray = array();
		}
		else {
			$limit = " limit 0,10";
		}
		for($i=0;$i<sizeof($res);$i++)
		{
			switch($res[$i]['EvidenceType'])
			{
				case 0:
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 0;
					}
					else {
						$return['evidenceType'] = 0;
					}
					break;
					/* EvidenceABNMDomain*/
				case 1:
					$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceABNMDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 1 ) 
								 AND `EvidenceABNMDomain`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$sql = "SELECT `Evidence`.*,`EvidenceABNMDomain`.`DomainName`,`EvidenceABNMDomain`.`HistoryIPNum`,`EvidenceABNMDomain`.`LastIPNum`
								,`EvidenceABNMDomain`.`Addictive`,`EvidenceABNMDomain`.`Cause`,`EvidenceABNMDomain`.`AttachFile`
								 FROM `Evidence` ,`EvidenceABNMDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 1 ) 
								 AND `EvidenceABNMDomain`.`EvidenceID`=`Evidence`.`EvidenceID` $limit";						
					$vo = $this->query($sql);
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 1;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$evidenceName .="ABNMDomain,";
						$totalCount = $this->query($countSql);
						$return['EvidenceType'] = 1;
						$return['ABNMDomain'] = $vo;
						$return['ABNMDomainTotalCount'] = $totalCount[0]['totalCount'];
					}

					break;
				case 2://fast-flux
					$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceFFDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 2 ) 
								 AND `EvidenceFFDomain`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$sql = "SELECT `Evidence`.*,`EvidenceFFDomain`.`DomainName`,`EvidenceFFDomain`.`IPNum`,`EvidenceFFDomain`.`SubnetNum`
									,`EvidenceFFDomain`.`ASNNum`,`EvidenceFFDomain`.`CountryNum`,`EvidenceFFDomain`.`TTLMin`,`EvidenceFFDomain`.`TTLMax`,`EvidenceFFDomain`.`AttachFile`
								 FROM `Evidence` ,`EvidenceFFDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 2 ) 
								 AND `EvidenceFFDomain`.`EvidenceID`=`Evidence`.`EvidenceID` $limit";						
					$vo = $this->query($sql);
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 2;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$totalCount = $this->query($countSql);
						$evidenceName .="FastFlux,";
						$return['EvidenceType'] = 2;
						$return['FastFlux'] = $vo;
						$return['FastFluxTotalCount'] = $totalCount[0]['totalCount'];
					}
					break;
				case 3:
					break;
				case 4:
					$return['EvidenceType'] = 4;
					break;
					/*EvidenceIsoLink*/
				case 5:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceIsoLink`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 5 ) 
								 AND `EvidenceIsoLink`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$sql = "SELECT `Evidence`.*,`EvidenceIsoLink`.`SiteName`,`EvidenceIsoLink`.`IsoLinkUrl`,`EvidenceIsoLink`.`ClientIP`
								,`EvidenceIsoLink`.`ServerIP`
								 FROM `Evidence` ,`EvidenceIsoLink`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 5 ) 
								 AND `EvidenceIsoLink`.`EvidenceID`=`Evidence`.`EvidenceID` $limit";						
					$vo = $this->query($sql);
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 5;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$evidenceName .="IsoLink,";
						$totalCount = $this->query($countSql);
						$return['EvidenceType'] = 5;
						$return['IsoLink'] = $vo;
						$return['IsoLinkTotalCount'] = $totalCount[0]['totalCount'];
					}
					break;
					/*EvidenceIRCBotByChannel*/
				case 6:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceIrcBotByChannel`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 6 ) 
								 AND `EvidenceIrcBotByChannel`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$sql = "SELECT `Evidence`.*,`EvidenceIrcBotByChannel`.`ChannelName`,`EvidenceIrcBotByChannel`.`ClientIPCnt`,`EvidenceIrcBotByChannel`.`ServerIPCnt`
								,`EvidenceIrcBotByChannel`.`NicknameCnt`,`EvidenceIrcBotByChannel`.`MetaDataCnt`,`EvidenceIrcBotByChannel`.`AttachFile`
								 FROM `Evidence` ,`EvidenceIrcBotByChannel`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 6 ) 
								 AND `EvidenceIrcBotByChannel`.`EvidenceID`=`Evidence`.`EvidenceID` $limit";						
					$vo = $this->query($sql);
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 6;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$evidenceName .="IrcBotByChannel,";
						$totalCount = $this->query($countSql);
						$return['EvidenceType'] = 6;
						$return['IrcBotByChannel'] = $vo;
						$return['IrcBotByChannelTotalCount'] = $totalCount[0]['totalCount'];
					}
					break;
				case 7:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceMalWeb`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 7 ) 
								 AND `EvidenceMalWeb`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$totalCount = $this->query($countSql);
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{
						$sql = "SELECT `Evidence`.*,`EvidenceMalWeb`.`DomainName`,`EvidenceMalWeb`.`WebIP`,`EvidenceMalWeb`.`WebUrl`,
								`EvidenceMalWeb`.`VictimType`,`EvidenceMalWeb`.`MalWebUrl`,`EvidenceMalWeb`.`MalHost`,`EvidenceMalWeb`.`AttachFile`
								 FROM `Evidence` ,`EvidenceMalWeb`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 7 )  
								 AND `EvidenceMalWeb`.`EvidenceID`=`Evidence`.`EvidenceID` $limit ";

						$vo = $this->query($sql);
						if($e_type !=1) {
							$return['MalWeb'] = $vo;
						}
					}
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 7;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$evidenceName .="EvidenceMalWeb,";
						$return['EvidenceType'] = 7;
						$return['EvidenceMalWebTotalCount'] = $totalCount[0]['totalCount'];
					}
					break;
				case 8:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceSQLI`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 8 ) 
								 AND `EvidenceSQLI`.`EvidenceID`=`Evidence`.`EvidenceID`";

					if($e_type != 1) {
						$return['EvidenceType'] = 8;
					}
					$evidenceName .="EvidenceSQLI,";
					$totalCount = $this->query($countSql);
					$return['EvidenceSQLITotalCount'] = $totalCount[0]['totalCount'];
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{
						$sql = "SELECT `Evidence`.*,`EvidenceSQLI`.`siteName`,`EvidenceSQLI`.`SQLIUrl`,`EvidenceSQLI`.`ClientIP`,
							`EvidenceSQLI`.`ServerIP`,`EvidenceSQLI`.`SQLICnt`,`EvidenceSQLI`.`SQLITool`,`EvidenceSQLI`.`AttachFile`
								 FROM `Evidence` ,`EvidenceSQLI`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 8 ) 
								 AND `EvidenceSQLI`.`EvidenceID`=`Evidence`.`EvidenceID` $limit ";

						$vo = $this->query($sql);
						if($e_type==1)
						{
							$returnArray[$i]['EvidenceType'] = 8;
							$returnArray[$i]['data'] = $vo;
						}
						else {
							$return['EvidenceType'] = 8;
							$return['SQLI'] = $vo;
							$return['EvidenceSQLITotalCount'] = $totalCount[0]['totalCount'];
						}
					}
					break;
				case 10:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceNBOSDDOS`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 10 ) 
								 AND `EvidenceNBOSDDOS`.`EvidenceID`=`Evidence`.`EvidenceID`";

					//$this->assign("EvidenceType",10);
					$evidenceName .="NBOSDDOS,";
					$totalCount = $this->query($countSql);
				//	$this->assign("NBOSDDOSTotalCount",$totalCount[0]['totalCount']);
					$return['EvidenceType'] = 10;
					$return['NBOSDDOSTotalCount'] = $totalCount[0]['totalCount'];
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{

						$sql = "SELECT `Evidence`.*,`EvidenceNBOSDDOS`.`TargetIP`,`EvidenceNBOSDDOS`.`IPNum`,`EvidenceNBOSDDOS`.`PPS`,`EvidenceNBOSDDOS`.`BPS`,`EvidenceNBOSDDOS`.`Duration`,`EvidenceNBOSDDOS`.`PktNum`,`EvidenceNBOSDDOS`.`Rule`,`EvidenceNBOSDDOS`.`ByteNum`,`EvidenceNBOSDDOS`.`AttachFile`
								 FROM `Evidence` ,`EvidenceNBOSDDOS`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 10 ) 
								 AND `EvidenceNBOSDDOS`.`EvidenceID`=`Evidence`.`EvidenceID` $limit ";

						$vo = $this->query($sql);
						if($e_type==1)
						{
							$returnArray[$i]['EvidenceType'] = 10;
							$returnArray[$i]['data'] = $vo;
						}
						else {
							$return['NBOSDDOS'] = $vo;
						}
					}
					break;
				case 13:
					$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence`
								 WHERE ( `$nameTable` = '$caseId' ) ";
					$totalCount = $this->query($countSql);
					//echo $this->getLastSql();
					$evidenceName .="NbosUahPort,";
					$return['EvidenceType'] = 13;
					$return['NbosUahPortTotalCount'] = $totalCount[0]['totalCount'];
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{
						$sql = "SELECT `Evidence`.*
								 FROM `Evidence` 
								 WHERE ( `$nameTable` = '$caseId' ) $limit ";						
						$vo = $this->query($sql);

						for($k=0;$k<count($vo);$k++)
						{
							$sql = "SELECT `EvidenceNBOSUAHT`.*
								 FROM `EvidenceNBOSUAHT`
								 WHERE  `EvidenceID`= ".$vo[$k]['EvidenceID'];
							$votemp = $this->query($sql);
							$votemp = $votemp[0];
							foreach ($votemp as $key=>$value)
							{
								$vo[$k][$key] = $value;
							}
						}


						foreach ($vo as $key => $value) {
							$name[$key] = $value['OccurNum'];
							$rating[$key] = $value['TimeLive'];

						}
						//	print_r($this->getLastSql());
						array_multisort($name,SORT_DESC,$rating,SORT_DESC, $vo);
						//print_r($vo);
						if($e_type==1)
						{
							$returnArray[$i]['EvidenceType'] = 13;
							$returnArray[$i]['data'] = $vo;
						}
						else {
							$return['NbosUahPort'] = $vo;
						}

					}
					break;
				case 12:
					$type = 12;
					$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID`";
					//echo $countSql;

					$totalCount = $this->query($countSql);
					$evidenceName .="Botnet,";
					$return['EvidenceType'] = 12;
					$return['BotnetTotalCount'] = $totalCount[0]['totalCount'];
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{
						$sql = "SELECT `Evidence`.*,`EvidenceBotnetAct`.*
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID` $limit ";						
						$vo = $this->query($sql);

						if($e_type==1)
						{
							$returnArray[$i]['EvidenceType'] = 12;
							$returnArray[$i]['data'] = $vo;
							fileWriteArray($returnArray);
						}
						else {
							$return['Botnet'] = $vo;
						}
					}
					break;
				case 14:
					$type = 14;
					$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceBotnetActCli`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetActCli`.`EvidenceID`=`Evidence`.`EvidenceID`";
					//echo $countSql;

					$totalCount = $this->query($countSql);
					$evidenceName .="Botnet,";
					$return['EvidenceType'] = $type;
					$return['BotnetTotalCount'] = $totalCount[0]['totalCount'];
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{
						$sql = "SELECT `Evidence`.*,`EvidenceBotnetActCli`.*
								 FROM `Evidence` ,`EvidenceBotnetActCli`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetActCli`.`EvidenceID`=`Evidence`.`EvidenceID` $limit ";						
						$vo = $this->query($sql);
						if($e_type==1)
						{
							$returnArray[$i]['EvidenceType'] = 14;
							$returnArray[$i]['data'] = $vo;
						}
						else {
							$return['Botnet'] = $vo;
						}
					}
					break;
				case 104:
					$type = 104;
					$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$totalCount = $this->query($countSql);
					$evidenceName .="Botnet,";
					$return['EvidenceType'] = $type;
					$return['BotnetTotalCount'] = $totalCount[0]['totalCount'];
					if($totalCount && $totalCount[0]['totalCount'] >0)
					{
						$sql = "SELECT `Evidence`.*,`EvidenceBotnetAct`.*
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID` $limit ";						
						$vo = $this->query($sql);
						if($e_type==1)
						{
							$returnArray[$i]['EvidenceType'] = 104;
							$returnArray[$i]['data'] = $vo;
						}
						else {
							$return['Botnet'] = $vo;
						}
					}
					break;
				case 101:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceSuricata`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 101 ) 
								 AND `EvidenceSuricata`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$sql = "SELECT `Evidence`.*,`EvidenceSuricata`.`SIP`,`EvidenceSuricata`.`RawCnt`,`EvidenceSuricata`.`DIP`,`EvidenceSuricata`.`SPort` ,`EvidenceSuricata`.`DPort`,`EvidenceSuricata`.`AlertMsg`,`EvidenceSuricata`.`AttachFile`
								 FROM `Evidence` ,`EvidenceSuricata`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 101 ) 
								 AND `EvidenceSuricata`.`EvidenceID`=`Evidence`.`EvidenceID` $limit";						
					$vo = $this->query($sql);
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 101;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$evidenceName .="Suricata,";
						$totalCount = $this->query($countSql);
						$return['EvidenceType'] = $type;
						$return['Suricata'] = $vo;
						$return['SuricataTotalCount'] = $totalCount[0]['totalCount'];
					}
					break;
				case 110:
					$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceWebShell`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 110 ) 
								 AND `EvidenceWebShell`.`EvidenceID`=`Evidence`.`EvidenceID`";
					$sql = "SELECT `Evidence`.*,`EvidenceWebShell`.*
								 FROM `Evidence` ,`EvidenceWebShell`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 110 ) 
								 AND `EvidenceWebShell`.`EvidenceID`=`Evidence`.`EvidenceID` $limit";						
					$vo = $this->query($sql);
					if($e_type==1)
					{
						$returnArray[$i]['EvidenceType'] = 110;
						$returnArray[$i]['data'] = $vo;
					}
					else {
						$evidenceName .="WebShell,";
						$totalCount = $this->query($countSql);
						$return['EvidenceType'] = $type;
						$return['WebShell'] = $vo;
						$return['WebShellTotalCount'] = $totalCount[0]['totalCount'];
					}
					break;
				default:
					break;
			}
		}
		if($e_type==1) return $returnArray;
		else
		{			
			$return['evidenceName'] = $evidenceName;
			$return['CaseId'] = $caseId;
			$return['totalCount'] = $totalCount[0]['totalCount'];
			return $return;
		}

	}

}
?>