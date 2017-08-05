<?php
class StrategyAction extends CommonAction {

	//继续观察
	public function keepLook()
	{
		return;

		$model = M("ResponseStrategy");
		$list = $model->where("Status=1")->select();
		print_r($list);
		for($i=0;$i<count($list);$i++)
		{
			$docids = array_values(array_unique(array_diff(split("#",$list[$i]['ResDocID']), array (""))));
			$in = "";

			for($m=0;$m<count($docids)-1;$m++)
			{
				$in .= $docids[$m].",";
			}

			if(count($docids)>0)
			{
				$in .= $docids[$m];
			}
			else {
				$this->error("数据错误,请检查");
			}
			$sql = "select * from ResponseDocument where id in ($in);";

			$res = $model->query($sql);//附件列表
			//echo "<hr>".$model->getLastSql()."<hr>--------";
			if($res === false)
			{
				$this->error("数据错误1!");
			}
			//print_r($res);
			//echo "--------<hr>";
			$attachList = array();
			for($k=0;$k<count($res);$k++)
			{
				$fileName = $res[$k]['FileName'];
				$saveName = $res[$k]['SaveName'];
				$saveName  = iconv("utf-8","GBK",$saveName);
				$filePath = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/'.$saveName;
				$attachList[$k+1]["path"] = $filePath;
				$attachList[$k+1]["name"] = iconv("GBK","utf-8",$saveName);
			}

			$attachList[0]["path"] = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/Report/'.$list[$i]['CaseID'].".xls";
			$attachList[0]["name"] = "案件详情.xls";
			$cid = $list[$i]['CaseID'];
			$sql = "select * from ResponseTicket where CaseId='$cid'";
			$res = $model->query($sql);
			$ticketId = $res[0]['id'];
			if(count($attachList)> 10)
			{
				echo "<hr>";
				print_r($attachList);
				echo "<hr>$cid<hr>";
				break;
			}
			$res = $this->addAttach($ticketId, $attachList, $list[$i]['CaseID']);
			//break;
			unset($attachList);
		}
			


	}

	//拷贝附件
	public function addAttach($ticketId,$attList,$caseId)
	{

		$path =   dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/'.$caseId.'/';
		if(!is_dir($path))
		{
			if(!mk_dir($path,0777))
			{
				$this->error("目录不存在。");
			}
		}
		$model = M("Attachment");
		if(empty($model))
		{
			$this->error("插入失败!");
		}
		for($i=0;$i<count($attList);$i++)
		{
			$pathinfo = pathinfo($attList[$i]['name']);
			$fileName = uniqid().".".$pathinfo['extension'];
			$dst = $path.$fileName;


			if(file_exists($attList[$i]['path']))
			{
				fileWrite($attList[$i]['path']);
			}
			else {
				fileWrite("not exitst");
			}
			if(copy($attList[$i]['path'],$dst) == FALSE)
			{
				$this->error("附件上传失败");
			}
			$data = array();
			$data['TicketId'] = $ticketId;
			$data['Type'] = 1;
			$data['FileName'] = $attList[$i]['name'];
			$data['SaveName'] = $fileName;

			$res = $model->add($data);
			if($res === false)
			{
				$this->error("数据错误");
			}
		}

		return 1;

	}
	function update() {
		$name="ResponseDocument";
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		if (false !== $list) {
			//成功提示
			$this->redirect("Strategy/config");
		} else {
			//错误提示
			$this->error ('编辑失败!');
		}
	}

	function edit() {
		$name="ResponseDocument";
		$model = M ( $name );
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById ( $id );
		$this->assign ( 'vo', $vo );
		$this->display ();
	}

	public function config()
	{

		$this->getClassType();//获取分类

		$sList = $this->getSummary();
		if(!is_array($sList))
		{
			$this->error("字段获取失败!");
		}

		$this->assign("sList",$sList);

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>';
		echo "<script type=\"text/javascript\">var summary_json = ".json_encode($sList)."</script>";
		$model = M("ResponseDocument");
		if(empty($model))
		{
			$this->error("db error");
		}

		$list = $model->select();
		if($list === false)
		{
			$this->error("db error");
		}
		$this->assign("list",$list);

		$this->display();
	}

