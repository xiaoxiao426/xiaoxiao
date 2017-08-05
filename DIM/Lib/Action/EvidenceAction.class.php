<?php
class EvidenceAction extends CommonAction
{
	public function index()
	{
		$type=$_REQUEST['evidenceType'];
		$caseId = $_REQUEST['caseId'];
		if(empty($type) || empty($caseId))
		{
			$this->error("非法查询!");
		}
		$model = M("Evidence");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$listRows = 20;

		if(strpos($caseId, "NJM") !== false)
		{
		
		$nameTable = "MergeCaseID";
		}
		else {
			$nameTable = "CaseID";
		}
		switch($type)
		{
			case 0:
				$this->assign("EvidenceType",0);
				break;
				/* EvidenceABNMDomain*/
			case 1:
				$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceABNMDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 1 ) 
								 AND `EvidenceABNMDomain`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$totalCount = $model->query($countSql);
				$this->assign("EvidenceType",1);
				$this->assign("evidenceName","ABNMDomain");
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceABNMDomain`.`DomainName`,`EvidenceABNMDomain`.`HistoryIPNum`,`EvidenceABNMDomain`.`LastIPNum`
								,`EvidenceABNMDomain`.`Addictive`,`EvidenceABNMDomain`.`Cause`,`EvidenceABNMDomain`.`AttachFile`
								 FROM `Evidence` ,`EvidenceABNMDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 1 ) 
								 AND `EvidenceABNMDomain`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;						
					$vo = $model->query($sql);

					$this->assign("ABNMDomain",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}

