<?php
class ThreatTplAction extends CommonAction
{
	public function index(){

	}
	//
	protected function _tid_list($table,$where,$field, $limit,$sortBy = '', $asc = false) {
		$model=new Model();
		if(empty($model))
		{
			$this->error("数据错误!");
		}
		//排序字段 默认为主键名
		if (isset ( $_REQUEST ['_order'] )) {
			$order = $_REQUEST ['_order'];
		} else {
			$order = "Cnt";
		}
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset ( $_REQUEST ['_sort'] )) {
			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
		} else {
			$sort = $asc ? 'asc' : 'desc';
		}
		//取得满足条件的记录数
		$count = $model->table($table)->where ( $where )->count ( '*' );
		if ($count > 0) {
			import ( "@.ORG.Page" );
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else if($limit){
				$listRows = $limit;
			}
			else {
				$listRows = '';
			}
			$p = new Page ( $count, $listRows );
			//分页查询数据

			$voList = $model->table($table)->field($field)->where ( $where )->order($order." ".$sort)->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
			//fileWrite($model->getLastSql());
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
		$this->assign("total",$count);
		//echo $model->getLastSql();
		Cookie::set ( '_currentUrl_', __SELF__ );
		return;
	}

	//tid显示细则页面
	public function UnitTID()
	{
		$gid=$_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		if(!isset($gid) || !isset($tid))
		{
			$this->error("数据错误!");
		}
		$table="CaseUnit";
		$where = " GroupID=$gid and TID=$tid ";
		$field="*";
		if($gid==4)
		{
			$limit=5;
		}
		else {
			$limit=20;
		}
		if($_REQUEST['limit'])
		{
			$limit = $_REQUEST['limit'];
			$this->assign("limitFlag",1);
		}
		$this->_tid_list($table,$where,$field,$limit);
		//	$this->_tid_list($table,$where,$field);
		$this->assign("gid",$gid);
		$this->assign("tid",$tid);
		$this->display();
	}

