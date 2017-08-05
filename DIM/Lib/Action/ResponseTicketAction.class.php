<?php
class ResponseTicketAction extends CommonAction
{
	public function upload() {
		//	fileWrite($_REQUEST['CaseId']);
		//fileWrite("filePath:".APP_PATH."\tAPP_PUBLIC_PATH:".APP_PUBLIC_PATH."::moduleName::".MODULE_NAME);
		if(!empty($_FILES)) {//如果有文件上传
			// 上传附件并保存信息到数据库
			$this->_upload("Attachment");
		}
	}

	public function test()
	{
		$idAtt = $_REQUEST['newUploadIds'];
		$ids = preg_split('/,/',$idAtt);
		$count = 0;

		$yId = "";
		$j = 0;
		for($i=0;$i<sizeof($ids)-1;$i++)
		{
			if($ids[$i])
			{
				$count++;
				$yId .= $ids[$i].",";
			}
		}
		if($ids[sizeof($ids)-1])
		{
			$count++;
		}
		//无附件
		if($count<=0)
		{
			C("TOKEN_ON",true);
			$this->assign ( 'jumpUrl', "__URL__/details/id/$tableId/Flag/0" );
			$this->success("操作成功!");
		}
		//最后一个附件
		if(sizeof($ids) > 0)
		{
			$yId .= $ids[sizeof($ids)-1];
		}

		//	fileWrite($yId);

		$attModel = M("Attachment");
		if(empty($attModel))
		{
			C("TOKEN_ON",true);
			$this->error("操作失败!");
		}
		$condition = array ("id" => array ('in', $yId) );
		$list=$attModel->where ( $condition )->setField ( 'TicketId', $ticketId );
	}
	//异步删除
	public function delete() {
		//删除指定记录
		$name="ResponseTicket";
		$model = M ($name);
		if (! empty ( $model )) {
			$id = $_REQUEST ['id'];
			if (isset ( $id )) {
				$list=$model->where ("id=$id")->delete();
				if($res === false)
				{
					$this->AjaxReturn("","",0,"");
				}
				else {
					$this->AjaxReturn("","",1,"");
				}
			} else {
				$this->AjaxReturn("","",0,"");
			}
		}
	}

	//插入
	public function insert()
	{
		$name="ResponseTicket";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$model->Responser = $_SESSION['loginUserName'];
		$caseId = $model->CaseId;
		if(empty($model->CaseId))
		{
			$this->error("数据错误!");
		}

		//权限验证
		if(!$this->can_edit_by_caseid($caseId))
		{
			$this->error("权限不够!");
			return;
		}

		//验证该条case是否存在
		$cModel = M("Cases");
		if(empty($cModel))
		{
			$this->error("数据错误!");
		}


		$sql = "update ResponseStrategy set Status=1 where CaseID='$caseId'";

		$list = $cModel->query($sql);
		if($list === false)
		{
			$this->error("数据库错误!");
		}

		$sql = "SELECT id from Cases where CaseId='$caseId'";
		$list = $cModel->query($sql);
		if($list === false)
		{
			$this->error("数据库错误!");
		}
		if(count($list) != 1)
		{
			$this->error("案件不存在!");
		}

		$cid = $list[0]['id'];
		/*	//获取最大num
		 $sql = "select max(Num) as Num from ResponseTicket where CaseId='$caseId'";
		 $list = $model->query($sql);
		 if($list === false)
		 {
			$this->error ( "数据查询错误!" );
			}

			if(count($list) == 0)
			{
			$model->Num = 1;
			}
			else {
			$model->Num = $list[0]['Num']+1;
			}
			*/
		$model->CreateTime = date("Y-m-d H:i:s");
		$model->Creator = $_SESSION[C('USER_AUTH_KEY')];

		//保存当前数据对象
		$list=$model->add();

		//fileWrite($model->getLastSql());
		if ($list===false) { //保存成功
			$this->error ('新增失败!'.$model->getDbError());
		}

		$ticketId = $model->getLastInsID();
		$idAtt = $_REQUEST['newUploadIds'];
		$ids = preg_split('/,/',$idAtt);
		$count = 0;

		$yId = "";
		$j = 0;
		for($i=0;$i<sizeof($ids)-1;$i++)
		{
			if($ids[$i])
			{
				$count++;
				$yId .= $ids[$i].",";
			}
		}
		if($ids[sizeof($ids)-1])
		{
			$count++;
		}

		$caseId = $_REQUEST['CaseId'];

		//无附件
		if($count<=0)
		{
			if(!$caseId)
			{
				$this->success("插入成功!");
			}
			else {
				$this->redirect('/Incident/details/id/'.$cid);
			}

			//$this->success("插入成功!");
		}
		//最后一个附件
		if(sizeof($ids) > 0)
		{
			$yId .= $ids[sizeof($ids)-1];
		}

		$attModel = M("Attachment");
		if(empty($attModel))
		{
			$this->error("插入失败!");
		}
		$condition = array ("id" => array ('in', $yId) );
		$list=$attModel->where ( $condition )->setField ( array('TicketId','Type'), array($ticketId,'1') );

		if ($list!==false) { //保存成功
			if(!$caseId)
			{
				$this->success("插入成功!");
			}
			else {
				$this->redirect('/Incident/details/id/'.$cid);
			}
		} else {
			//失败提示
			$this->error ('新增失败!'.$model->getDbError());
		}
	}

