<?php
class CaseSummeryModel extends CommonModel
{
	public  $caseIDNum = 0;
	public $prefix = 'NJ';

	//获取Case ID
	private function getCaseID()
	{
		$caseID = "";
		if($this->caseIDNum > 0)
		{
			$this->caseIDNum++;
			$caseID = $this->prefix.'o-'.date("Y").sprintf("%06u",$this->caseIDNum%1000000);
			return $caseID;
		}
		$sql = "select CaseId from Cases where CaseId is not null limit 0,1";
		$res = $this->query($sql);
		if($res !== false && count($res) ==1 && strlen($res[0]['CaseId'])>0)
		{
			$this->prefix= substr($res[0]['CaseId'], 0,2);
		}
		else {
			$this->prefix="NJ";
		}
		$sql = "select Max(CaseID) as caseID from CaseSummery";
		$res = $this->query($sql);
		if($res !== false && count($res) ==1 && strlen($res[0]['caseID'])>0)
		{
			$str = substr($res[0]['caseID'], 8,6);
			$this->caseIDNum = intval($str);
			$this->caseIDNum++;
			$caseID = $this->prefix.'o-'.date("Y").sprintf("%06u",$this->caseIDNum%1000000);
			return $caseID;
		}
		else
		{
			return -1;
		}
	}
	//更新响应策略
	private function updateStrategy($caseID)
	{
		$dateTime = date("Y-m-d H:i:s");
		$sql = "UPDATE ResponseStrategy set Status=0,LastTime='$dateTime' where CaseID='".$caseID."'";
		$res = $this->query($sql);
		if($res === false)
		{
			fileWrite($this->getLastSql()."__".$this->getDbError());
			return -1;
		}
		return 1;
	}
	//获取单位对应的响应人员
	private function getUnitCaseResponsor($unit)
	{
		return 22;
	}
	private function insertAll110001(&$data,$docArray=null)
	{
		$addArray = array();
		$straSql = "";
		$key = "IP";
		$caseIDArray = array();

		$dateTime = date("Y-m-d H:i:s");
		for($i=0;$i<count($data);$i++)
		{
			$data[$i]['Comment'] = $data[$i]['SrcPort'];
			unset($data[$i]['SrcPort']);
			$list = $this->where($key."=".$data[$i][$key]." and TID=110001")->select();
			if(count($list)>0)
			{
				$data[$i]['id'] = $list[0]['id'];
				$res = $this->save($data[$i]);

				if($res === false)
				{
					fileWrite($this->getLastSql()."__".$this->getDbError());
					return -1;
				}
				//新汇报，需要重新发送，故更新Status
				$caseID = $list[0]['CaseID'];
				$this->updateStrategy($caseID);
				$caseIDArray[] = $caseID;//需要进行更新的case
			}
			else {//需要插入case_summery
				
				$num = count($addArray);
				$flag = false;
				for($j=0;$j<$num;$j++)
				{
					if($data[$i][$key] == $addArray[$j][$key])
					{
						$flag = true;
						break;
					}
				}
				if($flag)
				{
					continue;
				}

				$Unit = $data[$i]['Unit'];
				$TID=$data[$i]['TID'];
				//查看Cases是否存在
				$sql = "select * from Cases where TID=$TID and Unit='$Unit';";
				$resList = $this->query($sql);
				if($resList === false)
				{
					fileWrite($this->getLastSql()."__".$this->getDbError());
					return -1;
				}

				if(count($resList) == 1)//存在
				{
					$CaseID = $resList[0]['CaseId'];
					$caseIDArray[] = $CaseID;//需要进行更新的case

					$this->updateStrategy($caseID);//更新响应策略状态
				}
				else {//不存在,插入Cases
					$subject = $Unit."-".get_threat_type_name($TID);
					$TcID = $data[$i]['TcID'];
					$StartTime = $data[$i]['FirstTime'];
					$LastTime = $data[$i]['LastTime'];

					$CaseID = $this->getCaseID();
					if($CaseID == -1)
					{
						return -1;
					}
					$caseResponsor = $this->getUnitCaseResponsor($Unit);
					$caseIDSql = "INSERT INTO Cases (Subject,TcID,TID,StartTime,LastTime,Unit,CaseType,CaseId,CaseResponsor,EvidenceNum,CaseRule) VALUES";
					$caseIDSql.="('$subject',$TcID,$TID,'$StartTime','$LastTime','$Unit',0,'$CaseID',$caseResponsor,2,3)";

					$res = $this->query($caseIDSql);
					if($res === false)
					{
						fileWrite($this->getLastSql()."__".$this->getDbError());
						return -1;
					}
				}

				$data[$i]['CaseID'] =$CaseID;
				$addArray[$num] = $data[$i];
				if($num == 400)
				{
					$res = $this->addAll($addArray);
					if($res === false)
					{
						return -1;
					}
					unset($addArray);
				}
			}//end if
		}//end ofr
		
		if(count($addArray) > 0)
		{
			$res = $this->addAll($addArray);
			if($res === false)
			{
				return -1;
			}
			unset($addArray);
		}
		return $this->updateCaseInfo($caseIDArray,110001);
	}
	//300005
	private function insertAll300005(&$data,$docArray=null)
	{
		$addArray = array();
		$straSql = "";
		$key = "IP";
		$caseIDArray = array();

		$dateTime = date("Y-m-d H:i:s");
		for($i=0;$i<count($data);$i++)
		{
			$list = $this->where($key."=".$data[$i][$key]." and TID=300005 and DstPort=".$data[$i]['DstPort'])->select();
			if(count($list)>0)
			{
				$data[$i]['id'] = $list[0]['id'];
				$res = $this->save($data[$i]);

				if($res === false)
				{
					fileWrite($this->getLastSql()."__".$this->getDbError());
					return -1;
				}
				//新汇报，需要重新发送，故更新Status
				$caseID = $list[0]['CaseID'];
				$this->updateStrategy($caseID);
				$caseIDArray[] = $caseID;//需要进行更新的case
			}
			else {//需要插入case_summery
				$num = count($addArray);
				$flag = false;
				for($j=0;$j<$num;$j++)
				{
					if($data[$i][$key] == $addArray[$j][$key] && $data[$i]['DstPort'] == $addArray[$j]['DstPort'])
					{
						$flag = true;
						break;
					}
				}
				if($flag)
				{
					continue;
				}

				$Unit = $data[$i]['Unit'];
				$TID=$data[$i]['TID'];
				//查看Cases是否存在
				$sql = "select * from Cases where TID=$TID and Unit='$Unit';";
				$resList = $this->query($sql);
				if($resList === false)
				{
					fileWrite($this->getLastSql()."__".$this->getDbError());
					return -1;
				}

				if(count($resList) == 1)//存在
				{
					$CaseID = $resList[0]['CaseId'];
					$caseIDArray[] = $CaseID;//需要进行更新的case

					$this->updateStrategy($caseID);//更新响应策略状态
				}
				else {//不存在,插入Cases
					$subject = $Unit."-".get_threat_type_name($TID);
					$TcID = $data[$i]['TcID'];
					$ReserveValue3 = $data[$i]['TotalFlow'];
					$StartTime = $data[$i]['FirstTime'];
					$LastTime = $data[$i]['LastTime'];

					$CaseID = $this->getCaseID();
					if($CaseID == -1)
					{
						return -1;
					}
					$caseResponsor = $this->getUnitCaseResponsor($Unit);
					$caseIDSql = "INSERT INTO Cases (Subject,TcID,TID,ReserveValue3,StartTime,LastTime,Unit,CaseType,CaseId,CaseResponsor,EvidenceNum,CaseRule) VALUES";
					$caseIDSql.="('$subject',$TcID,$TID,$ReserveValue3,'$StartTime','$LastTime','$Unit',0,'$CaseID',$caseResponsor,2,2)";

					$res = $this->query($caseIDSql);
					if($res === false)
					{
						fileWrite($this->getLastSql()."__".$this->getDbError());
						return -1;
					}
				}

				$data[$i]['CaseID'] =$CaseID;
				$addArray[$num] = $data[$i];
				if($num == 400)
				{
					$res = $this->addAll($addArray);
					if($res === false)
					{
						return -1;
					}
					unset($addArray);
				}
			}//end if
		}//end ofr
		if(count($addArray) > 0)
		{
			$res = $this->addAll($addArray);
			if($res === false)
			{
				return -1;
			}
			unset($addArray);
		}
		return $this->updateCaseInfo($caseIDArray,300005);
	}

