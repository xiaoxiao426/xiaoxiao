<?php
// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2007 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$


function getCaseRule($id)
{
	
	if (empty ( $id )) {
		return '无';
	}
	
	$model = M ( "CaseRule" );
	$list = $model->getField ( 'id,Note' );
	fileWrite($list);
	$name = $list [$id];

	return $name;
}
function EchoPort($port)
{
if(!$port) echo "*";
else echo $port;
}
//输出，若为空则输出0
function myEcho($num)
{
	if(!$num) echo 0;
	else echo $num;
}
//根据数字来获取IP
function getTcIdConfig()
{
	$class = Array();
	//$class[0]['name'] = "spam";
	//$class[0]['TcID'] = 5;

	$class[1]['name'] = "phishing";
	$class[1]['TcID'] = 4;

	$class[2]['name'] = "virus";
	$class[2]['TcID'] = 2;

	$class[3]['name'] = "DDoS";
	$class[3]['TcID'] = 3;

	$class[4]['name'] = "scan";
	$class[4]['TcID'] = 6;

	$class[5]['name'] = "invasion";
	$class[5]['TcID'] = 7;

	$class[6]['name'] = "platform";
	$class[6]['TcID'] = 1;

	$class[7]['name'] = "infomation";
	$class[7]['TcID'] = 9;
	return $class;
}

function fileWrite($str,$fileName="test.txt",$pattern="a+")
{
	$file = fopen($fileName,$pattern);
	fwrite($file, date("Y:m:d H:i:s  ").$str."\r\n");
	fclose($file);
}
function xmlWrite($str,$fileName="test.xml",$pattern="a+")
{
	$file = fopen($fileName,$pattern);
	fwrite($file, $str);
	fclose($file);
}


function fileWriteArray($arr,$fileName="test.txt")
{
	$string_start   = "<?php\n";
	$string_process = var_export($arr, TRUE);
	$string_end     = "\n?>";
	$string         = $string_start.$string_process.$string_end;
	
	$file = fopen($fileName,'a+');
	fwrite($file, date("Y:m:d H:i:s").$string."\r\n");
	fclose($file);
	///echo file_put_contents('test_array'.date("H-i-s").'.txt', $string);
}

function getTypeColor($i)
{
	$colorArray = array('588526','2AD62A','ee99dc','1D8BD1','DBDC25','A186BE','9D080D'
	,'B3AA00','F1683C','8E468E','D64646','008E8E','FF8E46','8BBA00','F6BD0F','AFD8F8');
	return $colorArray[$i%sizeof($colorArray)];
}
//公共函数
function toDate($time, $format = 'Y-m-d H:i:s') {
	if (empty ( $time )) {
		return '';
	}
	$format = str_replace ( '#', ':', $format );
	return date ($format, $time );
}

