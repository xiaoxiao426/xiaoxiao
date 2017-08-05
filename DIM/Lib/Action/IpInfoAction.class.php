<?php
class IpInfoAction extends CommonAction
{
	//添加单位关注ip
	public function insertConcern()
	{
		$model = M("UnitConcernIP");
		if(empty($model))
		{
			$this->error("数据库错误！");
		}
		$Unit = $_REQUEST['Unit'];
		$IP = $_REQUEST['IP'];
		$desp = $_REQUEST['description'];
		if(!isset($Unit) || strlen($Unit) < 1)
		{
			$this->error("请选择单位!");
		}
		//输入添加
		if(isset($IP) && strlen($IP) > 0)
		{
			$ips = split(",", $IP);
			$info = array();
			$k = 0;
			for($i=0;$i<count($ips);$i++)
			{
				$ips[$i] = trim($ips[$i]);
				if(strlen($ips[$i]) > 1)
				{
					$info[$k]['IP'] = ip2long($ips[$i]);
					$info[$k]['Unit'] = $Unit;
					$info[$k]['Description'] = $desp;
					$info[$k]['CreateTime'] = date("Y-m-d H:i:s");
					$info[$k]['UpdateTime'] = date("Y-m-d H:i:s");
					$k++;
				}
			}
			$res = $model->addAll($info);
			if($res === false)
			{
				$this->error("插入失败!");
			}

		}
		//文件上传
		if(!empty($_FILES) && count($_FILES) > 0) {
				
			$str=file_get_contents($_FILES['ipfile']['tmp_name']);
			if(strlen($str) > 1)
			{
				$str = str_replace("\r\n", "#", $str);
				$str = str_replace("\n", "#", $str);
				$str = str_replace("\r", "#", $str);
				$array = split('#',$str);
				fileWriteArray($array);
				$info = array();
				$k = 0;
				for($i=0;$i<count($array);$i++)
				{
					$infoA = split(" ",$array[$i]);
					$info[$k]['IP'] = ip2long($infoA[0]);
					$info[$k]['Unit'] = $Unit;
					$info[$k]['Description'] = $infoA[1];
					$info[$k]['CreateTime'] = date("Y-m-d H:i:s");
					$info[$k]['UpdateTime'] = date("Y-m-d H:i:s");
					$k++;
				}

				$res = $model->addAll($info);
				if($res === false)
				{
					$this->error("插入失败!");
				}
			}
		}
		$cmd = "wget -T 0 --output-document=/dev/null http://127.0.0.1/DIM_CHAIRS/DIM/index.php/Public/CasesShadowUpdate";
		$outputfile = "test.txt";
		$pidfile = "test.txt";
		exec(sprintf("%s > %s 2>&1 & echo $! > %s", $cmd, $outputfile, $pidfile));
		$this->redirect("unitIP", "");
	}
	//单位配置IP
	public function unitIP()
	{
		$model = M("UnitConcernIP");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$ip = $_REQUEST['ip'];
		if(strlen($ip) > 1)
		{
			$where = "IP=".ip2long($ip);
		}
		$list = $model->where($where)->select();
		if($list === false)
		{
			$this->error("数据库错误!");
		}
		$this->assign("list",$list);

		$model = M("Unit");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$unitList = $model->select();
		$this->assign("unitList",$unitList);
		$this->display();
	}

	//更新
	public function updateConcern()
	{
		$name="UnitConcernIP";
		$model = M ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		if($list === false)
		{
			$this->error("数据错误!".$model->getLastSql());
		}
		$this->redirect("unitIP", "");
	}
	public function ipConEdit()
	{
		$name="UnitConcernIP";
		$model = M ($name);
		$id = $_REQUEST['id'];
		$vo = $model->where("id=$id")->find();
		$this->assign("vo",$vo);
		$this->display();
	}
	//删除关注的ip
	public function conDelete()
	{
		$name="UnitConcernIP";
		$model = M ($name);
		if (!empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				$list=$model->where ( $condition )->delete();
				if ($list===false) {
					$this->error ('删除失败！');
				}
			} else {
				$this->error ( '非法操作' );
			}
		}
		else {
			$this->error("数据错误!");
		}