	public function getTpl($cid)
	{
		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("数据错误！");
		}
		$map['CaseID'] = $cid;

		$count = $model->where( $map )->count ( '*' );
		if ($count > 0) {
			$voList = $model->where($map)->findAll ( );
			$this->assign ( 'list', $voList );
		}
		$this->assign("total",$count);
	}

	//模板显示
	public function stratpl()
	{
		$cid = $_REQUEST['cid'];

		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("数据错误！");
		}
		$map['CaseID'] = $cid;
		$this->_list($model, $map);

		$this->display();
	}
	//发送页面
	public function report()
	{
		$id = $_REQUEST['sid'];
		$docid= urldecode($_REQUEST['docid']);
		$cid = $_REQUEST['cid'];
		$sendFlag = $_REQUEST['sendFlag'];
		if(isset($sendFlag) && $sendFlag)
		{
			$sendFlag = 1;
		}
		else {
			$sendFlag = 0;
		}

		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("data error");
		}
		$stra = $model->where("id=$id")->select();
		$status = $_REQUEST['status'];
		if(isset($status) && $status == 1)
		{
		}
		else
		{

			$this->getReportDetail($model,$stra[0]['TID'],$cid);
		}
		$paras = array("sid"=>$id,"docid"=>urlencode($docid),"cid"=>$cid,"sendFlag"=>$sendFlag);
		$this->redirect("index2", $paras);
	}

	public function index2()
	{

		$id = $_REQUEST['sid'];
		$docids= $_REQUEST['docid'];
		$cid = $_REQUEST['cid'];
		$sendFlag = $_REQUEST['sendFlag'];

		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("数据错误1!");
		}
		$stra = $model->where("id=$id")->select();
		if($stra === false)
		{
			fileWrite($model->getLastSql());
			$this->error("数据错误2!");
		}
		$unit = $stra[0]['Unit'];
		$stra[0]['Email'] = $this->getEmailByUnit($unit);
		//$stra[0]['Email'] = "753007395@qq.com";

		$this->assign("docids",$docids);

		$docids = array_values(array_unique(array_diff(split("#",urldecode($docids)), array (""))));
		$in = "";

		for($i=0;$i<count($docids)-1;$i++)
		{
			$in .= $docids[$i].",";
		}

		if(count($docids)>0)
		{
			$in .= $docids[$i];
		}
		else {
			$this->error("数据错误,请检查");
		}
		$sql = "select * from ResponseDocument where id in ($in);";

		$res = $model->query($sql);
		if($res === false)
		{
			$this->error("数据错误1!");
		}
		if(!$sendFlag)
		{
			$this->assign("resdoc",$res);
			$this->assign("case",$stra[0]);
			$this->display();
		}
		else {
			$this->error("数据错误!");
		}

	}

	private function getReportDetail($model,$tid,$caseID)
	{
		if(!isset($tid) || empty($model))
		{
			return -1;
		}
		$map['TID'] = $tid;
		$sql = "select * from CaseSummery where CaseID='$caseID'";
		$res = $model->query($sql);
		if($res === false)
		{
			return -1;
		}
		$nameArray = array("IP","端口","事件类型","总流量(MB)","出现次数","首次检测时间","最近出现时间");
		$filed = array("IP","port","TID_name","TotalFlow","OccurNum","FirstTime","LastTime");
		$fileName = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/Report/'.$caseID.".xls";
		@unlink($fileName);
		for($i=0;$i<count($res);$i++)
		{
			$res[$i]['port'] = $this->showPort($res[$i]['DstPort']);
			$res[$i]['TID_name'] = get_threat_type_name($res[$i]['TID']);
			$res[$i]['IP'] = long2ip($res[$i]['IP']);
		}
		$res = $this->PHPExcelExport($nameArray, $filed, $res, $fileName);
		return $fileName;
	}

	public function PHPExcelExport($nameArray,$filed,$res,$name)
	{
		if(count($nameArray)<1 || count($res) <1)
		{
			return -1;
		}
		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Public/PHPExcel/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getProperties()->setCreator("CHAIRS")
		->setLastModifiedBy("CHAIRS")
		->setTitle("CHAIRS")
		->setSubject("CHAIRS")
		->setDescription("CHAIRS")
		->setKeywords("CHAIRS")
		->setCategory("CHAIRS");

		// Add some data
		$curSheet = $objPHPExcel->getActiveSheet();
		for($i=0;$i<count($nameArray);$i++)
		{
			$cname=$this->getColumnName($i);
			$curSheet->getStyle($cname)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$cname .="1";
			$curSheet->setCellValue($cname, $nameArray[$i]);
		}

		$xls_row = 2;
		for($i=0;$i<sizeof($res);$i++)
		{
			$row = $res[$i];

			for($j=0;$j<count($filed);$j++)
			{
				$curSheet->setCellValueByColumnAndRow($j, $xls_row, $row[$filed[$j]]);
			}
			$xls_row++;
		}
		$objPHPExcel->getActiveSheet()->setTitle();
		//Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save($name);
		return 1;
	}

	private function showPort($port)
	{
		if(!isset($port))
		{
			return "*";
		}
		else
		{
			return $port;
		}
	}
	private function getEmailByUnit($unit)
	{
		$model = M("UnitUsersRelation");
		if(empty($model))
		{
			$this->error("数据错误2!");
		}
		$res = $model->where("UnitName='".$unit."'")->select();
		if($res === false)
		{
			$this->error("数据错误3!");
		}

		if(count($res)  == 0)
		{
			fileWrite($model->getLastSql());
			$this->error("邮箱未设置，请与管理员联系");
		}
		return $res[0]['Email'];
	}
	//首页，显示所有待响应事件
	public function index(){

		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("数据错误！");
		}
		$map = array();
		$map['Status'] = 0;
		$this->_list($model, $map);
		//echo $model->getLastSql();
		$this->display();
	}

	protected function _list($model, $map, $sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : "id";
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		$count = $model->where ( $map )->count ( '*' );
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
			$voList = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll();
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

	//ajax
	public function getSummary()
	{
		$new_list = array();
		if(isset($_SESSION["CaseSummary"]))
		{
			$new_list = $_SESSION['CaseSummary'];
		}
		else {
			$model = M("CaseSummary");
			$sql = "show full columns from CaseSummery";
			$list = $model->query($sql);
			if($list === false)
			{
				return -1;
			}

			for($i=0;$i<count($list);$i++)
			{
				$field = $list[$i]['Field'];
				if($field == "id" || $field == "TID" || $field == "TcID" || $field == "CaseID")
				{
				}
				else {
					$num = count($new_list);
					$new_list[$num] = array();
					$new_list[$num]['Field'] = $list[$i]['Field'];
					$new_list[$num]['Type'] = $list[$i]['Type'];
					$new_list[$num]['Comment'] = $list[$i]['Comment'];
				}
			}
			unset($list);
			$_SESSION['CaseSummary'] = $new_list;
		}
		$i = 0;
		return $new_list;
	}
	//获取案件分类表
	private function getClassType()
	{
		$model = M("ThreatClassType");
		$list = $model->select();
		$this->assign("threatClassType",$list);
	}

	public function delete() {
		//删除指定记录
		$name="ResponseDocument";
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				$list=$model->where ( $condition )->select();
				if($list !== false)
				{
					for($i=0;$i<count($list);$i++)
					{
						$file = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/'.$list[$i]['SaveName'];
						$res = @unlink($file);
						//fileWrite($res."_-".$file);
					}
				}
				$list=$model->where ( $condition )->delete();
				if ($list!==false) {
					$this->success ('删除成功！' );
				} else {
					$this->error ('删除失败！');
				}
			} else {
				$this->error ( '非法操作' );
			}
		}
	}

	function direcUpload($uploadId="file",$pathAdd="Strategy")
	{
		$flag = true;
		$name = rand_string(6);
		//case 路径
		if(strlen($pathAdd) > 0)
		{
			$directory = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/'.$pathAdd.'/';
		}
		else {
			$directory = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/';
		}
		//fileWrite($directory);
		if(($_FILES[$uploadId]["type"]) && $_FILES[$uploadId]["size"] < 2000000)
		{

			if ($_FILES[$uploadId]["error"] > 0)
			{
				//fileWrite("Return Code: " . $_FILES["file"]["error"]);
				return "";
			}
			else
			{
				//检查文价夹是否存在
				$fileHead = $directory;
				if(!is_dir($fileHead))
				{
					if(mkdir($fileHead,0777))
					{
						//fileWrite("创建成功");
					}
					else {
						return "";
					}
				}
				//检查文件夹是否存在


				//fileWrite($_FILES["file"]["name"]);
				$fileName = $fileHead ."/". $name."-".$_FILES["file"]["name"] ;
				$array = array();
				$array['SaveName'] = $name."-".$_FILES["file"]["name"];
				$array['FileName'] = $_FILES["file"]["name"];
				if (file_exists($fileName))
				{
					return "";
				}
				$fileName = iconv("utf-8","GBK",$fileName);
				if(move_uploaded_file($_FILES[$uploadId]["tmp_name"],$fileName))
				{
					return $array;
				}
				else
				{
					return "";
				}
			}
		}
		else {
			return "";
		}
	}

	public function sendReportInsert($data)
	{
		$name="Ticket";
		$model = D ($name);

		$name="ResponseTicket";
		$resModel = D ($name);
		$resData = array();
		if (empty($model) || empty($resModel)) {
			$this->error ( $model->getError () );
		}

		$data['Creator'] = $_SESSION[C('USER_AUTH_KEY')];
		$data['CreateTime'] = date("Y-m-d H:i:s");

		//保存当前数据对象
		$list=$model->add($data);
		if($list === false)
		{
			$this->error("send error");
		}

		$ticketId = $model->getLastInsID();
		$attachList = array();
		$attachList1 = urldecode($data['attach1']);


		for($i=0;$i<count($data['FileName']);$i++)
		{
			$fileName = urldecode($data['FileName'][$i]);
			$saveName = urldecode($data['SaveName'][$i]);
			$saveName  = iconv("utf-8","GBK",$saveName);
			$filePath = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/'.$saveName;
			$attachList[$i+1]["path"] = $filePath;
			$attachList[$i+1]["name"] = iconv("GBK","utf-8",$saveName);
		}

		$attachList[0]["path"] = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/Report/'.$attachList1;
		$attachList[0]["name"] = "案件详情.xls";
		//fileWriteArray($attachList);
		//	$model->ReceiverAddress = "753007395@qq.com";
		$res = $this->sendMail($data['ReceiverAddress'], $data['Subject'], $data['Content'],$data['Cc'], $attachList);
		if($res ==1)
		{
			$sid = $data['sid'];
			$sql = "update ResponseStrategy set Status=1 where id=$sid";
			$res = $model->query($sql);

		}
		else {
			$this->error("send error");
		}

		//response ticket 数据
		$resData['ResponseTime'] = date("Y-m-d H:i:s");
		$resData['CreateTime'] = date("Y-m-d H:i:s");
		$resData['Creator'] = $_SESSION[C('USER_AUTH_KEY')];
		$resData['Responser'] = $_SESSION['loginUserName'];
		$resData['Type']=0;
		$resData['CaseId']=$data['CaseId'];
		$resData['ResponseContent']="向".$data['ReceiverAddress']."发送安全通告";

		$res = $resModel->add($resData);
		//fileWrite($resModel->getLastSql());
		$ticketId = $resModel->getLastInsID();
		
		if($res !==false)
		{
			$res = $this->addAttach($ticketId, $attachList, $resData['CaseId']);
			$this->redirect("/Strategy/index");
			//$this->success("发送成功!");
		}
		else {
			$this->error("发送失败!");
			fileWrite("ticket_error!");
		}

	}
	//发送邮件 并记录ticket
	public function StraInsert()
	{

		$data = array();
		$data['CaseId'] = $_REQUEST['CaseId'];
		$data['cid'] = $_REQUEST['cid'];
		//fileWriteArray($_REQUEST);

		$data['docid'] = $_REQUEST['id'];
		$data['FileName'] = $_REQUEST['FileName'];
		$data['SaveName'] = $_REQUEST['SaveName'];
		$data['attach1'] = $_REQUEST['attach1'];
		$data['Type'] = $_REQUEST['Type'];
		$data['sid'] = $_REQUEST['sid'];
		$data['Subject'] = $_REQUEST['Subject'];
		$data['ReceiverAddress'] = $_REQUEST['ReceiverAddress'];
		$data['Content'] = $_REQUEST['Content'];
		$data['Cc'] = $_REQUEST['Cc'];
		//fileWriteArray($data);
		$this->sendReportInsert($data);
	}

	public function insertStra()
	{
		$model = M("ResponseStrategy");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$CaseId = $_REQUEST['CaseId'];
		$docId = $_REQUEST['id'];
		
		$sql = "select * from Cases where CaseId='$CaseId'";
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		
		
		$res  = $model->where("CaseID='$CaseId'")->select();
		if($res === false )
		{
			$this->error("数据错误!");
		}
		else if(count($res) == 0)//不存在添加
		{
			$data['CaseID'] = $CaseId;
			$data['Unit'] = $list[0]['Unit'];
			$data['TcID'] = $list[0]['TcID'];
			$data['TID'] = $list[0]['TID'];
			$data['ResDocID'] = "#".$docId."#";
			$data['Status'] = 0;
			$data['LastTime'] = date("Y-m-d H:i:s");
			$res = $model->add($data);
			if($res === false)
			{
				$this->error("添加失败!");
			}
			$resPonseData['CaseId']=$CaseId;
			$resPonseData['Type'] = 0;
			$resPonseData['Reporter'] = $_SESSION['loginUserName'];
			$resPonseData['ReportContent'] = "生成响应报告";
			$resPonseData['Creator'] = $_SESSION[C('USER_AUTH_KEY')];
			$resPonseData['CreateTime']= date("Y-m-d H:i:s");
			$resPonseData['ResponseTime']= date("Y-m-d H:i:s");
			unset($model);
			$model=M("ResponseTicket");
			$res = $model->add($resPonseData);
			if($res === false)
			{
				$this->error("添加失败!");
			}
		}
		else {//存在更新
			$docids = $res[0]['ResDocID'].$docId."#";
			$id = $res[0]['id'];
			$time = date("Y-m-d H:i:s");
			$sql = "update ResponseStrategy set ResDocID='$docids',LastTime='$time',Status=0 where id = $id;";
			$res = $model->query($sql);
			if($res === false)
			{
				$this->error("添加失败!");
			}
		}
		$this->redirect("/Incident/details",array("cid"=>$CaseId));
	}

	public function insert(){

		$array = $this->direcUpload();
		if(!is_array($array))
		{
			$this->error("插入失败！");
		}

		$name="ResponseDocument";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$model->SaveName = $array['SaveName'];
		$model->FileName = $array['FileName'];
		$list=$model->add();
		if ($list===false) { //保存成功
			$this->error ('新增失败!'.$model->getDbError());
		}
		else
		{
			$this->success("新增成功!");
		}
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


	/*
	 * ajax 方式返回
	 * */
	public function tidChange()
	{
		if(empty($_REQUEST['id']))
		{
			$this->AjaxReturn("","",0,"");
			return;
		}
		$id = $_REQUEST['id'];
		$model = M("ResponseDocument");
		if(empty($model))
		{
			$this->AjaxReturn("","",0,"");
		}

		$list = $model->select();
		if($list === false)
		{
			$this->AjaxReturn("","",0,"");
		}
		$html = '<table class="ticket-list" width="100%" style="font-size: 0.7em;"><tbody><tr class="collection-as-table">
			<th class="collection-as-table">类型</th><th class="collection-as-table">过滤条件</th>		
			<th class="collection-as-table">描述</th><th class="collection-as-table">响应文档</th>
			<th class="collection-as-table">操作</th>';	

		for($i=0;$i<count($list);$i++)
		{
			if($i%2 == 0)
			{
				$html .= "<tr class='oddline'>";
			}
			else
			{
				$html .= "<tr class='evenline'>";
			}
			$html .= '<td class="collection-as-table-break">'.get_threat_class_type_name($list[$i]['ThreatClass'])."-".get_threat_type_name($list[$i]['ThreatType']).'</td>';
			$html .= '<td class="collection-as-table-break">'.$list[$i]['Condition'].'</td>';
			$html .= '<td class="collection-as-table-break">'.$list[$i]['Feature'].'</td>';
			$html .= '<td class="collection-as-table">'.$list[$i]['FileName'].'</td>';
			$html .= '<td class="collection-as-table"><a href="__URL__/delete/id/'.$list[$i]['id'].'">删除</a></td>';

			$html .= "</tr>";

		}

		$html .= '</tbody></table>';

		$this->AjaxReturn($html,"",1,"");
	}

	public function sendMail($sendto_email,$subject,$body,$Cc,$attachList)
	{
		error_reporting(E_STRICT);
		$body = $body."<br/><br/><br/>本邮件由系统自动生成，请勿回复<br/>";
		$body = str_replace("\r\n", "<br/>",$body );
		date_default_timezone_set("Asia/Shanghai");

		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Public/PHPMailer/classphpmailer.php';
		$mail = new PHPMailer(); //新建一个邮件发送类对象
		$mail->IsSMTP();                // send via SMTP
		$host = "smtp.163.com";
		$mail_from ="njcert@163.com";
		$mail_from_pwd = "$#@!dim";
		//
		$mail->Host =$host; // SMTP 邮件服务器地址，这里需要替换为发送邮件的邮箱所在的邮件服务器地址
		$mail->SMTPAuth = true;         // turn on SMTP authentication 邮件服务器验证开
		$mail->Username = $mail_from;   // SMTP服务器上此邮箱的用户名，有的只需要@前面的部分，有的需要全名。请替换为正确的邮箱用户名
		$mail->Password = $mail_from_pwd;        // SMTP服务器上该邮箱的密码，请替换为正确的密码
		$mail->From = $mail_from;    // SMTP服务器上发送此邮件的邮箱，请替换为正确的邮箱，$mail->Username 的值是对应的。
		$mail->FromName =  "njcert";  // 真实发件人的姓名等信息，这里根据需要填写
		$mail->CharSet = "utf-8";            // 这里指定字符集！
		$mail->Encoding = "base64";
		$mail->AddAddress($sendto_email,$sendto_name);  // 收件人邮箱和姓名
		$mail->AddReplyTo("dim@njcert.edu.cn","dim");
		if(strlen($Cc)>0)
		{
			$mail->AddAddress($Cc);   //添加Cc
		}

		//$mail->WordWrap = 50; // set word wrap
		for($i=0;$i<count($attachList);$i++)
		{

			$file = $attachList[$i]['path'];
			if(file_exists($file))
			{
				$mail->AddAttachment($file,$attachList[$i]['name']); // 附件处理
			}
			else {
				fileWrite("attach error");
			}

		}
		//$mail->AddAttachment("test.txt"); // 附件处理
		//$mail->AddAttachment(“/tmp/image.jpg”, “new.jpg”);
		//$mail->IsHTML(true);  // send as HTML
		// 邮件主题
		$mail->Subject = $subject;

		// 邮件内容
		$mail->Body ="<html><head><meta http-equiv=”Content-Language” content=”zh-cn”>  <meta http-equiv=”Content-Type” content=”text/html; charset=utf-8″></head><body>$body</body></html>";
		$mail->AltBody ="text/html";
		if(!$mail->Send())
		{
			fileWrite("邮件错误信息: ".$mail->ErrorInfo);
			return 0;
		}
		else {
			return 1;
		}
		//$this->display();
	}

	//匿名到njcert邮箱，实际未使用
	public function sendMail1($sendto_email,$subject,$my_html_message,$Cc,$attachList)
	{
		//$sendto_email = "753007395@qq.com";
		$my_html_message = $my_html_message."<br/><br/><br/>本邮件由系统自动生成，请勿回复<br/>";
		require_once("Mail.php");
		require_once("Mail/mime.php");
		//$subject=iconv("utf-8","gb2312",$subject);
		$recipients  = "<$sendto_email>";

		$headers["From"] = 'njcert@163.com';
		$headers["To"] = "<$sendto_email>";
		$headers["Subject"] = "=?UTF-8?B?".base64_encode($subject)."?=";
		$headers["Content-Type"] = 'text/html;charset="utf-8"'.'/r/n';
		$headers["Content-Language"]="zh-cn";
		$headers["Content-Transfer-Encoding"]="base64";
		$headers["Date"] = date("D, d M Y H:i:s O");
		$crlf = "\n";
		$mime = new Mail_mime(array('eol' => $crlf));
		$my_html_message = str_replace("\r\n", "<br/>",$my_html_message );

		$mime->setHTMLBody($my_html_message);


		for($i=0;$i<count($attachList);$i++)
		{
			$file = $attachList[$i]['path'];
			if(file_exists($file))
			{
				$mime->addAttachment($file,"text-plain",$attachList[$i]['name'],true,'base64','attachment','utf-8');
				//break;
			}
			else {
				fileWrite("sss");
			}


		}
		//fileWriteArray($mime);
		$mime_param["html_charset"] = 'utf-8';
		$message = $mime->get($mime_param);
		$headers = $mime->headers($headers);
		$params["debug"] = true; /* 调试模式 */
		$params["host"] = 'smtp.163.com';
		$params["auth"] = true; /* 默认不用认证 */
		$params["localhost"] = 'demo';
		$params['port'] = 25;
		$params['from'] = "njcert@163.com";
		$params['password'] = "$#@!dim";
		$mail_message =& Mail::factory('smtp', $params);

		$res = $mail_message->send($recipients, $headers, $message);
		if(PEAR::isError($mail_res)){
			die($res->getMessage());
		}
		return 1;
	}

	public function download()
	{
		$fileName = $_REQUEST['dname'];
		$realName = $_REQUEST['rname'];
		$path = $_REQUEST['path'];

		if(strlen($fileName) <= 0)
		{
			$this->error("数据错误!");
		}

		Header("Content-type:application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Content-Disposition:attachment;charset=utf-8;filename=".$realName);

		//$fileName = iconv("GBK","utf-8",$fileName);
		if(isset($path) && $path != "")
		{
			$path = $path ."/";
		}
		else {
			$fileName  = iconv("utf-8","GBK",$fileName);
		}


		$filePath = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Strategy/'.$path.$fileName;

		//fileWrite($filePath);
		$ua = $_SERVER["HTTP_USER_AGENT"];
		header('Content-Type: application/octet-stream');
		$encoded_filename = urlencode($realName);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		if (preg_match("/MSIE/", $ua)) {
			header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8\'\'' . $realName. '"');
		} else {
			header('Content-Disposition: attachment; filename="' . $realName . '"');
		}

		//	fileWrite($filePath);
		if(file_exists($filePath) )//判断文件是否存在并打开
		{
			$file=fopen($filePath,"r");
			if($file)
			{
				//	fileWrite("\r\ntestsss");
				echo fread($file,filesize($filePath));
				fclose($file);
			}
			else {
				fileWrite("can not read file!");
			}

		}
		else
		{
			fileWrite("file not exists".$filePath);
		}

	}
}
?>