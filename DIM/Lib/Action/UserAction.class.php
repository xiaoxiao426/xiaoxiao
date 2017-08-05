<?php
// 后台用户模块
class UserAction extends CommonAction {
	function _filter(&$map){
		$map['id'] = array('egt',2);
		$map['account'] = array('like',"%".$_POST['account']."%");
	}

	//获取档位名称
	public function getUnit()
	{
		$model = M("Unit");
		$list = $model->select();		
		$this->assign("UnitList",$list);
	}
	//协同用户
	public function joint()
	{
		$name="ContactUsers";
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$field = "id,name,organization,mobilePhone,emailAddress";
		if($_REQUEST['name'])
		{
			$map=array();
			$map['name'] = array("like","%".$_REQUEST['name']."%");
			$this->assign("name",$_REQUEST['name']);
			$list = $model->where($map)->field($field)->select();
		}
		else {
			$list = $model->field($field)->select();
		}
		$this->assign("list",$list);
		$this->display();
	}
	//本地用户
	public function localUser()
	{
		$name = "Users";
		$model = M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$field = "id,name,power,mobilePhone,emailAddress";
		if($_REQUEST['name'])
		{
			$map=array();
			$map['name'] = array("like","%".$_REQUEST['name']."%");
			$map['power'] = array('lt',3);
			$this->assign("name",$_REQUEST['name']);
			$list = $model->where($map)->field($field)->select();
		}
		else {
			$map=array();
			$map['power'] = array('lt',3);
			$list = $model->where($map)->field($field)->select();
		}
		//echo $model->getLastSql();
		$this->assign("list",$list);
		$this->display();
	}
	// 检查帐号
	public function checkAccount() {
		if(!preg_match('/^[a-z]\w{4,}$/i',$_POST['account'])) {
			$this->error( '用户名必须是字母，且5位以上！');
		}
		$User = M("User");
		// 检测用户名是否冲突
		$name  =  $_REQUEST['account'];
		$result  =  $User->getByAccount($name);
		if($result) {
			$this->error('该用户名已经存在！');
		}else {
			$this->success('该用户名可以使用！');
		}
	}

	// 插入数据
	public function insert() {
		// 创建数据对象
		$User	 =	 D("Users");
		if(!$User->create()) {
			$this->error($User->getError());
		}else{
			// 写入帐号数据
			if($result	 =	 $User->add()) {
				$this->addRole($result);
				$this->success('用户添加成功！');
			}else{
				$this->error('用户添加失败！');
			}
		}
	}

	protected function addRole($userId) {
		//新增用户自动加入相应权限组
		$RoleUser = M("RoleUser");
		$RoleUser->user_id	=	$userId;
		// 默认加入网站编辑组
		$RoleUser->role_id	=	3;
		$RoleUser->add();
	}

	function update() {
		$name="Users";
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
	//重置密码
	public function resetPwd()
	{
		$id  =  $_POST['id'];
		$password = $_POST['password'];
		if(''== trim($password)) {
			$this->error('密码不能为空！');
		}
		$User = M('User');
		$User->password	=	md5($password);
		$User->id			=	$id;
		$result	=	$User->save();
		if(false !== $result) {
			$this->success("密码修改为$password");
		}else {
			$this->error('重置密码失败！');
		}
	}

	public function getOrgnization()
	{
		$name= "PeerOrgnization";
		$model=M($name);
		if(empty($model))
		{
			$this->error("数据错误!");
			return;
		}
		$field="id,OrgName";
		$list = $model->field($field)->select();
		$this->assign("list",$list);
	}
	//本地用户添加
	public function localAdd()
	{
		$this->getUnit();
		$this->display();
	}

	//协查用户添加
	public function jointAdd()
	{
		$this->getOrgnization();
		$this->display();
	}

	//本地用户更新
	public function localUpdate()
	{
		//B('FilterString');
		$name="Users";
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}

		$id=$model->id;
		if(!$id)
		{
			$this->error("用户信息错误!");
		}

		$ids=$_REQUEST['UnitList'];
		if(!$ids)
		{
			$this->error("请选择组织!");
		}

		// 更新数据
		$list=$model->save ();
		if (false !== $list) {
			$email = $_REQUEST['emailAddress'];

			$sql = "delete from UnitUsersRelation where UserId=$id";
			$res = $model->query($sql);
			if($res === false)
			{
				$this->error("编辑失败!");
			}

			for($i=0;$i<count($ids);$i++)
			{
				$unitid=$ids[$i];
				$uname = $this->getUnitNameByUnitId($unitid);
				if($uname == -1)
				{
					$this->error ('编辑失败!'.$model->getLastSql());
				}
				$sql = "INSERT INTO `UnitUsersRelation` VALUES (NULL,$unitid,'$uname','$email',$id,0);";
				$res = $model->query($sql);
				if($res !== false)
				{

				}
				else {
					$this->error ('编辑失败!'.$model->getLastSql());
				}

				if(isset($_REQUEST['AutoFlag']))//自动发送警报
				{
					$AutoFlag = $_REQUEST['AutoFlag'];
					$sql = "update `Unit` set AutoFlag=$AutoFlag where id=$unitid;";
					$res = $model->query($sql);
					if($res !== false)
					{

					}
					else {
						$this->error ('新增失败!'.$model->getLastSql());
					}
				}
			}

		} else {
			//错误提示
			$this->error ('编辑失败!');
		}

		$this->assign ( 'jumpUrl', "__ROOT__/DIM/index.php/User/localUser" );
		$this->success ('编辑成功!');
	}
	//协查用户更新
	public function jointUpdate()
	{
		$name="ContactUsers";
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		if (false !== $list) {
			//成功提示
			$this->assign ( 'jumpUrl', "__ROOT__/DIM/index.php/User/joint" );
			$this->success ('编辑成功!');
		} else {
			//错误提示
			$this->error ('编辑失败!');
		}
	}

