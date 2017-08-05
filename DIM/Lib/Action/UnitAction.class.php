<?php
class UnitAction extends CommonAction
{
	public function update(){
		$name=$this->getActionName();
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		if (false !== $list) {
			//成功提示
			$this->redirect("config");
		} else {
			//错误提示
			$this->error ('编辑失败!');
		}
	}


	public function delete() {
		//删除指定记录
		$name=$this->getActionName();
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
			if (isset ( $id )) {
				$condition = array ($pk => array ('in', explode ( ',', $id ) ) );
				$list=$model->where ( $condition )->setField ( 'Status', - 1 );
				if ($list!==false) {
					$this->redirect("config");
				} else {
					$this->error ('删除失败！');
				}
			} else {
				$this->error ( '非法操作' );
			}
		}
	}
	public function unitEdit()
	{
		$id = $_REQUEST['id'];
		if(!isset($id) || !$id)
		{
			$this->error("参数错误!");
		}
		$model = M("Unit");
		$vo = $model->where("id=$id")->select();
		$this->vo = $vo[0];
		$this->display();
	}

	//配置页
	public function config()
	{
		$model = M("Unit");
		if(empty($model))
		{
			$this->error("数据库错误!");
		}
		$unitName = $_POST['UnitName'];

		if(strlen($unitName) > 0)
		{
			$map['UnitName'] = array('like',"%".$unitName."%");
		}
		$list = $model->where($map)->select();
		if($list === false)
		{
			$this->error("数据库错误!");
		}
		$this->unitList = $list;
		$this->display();
	}

	public function insert()
	{
		$name="Unit";
		$model = D ($name);
		$res = $model->create();
		if($res === false)
		{
			$this->error("数据创建失败!");
		}

		$res = $model->add();
		if($res === false)
		{
			$this->error("添加失败!");
		}
		$this->redirect("config");
	}
}
?>