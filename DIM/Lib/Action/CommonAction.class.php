<?php
class CommonAction extends Action {
	function _initialize() {
		// 用户权限检查
		if (C ( 'USER_AUTH_ON' ) && !in_array(MODULE_NAME,explode(',',C('NOT_AUTH_MODULE')))) {
			import ( '@.ORG.RBAC' );
			if (! RBAC::AccessDecision ()) {
				//检查认证识别号
				if (! $_SESSION [C ( 'USER_AUTH_KEY' )]) {
					//跳转到认证网关
					redirect ( PHP_FILE . C ( 'USER_AUTH_GATEWAY' ) );
				}
				// 没有权限 抛出错误
				if (C ( 'RBAC_ERROR_PAGE' )) {
					// 定义权限错误页面
					redirect ( C ( 'RBAC_ERROR_PAGE' ) );
				} else {
					if (C ( 'GUEST_AUTH_ON' )) {
						$this->assign ( 'jumpUrl', PHP_FILE . C ( 'USER_AUTH_GATEWAY' ) );
					}
					// 提示错误信息
					$this->error ( L ( '_VALID_ACCESS_' ) );
				}
			}
		}
	}
	public function index() {
		//列表过滤器，生成查询Map对象
		$map = $this->_search ();
		if (method_exists ( $this, '_filter' )) {
			$this->_filter ( $map );
		}
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
		return;
	}

	//确定登录用户是否可以修改数据
	/*
	 * caseId:事件Id
	 * 返回：false-不可以，true-可以
	 * */
	public function can_edit_by_caseid($caseId)
	{
		//如果是管理员，返回true
		if($_SESSION['userPower'] > 1)
		{
			return true;
		}
		//
		if(empty($caseId))
		{
			return false;
		}

		$sid= $_SESSION[C('USER_AUTH_KEY')];
		if(!$sid)
		{
			return false;
		}

		$model=M("Cases");
		if(empty($model))
		{
			return false;
		}
		//事件负责人可以有权限		
		//同一个单位可以有权限
		$list = $model->query("select Unit,CaseResponsor from Cases where CaseId='$caseId'");
		if($list === false)
		{
			return false;
		}
		if($list[0]['CaseResponsor'] == $sid)
		{
			return true;
		}
		$flag = false;
		$array=$this->getUnitNameByUid($sid);
		if(is_array($array))
		{
			for($k=0;$k<count($array);$k++)
			{
				if($list[0]['Unit'] == $array[$k]['UnitName'])
				{
					$flag = true;
					break;
				}
			}
		}
		return $flag;
	}

	//根据用户id获取unit的id
	public function getUnitNameByUid($uid)
	{
		if(!isset($uid))
		{
			return -1;
		}

		$model = M("UnitUsersRelation");
		if(empty($model))
		{
			return -1;
		}
		$list = $model->where("UserId=$uid")->select();
		//echo $model->getLastSql();
		if($list === false || count($list) < 1)
		{
			return -1;
		}
		return $list;
	}
	//根据id获取是否可以修改
	public function can_edit_by_id($id)
	{
		//如果是管理员，返回true
		if($_SESSION['userPower'] > 1)
		{
			return true;
		}
		//
		if(empty($id))
		{
			return false;
		}

		$sid= $_SESSION[C('USER_AUTH_KEY')];
		if(!$sid)
		{
			return false;
		}

		$model=M("Cases");
		if(empty($model))
		{
			return false;
		}

		//同一个单位可以有权限
		$list = $model->query("select Unit,CaseResponsor from Cases where id=$id");
		if($list === false)
		{
			return false;
		}

		//是处理人
		if($list[0]['CaseResponsor'] == $sid)
		{
			return true;
		}
		$flag = false;
		$array=$this->getUnitNameByUid($sid);
		if(is_array($array))
		{
			for($k=0;$k<count($array);$k++)
			{
				if($list[0]['Unit'] == $array[$k]['UnitName'])
				{
					$flag = true;
					break;
				}
			}
		}

		return $flag;
	}