	//根据单位id获取单位名称
	public function getUnitNameByUnitId($uid)
	{
		if(!isset($uid))
		{
			return -1;
		}
		$model = M("Unit");
		if(empty($model))
		{
			return -1;
		}
		$list = $model->where("id=$uid")->select();
		if($list === false || count($list) != 1)
		{
			return -1;
		}
		return $list[0]['UnitName'];
	}

	//本地用户插入
	public function localInsert()
	{
		C("TOKEN_ON",false);
		$name="Users";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$ids=$_REQUEST['UnitList'];
		if(!$ids)
		{
			$this->error("请选择组织!");
		}
		$model->password =md5($model->password);
		//保存当前数据对象
		$list=$model->add ();
		if ($list!==false) { //保存成功
			$uid=$model->getLastInsID();
			$email = $_REQUEST['emailAddress'];
			for($i=0;$i<count($ids);$i++)
			{
				$id=$ids[$i];
				$uname = $this->getUnitNameByUnitId($id);
				if($uname == -1)
				{
					$this->error ('新增失败!'.$model->getLastSql());
				}
				$sql = "INSERT INTO `UnitUsersRelation` VALUES (NULL,$id,'$uname','$email',$uid,0);";
				$res = $model->query($sql);
				if($res !== false)
				{

				}
				else {
					$this->error ('新增失败!'.$model->getLastSql());
				}
				if(isset($_REQUEST['AutoFlag']) && $_REQUEST['AutoFlag'])//自动发送警报
				{
					$sql = "update `Unit` set AutoFlag=1 where id=$id;";
					$res = $model->query($sql);
					if($res !== false)
					{

					}
					else {
						$this->error ('新增失败!'.$model->getLastSql());
					}
				}

			}

		} else {
			//失败提示
			$this->error ('新增失败2!'.$model->getLastSql());
		}

		$this->assign ( 'jumpUrl', "__ROOT__/DIM/index.php/User/localUser" );
		$this->success ('新增成功!');
	}

	//协查用户插入
	public function jointInsert()
	{
		$name="ContactUsers";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		//保存当前数据对象
		$list=$model->add ();
		if ($list!==false) { //保存成功
			$this->assign ( 'jumpUrl', "__ROOT__/DIM/index.php/User/joint" );
			$this->success ('新增成功!');
		} else {
			//失败提示
			$this->error ('新增失败!');
		}
	}

	//本地用户删除
	public function localDelete()
	{
		if(!isset($_SESSION["userPower"]) || $_SESSION["userPower"] < 2)
		{
			$this->error ( '权限不够' );
		}
		$name="Users";
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = "id";
			$id = $_REQUEST ["id"];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
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
	//协查用户删除
	public function jointDelete()
	{
		$name="ContactUsers";
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = "id";
			$id = $_REQUEST ["id"];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
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

	//编辑
	function edit() {
		$name="Users";
		$model = M ( $name );
		$id = $_REQUEST [$model->getPk ()];
		if(!$id)
		{
			$id = $_SESSION[C('USER_AUTH_KEY')];
		}

		$vo = $model->getById ( $id );
		//print_r($model->getLastSql());
		$this->assign ( 'vo', $vo );
		$this->display ();
	}

	//本地编辑
	function localEdit() {
		//$this->getOrgnization();
		$this->getUnit();
		$name="Users";
		$model = M ( $name );
		$id = $_REQUEST ["id"];
		if(!$id)
		{
			$id = $_SESSION[C('USER_AUTH_KEY')];
		}

		$vo = $model->getById ( $id );
		$this->assign ( 'vo', $vo );
		$sql = "select UnitId from UnitUsersRelation where UserId=$id;";
		$res = $model->query($sql);
		if($res ===false)
		{
			$this->error($model->getLastSql());
		}
		$this->assign("unitids",$res);
		
		
		$sql = "select AutoFlag from Unit where UserId=$id order by AutoFlag desc limit 0,1;";
		$res = $model->query($sql);
		if($res ===false)
		{
			$this->error($model->getLastSql());
		}
		$this->assign("AutoFlag",0);
		if(count($res) > 0)
		{
			$this->assign("AutoFlag",$res[0]['AutoFlag']);
		}
		
		$this->display ();
	}
	//本地编辑
	function jointEdit() {
		$this->getOrgnization();
		$name="ContactUsers";
		$model = M ( $name );
		$id = $_REQUEST ["id"];
		if(!$id)
		{
			$id = $_SESSION[C('USER_AUTH_KEY')];
		}

		$vo = $model->getById ( $id );
		$this->assign ( 'vo', $vo );
		$this->display ();
	}
}
?>