function get_case_status_array()
{
	$case_status = array();
	$i=0;
	$case_status[$i]['id']=$i;
	$case_status[$i]['name']="open";
	$case_status[$i]['dep']="处理中";
	
	
	$i=1;
	$case_status[$i]['id']=$i;
	$case_status[$i]['name']="suspended";
	$case_status[$i]['dep']="挂起";
	
	$i=2;
	$case_status[$i]['id']=$i;
	$case_status[$i]['name']="waiting";
	$case_status[$i]['dep']="等待中";
	
	$i=3;
	$case_status[$i]['id']=$i;
	$case_status[$i]['name']="resolved";
	$case_status[$i]['dep']="已处理";
	
	
	return $case_status;
}
function get_case_statue($eng)
{
	$chinese = "";
	switch($eng)
	{
		case "open":
			$chinese = "待处理";
			break;
		case "new":
			$chinese = "新建";
			break;
		case "waiting":
			$chinese = "跟踪中";
			break;
		case "suspended":
			$chinese = "挂起";
			break;
		case "resolved":
			$chinese = "已处理";
			break;
		default:
			$chinese = "无";
	}
	return $chinese;
}
function get_client_ip() {
	if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" ))
	$ip = getenv ( "HTTP_CLIENT_IP" );
	else if (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" ))
	$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
	else if (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" ))
	$ip = getenv ( "REMOTE_ADDR" );
	else if (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" ))
	$ip = $_SERVER ['REMOTE_ADDR'];
	else
	$ip = "unknown";
	return ($ip);
}
// 缓存文件
function cmssavecache($name = '', $fields = '') {
	$Model = D ( $name );
	$list = $Model->select ();
	$data = array ();
	foreach ( $list as $key => $val ) {
		if (empty ( $fields )) {
			$data [$val [$Model->getPk ()]] = $val;
		} else {
			// 获取需要的字段
			if (is_string ( $fields )) {
				$fields = explode ( ',', $fields );
			}
			if (count ( $fields ) == 1) {
				$data [$val [$Model->getPk ()]] = $val [$fields [0]];
			} else {
				foreach ( $fields as $field ) {
					$data [$val [$Model->getPk ()]] [] = $val [$field];
				}
			}
		}
	}
	$savefile = cmsgetcache ( $name );
	// 所有参数统一为大写
	$content = "<?php\nreturn " . var_export ( array_change_key_case ( $data, CASE_UPPER ), true ) . ";\n?>";
	file_put_contents ( $savefile, $content );
}

function cmsgetcache($name = '') {
	return DATA_PATH . '~' . strtolower ( $name ) . '.php';
}
function getStatus($status, $imageShow = true) {
	switch ($status) {
		case 0 :
			$showText = '禁用';
			$showImg = '<IMG SRC="' . WEB_PUBLIC_PATH . '/Images/locked.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="禁用">';
			break;
		case 2 :
			$showText = '待审';
			$showImg = '<IMG SRC="' . WEB_PUBLIC_PATH . '/Images/prected.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="待审">';
			break;
		case - 1 :
			$showText = '删除';
			$showImg = '<IMG SRC="' . WEB_PUBLIC_PATH . '/Images/del.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="删除">';
			break;
		case 1 :
		default :
			$showText = '正常';
			$showImg = '<IMG SRC="' . WEB_PUBLIC_PATH . '/Images/ok.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="正常">';

	}
	return ($imageShow === true) ?  $showImg  : $showText;

}
function getDefaultStyle($style) {
	if (empty ( $style )) {
		return 'blue';
	} else {
		return $style;
	}

}
function IP($ip = '', $file = 'UTFWry.dat') {
	$_ip = array ();
	if (isset ( $_ip [$ip] )) {
		return $_ip [$ip];
	} else {
		import ( "ORG.Net.IpLocation" );
		$iplocation = new IpLocation ( $file );
		$location = $iplocation->getlocation ( $ip );
		$_ip [$ip] = $location ['country'] . $location ['area'];
	}
	return $_ip [$ip];
}


function get_pawn($pawn) {
	if ($pawn == 0)
	return "<span style='color:green'>没有</span>";
	else
	return "<span style='color:red'>有</span>";
}
function get_patent($patent) {
	if ($patent == 0)
	return "<span style='color:green'>没有</span>";
	else
	return "<span style='color:red'>有</span>";
}

function get_case_unit_name($gid)
{
	$name = "";
	switch($gid)
	{
		case 1:
			$name = "挂马网站";
			break;
		case 2:
			$name = "内部后门";
			break;
		case 3:
			$name = "攻击";
			break;
		case 4:
			$name = "外部威胁点";
			break;
		default:
			break;
	}
	return $name;
}

//获取案件分类在不同group下显示的分类名
function get_threat_group_name($gid,$tid) {
	if ( empty($tid)) {
		return '未分类';
	}
	if(empty($gid))
	{
		return get_threat_type_name($tid);
	}

	$model = M ( "GroupTID" );
	$map = array();
	$map['GroupID'] = $gid;
	$map['TID'] = $tid;
	$list = $model->where($map)->select();
	if($list === false)
	{
		return "分类错误";
	}
	//fileWrite($model->getLastSql());
	if(count($list) == 0)
	{
		return get_threat_type_name($tid);
	}
	return $list[0]['Name'];
}

//获取案件分类
function get_threat_type_name($id) {
	if (empty ( $id )) {
		return '未分类';
	}
	if($id ==-1)
	{
		return "合并事件";
	}
	if (isset ( $_SESSION ['threatTypeList'] )) {
		return $_SESSION ['threatTypeList'] [$id];
	}
	$model = M ( "ThreatType" );
	$list = $model->getField ( 'TID,Name' );
	//	fileWrite($model->getLastSql());
	$_SESSION ['threatTypeList'] = $list;
	$name = $list [$id];

	return $name;
}

//获取案件分类
function get_threat_class_type_name($id) {
	if (empty ( $id )) {
		return '未分类';
	}
if($id ==-1)
	{
		return "合并事件";
	}
	if (isset ( $_SESSION ['ThreatClassTypeList'] )) {
		return $_SESSION ['ThreatClassTypeList'] [$id];
	}
	$model = M ( "ThreatClassType" );
	$list = $model->getField ( 'TcID,Name' );
	//	fileWrite($model->getLastSql());
	$_SESSION ['ThreatClassTypeList'] = $list;
	$name = $list [$id];
	return $name;
}


//获取证据分类
function get_evidence_type_name($id) {
	if (empty ( $id )) {
		return '未分类';
	}
	if (isset ( $_SESSION ['EvidenceTypeList'] )) {
		return $_SESSION ['EvidenceTypeList'] [$id];
	}
	$model = M ( "EvidenceType" );
	$list = $model->getField ( 'EvidenceTypeID,EvidenceTypeName' );
	//	fileWrite($model->getLastSql());
	$_SESSION ['EvidenceTypeList'] = $list;
	$name = $list [$id];

	return $name;
}


function getCardStatus($status) {
	switch ($status) {
		case 0 :
			$show = '未启用';
			break;
		case 1 :
			$show = '已启用';
			break;
		case 2 :
			$show = '使用中';
			break;
		case 3 :
			$show = '已禁用';
			break;
		case 4 :
			$show = '已作废';
			break;
	}
	return $show;

}

function showStatus($status, $id) {
	switch ($status) {
		case 0 :
			$info = '<a href="javascript:resume(' . $id . ')">恢复</a>';
			break;
		case 2 :
			$info = '<a href="javascript:pass(' . $id . ')">批准</a>';
			break;
		case 1 :
			$info = '<a href="javascript:forbid(' . $id . ')">禁用</a>';
			break;
		case - 1 :
			$info = '<a href="javascript:recycle(' . $id . ')">还原</a>';
			break;
	}
	return $info;
}

/**
 +----------------------------------------------------------
 * 获取登录验证码 默认为4位数字
 +----------------------------------------------------------
 * @param string $fmode 文件名
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function build_verify($length = 4, $mode = 1) {
	return rand_string ( $length, $mode );
}

function sort_by($array, $keyname = null, $sortby = 'asc') {
	$myarray = $inarray = array ();
	# First store the keyvalues in a seperate array
	foreach ( $array as $i => $befree ) {
		$myarray [$i] = $array [$i] [$keyname];
	}
	# Sort the new array by
	switch ($sortby) {
		case 'asc' :
			# Sort an array and maintain index association...
			asort ( $myarray );
			break;
		case 'desc' :
		case 'arsort' :
			# Sort an array in reverse order and maintain index association
			arsort ( $myarray );
			break;
		case 'natcasesor' :
			# Sort an array using a case insensitive "natural order" algorithm
			natcasesort ( $myarray );
			break;
	}
	# Rebuild the old array
	foreach ( $myarray as $key => $befree ) {
		$inarray [] = $array [$key];
	}
	return $inarray;
}

/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
	$str = '';
	switch ($type) {
		case 0 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 1 :
			$chars = str_repeat ( '0123456789', 3 );
			break;
		case 2 :
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3 :
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($len > 10) { //位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat ( $chars, $len ) : str_repeat ( $chars, 5 );
	}
	if ($type != 4) {
		$chars = str_shuffle ( $chars );
		$str = substr ( $chars, 0, $len );
	} else {
		// 中文随机字
		for($i = 0; $i < $len; $i ++) {
			$str .= msubstr ( $chars, floor ( mt_rand ( 0, mb_strlen ( $chars, 'utf-8' ) - 1 ) ), 1 );
		}
	}
	return $str;
}
function pwdHash($password, $type = 'md5') {
	return hash ( $type, $password );
}
?>