		$this->redirect("unitIP");
	}
	//删除
	public function delete() {
		//删除指定记录
		$name="IPInfo";
		$model = M ($name);
		if (!empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				$list=$model->where ( $condition )->delete();
				if ($list===false) {
					$this->error ('删除失败！');
				}

				//删除对应关系
				$sql = "delete from IPRoleRel where IPIndex=$id";
				$list = $model->query($sql);
				if ($list===false) {
					$this->error ('删除失败！');
				}

				//删除对应关系
				$sql = "delete from IPStaRel where IPIndex=$id";
				$list = $model->query($sql);
				if ($list===false) {
					$this->error ('删除失败！');
				}


			} else {
				$this->error ( '非法操作' );
			}
		}
		else {
			$this->error("数据错误!");
		}

		$this->redirect("IpInfo/reportIP");

	}

	//更新程序,关系先删后加
	public function update()
	{
		$name="IPInfo";
		$model = M ($name);
		$roleModel = M("IPRoleRel");
		$sateModel = M("IPStaRel");
		if(empty($model) || empty($roleModel) || empty($sateModel))
		{
			$this->error("数据错误!");
		}

		$id = $_REQUEST['id'];
		if(!isset($id))
		{
			$this->error("数据错误!");
		}
		$model->startTrans();
		$roleModel->startTrans();
		$sateModel->startTrans();
		//删除对应关系
		$sql = "delete from IPRoleRel where IPIndex=$id";
		$list = $roleModel->query($sql);
		if ($list===false) {
			$roleModel->rollback();
			$this->error ('更新失败！');
		}

		//删除对应关系
		$sql = "delete from IPStaRel where IPIndex=$id";
		$list = $sateModel->query($sql);
		if ($list===false) {
			$sateModel->rollback();
			$roleModel->rollback();
			$this->error ('更新失败！');
		}

		//将新关系插入
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}

		//保存当前数据对象
		$list=$model->save();
		if ($list===false) {
			$model->rollback();
			$sateModel->rollback();
			$roleModel->rollback();
			$this->error ('更新失败!');
		}
		$index = $_REQUEST['id'];
		//解析role
		$roles = $_REQUEST['SafeRole'];
		$roleModel->IPIndex = $index;

		for($i=0;$i<count($roles);$i++)
		{
			$roleModel->RoleIndex = $roles[$i];
			$res = $roleModel->add();
			if($res === false)
			{
				$roleModel->rollback();
				$model->rollback();
				$this->error("数据错误!");
			}
		}

		//解析role
		$roles = $_REQUEST['SafeSta'];
		$sateModel->IPIndex = $index;
		for($i=0;$i<count($roles);$i++)
		{
			$sateModel->StaIndex = $roles[$i];
			$res = $sateModel->add();
			if($res === false)
			{
				$sateModel->rollback();
				$roleModel->rollback();
				$model->rollback();
				$this->error("数据错误!");
			}
		}

		$roleModel->commit();
		$sateModel->commit();
		$model->commit();
		$this->redirect("IpInfo/reportIP");
	}
	//编辑
	function ipedit() {
		$model = M("IPSafeSta");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$list = $model->select();
		if($list === false)
		{
			$this->error("数据错误!");
		}

		$this->assign("SafeSta",$list);

		unset($model);
		//安全角色
		$model = M("IPSafeRole");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$list = $model->select();
		if($list === false)
		{
			$this->error("数据错误!");
		}

		$this->assign("SafeRole",$list);

		$name="IPInfo";
		$model = M ( $name );
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById ( $id );
		$this->assign ( 'vo', $vo );

		//获取对应的角色
		$sql = "select * from IPRoleRel where IPIndex = $id";
		$res = $model->query($sql);
		if($res == false)
		{
			$this->error("数据查询错误!");
		}
		$this->assign("iprole",$res);

		//获取对应的角色
		$sql = "select * from IPStaRel where IPIndex = $id";
		$res1 = $model->query($sql);
		if($res1 == false)
		{
			$this->error("数据查询错误!");
		}
		$this->assign("ipstat",$res1);

		$this->display ();
	}

	//首页
	public function reportIP()
	{
		$model = M("IPSafeSta");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$list = $model->select();
		if($list === false)
		{
			$this->error("数据错误!");
		}

		$this->assign("SafeSta",$list);


		unset($model);
		//安全角色
		$model = M("IPSafeRole");
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		$list = $model->select();
		if($list === false)
		{
			$this->error("数据错误!");
		}

		$this->assign("SafeRole",$list);

		//查找已存在IP，分页
		unset($model);
		$model = M("IPInfo");
		if(empty($model))
		{
			$this->error("数据错误!");
		}

		$map = array();
		if(isset($_REQUEST['IPF']) && $_REQUEST['IPF'])
		{
			$map['IP'] = ip2long($_REQUEST['IPF']);
		}
		$this->_list($model, $map);
		$this->display();
	}


	//
	protected function _list($model, $map, $sortBy = '', $asc = false) {
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
			$voList = $model->where($map)->limit($p->firstRow . ',' . $p->listRows)->select();

			if($voList === false)
			{
				$this->error("数据错误!");
			}

			if(count($voList) > 0)
			{
				$roleModel = M("IPRoleRel");
				$sateModel = M("IPStaRel");
				if(empty($roleModel) || empty($sateModel))
				{
					$this->error("数据错误!");
				}
			}
			//获取每个$voList的角色及状态
			for($i=0;$i<count($voList);$i++)
			{
				$ipIndex = $voList[$i]['id'];
				$voList[$i]['role'] = "";
				$roleList = $roleModel->where("IPIndex=$ipIndex")->join("inner join IPSafeRole on IPSafeRole.id = IPRoleRel.RoleIndex")->select();
				if($roleList === false)
				{
					$this->error("数据错误!");
				}
				for($j=0;$j<count($roleList);$j++)
				{
					$voList[$i]['role'] .= $roleList[$j]['IPRole']."   ";
				}


				$voList[$i]['sate'] = "";
				$roleList = $sateModel->where("IPIndex=$ipIndex")->join("inner join IPSafeSta on IPSafeSta.id = IPStaRel.StaIndex")->select();

				if($roleList === false)
				{
					$this->error("数据错误!");
				}

				for($j=0;$j<count($roleList);$j++)
				{
					$voList[$i]['sate'] .= $roleList[$j]['name']."   ";
				}
			}
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

	public function insert()
	{
		//	print_r($_REQUEST);

		$name="IPInfo";
		$model = D ($name);
		$roleModel = M("IPRoleRel");
		$sateModel = M("IPStaRel");
		if(empty($model) || empty($roleModel) || empty($sateModel))
		{
			$this->error("数据错误!");
		}
		$model->startTrans();
		$roleModel->startTrans();
		$sateModel->startTrans();
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}

		$model->IP = ip2long($model->IP);
		//保存当前数据对象
		$list=$model->add ();
		if ($list===false) {
			$model->rollback();
			$this->error ('新增失败!请确保IP没有重复!');
		}
		$index = $model->getLastInsID();
		//解析role
		$roles = $_REQUEST['SafeRole'];
		$roleModel->IPIndex = $index;

		for($i=0;$i<count($roles);$i++)
		{
			$roleModel->RoleIndex = $roles[$i];
			$res = $roleModel->add();
			if($res === false)
			{
				$roleModel->rollback();
				echo "<hr>";
				echo $roleModel->getLastSql();
				$model->rollback();
				$this->error("数据错误!");
			}
		}

		//解析role
		$roles = $_REQUEST['SafeSta'];
		$sateModel->IPIndex = $index;
		for($i=0;$i<count($roles);$i++)
		{
			$sateModel->StaIndex = $roles[$i];
			$res = $sateModel->add();
			if($res === false)
			{
				$sateModel->rollback();
				$roleModel->rollback();
				$model->rollback();
				$this->error("数据错误!");
			}
		}

		$roleModel->commit();
		$sateModel->commit();
		$model->commit();

		$this->redirect("/IpInfo/reportIP");
	}
}
?>