	//确定登录用户是否可以访问页面
	public function can_access()
	{

	}
	//获取运行管理配置数据库连接
	public function getConnetManString()
	{
		/*
		 * DB_TYPE_MAN=>'mysql'
		 * 'DB_HOST_MAN'=>'127.0.0.1',  //运行管理数据库配置
		 'DB_NAME_MAN'=>'CHAIRS_MAN', //运行管理数据库配置
		 'DB_USER_MAN'=>'root', //运行管理数据库配置
		 'DB_PWD_MAN'=>'0000', //运行管理数据库配置
		 'DB_PORT_MAN'=>'3306', //运行管理数据库配置

		 "mysql://root:0000@127.0.0.1:3306/CHAIRS_MAN"
		 */
		$type = C('DB_TYPE_MAN');
		if(empty($type))
		{
			$type="mysql";
		}
		$host = C('DB_HOST_MAN');
		if(empty($host))
		{
			$host="127.0.0.1";
		}

		$port = C('DB_PORT_MAN');
		if(empty($port))
		{
			$port="127.0.0.1";
		}

		$name = C('DB_NAME_MAN');
		$user = C('DB_USER_MAN');
		$pwd = C('DB_PWD_MAN');
		if(empty($name) || empty($pwd) || empty($name))
		{
			$this->error("数据库配置信息错误!");
		}
		$str = $type."://".$user.":".$pwd."@".$host.":".$port."/".$name;
		return $str;
	}
	/**
	 +----------------------------------------------------------
	 * 取得操作成功后要返回的URL地址
	 * 默认返回当前模块的默认操作
	 * 可以在action控制器中重载
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 * @throws ThinkExecption
	 +----------------------------------------------------------
	 */
	function getReturnUrl() {
		return __URL__ . '?' . C ( 'VAR_MODULE' ) . '=' . MODULE_NAME . '&' . C ( 'VAR_ACTION' ) . '=' . C ( 'DEFAULT_ACTION' );
	}

	/**
	 +----------------------------------------------------------
	 * 根据表单生成查询条件
	 * 进行列表过滤
	 +----------------------------------------------------------
	 * @access protected
	 +----------------------------------------------------------
	 * @param string $name 数据对象名称
	 +----------------------------------------------------------
	 * @return HashMap
	 +----------------------------------------------------------
	 * @throws ThinkExecption
	 +----------------------------------------------------------
	 */
	protected function _search($name = '') {
		//生成查询条件
		if (empty ( $name )) {
			$name = $this->getActionName();
		}
		$model = D ( $name );
		$map = array ();
		foreach ( $model->getDbFields () as $key => $val ) {
			if (isset ( $_REQUEST [$val] ) && $_REQUEST [$val] != '') {
				$map [$val] = $_REQUEST [$val];
			}
		}
		return $map;

	}