				break;
			case 2://fast-flux
				$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceFFDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 2 ) 
								 AND `EvidenceFFDomain`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$this->assign("EvidenceType",2);
				$this->assign("evidenceName","FastFlux");

				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceFFDomain`.`DomainName`,`EvidenceFFDomain`.`IPNum`,`EvidenceFFDomain`.`SubnetNum`
									,`EvidenceFFDomain`.`ASNNum`,`EvidenceFFDomain`.`CountryNum`,`EvidenceFFDomain`.`TTLMin`,`EvidenceFFDomain`.`TTLMax`,`EvidenceFFDomain`.`AttachFile`
								 FROM `Evidence` ,`EvidenceFFDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 2 ) 
								 AND `EvidenceFFDomain`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;
					$vo = $model->query($sql);
					$this->assign("FastFlux",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 3:
				break;
			case 4:
				$this->assign("EvidenceType",4);
				break;
				/*EvidenceIsoLink*/
			case 5:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceIsoLink`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 5 ) 
								 AND `EvidenceIsoLink`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$this->assign("EvidenceType",5);
				$this->assign("evidenceName","IsoLink");

				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceIsoLink`.`SiteName`,`EvidenceIsoLink`.`IsoLinkUrl`,`EvidenceIsoLink`.`ClientIP`
								,`EvidenceIsoLink`.`ServerIP`
								 FROM `Evidence` ,`EvidenceIsoLink`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 5 ) 
								 AND `EvidenceIsoLink`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;
					$vo = $model->query($sql);
					$this->assign("IsoLink",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
					
				break;
				/*EvidenceIRCBotByChannel*/
			case 6:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceIrcBotByChannel`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 6 ) 
								 AND `EvidenceIrcBotByChannel`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$this->assign("EvidenceType",6);
				$this->assign("evidenceName","IrcBotByChannel");
				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceIrcBotByChannel`.`ChannelName`,`EvidenceIrcBotByChannel`.`ClientIPCnt`,`EvidenceIrcBotByChannel`.`ServerIPCnt`
								,`EvidenceIrcBotByChannel`.`NicknameCnt`,`EvidenceIrcBotByChannel`.`MetaDataCnt`,`EvidenceIrcBotByChannel`.`AttachFile`
								 FROM `Evidence` ,`EvidenceIrcBotByChannel`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 6 ) 
								 AND `EvidenceIrcBotByChannel`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;
					$vo = $model->query($sql);
					$this->assign("IrcBotByChannel",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 7:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceMalWeb`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 7 ) 
								 AND `EvidenceMalWeb`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$this->assign("EvidenceType",7);
				$this->assign("evidenceName","MalWeb");
				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceMalWeb`.`DomainName`,`EvidenceMalWeb`.`WebIP`,`EvidenceMalWeb`.`WebUrl`,
								`EvidenceMalWeb`.`VictimType`,`EvidenceMalWeb`.`MalWebUrl`,`EvidenceMalWeb`.`MalHost`,`EvidenceMalWeb`.`AttachFile`
								 FROM `Evidence` ,`EvidenceMalWeb`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 7 ) 
								 AND `EvidenceMalWeb`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;
					$vo = $model->query($sql);
					$this->assign("MalWeb",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 8:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceSQLI`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 8 ) 
								 AND `EvidenceSQLI`.`EvidenceID`=`Evidence`.`EvidenceID`";

				$this->assign("EvidenceType",8);
				$this->assign("evidenceName","EvidenceSQLI");
				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceSQLI`.`siteName`,`EvidenceSQLI`.`SQLIUrl`,`EvidenceSQLI`.`ClientIP`,
							`EvidenceSQLI`.`ServerIP`,`EvidenceSQLI`.`SQLICnt`,`EvidenceSQLI`.`SQLITool`,`EvidenceSQLI`.`AttachFile`
								 FROM `Evidence` ,`EvidenceSQLI`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 8 ) 
								 AND `EvidenceSQLI`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;

					$vo = $model->query($sql);
					$this->assign("SQLI",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 10:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceNBOSDDOS`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 10 ) 
								 AND `EvidenceNBOSDDOS`.`EvidenceID`=`Evidence`.`EvidenceID`";

				$this->assign("EvidenceType",10);
				$this->assign("evidenceName","EvidenceNBOSDDOS");
				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceNBOSDDOS`.`TargetIP`,`EvidenceNBOSDDOS`.`IPNum`,`EvidenceNBOSDDOS`.`PPS`,`EvidenceNBOSDDOS`.`BPS`,`EvidenceNBOSDDOS`.`Duration`,`EvidenceNBOSDDOS`.`PktNum`,`EvidenceNBOSDDOS`.`Rule`,`EvidenceNBOSDDOS`.`ByteNum`,`EvidenceNBOSDDOS`.`AttachFile`
								 FROM `Evidence` ,`EvidenceNBOSDDOS`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 10 ) 
								 AND `EvidenceNBOSDDOS`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;

					$vo = $model->query($sql);
					//	print_r($vo);
					$this->assign("NBOSDDOS",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 13:
				$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceNBOSUAHT`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceNBOSUAHT`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$totalCount = $model->query($countSql);
				$this->assign("EvidenceType",$type);
				$this->assign("evidenceName","NbosUahPort");
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceNBOSUAHT`.*
								 FROM `Evidence` ,`EvidenceNBOSUAHT`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceNBOSUAHT`.`EvidenceID`=`Evidence`.`EvidenceID` order by OccurNum desc limit ".$p->firstRow . ',' . $p->listRows;						
					$vo = $model->query($sql);
					foreach ($vo as $key => $value) {
						$name[$key] = $value['OccurNum'];
						$rating[$key] = $value['TimeLive'];

					}
					array_multisort($name,SORT_DESC,$rating,SORT_DESC, $vo);
					$this->assign("NbosUahPort",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}

				$this->assign("EvidenceType",13);
				break;
			case 12:
			case 104:
				$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$totalCount = $model->query($countSql);
				$this->assign("EvidenceType",$type);
				$this->assign("evidenceName","Botnet");
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceBotnetAct`.*
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;						
					$vo = $model->query($sql);

					$this->assign("Botnet",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 14:
				$countSql = "SELECT count(*) as totalCount
								 FROM `Evidence` ,`EvidenceBotnetActCli`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetActCli`.`EvidenceID`=`Evidence`.`EvidenceID`";
				$totalCount = $model->query($countSql);
				$this->assign("EvidenceType",$type);
				$this->assign("evidenceName","Botnet");
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceBotnetActCli`.*
								 FROM `Evidence` ,`EvidenceBotnetActCli`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetActCli`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;						
					$vo = $model->query($sql);

					$this->assign("Botnet",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 110:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceWebshell`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 110 ) 
								 AND `EvidenceWebshell`.`EvidenceID`=`Evidence`.`EvidenceID`";


				$this->assign("EvidenceType",110);
				$this->assign("evidenceName","Webshell");
				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceWebshell`.*
								 FROM `Evidence` ,`EvidenceWebshell`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 110 ) 
								 AND `EvidenceWebshell`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;

					$vo = $model->query($sql);
					$this->assign("Webshell",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			case 101:
				$countSql = "SELECT count(*) as totalCount
								  FROM `Evidence` ,`EvidenceSuricata`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 101 ) 
								 AND `EvidenceSuricata`.`EvidenceID`=`Evidence`.`EvidenceID`";


				$this->assign("EvidenceType",101);
				$this->assign("evidenceName","Suricata");
				$totalCount = $model->query($countSql);
				if($totalCount && $totalCount[0]['totalCount'] >0)
				{
					import ( "@.ORG.Page" );
					$p = new Page ( $totalCount[0]['totalCount'], $listRows );
					$sql = "SELECT `Evidence`.*,`EvidenceSuricata`.`SIP`,`EvidenceSuricata`.`RawCnt`,`EvidenceSuricata`.`DIP`,`EvidenceSuricata`.`SPort` ,`EvidenceSuricata`.`DPort`,`EvidenceSuricata`.`AlertMsg`,`EvidenceSuricata`.`AttachFile`
								 FROM `Evidence` ,`EvidenceSuricata`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 101 ) 
								 AND `EvidenceSuricata`.`EvidenceID`=`Evidence`.`EvidenceID` limit ".$p->firstRow . ',' . $p->listRows;

					$vo = $model->query($sql);
					$this->assign("Suricata",$vo);
					$page = $p->show ();
					$this->assign ( "page", $page );
				}
				break;
			default:
				break;
		}
		//		fileWrite($model->getLastSql());
		$this->assign("pageFlag",1);
		$this->assign("CaseId",$caseId);
		$this->assign("totalCount",$totalCount[0]['totalCount']);
		$this->display();
	}
	//获取证据类型
	public function getEvidenceType()
	{
		$name = "EvidenceType";
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$list = $model->select();
		$this->assign("evidenceType",$list);
	}

	public function add()
	{
		if(!isset($_REQUEST['id']) && !isset($_REQUEST['CaseId']))
		{
			$this->error("数据错误!");
		}
		$id = $_REQUEST['id'];
		$CaseId = $_REQUEST['CaseId'];
		$this->getEvidenceType();
		$name = "Cases";
		$model= M($name);
		if(!empty($model))
		{
			$field = "`Cases`.*,`Users`.`name`";
			$join = "left join `Users` on `Users`.id=`Cases`.CaseResponsor";
			if($id)
			{
				$vo = $model->where("`Cases`.id=$id")->field($field)->join($join)->select();
			}
			else {
				$vo = $model->where("`Cases`.CaseId='$CaseId'")->field($field)->join($join)->select();
			}

			$this->assign ( 'vo', $vo[0] );
		}

		/*初始化div内容commonAdd*/
		$fileName = dirname(dirname(dirname(__FILE__))).'/Tpl/default/EvidenceTpl/CommonAdd.html';
		$file = fopen($fileName,"r");
		$content = fread($file,filesize($fileName));
		fclose($file);
		$this->assign("commonAdd",$content);
		$this->display();
	}
	public function details()
	{
		$this->display();
	}

	public function insert()
	{
		$name = "Evidence";
		$model = M($name);
		$fileId = $_REQUEST['CaseID'];
		$caseId = $_REQUEST['tableId'];
		if (empty($model) || false === $model->create ()) {
			$this->error ( $model->getError () );
		}

		$model->startTrans();
		//读写锁定
		$sql = "lock table `Evidence` read; lock table `Evidence` write;";
		$model->query($sql);

		$sql = "select max(`EvidenceID`) as MaxEviId from `Evidence`;";
		$list = $model->query($sql);
		if($list === false || count($list) <=0)
		{
			$this->error("数据错误!".$model->getLastSql());
		}
		$evidenceId = $list[0]['MaxEviId'];
		$EvidenceType = $model->EvidenceType;
		$evidenceId = $this->getEvidenceId($evidenceId);
		$model->EvidenceID = $evidenceId;

		$list=$model->add();
		//	fileWrite($model->getLastSql());
		$lastId = $model->getLastInsID();
		$sql = "unlock table `Evidence`";
		$model->query($sql);

		if ($list===false) {
			//失败提示

			$this->error ('新增失败!');
			return;
		}

		//证据附表
		$flag = true;
		$dbName = "";
		$attachFile = 1;
		switch($EvidenceType)
		{
			case 0:
				$flag = false;
				break;
				/* EvidenceABNMDomain*/
			case 1:
				$dbName = 'EvidenceABNMDomain';
				$name = $evidenceId.date("YmdHis");
				$attachFile = $this->direcUpload($name,"file",'',$fileId);
				//带有附件
				break;
			case 2:
				$dbName = 'EvidenceFastFlux';
				break;
			case 3:
				$flag = false;
				break;
			case 4:
				$flag = false;
				break;
				/*EvidenceIsoLink*/
			case 5:
				$dbName = 'EvidenceIsoLink';
				break;
				/*EvidenceIRCBotByChannel*/
			case 6:
				$dbName = 'EvidenceIrcBotByChannel';
				$name = $evidenceId.date("YmdHis");
				$attachFile = $this->direcUpload($name,"file",'',$fileId);
				//带有附件
				break;
			case 7:
				$flag = false;
				break;
			case 8:
				$dbName = 'EvidenceSQLI';
				$attachFile = $this->direcUpload($name,"file",'',$fileId);
				break;
			case 10:
				$dbName = 'EvidenceNBOSDDOS';
				$name = $evidenceId.date("Ymd");
				$attachFile = $this->direcUpload($name,"file",'',$fileId);
				break;
			default:
				$flag = false;
				break;
		}

		if($flag == false)
		{
			$model->commit();
			if(!$caseId)
			{
				$this->success("插入成功!");
			}
			else {
				$this->redirect('/Incident/details/id/'.$caseId);
			}
			return;
		}
		//检查附件上传是否成功
		if(strlen($attachFile) == 0)
		{
			$model->rollback();
			$this->error("文件上传失败，请检查文件格式和文件大小!");
			return;
		}
		$attachModel = M($dbName);
		if (empty($attachModel) || false === $attachModel->create ()) {
			$this->error ( $attachModel->getError () );
		}
		$attachModel->startTrans();
		$attachModel->EvidenceID = $evidenceId;
		if(strlen($attachFile) > 1)
		{
			$attachModel->AttachFile = $attachFile;
		}
		$list=$attachModel->add();
		//	fileWrite($attachModel->getLastSql());
		if ($list===false) {
			//失败提示
			$model->rollback();
			$attachModel->rollback();
			$this->error ('新增失败!');
		}
		else
		{
			$model->commit();
			$attachModel->commit();
			if(!$caseId)
			{
				$this->success("插入成功!");
			}
			else {
				$this->redirect('/Incident/details/id/'.$caseId);
			}
		}
	}

	//Ajax获取证据添加的内容，根据不同的EvidenceType
	public function getEvidenceAdd()
	{
		$type = $_REQUEST['id'];
		if($type === false)
		{
			$type = 0;
		}
		switch($type)
		{
			case 0:
				$fileName = 'CommonAdd.html';
				break;
				/* EvidenceABNMDomain*/
			case 1:
				$fileName = 'ABNMDomainAdd.html';
				break;
			case 2:
				$fileName = 'FastFluxAdd.html';
				break;
			case 3:
				$fileName = 'CommonAdd.html';
				break;
			case 4:
				$fileName = 'CommonAdd.html';
				break;
				/*EvidenceIsoLink*/
			case 5:
				$fileName = 'IsoLinkAdd.html';
				break;
				/*EvidenceIRCBotByChannel*/
			case 6:
				$fileName = 'IrcBotByChannelAdd.html';
				break;
			case 7:
				$fileName = 'CommonAdd.html';
				break;
			case 8:
				$fileName = 'SQLIAdd.html';
				break;
			case 10:
				$fileName = 'NBOSDDOSAdd.html';
				break;
			case 12:
			case 104:
				$fileName = 'BotnetAdd.html';
				break;
			default:
				$fileName = 'CommonAdd.html';
				break;
		}

		$fileName = dirname(dirname(dirname(__FILE__))).'/Tpl/default/EvidenceTpl/'.$fileName;
		$file = fopen($fileName,"r");
		$content = fread($file,filesize($fileName));
		fclose($file);
		$this->AjaxReturn($content,"",1,"");
		//	$this->ajaxReturn($content);
	}


	/*
	 * 根据上次的最大EvidenceId获取当前最大的EvidenceId
	 * */
	public function getEvidenceId($maxId)
	{
		$currentY = date("Y");
		//caseId 为0 或者跨年
		if(empty($maxId) || $maxId == 0 || $currentY != substr($maxId, 0,4))
		{
			$maxId = $currentY."000001";
		}
		else {
			$maxId += 1;
		}
		return $maxId;
	}

	//log日志文件显示
	public function logDetails()
	{
		$fileName = $_REQUEST['rname'];
		if(strlen($fileName) <= 0)
		{
			$this->error("数据错误!");
		}

		if($this->getExt($fileName) != "log")
		{
			print_r("Cannot open ".$fileName);
			return;
		}

		$filePath = dirname(dirname(dirname(dirname(__FILE__))))."/Public/Uploads/".$fileName;

		$filePath = iconv("UTF-8","GBK",$filePath);//包含中文字符时需要转换
		if(file_exists($filePath) )//判断文件是否存在并打开
		{
			$file=fopen($filePath,"r");
			if($file)
			{
				echo fread($file,filesize($filePath));
				fclose($file);
			}
			else {
				print_r("can not read file!");
			}
		}
		else
		{
			print_r("file not exists");
		}
	}

	private function getExt($filename) {
		$pathinfo = pathinfo($filename);
		return $pathinfo['extension'];
	}
}
?>