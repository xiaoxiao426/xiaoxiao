<?php
class IncidentAction extends CommonAction {
	public  function download()
	{
		$fileName = $_REQUEST['dname'];
		$realName = $_REQUEST['rname'];
		$caseId = $_REQUEST['CaseId'];

		$filePath = dirname(dirname(dirname(dirname(__FILE__))))."/Public/Uploads/".$caseId."/".$fileName;
		fileWrite("$filePath");
		Header("Content-type:application/octet-stream");
		Header("Accept-Ranges: bytes");
		//	$realName = @iconv("UTF-8","GBK",$realName);
		//	$realName = urldecode($realName);
		Header("Content-Disposition:attachment;charset=utf-8;filename=".$fileName);
		//$filePath = iconv("UTF-8","GBK",$filePath);//包含中文字符时需要转换
		//$filePath = iconv("UTF-8","GBK",$filePath);

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
			fileWrite("$filePath not exists");
		}
	}
	//处理事件
	public function dealTheIncident()
	{
		$cid = $_REQUEST['cid'];
		$uid = $_SESSION[C('USER_AUTH_KEY')];
		if(!$cid || !$uid)
		{
			$this->ajaxReturn("","",0,"");
		}
		$model = M("Cases");
		if(empty($model))
		{
			$this->ajaxReturn("","",0,"");
		}

		$sql = "UPDATE Cases set CaseResponsor=$uid,ProgressState='open' where id=$cid";
		$res = $model->query($sql);
		if($res === false)
		{
			$this->ajaxReturn(0);
		}
		$this->ajaxReturn($_SESSION['loginUserName'],get_case_statue('open'),1,"");
	}

	//取消处理事件
	public function unDealTheIncident()
	{
		$cid = $_REQUEST['cid'];
		if(!$cid )
		{
			$this->ajaxReturn("","",0,"");
		}
		$model = M("Cases");
		if(empty($model))
		{
			$this->ajaxReturn("","",0,"");
		}

		$sql = "UPDATE Cases set CaseResponsor=NULL,ProgressState='new' where id=$cid";
		$res = $model->query($sql);
		//	fileWrite($model->getLastSql());
		if($res === false)
		{
			$this->ajaxReturn("","",0,"");
		}
		//fileWrite(get_case_statue('new'));
		$this->ajaxReturn("",get_case_statue('new'),1,"");
	}


	//获取用户
	public function getUser()
	{
		$model = M("Users");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$sql = "select * from Users where power<=1;";//查找普通用户
		$list = $model->query($sql);
		if($list === false)
		{
			$this->error("数据错误!");
		}
		$this->assign("user",$list);
	}
	//获取档位名称
	public function getUnit()
	{
		$model = M("Unit");
		$list = $model->select();
		$this->assign("UnitList",$list);
	}
	// 框架首页
	public function index() {
		$this->getClassType();
		$this->getUnit();
		$this->getUser();
		//print_r($_REQUEST);
		if(isset($_REQUEST['CaseType']) && $_REQUEST['CaseType'] != 0)
		{
			$name = "Cases";
		}
		else {
			$name = "CasesShadow";
		}

		$map = Array();
		$map = $this->_search ($name);
		if(isset($map['CaseResponsor']) && $map['CaseResponsor'])
		{
			if($map['CaseResponsor'] == -1)
			{
				unset($map['CaseResponsor']);
			}
		}
		else
		{
			$map['CaseResponsor'] = $_SESSION[C('USER_AUTH_KEY')];
		}
		$this->assign("curResponser",$map['CaseResponsor']);
		//单位关注IP
		if(isset($map['ConcernFlag']) && $map['ConcernFlag'])
		{
			$this->assign("ConcernFlag",$map['ConcernFlag']);
		}
		else
		{
			unset($map['ConcernFlag']);
		}

		$this->assign("ProgressState",$map['ProgressState']);

		if(isset($map['StartTime']))
		{
			$this->assign("StartTime",$map['StartTime']);
			$map['_string'] = " StartTime >'".$map['StartTime']."'";
		}

		if(isset($map['LastTime']))
		{
			$this->assign("LastTime",$map['LastTime']);
			if(isset($map['_string']))
			{
				$map['_string'] = " (StartTime >'".$map['StartTime']."' and LastTime < '".$map['LastTime']."')";
			}
			else {
				$map['_string'] = " LastTime < '".$map['LastTime']."'";
			}
		}
		unset($map['StartTime']);
		unset($map['LastTime']);
		//主题
		if(isset($map['Subject']))
		{
			$this->assign("Subject",$map['Subject']);
			$map['Subject'] = array('like',"%".$map['Subject']."%");
		}

		//现象
		if(isset($map['Phenomenon']))
		{
			$this->assign("Phenomenon",$map['Phenomenon']);
			$map['Phenomenon'] = array('like',"%".$map['Phenomenon']."%");
		}
		//初始访问为当前用户
		//		if(!isset($_REQUEST['CaseResponsor']) && !isset($_REQUEST['p']))
		//		{
		//		//	$map['CaseResponsor'] = $_SESSION[C('USER_AUTH_KEY')];
		//		}
		//		$this->assign("curResponser",$map['CaseResponsor']);
		if(isset($map['DeleteFlag']))
		{
			$this->assign("DelFlag",$map['DeleteFlag']);
		}
		else {
			$map['DeleteFlag'] = 0;
		}
		if(isset($map['Unit']))
		{
			$this->assign("UnitName",$map['Unit']);
		}
		//分类
		if($map['TcID'])
		{
			$this->assign("curTCID",$map['TcID']);
			$this->getType($map['TcID']);
			$map["$name.TcID"] = $map['TcID'];
			unset($map['TcID']);
		}
		else {
			$this->assign("curTCID",-1);
		}
		//类型
		if($map['TID'])
		{
			$this->assign("curTID",$map['TID']);
			$map["$name.TID"] = $map['TID'];
			unset($map['TID']);
		}
		else {
			$this->assign("curTID",-1);
		}

		//类型,背景库
		if(!isset($_REQUEST['CaseType']) && !isset($_REQUEST['p']))
		{
			$map['CaseType'] = 0;
		}
			
		if(isset($map['CaseType']))
		{
			$this->assign("CaseType",$map['CaseType']);
			$map["$name.CaseType"] = $map['CaseType'];
			unset($map['CaseType']);
		}
		else {

			$this->assign("CaseType",-1);
		}

		$model = M($name);

		if(!isset($_SESSION['loginUserName']))
		{
			$this->error("请重新登录!");
		}
		//print_r($map);
		if( !empty ( $model ))
		{
			$this->_list ( $model, $map ,$name);
		}
		//echo $model->getLastSql();

		$this->display();
	}

	protected function _list($model, $map, $name,$sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] ) && $_REQUEST ['_order'] !='') {
			$order = $_REQUEST ['_order'];
		} else {
			$order = 'StartTime';
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] ) ) {
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
			$filed = "$name.*";
			$voList = $model->where($map)->field($filed)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select( );
			//echo $model->getlastsql();
			//分页跳转的时候保证查询条件
			foreach ( $map as $key => $val ) {
				if (! is_array ( $val )) {
					$key=str_replace("$name.", "", $key);
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
				else {
					$val = str_replace("%", "", $val[1]);
					$p->parameter .= "$key=" . urlencode ( $val ) . "&";
				}
			}
			$p->parameter .= "_order=" . urlencode ( $order ) . "&";
			if($sort == "desc")
			{
				$p->parameter .= "_sort=0&";
			}
			else
			{
				$p->parameter .= "_sort=1&";
			}
			if(!isset($map['CaseResponsor']))
			{
				$p->parameter .= "CaseResponsor=-1&";
			}

			//print_r($map);
			//分页显示
			$page = $p->show ();
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式

			//检查是否存在Strategy
			for($k=0;$k<count($voList);$k++)
			{
				//$sql = "select count(*) from ResponseStrategy where CaseID='".$voList[$k]['CaseId']."'";
				$CaseID = $voList[$k]['CaseId'];
				$voList[$k]['StrageFlag'] = 0;
				if(strpos($CaseID, "NJo") !== false)
				{
					$voList[$k]['StrageFlag'] = 1;
				}
			}
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

	//删除，无页面
	public function delete() {
		//删除指定记录
		$name="Cases";
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = "id";
			$id = $_REQUEST [$pk];
			if(!$this->can_edit_by_id($id))
			{
				$this->error("权限不够!");
				return;
			}
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				$list=$model->where ( $condition )->setField ( 'DeleteFlag', 1 );
				if ($list!==false) {
					$this->assign('jumpUrl','__URL__/index');
					$this->success ('删除成功！' );
				} else {
					$this->error ('删除失败！');
				}
			} else {
				$this->error ( '非法操作' );
			}
		}
	}

	//误报
	function errorReport()
	{
		$id = $_REQUEST['id'];
		$model=M("Evidence");
		if(empty($model))
		{
			$this->AjaxReturn("","",0,"");
		}

		$array = explode ( ',', $id );
		for($i=0;$i<count($array);$i++)
		{
			if(empty($array[$i]))
			{
				unset($array[$i]);
			}
		}
		$condition = array ("EvidenceID" => array ('in',$array ) );
		//直接删除证据,暂时不管其他附表:证据表删除，附表内容不会显示
		$res = $model->where($condition)->delete();
		if($res === false)
		{
			$this->AjaxReturn("","",0,"");
		}
		else {
			$this->AjaxReturn("","",1,"");
		}

		/*	查询证据表对应的EvidenceType,根据EvidenceTyp来删除附表内容,但是如果有数据
		 *   恢复要求的话，不如不删
		 * $field="EvidenceID,EvidenceType";
		 $field="EvidenceID,EvidenceType";
		 $list = $model->field($field)->where($condition)->select();
		 if($res==false)
		 {
			$this->AjaxReturn("","",0,"");
		 }
			for($i=0;$i<sizeof($list);$i++)
			{

			}
			*/
		//$this->AjaxReturn("","",1,"");
	}

	//非误报
	function notErrorReport()
	{
		$id = $_REQUEST['id'];
		fileWrite("非误报".$id);
		$this->AjaxReturn("","",1,"");
	}

	private function getCart()
	{
		import ( "@.ORG.Cart" );
		$sName = $_SESSION[C('USER_AUTH_KEY')]."_case";
		$cart = new Cart($sName);
		if($cart)
		{
			return $cart;
		}
		else
		{
			return -1;
		}
	}

	//输入cart
	public function ajaxAddCart()
	{

		$str = $_REQUEST['str'];
		if(strlen($str) <= 0)
		{
			$this->AjaxReturn("","",0,"");
		}

		$array = split(",",$str);
		$cart = $this->getCart();
		//$cart->emptycart();
		$i = 0;
		for($i=0;$i<count($array);$i+=2)
		{
			if($array[$i])
			$cart->updateCart($array[$i],$array[$i+1]);
		}

		$this->AjaxReturn("","",1,"");
	}

	//购物车删除
	public function ajaxDelCart()
	{
		$id = $_REQUEST['id'];
		if(strlen($id) <= 0)
		{
			$this->AjaxReturn("","",0,"");
		}


		$cart = $this->getCart();
		$cart->delcart($id);

		$this->AjaxReturn("","",1,"");
	}
	//获取cart内容
	public function ajaxGetCart()
	{
		$cart = $this->getCart();

		$cart_list = $cart->getCart();

		if($cart_list == "")
		{
			$this->AjaxReturn("","",1,"");
		}
		$i = 0;
		$str = "";
		for($i=0;$i<count($cart_list);$i++)
		{
			$str .= '<p><a href="../Incident/details/cid/'.$cart_list[$i][0].'">'.$cart_list[$i][1].'</a>
			   <img src="../../../Public/Images/b_drop.png" onclick="deleteCart(\''.$cart_list[$i][0].'\');"></img></p>';
		}

		$str = iconv("gbk","utf-8",$str);

		$this->ajaxReturn($str,"",1,"JSON");
	}

	//事件合并
	function allCaseMerge()
	{
		set_time_limit(0);
		$id = $_REQUEST['id'];
		$cart = $this->getCart();
		$id = $cart->getCartIds();

		$idArray = explode ( ',', $id );
		if(empty($idArray) || count($idArray) < 2)
		{
			$this->AjaxReturn("","",0,"");
		}
		$model = D("Cases");
		if(empty($model))
		{
			return 0;
		}

		$condition = array ("CaseID" => array ('in', explode ( ',', $id ) ) );
		$caseInfo = $model->where($condition)->select();

		$i = 0;
		$count = 0;
		$k = 0;
		//统计合成事件个数
		for($i=0;$i<count($caseInfo);$i++)
		{
			if($caseInfo[$i]['MergeType'] == 1)
			{
				$k = $i;
				$count++;
			}
		}
		$begin = 0;
		if($count > 1)//合并事件数大于1，不能合并
		{
			$this->AjaxReturn("","",0,"");
		}
		else if($count == 1)//合并到该条
		{
			$dstInfo = $caseInfo[$k];
			unset($caseInfo[$k]);
			$begin = 0;
		}
		else {//添加
			$dstInfo = $model->addMergeCaseInfo($caseInfo[0]);
			$begin = 1;
		}

		for($i=$begin;$i<count($caseInfo);$i++)
		{

			if($caseInfo[$i]['CaseId'])
			{
				$srcInfo = $caseInfo[$i];
				$flag = $model->myMergeCasesInfo($dstInfo,$srcInfo);

			}
			else {

			}
		}
		$cart->emptycart();//清空记录
		$this->AjaxReturn("","",1,"");
	}

	//插入
	public function insert(){
		//案件
		$caseModel= D("Cases");
		if (false === $caseModel->create ()) {
			$this->error ( $caseModel->getError () );
		}

		$subject = $caseModel->Subject;
		$caseModel->MergeType = 0;
		$caseModel->caseType = 2;
		//写文件
		$str = "";
		$caseModel->ThreatSrcSummary = str_replace("\r\n", "<br />", $caseModel->ThreatSrcSummary);
		$caseModel->ThreatDstSummary = str_replace("\n", "<br />", $caseModel->ThreatDstSummary);


		$res = $caseModel->add();
		if($res === false)
		{
			fileWrite($caseModel->getLastSql());
			$this->error("插入失败1!");
		}
		$cid = $caseModel->getLastInsID();
		$num = sprintf("%06d",$cid%1000000);
		$caseID = "NJR-".date("Y").$num;
		fileWrite($num);
		fileWrite($caseID);
		$sql = "update Cases set CaseId='$caseID' where id=$cid;";
		$res = $caseModel->query($sql);

		$savePath =   dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/'.$caseID.'/';
		if(!is_dir($savePath))
		{
			if(!mk_dir($savePath,0777))
			{
				$this->error("目录不存在!");
			}
		}

		$rModel = M("ResponseTicket");
		$rModel->Reporter = $_SESSION['loginUserName'];
		$rModel->CaseId = $caseID;
		$rModel->CreateTime = date("Y-m-d H:i:s");
		$rModel->Creator = $_SESSION[C('USER_AUTH_KEY')];
		$rModel->ReportContent = $subject;
		$rModel->Type= 1;

		$res = $rModel->add();
		if($res === false)
		{
			fileWrite($rModel->getLastSql());
			$this->error("插入失败2!");
		}
		$this->redirect("/Incident/details/id/$cid");
		//附件处理
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
	public function report()
	{
		$this->getClassType();
		$this->display();
	}
	public function _before_edit()
	{
		$model = M("ThreatType");
		$list = $model->select();
		$this->assign("threatType",$list);
	}
	//编辑页面
	public function edit(){
		$name = "Cases";
		$model = M ( $name );
		$id = $_REQUEST ["id"];

		if(!$this->can_edit_by_id($id))
		{
			$this->error("权限不够!");
			return;
		}
		if(!empty($model))
		{
			//先获取附件
			$filed = "`Attachment`.*";
			$join = "right join `Ticket` on `Ticket`.CaseId = `Cases`.CaseId right join `Attachment` on `Attachment`.TicketId = `Ticket`.`id`";
			$vo = $model->where("`Cases`.id=$id  AND `Attachment`.Type=0")->field($field)->join($join)->select();
			$this->assign("attachList",$vo);
			$ids = "";
			for($i=0;$i<count($vo);$i++)
			{
				$ids .=$vo[$i]['id'];
			}
			$this->assign("ids",$ids);

			//获取大类
			$this->getClassType();
			//die();
			$field = "`Cases`.*,`Users`.`name`";
			$join = "left join `Users` on `Users`.id=`Cases`.CaseResponsor";
			$vo = $model->where("`Cases`.id=$id")->field($field)->join($join)->select();

			//获取小类
			$this->getType($vo[0]['TcID']);
			$this->assign ( 'vo', $vo[0] );
		}
		$this->display();
	}
	//更新
	public function update()
	{
		C("TOKEN_ON",false);
		$name = "Cases";
		$model = D ( $name );
		$tableId = $_REQUEST['id'];
		if(isset($tableId) && !$this->can_edit_by_id($tableId))
		{
			$this->error("权限不够!");
			return;
		}
		if(empty($tableId))
		{
			C("TOKEN_ON",true);
			$this->error("数据错误！");
		}
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		if(empty($model->id))
		{
			$data = $_REQUEST;
			$list=$model->save ($data);
		}
		else
		{
			$list=$model->save ();
		}
		// 更新数据


		if (false === $list) {
			//错误提示
			C("TOKEN_ON",true);
			fileWrite($model->getLastSql());
			$this->error ('编辑失败!');
			return;
		}

		$caseId = $_REQUEST['CaseId'];
		/*申请单插入*/
		$name="Ticket";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$model->id = null;
		$model->Creator = $_SESSION[C('USER_AUTH_KEY')];
		$model->CreateTime = date("Y-m-d H:i:s");
		$model->CaseId = $caseId;
		//保存当前数据对象
		$list=$model->add ();

		if ($list===false) {
			C("TOKEN_ON",true);
			$this->error ('操作失败!');
		}
		//$ticketId = $model->getLastInsID();

		//	fileWrite($attModel->getLastSql());
		if ($list!==false) { //保存成功
			C("TOKEN_ON",true);
			$this->redirect('/Incident/details/id/'.$tableId.'/Flag/0');
			//			$this->assign ( 'jumpUrl', "__URL__/details/id/$tableId/Flag/0" );
			//$this->success ('操作成功!');
		} else {
			C("TOKEN_ON",true);
			//失败提示
			$this->error ('操作失败!');
		}
	}

	public function upload() {
		//fileWrite("filePath:".APP_PATH."\tAPP_PUBLIC_PATH:".APP_PUBLIC_PATH."::moduleName::".MODULE_NAME);
		if(!empty($_FILES)) {//如果有文件上传
			// 上传附件并保存信息到数据库
			$this->_upload("Attachment");
		}
	}


	public function details()
	{
		$name = "Cases";
		$model = M ( $name );
		$id = $_REQUEST ["id"];
		$cid  = $_REQUEST['cid'];
		if(!$id && !$cid)
		{
			$this->error("非法访问!");
		}

		$qx = false;
		if($cid)
		{
			$qx = $this->can_edit_by_caseid($cid);
		}
		else {
			$qx = $this->can_edit_by_id($id);
		}
		$this->assign("qx",$qx);

		if(isset($_REQUEST['Flag']) && $_REQUEST['Flag'] ==1)
		{
			$caseResponsor = $_SESSION[C('USER_AUTH_KEY')];
			if($cid)
			{
				$model->where("CaseId = '$cid' and CaseResponsor = $caseResponsor")->setField("UpdateFlag",0);
				$model->query("update CasesShadow set UpdateFlag=0 where CaseId = '$cid' and CaseResponsor = $caseResponsor");
			}
			else {
				$model->where("id = $id and CaseResponsor = $caseResponsor")->setField("UpdateFlag",0);
				$model->query("update CasesShadow set UpdateFlag=0 where id = $id and CaseResponsor = $caseResponsor");
			}
			//fileWrite($model->getLastSql());
		}
		$caseId = "";
		if(!empty($model))
		{
			//获取案件基本信息
			$field = "`Cases`.*,`Users`.`name`";
			$join = "left join `Users` on `Users`.id=`Cases`.CaseResponsor";

			if($cid)
			{
				$vo = $model->where("`Cases`.CaseId='$cid'")->field($field)->join($join)->select();
			}
			else {
				$vo = $model->where("`Cases`.id=$id")->field($field)->join($join)->select();
			}
			//echo $model->getLastSql();
			//print_r($vo);
			//die();
			if(empty($vo) || count($vo) == 0)
			{
				$this->error("数据错误!");
			}
			$caseId = $vo[0]['CaseId'];
			$rid = $vo[0]['id'];
			$rtid = $vo[0]['TID'];
			$vo = $vo[0];
			//换行问题
			$vo['ThreatSrcIPList'] = str_replace("\n", "<br />", $vo['ThreatSrcIPList']);
			$vo['ThreatSrcDomainList'] = str_replace("\n", "<br />", $vo['ThreatSrcDomainList']);
			$vo['ThreatDstIPList'] = str_replace("\n", "<br />", $vo['ThreatDstIPList']);
			$vo['ThreatDstDomainList'] = str_replace("\n", "<br />", $vo['ThreatDstDomainList']);
			$this->assign ( 'vo', $vo );
		}
		else {
			$this->error("数据错误!".$model->getLastSql());
		}
		//源宿相关暂时不用
		$baseId = $id;
		$id = $caseId;


		//响应策略
		import('@.Action.StrategyAction');
		$strategyAction = new StrategyAction();
		$strategyAction->getTpl($vo['CaseId']);
		//证据

		$this->getEvidence($id,$vo['MergeType']);


		//响应过程，为ticket表
		$model = M("ResponseTicket");
		$field = "CaseId";
		if($vo['MergeType'] == 1)
		{
			$field = "MergeCaseID";

			$sql = "select distinct(CaseId) as CaseId from ResponseTicket where MergeCaseID = '$id'";
			$res = $model->query($sql);
			if($res === false)
			{

			}
			else {
				$this->assign("childList",$res);
			}
		}
		if(!empty($model))
		{
			$ticketList = $model->where("$field='".$id."'")->order("ResponseTime desc")->select();
			//获取响应过程对应的附件
			$sql = "select Attachment.* from Attachment where Attachment.TicketId in (
			select id from ResponseTicket where $field = '$id'
			) and Attachment.Type=1";		
			$vo = $model->query($sql);
			//附件数组
			for($j=0;$j<sizeof($ticketList);$j++)
			{
				$ticketList[$j]['attchment'] = Array();
			}
			for($i=0;$i<sizeof($vo);$i++)//依次检查附件列表
			{
				for($j=0;$j<sizeof($ticketList);$j++)
				{
					if($ticketList[$j]['id'] == $vo[$i]['TicketId'])
					{
						$count = sizeof($ticketList[$j]['attchment']);
						$ticketList[$j]['attchment'][$count] = $vo[$i];
						break;//查找到，可以break
					}
				}
			}
			$this->assign("responseList",$ticketList);
			//fileWriteArray($ticketList);
		}

		$this->display();
	}

	public function CaseMerge()
	{
		//调用后台程序
		$sid = $_REQUEST['sid'];
		$cid = $_REQUEST['cid'];//先一个
		if(!$sid || !$cid)
		{
			$this->error("参数错误!");
		}
		$fileName = "/var/www/html/DIM_CHAIRS/DIM/dist/WeaselMergeCase.jar";
		//echo "java -jar $fileName -s NJ0-2013000042 -d NJ0-2013000043 -t 0<hr>";
		$str =  exec("java -jar $fileName -s $sid -d $cid -t 0",$return);

		//die("java -jar $fileName -s $sid -d $cid -t 0");
		//echo "<hr>$str<hr>";
		if(strlen($str)>0)
		{
			$this->redirect('/Incident/details/cid/'.$sid);
		}
		else {
			$this->error("合并失败");
		}
		return;

		$this->redirect('/Incident/details/id/'.$parentInfo['id']);
	}
	//单条事件的证据列表,可能有多种证据
	public function incident_evidence()
	{
		$caseId = $_REQUEST['id'];
		if(!isset($caseId))
		{
			$this->error("数据错误!");
		}
		$this->getEvidence($caseId,0);
		$this->assign("CaseId",$caseId);
		$this->display();
	}


	/*
	 * 威胁源宿IP详情的查看
	 * */
	public function threatIP()
	{
		$flag = $_REQUEST['flag'];//flag-0:威胁宿 flag-1：威胁源
		$CaseId = $_REQUEST['id'];
		if(empty($CaseId))
		{
			$this->error("数据错误!");
			return;
		}
		if($flag == 0)
		{
			$model = M("ThreatDstIP");
			$filed = "EntityID,CaseID,TcID,TID,DetectRuleID,
			IPMask,ISP,ASN,Country,Orgnization,FirstTime,LastTime,FakeFlag,IPAddress";
		}
		else {
			$model = M("ThreatSrcIP");
			$filed = "EntityID,CaseID,TcID,TID,DetectRuleID,
			IPMask,ISP,ASN,Country,Orgnization,FirstTime,LastTime,FakeFlag,IPAddress";
		}

		if(empty($model))
		{
			$this->error("数据错误!");
			return;
		}

		$ips = $model->where("CaseID='$CaseId'")->field($filed)->group("IPAddress")->select();
		$this->assign("threatIP",$ips);

		$this->display();
	}

	/*
	 * 威胁源宿域名详情的查看
	 * */
	public function threatDomain()
	{
		$flag = $_REQUEST['flag'];//flag-0:威胁宿 flag-1：威胁源
		$CaseId = $_REQUEST['id'];
		if(empty($CaseId))
		{
			$this->error("数据错误!");
			return;
		}
		if($flag == 0)
		{
			$domainModel = M("ThreatDstDomain");
		}
		else {
			$domainModel = M("ThreatSrcDomain");
		}
		if(empty($domainModel))
		{
			$this->error("数据错误!");
			return;
		}

		$domain = $domainModel->where("CaseID='$CaseId'")->select();
		$this->assign("threatDomain",$domain);
		$this->display();
	}

	/*
	 * 威胁源宿详情的查看
	 * */
	public function threat()
	{
		$flag = $_REQUEST['flag'];//flag-0:威胁宿 flag-1：威胁源
		$CaseId = $_REQUEST['id'];
		if(empty($CaseId))
		{
			$this->error("数据错误!");
			return;
		}
		if($flag == 0)
		{
			$model = M("ThreatDstIP");
			$domainModel = M("ThreatDstDomain");
		}
		else {
			$model = M("ThreatSrcIP");
			$domainModel = M("ThreatSrcDomain");
		}
		if(empty($model) || empty($domainModel))
		{
			$this->error("数据错误!");
			return;
		}
		$ips = $model->where("CaseID='$CaseId'")->select();
		$this->assign("threatIP",$ips);
		$domain = $domainModel->where("CaseID='$CaseId'")->select();
		$this->assign("threatDomain",$domain);
		$this->display();

	}

	/*
	 * 根据不同的EvidenceType来确定查询的证据.
	 * type=0:赋值；type=1：返回
	 * */
	public function getEvidence($caseId,$mergeType,$e_type=0)
	{
		//汇报必须要处理的事件，证据在CaseSummery中
		if(strpos($caseId, "NJo") !== false)
		{
			$EvidenceType[0]['EvidenceType'] = -1;
			$this->assign("evidenceType",$EvidenceType);
			//print_r($EvidenceType);
			$model = M("CaseSummery");
			if(!empty($model))
			{
				$list = $model->where("CaseID='$caseId'")->select();
				//print_r($list);
				if($list  === false)
				{

				}
				else
				{
					$this->assign("NBOSUDPDDOS",$list);
				}
			}
			$this->assign("evidenceName","NBOSUDPDDOS");
			$this->assign("CaseId",$caseId);
			$this->assign("EvidenceType",-1);
			return;
		}
		$model = D("Evidence");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$res = $model->getEvidence($caseId,$mergeType,$e_type);
		//print_r($res);
		if(!is_array($res) && $res == -1)
		{
			$this->error("数据错误!");
		}
		if($e_type ==1)
		{
			return $res;
		}
		else {
			foreach ($res as $key=>$val)
			{
				$this->assign($key,$val);
			}
		}

	}

	public function pdfExport($content,$fileFlag = false)
	{
		set_time_limit(0);
		//require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Public/tcpdf/config/tcpdf_config.php';
		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Public/tcpdf/mytcpdf.php';
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetFont('stsongstdlight', '', 20);
		$pdf->SetAuthor('NJCERT');
		$pdf->SetTitle('CHAIRS');
		$pdf->SetSubject('CHAIRS');
		$pdf->SetKeywords('CHAIRS');
		// set default header data
		$PDF_HEADER_LOGO = dirname(dirname(dirname(dirname(__FILE__))))."/Public/CSS/logo_l.png";
		//   $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, "CHAIRS", array(0,64,255), array(0,64,128));
		//	$pdf->setFooterData(array(0,64,0), array(0,64,128));

		// set header and footer fonts
		//	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// set default font subsetting mode
		$pdf->setFontSubsetting(true);
		$pdf->AddPage();

		// set text shadow effect
		$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));


		//$pdf->SetFont('msungstdlight', '', 11);

		// Set some content to print
		// Set some content to print


		if($fileFlag)
		{
			$pdf->SetFont('msungstdlight', '', 11);
			$pdf->writeHTML($content['directory'], true, false, true, false, '');
			$name = iconv("utf-8","GBK",$content['fileName']);
			$name = dirname(dirname(dirname(dirname(__FILE__)))).'/Public/pdf/'.$name;

			$pdf->Output($name, 'F');//F=到文件
		}
		else {
			$pdf->SetFont('stsongstdlight', '', 12);
			$pdf->writeHTML($content['directory'], true, false, true, false, '');
			$name = iconv("utf-8","GBK",$content['fileName']);
			$pdf->Output($name, 'I');//I=
		}
		//$pdf->Output('CHAIRS_cases.pdf', 'I');//到浏览器
		$pdf->Close();
	}

	public function toPdf()
	{
		$map = Array();
		$map = $this->_search ($name);

		//主题
		if(isset($map['Subject']))
		{
			$this->assign("Subject",$map['Subject']);
			$map['Subject'] = array('like',"%".$map['Subject']."%");
		}
		$this->CasesPdfExport($map);
	}


	public function sendMail($sendto_email,$subject,$my_html_message,$Cc,$attachList)
	{
		$my_html_message = "please donot reply";
		require_once("Mail.php");
		require_once("Mail/mime.php");
		//$subject=iconv("utf-8","gb2312",$subject);
		$recipients  = "<$sendto_email>";
		$headers["From"] = 'chairs@163.com';
		$headers["To"] = "<$sendto_email>";
		$headers["Subject"] = "=?UTF-8?B?".base64_encode($subject)."?=";
		$headers["Content-Type"] = 'text/html;charset="utf-8"';
		$headers["Content-Language"]="zh-cn";
		$headers["Content-Transfer-Encoding"]="base64";
		$headers["Date"] = date("D, d M Y H:i:s O");
		$crlf = "\n";
		$mime = new Mail_mime(array('eol' => $crlf));
		$mime->setHTMLBody($my_html_message);

		for($i=0;$i<count($attachList);$i++)
		{
			$file = dirname(dirname(dirname(dirname(__FILE__))))."/Public/pdf/".$attachList[$i]["SaveName"];
			$mime->addAttachment($file,"text/plain");
		}
		$mime_param["html_charset"] = 'utf-8';
		$message = $mime->get();
		$headers = $mime->headers($headers);
		$params["debug"] = FALSE; /* 调试模式 */
		$params["host"] = 'njnet.edu.cn';
		$params["auth"] = FALSE; /* 默认不用认证 */
		$params["localhost"] = 'demo';
		$mail_message =& Mail::factory('smtp', $params);

		$res = $mail_message->send($recipients, $headers, $message);
		if(PEAR::isError($mail_res)){
			die($res->getMessage());
		}
		return 1;
	}

	//自动发送邮件
	public function AutoPdfSend()
	{
		$model = M("Unit");
		$list = $model->where("AutoFlag = 1")->select();

		set_time_limit(0);
		$map = array();
		$map['CaseType']=array(array('eq',0),array('eq',1),'or');
		$btime = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
		$etime = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
		$map['_string'] = "LastTime>='$btime' and LastTime<='$etime'";

		for($i=0;$i<count($list);$i++)
		{
			$map['Unit']= $list[$i]['UnitName'];
			$fileName = $list[$i]['id'].date('Y-m-d').".pdf";
			$this->CasesPdfExport($map,$fileName,true);
			$subject = $list[$i]['UnitName']."-安全事件统计";
			$Cc="";
			$attachList = array();
			$attachList[0]["SaveName"] = $fileName;
			$attachList[0]["FileName"] = $fileName;
			$this->sendMail($list[$i]['Email'], $subject, "", $Cc, $attachList);
		}
	}

	public function now_pdf()
	{
		set_time_limit(0);
		$map = array();
		$map['CaseType']=array(array('eq',0),array('eq',1),'or');
		$btime = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
		$etime = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
		$map['_string'] = "LastTime>='$btime' and LastTime<='$etime'";
		$map['Unit']= "南京理工大学";
		$this->CasesPdfExport($map,true);

		////			return;
		//
		$map['Unit']= "苏州大学";
		$this->CasesPdfExport($map,true);

		$map['Unit']= "江南大学";
		$this->CasesPdfExport($map,true);

		$map['Unit']= "南京大学";
		$this->CasesPdfExport($map,true);

		$map['Unit']= "东南大学";
		$this->CasesPdfExport($map,true);
		$map['Unit']= "江苏教育信息管理中心";
		$this->CasesPdfExport($map,true);
		$map['Unit']= "江苏大学";
		$this->CasesPdfExport($map,true);

		$map['Unit']= "南京师范大学";
		$this->CasesPdfExport($map,true);

	}

	public function IndexToPdf()
	{
		$map = array();
		$map['DeleteFlag'] = 0;//全部案件数
		$map['CaseResponsor'] = $_SESSION[C('USER_AUTH_KEY')];
		$map['_string'] = "`ProgressState` = 'open'";
		$map['CaseType']=array(array('eq',0),array('eq',1),'or');
		$this->CasesPdfExport($map);
	}
	/*
	 * 根据不同的EvidenceType来确定查询的证据.
	 * */
	public function CasesPdfExport($map,$fileName="",$fileFlag=false)
	{
		set_time_limit(0);
		$name = "Cases";
		$model = M($name);

		$content=array();
		$content['fileName'] = $fileName;
		$content['directory_head']="<div style='padding-left:50px;'><h1>事件列表</h1><br/><h3>".$map['Unit']."</h3>";
		$str =  '<table border="1" cellspacing="0" cellpadding="3">
								<tr><th >编号</th>
								<th >主题</th>
								<th >类型</th>
								<th >开始时间</th>
								<th >结束时间</th></tr>';
		$tidMap=array();
		$tidMap[0] = 300002;
		$tidMap[1] = 600002;
		$tidMap[2] = 600003;
		$tidMap[3] = 700003;
		$tidMap[4] = 700004;
		$tidMap[5] = 700700;
		for($m=0;$m<count($tidMap);$m++)
		{
			$map['TID'] = $tidMap[$m];
			$count = $model->where ( $map )->count ( 'id' );
			echo $model->getLastSql()."<hr />";
			if ($count > 0)
			{

				$filed = "`Cases`.*";
				$count = 300;
				$j = 0;

				while(1)
				{
					$start = $j*$count;
					$j++;
					$voList = $model->where($map)->field($filed)->limit("$start,$count")->order("LastTime desc")->select();
					if($voList === false)
					{
						die("数据查询错误!");
					}
					if(count($voList) ==0)
					{
						break;
					}

					//输出
					for($i=0;$i<count($voList);$i++)
					{
						$str.= "<tr>";
						$type = get_threat_group_name($voList[$i]['GroupID'],$voList[$i]['TID']);
						$str.= "<td>".$voList[$i]['CaseId']."</td>";
						$str.= "<td>".$voList[$i]['Subject']."</td>";
						$str.= "<td>".$type."</td>";
						$str.= "<td>".$voList[$i]['StartTime']."</td>";
						$str.= "<td>".$voList[$i]['LastTime']."</td>";
						$str.= "</tr>";
					}
				}
			}//数量大于0
			else
			{

			}
		}

		//
		$str.="</table></div>";
		$content['directory']= $str;
		$this->pdfExport($content,$fileFlag);
	}
	/*
	 * 根据不同的EvidenceType来确定查询的证据.
	 * */
	public function EvidenceExport()
	{
		$caseId = $_REQUEST['CaseId'];
		if(empty($caseId))
		{
			$this->error("数据错误!");
		}
		//查找相关证据
		$model = M("Evidence");
		if(!empty($model))
		{
			//首先查找EvidenceType
			$sql = "select distinct `EvidenceType` from `Evidence` where `EvidenceType` is not null and `$nameTable`='$caseId'";
			$res = $model->query($sql);

			if($res === false)
			{
				$this->error("数据查询错误!");
				return;
			}
			$this->assign("evidenceType",$res);
			//print_r($res);
			$map = Array();
			$map['CaseId'] = $caseId;
			$join = "";
			$vo = Array();
			$field = "";
			$evidenceName = "";//供页面误报用
			for($i=0;$i<sizeof($res);$i++)
			{
				switch($res[$i]['EvidenceType'])
				{
					case 0:
						break;
						/* EvidenceABNMDomain*/
					case 1:
						$sql = "SELECT `Evidence`.*,`EvidenceABNMDomain`.`DomainName`,`EvidenceABNMDomain`.`HistoryIPNum`,`EvidenceABNMDomain`.`LastIPNum`
								,`EvidenceABNMDomain`.`Addictive`,`EvidenceABNMDomain`.`Cause`,`EvidenceABNMDomain`.`AttachFile`
								 FROM `Evidence` ,`EvidenceABNMDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 1 ) 
								 AND `EvidenceABNMDomain`.`EvidenceID`=`Evidence`.`EvidenceID`";						
						$vo = $model->query($sql);
						$name = "访问异常";
						break;
					case 2://fast-flux
						$sql = "SELECT `Evidence`.*,`EvidenceFFDomain`.`DomainName`,`EvidenceFFDomain`.`IPNum`,`EvidenceFFDomain`.`SubnetNum`
									,`EvidenceFFDomain`.`ASNNum`,`EvidenceFFDomain`.`CountryNum`,`EvidenceFFDomain`.`TTLMin`,`EvidenceFFDomain`.`TTLMax`,`EvidenceFFDomain`.`AttachFile`
								 FROM `Evidence` ,`EvidenceFFDomain`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 2 ) 
								 AND `EvidenceFFDomain`.`EvidenceID`=`Evidence`.`EvidenceID`";						
						$vo = $model->query($sql);
						$name = "Fast-flux";
						break;
					case 3:
						break;
					case 4:
						break;
						/*EvidenceIsoLink*/
					case 5:
						$sql = "SELECT `Evidence`.*,`EvidenceIsoLink`.`SiteName`,`EvidenceIsoLink`.`IsoLinkUrl`,`EvidenceIsoLink`.`ClientIP`
								,`EvidenceIsoLink`.`ServerIP`
								 FROM `Evidence` ,`EvidenceIsoLink`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 5 ) 
								 AND `EvidenceIsoLink`.`EvidenceID`=`Evidence`.`EvidenceID`";						
						$vo = $model->query($sql);
						$name = "IsoLink";
						break;
						/*EvidenceIRCBotByChannel*/
					case 6:
						$sql = "SELECT `Evidence`.*,`EvidenceIrcBotByChannel`.`ChannelName`,`EvidenceIrcBotByChannel`.`ClientIPCnt`,`EvidenceIrcBotByChannel`.`ServerIPCnt`
								,`EvidenceIrcBotByChannel`.`NicknameCnt`,`EvidenceIrcBotByChannel`.`MetaDataCnt`,`EvidenceIrcBotByChannel`.`AttachFile`
								 FROM `Evidence` ,`EvidenceIrcBotByChannel`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 6 ) 
								 AND `EvidenceIrcBotByChannel`.`EvidenceID`=`Evidence`.`EvidenceID`";						
						$vo = $model->query($sql);
						$name = "IrcBotByChannel";
						break;
					case 7:
						$sql = "SELECT `Evidence`.*,`EvidenceMalWeb`.`DomainName`,`EvidenceMalWeb`.`WebIP`,`EvidenceMalWeb`.`WebUrl`,
								`EvidenceMalWeb`.`VictimType`,`EvidenceMalWeb`.`MalWebUrl`,`EvidenceMalWeb`.`MalHost`,`EvidenceMalWeb`.`AttachFile`
								 FROM `Evidence` ,`EvidenceMalWeb`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 7 )  
								 AND `EvidenceMalWeb`.`EvidenceID`=`Evidence`.`EvidenceID` ";

						$vo = $model->query($sql);
						$name = "MalWeb";
						break;
					case 8:
						$sql = "SELECT `Evidence`.*,`EvidenceSQLI`.`siteName`,`EvidenceSQLI`.`SQLIUrl`,`EvidenceSQLI`.`ClientIP`,
							`EvidenceSQLI`.`ServerIP`,`EvidenceSQLI`.`SQLICnt`,`EvidenceSQLI`.`SQLITool`,`EvidenceSQLI`.`AttachFile`
								 FROM `Evidence` ,`EvidenceSQLI`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 8 ) 
								 AND `EvidenceSQLI`.`EvidenceID`=`Evidence`.`EvidenceID` ";

						$vo = $model->query($sql);
						$name = "SQLI";
						break;
					case 10:

						$sql = "SELECT `Evidence`.*,`EvidenceNBOSDDOS`.`TargetIP`,`EvidenceNBOSDDOS`.`IPNum`,`EvidenceNBOSDDOS`.`PPS`,`EvidenceNBOSDDOS`.`BPS`,`EvidenceNBOSDDOS`.`Duration`,`EvidenceNBOSDDOS`.`PktNum`,`EvidenceNBOSDDOS`.`Rule`,`EvidenceNBOSDDOS`.`ByteNum`,`EvidenceNBOSDDOS`.`AttachFile`
								 FROM `Evidence` ,`EvidenceNBOSDDOS`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 10 ) 
								 AND `EvidenceNBOSDDOS`.`EvidenceID`=`Evidence`.`EvidenceID` ";

						$vo = $model->query($sql);
						$name = "NBOSDDOS";
						break;
					case 13:
						$sql = "SELECT `Evidence`.*,`EvidenceNBOSUAHT`.*
								 FROM `Evidence` ,`EvidenceNBOSUAHT`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 13 ) 
								 AND `EvidenceNBOSUAHT`.`EvidenceID`=`Evidence`.`EvidenceID` order by OccurNum desc ";						
						$vo = $model->query($sql);
						$name = "NBOSUAHT";
						break;
					case 12:
						$type = 12;
						$sql = "SELECT `Evidence`.*,`EvidenceBotnetAct`.*
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID` ";						
						$vo = $model->query($sql);
						$name = "BotnetAct";
						break;
					case 14:
						$type = 14;
						$sql = "SELECT `Evidence`.*,`EvidenceBotnetActCli`.*
								 FROM `Evidence` ,`EvidenceBotnetActCli`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetActCli`.`EvidenceID`=`Evidence`.`EvidenceID` ";						
						$vo = $model->query($sql);
						$name = "BotnetAct";
						break;
					case 104:
						$type = 104;
						$sql = "SELECT `Evidence`.*,`EvidenceBotnetAct`.*
								 FROM `Evidence` ,`EvidenceBotnetAct`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = $type ) 
								 AND `EvidenceBotnetAct`.`EvidenceID`=`Evidence`.`EvidenceID` ";						
						$vo = $model->query($sql);
						$name = "BotnetAct";
						break;
					case 101:
						$sql = "SELECT `Evidence`.*,`EvidenceSuricata`.`SIP`,`EvidenceSuricata`.`RawCnt`,`EvidenceSuricata`.`DIP`,`EvidenceSuricata`.`SPort` ,`EvidenceSuricata`.`DPort`,`EvidenceSuricata`.`AlertMsg`,`EvidenceSuricata`.`AttachFile`
								 FROM `Evidence` ,`EvidenceSuricata`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 101 ) 
								 AND `EvidenceSuricata`.`EvidenceID`=`Evidence`.`EvidenceID`";						
						$vo = $model->query($sql);
							
						break;
					case 110:
						$sql = "SELECT `Evidence`.*,`EvidenceWebShell`.*
								 FROM `Evidence` ,`EvidenceWebShell`
								 WHERE ( `$nameTable` = '$caseId' ) AND ( `EvidenceType` = 110 ) 
								 AND `EvidenceWebShell`.`EvidenceID`=`Evidence`.`EvidenceID`";						
						$vo = $model->query($sql);
						break;
					default:
						break;
				}//end of switch

				if(count($vo) >0)
				{
					$nameArray = Array();
					$temp = $vo[0];
					foreach($temp as $key => $value)
					{
						$nameArray[]=$key;
					}
					$name="事件$caseId证据($name)列表";
					$this->PHPExcelExport($nameArray, $vo, $name);
				}
			}//end for
		}
	}
}
?>