	//更新汇总caseinfo
	private function updateCaseInfo($caseIDArray,$TID)
	{
		if(count($caseIDArray) == 0)
		{
			return 1;
		}
		$caseIDArray = array_unique($caseIDArray);
		switch($TID)
		{
			case 300005:
				for($i=0;$i<count($caseIDArray);$i++)
				{
					$CaseID = $caseIDArray[$i];
					$info = $this->where("CaseID='".$CaseID."'")->select();
					if($info === false)
					{
						fileWrite($this->getLastSql()."__".$this->getDbError());
						return -1;
					}
					//$subject = "";
					$lastTime = "1970-12-12 12:12:12";
					$firstTime = "2088-12-12 12:12:12";
					$ReserveValue3 = 0;
					for($j=0;$j<count($info);$j++)
					{
						if($lastTime < $info[$j]['LastTime'])
						{
							$lastTime = $info[$j]['LastTime'];
						}

						if($firstTime > $info[$j]['FirstTime'])
						{
							$firstTime =  $info[$j]['FirstTime'];
						}

						$ReserveValue3 += $info[$j]['TotalFlow'];
					}
					$sql = "UPDATE Cases set LastTime='$lastTime',StartTime='$firstTime',ReserveValue3=$ReserveValue3 where CaseId='$CaseID';";
					$res = $this->query($sql);
					if($res === false)
					{
						fileWrite($this->getLastSql()."__".$this->getDbError());
						return -1;
					}
				}
				break;
			case 110001:
				for($i=0;$i<count($caseIDArray);$i++)
				{
					$CaseID = $caseIDArray[$i];
					$info = $this->where("CaseID='".$CaseID."'")->select();
					if($info === false)
					{
						fileWrite($this->getLastSql()."__".$this->getDbError());
						return -1;
					}
					//$subject = "";
					$lastTime = "1970-12-12 12:12:12";
					$firstTime = "2088-12-12 12:12:12";
					$ReserveValue3 = 0;
					for($j=0;$j<count($info);$j++)
					{
						if($lastTime < $info[$j]['LastTime'])
						{
							$lastTime = $info[$j]['LastTime'];
						}

						if($firstTime > $info[$j]['FirstTime'])
						{
							$firstTime =  $info[$j]['FirstTime'];
						}						
					}
					$sql = "UPDATE Cases set LastTime='$lastTime',StartTime='$firstTime' where CaseId='$CaseID';";
					$res = $this->query($sql);
					if($res === false)
					{
						fileWrite($this->getLastSql()."__".$this->getDbError());
						return -1;
					}
				}
				break;
		}
		return 1;
	}

	public function myAddAll($data,$key="IP")
	{
		if(!is_array($data) || count($data) ==0 )
		{
			return -1;
		}

		$TID = $data[0]["TID"];
		/* $sql = "select id,`Condition` from ResponseDocument where TID=".$data[0]["TID"];
		 $res = $this->query($sql);
		 if($res === false)
		 {
			//print_r($this->getLastSql()."__".$this->getDbError());
			return -1;
			}
			$docArray = $res;
			$qian=array(" ","　","\t","\n","\r");
			$hou=array("","","","","");
			for($kk=0;$kk<count($docArray);$kk++)
			{
			$docArray[$kk]['Condition'] = str_replace($qian,$hou,$docArray[$kk]['Condition']);
			}*/

		$lastRes = -1;
		switch($TID)
		{
			case 300005:
				$lastRes = $this->insertAll300005($data);
				break;
			case 110001:
				$lastRes=$this->insertAll110001($data);
				break;
			default:
				break;
		}

		return $lastRes;
	}
}

?>