	//DDoS
	public function DDoS()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		if(!isset($gid) || !isset($tid) || !isset($unit))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);
	//	$time = date();//LastTime
	//	$time = date("Y-m-d H:i:s",strtotime('-3 day'));
		$table="Cases";
		$where = " Cases.DeleteFlag = 0 AND Cases.TID = $tid AND Cases.Unit='$unit'";
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="`Cases`.ReserveValue5,`Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,
				`Cases`.StartTime,`Cases`.LastTime,`Cases`.ReserveValue1,`Cases`.ReserveValue2,`Cases`.ReserveValue3,`Cases`.ReserveValue4,ReserveValue7";

		$limit=20;

		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("gid",$gid);
		$this->display();
	}

	//非授权流量
	public function botnet()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		if(!isset($gid) || !isset($tid) || !isset($unit))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);

	//	$time = date("Y-m-d H:i:s",strtotime('-3 day'));
		$table="Cases";
		$where = " Cases.DeleteFlag = 0 AND Cases.TID = $tid AND Cases.Unit='$unit'";
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="`Cases`.ReserveValue5,`Cases`.ReserveValue1,`Cases`.ReserveValue2,`Cases`.id,`Cases`.CaseID,
				`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,	`Cases`.EvidenceNum";

		$limit=20;
		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("gid",$gid);
		$this->display();
	}

	//非授权流量
	public function botnetbd()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		$unitFlag=$_REQUEST['unitFlag'];
		if(!isset($gid) || !isset($tid))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);

		$table="Cases";
		if($unitFlag)
		{
			$where = "  Cases.DeleteFlag = 0 AND Cases.TID = $tid";
		}
		
		if(strlen($unit)>0)
		{
			$where = "  Cases.DeleteFlag = 0 AND Cases.TID = $tid AND Cases.Unit='$unit'";
		}

//		$time = date("Y-m-d H:i:s",strtotime('-3 day'));
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="`Cases`.ReserveValue5,`Cases`.ReserveValue1,`Cases`.ReserveValue2,`Cases`.id,`Cases`.CaseID,
				`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,	`Cases`.EvidenceNum,Unit";
		$limit=20;
		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("unitFlag",$unitFlag);
		$this->assign("gid",$gid);
		$this->display();
	}

	//非授权流量
	public function NBOSUahPort()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];

		$unitFlag=$_REQUEST['unitFlag'];
		if(!isset($gid) || !isset($tid))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);

		$table="Cases";
		if($unitFlag)
		{
			$where = "  Cases.DeleteFlag = 0 AND Cases.TID = $tid";
		}
		if(strlen($unit)>0)
		{
			$where = "  Cases.DeleteFlag = 0 AND Cases.TID = $tid AND Cases.Unit='$unit'";
		}

	//	$time = date("Y-m-d H:i:s",strtotime('-3 day'));
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="`Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.ReserveValue1,ReserveValue5,
				`Cases`.StartTime,`Cases`.LastTime,`Cases`.ReserveValue2,Unit";
		$limit=20;

		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("unitFlag",$unitFlag);
		$this->assign("gid",$gid);
		$this->display();
	}
	//恶意网页
	public function invasion()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		if(!isset($gid) || !isset($tid) || !isset($unit))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);
	//	$time = date("Y-m-d H:i:s",strtotime('-3 day'));//三天内事件		
		$table="Cases";
		$where = " Cases.DeleteFlag = 0 AND Cases.TID = $tid AND Cases.Unit='$unit'";
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="`Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.ReserveValue1,
				`Cases`.StartTime,`Cases`.LastTime,`Cases`.ReserveValue2";

		$limit=20;
		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("gid",$gid);
		$this->display();
	}
	//webshell
	public function webshell()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		if(!isset($gid) || !isset($tid) || !isset($unit))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);

		$table="Cases";
		$where = "DeleteFlag = 0 and  TID = $tid AND Cases.Unit='$unit'";
		
	//	$time = date("Y-m-d H:i:s",strtotime('-3 day'));
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="id,TcID,CaseID,Subject,ThreatSrcSummary,ThreatDstSummary,EvidenceNum,StartTime,LastTime,ReserveValue1,ReserveValue2";
		$limit=20;
		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("gid",$gid);
		$this->display();
	}

	//钓鱼网站
	public function phishing()
	{
		$gid = $_REQUEST['gid'];
		$tid=$_REQUEST['tid'];
		$unit=$_REQUEST['unit'];
		if(!isset($gid) || !isset($tid) || !isset($unit))
		{
			$this->error("数据错误!");
		}
		$unit=urldecode($unit);
	//	$time = date("Y-m-d H:i:s",strtotime('-3 day'));

		$table="Cases,CaseSrcDstRelation,ThreatSrc";
		$where = " CaseSrcDstRelation.CaseID = Cases.CaseID and ThreatSrc.EntityID = CaseSrcDstRelation.SrcID and ThreatDst.EntityID = CaseSrcDstRelation.DstID and Cases.DeleteFlag = 0 AND Cases.TID = $tid AND Cases.Unit='$unit'";
		$where .= " and GroupID=$gid AND (Cases.CaseType=0 or Cases.CaseType=1)";
		$field="`Cases`.id,`Cases`.CaseID,`Cases`.Subject,`Cases`.TID,`Cases`.StartTime,`Cases`.LastTime,`ThreatSrc`.`IPCnt`,`ThreatSrc`.`DomainCnt`,`ThreatDst`.`IPCnt` as dIPCnt";
		$limit=20;
		$this->_list($table,$where,$field,$limit);
		$this->assign("unit",$unit);
		$this->assign("tid",$tid);
		$this->assign("gid",$gid);
		$this->display();
	}
	protected function _list($table,$where,$field, $limit,$sortBy = '', $asc = false) {

		$model=new Model();
		if(empty($model))
		{
			$this->error("数据错误!");
		}
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
		$count = $model->table($table)->where ( $where )->count ( '*' );
		if ($count > 0) {
			import ( "@.ORG.Page" );
			//创建分页对象
			if (! empty ( $_REQUEST ['listRows'] )) {
				$listRows = $_REQUEST ['listRows'];
			} else if($limit){
				$listRows = $limit;
			}
			else {
				$listRows = '';
			}

			$p = new Page ( $count, $listRows );
			//分页查询数据

			$time = date("Y-m-d H:i:s",strtotime('-300 day'));;
			$where .=" AND LastTime > '$time'";
			$voList = $model->table($table)->field($field)->where ( $where )->order( "ReserveValue2 desc,ReserveValue1 desc,`Cases`.LastTime desc ")->limit($p->firstRow . ',' . $p->listRows)->findAll ( );
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
		//	echo $model->getLastSql();
		Cookie::set ( '_currentUrl_', __SELF__ );
		return;
	}


}
?>