	/**
	 +----------------------------------------------------------
	 * 根据表单生成查询条件
	 * 进行列表过滤
	 +----------------------------------------------------------
	 * @access protected
	 +----------------------------------------------------------
	 * @param Model $model 数据对象
	 * @param HashMap $map 过滤条件
	 * @param string $sortBy 排序
	 * @param boolean $asc 是否正序
	 +----------------------------------------------------------
	 * @return void
	 +----------------------------------------------------------
	 * @throws ThinkExecption
	 +----------------------------------------------------------
	 */
	protected function _list($model, $map, $sortBy = '', $asc = false) {
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = ! empty ( $sortBy ) ? $sortBy : $model->getPk ();
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
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

			$voList = $model->where($map)->order( "`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
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

	function insert() {
		//B('FilterString');
		$name=$this->getActionName();
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		//保存当前数据对象
		$list=$model->add ();
		if ($list!==false) { //保存成功
			$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success ('新增成功!');
		} else {
			//失败提示
			$this->error ('新增失败!');
		}
	}

	public function add() {
		$this->display ();
	}

	function read() {
		$this->edit ();
	}

	function edit() {
		$name=$this->getActionName();
		$model = M ( $name );
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById ( $id );
		$this->assign ( 'vo', $vo );
		$this->display ();
	}

	function update() {
		//B('FilterString');
		$name=$this->getActionName();
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		if (false !== $list) {
			//成功提示
			$this->assign ( 'jumpUrl', Cookie::get ( '_currentUrl_' ) );
			$this->success ('编辑成功!');
		} else {
			//错误提示
			$this->error ('编辑失败!');
		}
	}
	/**
	 +----------------------------------------------------------
	 * 默认删除操作
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 * @throws ThinkExecption
	 +----------------------------------------------------------
	 */
	public function delete() {
		//删除指定记录
		$name=$this->getActionName();
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				$list=$model->where ( $condition )->setField ( 'status', - 1 );
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
	public function foreverdelete() {
		//删除指定记录
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				if (false !== $model->where ( $condition )->delete ()) {
					//echo $model->getlastsql();
					$this->success ('删除成功！');
				} else {
					$this->error ('删除失败！');
				}
			} else {
				$this->error ( '非法操作' );
			}
		}
		$this->forward ();
	}

	public function clear() {
		//删除指定记录
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			if (false !== $model->where ( 'status=1' )->delete ()) {
				$this->assign ( "jumpUrl", $this->getReturnUrl () );
				$this->success ( L ( '_DELETE_SUCCESS_' ) );
			} else {
				$this->error ( L ( '_DELETE_FAIL_' ) );
			}
		}
		$this->forward ();
	}
	/**
	 +----------------------------------------------------------
	 * 默认禁用操作
	 *
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 * @throws FcsException
	 +----------------------------------------------------------
	 */
	public function forbid() {
		$name=$this->getActionName();
		$model = D ($name);
		$pk = $model->getPk ();
		$id = $_REQUEST [$pk];
		$condition = array ($pk => array ('in', $id ) );
		$list=$model->forbid ( $condition );
		if ($list!==false) {
			$this->assign ( "jumpUrl", $this->getReturnUrl () );
			$this->success ( '状态禁用成功' );
		} else {
			$this->error  (  '状态禁用失败！' );
		}
	}
	public function checkPass() {
		$name=$this->getActionName();
		$model = D ($name);
		$pk = $model->getPk ();
		$id = $_GET [$pk];
		$condition = array ($pk => array ('in', $id ) );
		if (false !== $model->checkPass( $condition )) {
			$this->assign ( "jumpUrl", $this->getReturnUrl () );
			$this->success ( '状态批准成功！' );
		} else {
			$this->error  (  '状态批准失败！' );
		}
	}

	public function recycle() {
		$name=$this->getActionName();
		$model = D ($name);
		$pk = $model->getPk ();
		$id = $_GET [$pk];
		$condition = array ($pk => array ('in', $id ) );
		if (false !== $model->recycle ( $condition )) {

			$this->assign ( "jumpUrl", $this->getReturnUrl () );
			$this->success ( '状态还原成功！' );

		} else {
			$this->error   (  '状态还原失败！' );
		}
	}

	public function recycleBin() {
		$map = $this->_search ();
		$map ['status'] = - 1;
		$name=$this->getActionName();
		$model = D ($name);
		if (! empty ( $model )) {
			$this->_list ( $model, $map );
		}
		$this->display ();
	}

	/**
	 +----------------------------------------------------------
	 * 默认恢复操作
	 *
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 * @throws FcsException
	 +----------------------------------------------------------
	 */
	function resume() {
		//恢复指定记录
		$name=$this->getActionName();
		$model = D ($name);
		$pk = $model->getPk ();
		$id = $_GET [$pk];
		$condition = array ($pk => array ('in', $id ) );
		if (false !== $model->resume ( $condition )) {
			$this->assign ( "jumpUrl", $this->getReturnUrl () );
			$this->success ( '状态恢复成功！' );
		} else {
			$this->error ( '状态恢复失败！' );
		}
	}


	function saveSort() {
		$seqNoList = $_POST ['seqNoList'];
		if (! empty ( $seqNoList )) {
			//更新数据对象
			$name=$this->getActionName();
			$model = D ($name);
			$col = explode ( ',', $seqNoList );
			//启动事务
			$model->startTrans ();
			foreach ( $col as $val ) {
				$val = explode ( ':', $val );
				$model->id = $val [0];
				$model->sort = $val [1];
				$result = $model->save ();
				if (! $result) {
					break;
				}
			}
			//提交事务
			$model->commit ();
			if ($result!==false) {
				//采用普通方式跳转刷新页面
				$this->success ( '更新成功' );
			} else {
				$this->error ( $model->getError () );
			}
		}
	}