	public function add(){
		if(!isset($_REQUEST['id']))
		{
			$this->error("数据错误!");
		}
		$id = $_REQUEST['id'];
		if(!$id || !$this->can_edit_by_caseid($id))
		{
			$this->error("权限不够!");
		}
		$this->assign("CaseId",$id);
		$tid = $_REQUEST['tid'];
		$docModel = D("ResponseDocument");
		$res = $docModel->getDocByTID($tid);
		//print_r($res);
		$this->assign("list",$res);
		
		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		
		$straList = $model->query("select ResDocID from ResponseStrategy where CaseID='$id';");
		if($straList === false)
		{
			$this->error("数据错误!");
		}
		$i=0;
		$res = array();
		for($i=0;$i<count($straList);$i++)
		{
			$ids = split("#", $straList[$i]['ResDocID']);
			for($j=0;$j<count($ids);$j++)
			{
				if(empty($ids[$j]) || $ids[$j] == "")
				{
					
				}
				else {
					$res[] = $ids[$j];
				}
			}
		}
		$this->assign("straList",$res);
		
		$this->display();
	}

	public function add_tiny(){
		if(!isset($_REQUEST['id']))
		{
			$this->error("数据错误!");
		}
		$id = $_REQUEST['id'];
			
		$this->assign("CaseId",$id);
		$this->display();
	}

	public function edit_tiny()
	{
		$id = $_REQUEST['id'];
		if(empty($id))
		{
			$this->error("数据错误!");
		}

		$model = M("ResponseTicket");
		$vo = $model->where("id=$id")->select();
		if(sizeof($vo) == 0)
		{
			$this->error("该响应不存在!");
		}
		$this->assign("vo",$vo[0]);

		//获取附件列表
		$this->getAttachment($id);
		$this->display();
	}

	public function edit()
	{
		$id = $_REQUEST['id'];
		if(empty($id))
		{
			$this->error("数据错误!");
		}

		$model = M("ResponseTicket");
		$vo = $model->where("id=$id")->select();
		if(sizeof($vo) == 0)
		{
			$this->error("该响应不存在!");
		}
		if(empty($vo[0]['Responser']))
		{
			$vo[0]['Responser']=$_SESSION['loginUserName'];
		}
		$this->assign("vo",$vo[0]);
		if(!$this->can_edit_by_caseid($vo[0]['CaseId']))
		{
			$this->error("权限不够!");
			return;
		}
		//获取附件列表
		$this->getAttachment($id);
		$this->display();
	}

	//获取ResponseTicket的附件
	private function getAttachment($ticketId)
	{
		$model = M("`Attachment`");
		$field = "`Attachment`.*";
		$vo = $model->where("`Attachment`.TicketId=$ticketId  AND `Attachment`.Type=1")->field($field)->select();
			
		$this->assign("attachList",$vo);
	}
	//更新,要处理新添加的附件
	public function update()
	{
		C('TOKEN_ON','0');
		$ticketId = $_REQUEST['id'];
		if(!isset($ticketId))
		{
			$this->error("数据错误!");
		}


		$name="ResponseTicket";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}


		//权限验证
		if(!$this->can_edit_by_caseid($model->CaseId))
		{
			$this->error("权限不够!");
			return;
		}

		$model->Responser = $_SESSION['loginUserName'];
		$model->CreateTime = date("Y-m-d H:i:s");
		$list=$model->save();
		if($list  === false)
		{
			$this->error("数据错误!");
		}
		//处理附件
		$idAtt = $_REQUEST['newUploadIds'];
		$ids = preg_split('/,/',$idAtt);
		$count = 0;

		$yId = "";
		$j = 0;
		for($i=0;$i<sizeof($ids)-1;$i++)
		{
			if($ids[$i])
			{
				$count++;
				$yId .= $ids[$i].",";
			}
		}
		if($ids[sizeof($ids)-1])
		{
			$count++;
		}
		$caseId = $_REQUEST['CaseId'];

		//无附件
		if($count<=0)
		{
			C("TOKEN_ON",true);
			if(!$caseId)
			{
				$this->success("插入成功!");
			}
			else {
				$this->redirect('/Incident/details/cid/'.$caseId);
			}
		}

		//最后一个附件
		if(sizeof($ids) > 0)
		{
			$yId .= $ids[sizeof($ids)-1];
		}

		$attModel = M("Attachment");
		if(empty($attModel))
		{
			C("TOKEN_ON",true);
			$this->error("操作失败!");
		}
		$condition = array ("id" => array ('in', $yId) );
		$list=$attModel->where ( $condition )->setField ( array('TicketId','Type'), array($ticketId,'1') );

		//保存当前数据对象
		if ($list!==false) { //保存成功
			C("TOKEN_ON",true);
			if(!$caseId)
			{
				$this->success("插入成功!");
			}
			else {
				$this->redirect('/Incident/details/cid/'.$caseId);
			}
		} else {
			$this->error ('保存失败!');
		}
	}
}
?>