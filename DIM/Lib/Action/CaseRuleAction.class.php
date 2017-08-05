<?php
class CaseRuleAction extends CommonAction {

	//获取案件分类表
	private function getClassType()
	{
		$model = M("ThreatClassType");
		$list = $model->select();
		$this->assign("threatClassType",$list);
	}

	//获取可配置参数
	public function getParam()
	{
		$new_list = array();


		$num = count($new_list);
		$new_list[$num] = array();
		$new_list[$num]['Field'] = "EvidenceNum";
		$new_list[$num]['Type'] = 'int';
		$new_list[$num]['Comment'] = "事件数";
			
		return $new_list;
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


	public function insert(){
		$name="CaseRule";
		$model = D ($name);
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		$list=$model->add();
		if ($list===false) { //保存成功
			$this->error ('新增失败!'.$model->getDbError());
		}
		else
		{
			$this->success("新增成功!");
		}
	}

	function update() {
		$name="CaseRule";
		$model = D ( $name );
		if (false === $model->create ()) {
			$this->error ( $model->getError () );
		}
		// 更新数据
		$list=$model->save ();
		if (false !== $list) {
			//成功提示
			$this->redirect("CaseRule/config");
		} else {
			//错误提示
			$this->error ('编辑失败!');
		}
	}

	function edit() {
		$name="CaseRule";
		$model = M ( $name );
		$id = $_REQUEST [$model->getPk ()];
		$vo = $model->getById ( $id );
		$this->assign ( 'vo', $vo );
		$this->display ();
	}


	public function delete() {
		//删除指定记录
		$name="CaseRule";
		$model = M ($name);
		if (! empty ( $model )) {
			$pk = $model->getPk ();
			$id = $_REQUEST [$pk];
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

	public function config()
	{

		$this->getClassType();//获取分类

		$sList = $this->getParam();
		if(!is_array($sList))
		{
			$this->error("字段获取失败!");
		}

		$this->assign("sList",$sList);

		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>';
		echo "<script type=\"text/javascript\">var summary_json = ".json_encode($sList)."</script>";
		$model = M("CaseRule");
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

}
?>