	/*
	 * 删除附件，但是文件并没有删除
	 * */
	public function deleteAttach()
	{
		$id = $_REQUEST['id'];
		if(!$id)
		{
			$this->AjaxReturn("","",0,"");
		}
		$db = M("Attachment");
		$map = array();
		$map['id'] = $id;

		$res = $db->where($map)->delete();

		if($res !== false)
		{
			$this->AjaxReturn("","",1,"");
		}
		else {
			$this->AjaxReturn("","",0,"");
		}
	}

	public function download()
	{
		$fileName = $_REQUEST['dname'];
		$realName = $_REQUEST['rname'];
		$caseId = $_REQUEST['CaseId'];

		if(strlen($fileName) <= 0 || strlen($caseId) <=0)
		{
			$this->error("数据错误!");
		}

	//	$fileName = "test.pdf";
		Header("Content-type:application/octet-stream");
		Header("Accept-Ranges: bytes");
		//	$realName = @iconv("UTF-8","GBK",$realName);
		//	$realName = urldecode($realName);
		Header("Content-Disposition:attachment;charset=utf-8;filename=".$fileName);
		//$filePath = iconv("UTF-8","GBK",$filePath);//包含中文字符时需要转换
		//$filePath = iconv("UTF-8","GBK",$filePath);

		$filePath = $fileName;
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
			fileWrite("$filePath file not exists");
		}
			
		return;
		$filePath = dirname(dirname(dirname(dirname(__FILE__))))."/Public/Uploads/".$caseId."/".$fileName;
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
			fileWrite("file not exists");
		}
	}


	function direcUpload($name="",$uploadId="file",$type="",$pathAdd="")
	{
		$flag = true;
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
				$returnName = $name."-".$_FILES["file"]["name"];
				if (file_exists($fileName))
				{
					return "";
				}
				$fileName = iconv("utf-8","GBK",$fileName);
				if(move_uploaded_file($_FILES[$uploadId]["tmp_name"],$fileName))
				{
					return $returnName;
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

	public function upload() {
		//fileWrite("filePath:".APP_PATH."\tAPP_PUBLIC_PATH:".APP_PUBLIC_PATH."::moduleName::".MODULE_NAME);
		if(!empty($_FILES)) {//如果有文件上传
			// 上传附件并保存信息到数据库
			$this->_upload(MODULE_NAME);
		}
	}

	protected function _upload($module='',$recordId='')
	{
		$caseId = $_REQUEST['CaseId'];
		import("@.ORG.UploadFile");
		$upload = new UploadFile();
		//设置上传文件大小
		$upload->maxSize  = 32922000;
		//设置上传文件类型
		$upload->allowExts  = array('rar','zip','doc','swf','txt','ppt','pdf','docx','html','jpg','log');
		//$upload->savePath =   '../Public/Uploads/';
		$upload->savePath = $_REQUEST['_uploadSavePath'];

		if($_REQUEST['reportTemp'])
		{
			$upload->savePath =   dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/Temp/';
		}
		else {
			$upload->savePath =   dirname(dirname(dirname(dirname(__FILE__)))).'/Public/Uploads/'.$caseId.'/';
			chmod($upload->savePath, 0777);
			//fileWrite($upload->savePath);
		}

		//fileWrite($upload->savePath);
		if(!is_dir($upload->savePath))
		{
			if(!mk_dir($upload->savePath,0777))
			{
				$this->error("目录不存在。");
			}
		}
		//	fileWrite($upload->savePath);
		if(isset($_POST['_uploadSaveRule'])) {
			//设置附件命名规则
			$upload->saveRule =  $_POST['_uploadSaveRule'];
		}else{
			$upload->saveRule = 'uniqid';
		}
		if(!empty($_POST['_uploadFileTable'])) {
			//设置附件关联数据表
			$module =  $_POST['_uploadFileTable'];
		}
		if(!empty($_POST['_uploadRecordId'])) {
			//设置附件关联记录ID
			$recordId =  $_POST['_uploadRecordId'];
		}
		if(!empty($_POST['_uploadFileId'])) {
			//设置附件记录ID
			$id =  $_POST['_uploadFileId'];
		}
		if(!empty($_POST['_uploadFileVerify'])) {
			//设置附件验证码
			$verify =  $_POST['_uploadFileVerify'];
		}
		if(!empty($_POST['_uploadUserId'])) {
			//设置附件上传用户ID
			$userId =  $_POST['_uploadUserId'];
		}else {
			$userId = isset($_SESSION[C('USER_AUTH_KEY')])?$_SESSION[C('USER_AUTH_KEY')]:0;
		}
		if(!empty($_POST['_uploadImgThumb'])) {
			//设置需要生成缩略图，仅对图像文件有效
			$upload->thumb =  $_POST['_uploadImgThumb'];
			$upload->imageClassPath = '@.ORG.Image';
		}
		if(!empty($_POST['_uploadThumbSuffix'])) {
			//设置需要生成缩略图的文件后缀
			$upload->thumbSuffix =  $_POST['_uploadThumbSuffix'];
		}
		if(!empty($_POST['_uploadThumbMaxWidth'])) {
			//设置缩略图最大宽度
			$upload->thumbMaxWidth =  $_POST['_uploadThumbMaxWidth'];
		}
		if(!empty($_POST['_uploadThumbMaxHeight'])) {
			//设置缩略图最大高度
			$upload->thumbMaxHeight =  $_POST['_uploadThumbMaxHeight'];
		}
		// 支持图片压缩文件上传后解压
		if(!empty($_POST['_uploadZipImages'])) {
			$upload->zipImages = true;
		}
		$uploadReplace =  false;
		if(isset($_POST['_uploadReplace']) && 1==$_POST['_uploadReplace']) {
			//设置附件是否覆盖
			$upload->uploadReplace =  true;
			$uploadReplace = true;
		}
		$uploadFileVersion = false;
		if(isset($_POST['_uploadFileVersion']) && 1==$_POST['_uploadFileVersion']) {
			//设置是否记录附件版本
			$uploadFileVersion =  true;
		}
		$uploadRecord  =  true;
		if(isset($_POST['_uploadRecord']) && 0==$_POST['_uploadRecord']) {
			//设置附件数据是否保存到数据库
			$uploadRecord =  false;
		}
		// 记录上传成功ID
		$uploadId =  array();
		$savename = array();
		$myShowList = array();
		$myCount = 0;
		//执行上传操作
		if(!$upload->upload()) {
			fileWrite($upload->getErrorMsg());
			if($this->isAjax() && isset($_POST['_uploadFileResult'])) {
				$uploadSuccess =  false;
				$ajaxMsg  =  $upload->getErrorMsg();
			}else {
				//捕获上传异常
				$this->error($upload->getErrorMsg());
			}
		}else {

			if($uploadRecord) {
				// 附件数据需要保存到数据库
				//取得成功上传的文件信息
				$uploadList = $upload->getUploadFileInfo();
				$remark = $_POST['remark'];
				//保存附件信息到数据库
				$Attach = M('Attachment');
				//启动事务
				//$Attach->startTrans();
				foreach($uploadList as $key=>$file) {
					//记录模块信息
					//保存附件信息到数据库
					$myCount++;
					//保存附件信息到数据库,add
					$file['FileName'] =   $file['name'];
					$file['SaveName'] =   $file['savename'];
					$file['Flag'] = 0;
					$uploadId[] =  $Attach->add($file);
					$myShowList[$myCount]['id'] =  $Attach->getLastInsID();
					$myShowList[$myCount]['name'] =  $file['name'];
					$myShowList[$myCount]['savename'] =  $file['savename'];
					$savename[] =  $file['savename'];
				}
				//提交事务
				//$Attach->commit();
			}
			$uploadSuccess =  true;
			$ajaxMsg  =  '';
		}
		// 判断是否有Ajax方式上传附件
		// 并且设置了结果显示Html元素
		if($this->isAjax() && isset($_POST['_uploadFileResult']) ){
			// Ajax方式上传参数信息
			$info = Array();
			$info['success']  = $uploadSuccess;
			$info['message']  = $ajaxMsg;

			//设置Ajax上传返回元素Id
			$info['uploadResult'] =  $_POST['_uploadFileResult'];
			if(isset($_POST['_uploadFormId'])) {
				//设置Ajax上传表单Id
				$info['uploadFormId'] =  $_POST['_uploadFormId'];
			}
			if(isset($_POST['_uploadResponse'])) {
				//设置Ajax上传响应方法名称
				$info['uploadResponse'] =  $_POST['_uploadResponse'];
			}
			if(!empty($uploadId)) {
				$info['uploadId'] = implode(',',$uploadId);
			}
			$info['savename']   = implode(',',$savename);
			//fileWriteArray($info);
			$this->ajaxUploadResult($info,$myShowList);
		}
		return ;
	}

	protected function ajaxUploadResult($info,$myShowList)
	{
		//fileWriteArray(json_encode($myShowList));
		// Ajax方式附件上传提示信息设置
		// 默认使用mootools opacity效果
		header("Content-Type:text/html; charset=utf-8");
		$show  = "<script language=\"JavaScript\"> window.parent.uploadComplete('".$info['uploadId']."','".json_encode($myShowList)."','".$info['success']."');</script>";
		//	fileWriteArray($show);
		exit($show);
		return;
		$show  .= ' var parDoc = window.parent.document;';
		$show  .= ' var result = parDoc.getElementById("'.$info['uploadResult'].'");';
		if(isset($info['uploadFormId'])) {
			$show  .= ' parDoc.getElementById("'.$info['uploadFormId'].'").reset();';
		}
		$show  .= ' result.style.display = "block";';
		$show .= " var myFx = new Fx.Style(result, 'opacity',{duration:600}).custom(0.1,1);";
		if($info['success']) {
			// 提示上传成功
			$show .=  'result.innerHTML = "<div style=\"color:#3333FF\"> 文件上传成功！</div>";';
			// 如果定义了成功响应方法，执行客户端方法
			// 参数为上传的附件id，多个以逗号分割
			if(isset($info['uploadResponse'])) {
				$show  .= 'window.parent.'.$info['uploadResponse'].'("'.$info['uploadId'].'","'.$info['savename'].'");';
			}
		}else {
			// 上传失败
			// 提示上传失败
			$show .=  'result.innerHTML = "<div style=\"color:#FF0000\"> 上传失败：'.$info['message'].'</div>";';
		}
		$show .= "\n".'</script>';
		//$this->assign('_ajax_upload_',$show);
		header("Content-Type:text/html; charset=utf-8");
		exit($show);
		return ;
	}
	/*public function success()
	 {
		$this->redirect('Index/index');
		}*/

	public function getColumnName($i)
	{
		$cname = "";
		if(floor($i/26)>0)
		{
			$cname = chr($i/26+64);
		}
		$cname .= chr($i%26+65);
		return $cname;
	}
	public function PHPExcelExport($nameArray,$res,$name)
	{

		if(count($nameArray)<1 || count($res) <1)
		{
			return -1;
		}
		//require_once 'C:\Program Files\Apache Software Foundation\Apache2.2\htdocs\aig016EHR\Public/PHPExcel/PHPExcel.php';
		require_once dirname(dirname(dirname(dirname(__FILE__)))).'/Public/PHPExcel/PHPExcel.php';
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
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

			for($j=0;$j<count($nameArray);$j++)
			{
				$curSheet->setCellValueByColumnAndRow($j, $xls_row, $row[$nameArray[$j]]);
			}
			$xls_row++;
		}

		//Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle(iconv("UTF-8","GBK" , $name));
		//Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();
		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		$fileName = iconv("UTF-8","GBK" , $name).date("Y-m-d").".xls";
		header('Content-Disposition: attachment;filename='.$fileName.'');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
	}
}
?>