<!--<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">-->

<html xmlns="http://www.w3.org/1999/xhtml">


<?php
header("Content-Type:text/html;charset=utf8");
include "page.class.php";
include "calendar.html";
?>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>安全事件查询</title>	
	
	<link rel="stylesheet" href="css/table_style1.css" type="text/css" />
	<link rel="stylesheet" href="css/fp_style.css" type="text/css" />
	<link href="../mongo_search/main-squished.css" rel="stylesheet" type="text/css" />
	 <style type="text/css">
	 body
	 {text-align:center;}
	 #tbs{
	 word-wrap:break-word;
	 table-layout:fixed;	
	 }
   table th,table td {
   word-wrap:break-word;
   table-layout:fixed;  
   }
    </style>
	 <script type="text/javascript">
window.onload = function(){
//document.getElementByName('Reporter').value = $_GET['Reporter'];

var trs = document.getElementById('tbs').getElementsByTagName('tr');
for(var i=0;i<trs.length;i++){
   if(i%2 == 0)
       trs[i].style.backgroundColor = 'rgb(255, 255, 255)';
	   else
	   trs[i].style.backgroundColor = '';  
   
   }
   
  /* 
  document.getElementById('sbt').onclick = function () {
     var val = document.getElementById('type').value;
	 
      if(val == 110){
	 
	  document.getElementByName('hostname').style.display = "";
	  
	  }
   };
   */
      
   
};


	
<?php 
	
    //$query1 = array('Reporter'=>$_POST["Reporter"],'Type'=>$_POST["type"]);
    //$query2 = array('StartTime'=>array("$gt" => 2015-01-20,"$lte" => 2016-01-20));
     
	 
	//$query2 = array('StartTime'=>array('$gte' => $_GET["StartTime"]),'EndTime'=>array('$lte' => $_GET["LastTime"]));
	//$query = array('i'=>array('$gt'=>20,"\$lte"=>30)); 
	//$query2 = array('Type'=>$_POST["type"],'Reporter'=>$_POST["Reporter"],'ThreatSrcIP'=>ip2long($_POST['srcip']),'ThreatDstIP'=>ip2long($_POST['dstip']),'StartTime'=>array('$gte' => $_POST["StartTime"],'$lte' => $_POST["LastTime"]));

	
    $tmp = $_GET;
	$whr = array();
	$whr_time1 = array();
	$whr_time2 = array();
	$whr2 = array();
	$whr3 = array();
	$args = "";
	$r = "";
	$t = "";
	$sr = "";
	$ds = "";
	$st = "";
	$lt = "";
	
	// $ii="219.219.191.247";
	
	// echo ip2long($ii);
	// echo "<hr/>";
      
		if(!empty($tmp['Reporter']))
		{
			$whr['Reporter'] = "{$tmp["Reporter"]}";
			//$args .= "&Reporter={$tmp['Reporter']}";
			$r = $tmp['Reporter'];
		}
		if(!empty($tmp['type']))
		{
			$whr['Type'] = "{$tmp["type"]}";
			///$args .= "&type = {$tmp['type']}";
			$t = $tmp['type'];		
		}
		if(!empty($tmp['srcip']))
		{
		$tmp_srcip = ip2long($tmp['srcip']);
		$whr['ThreatSrcIP'] = "{$tmp_srcip}";
		//$args .= "&srcip = {$tmp_srcip}";
		$sr = $tmp['srcip'];
		}
		if(!empty($tmp['dstip']))
		{
		$tmp_dstip = ip2long($tmp['dstip']);
		
		$whr['ThreatDstIP'] = "{$tmp_dstip}";
		//$args .= "&dstip = {$tmp_dstip}";
		$ds = $tmp['dstip'];
		}
		if(!empty($tmp['StartTime']))
		{
		   $whr_time1['$gt'] = "{$tmp['StartTime']}";
		   $whr['StartTime'] = $whr_time1;
		   //var_dump($whr2);
		   $st = $tmp['StartTime'];
		  
		}
		if(!empty($tmp['LastTime']))
		{
		   $whr_time2['$lt'] = "{$tmp['LastTime']}";
		   $whr['EndTime'] = $whr_time2;
		   $lt = $tmp['LastTime'];
		}		
	
?>

	
	 </script>
</head>

<body  style="text-align:center;align:center;background: #eaeaea;background-repeat: repeat-y;">

<div style="align:center; text-align: center;">
<div class="titlebox" align="center" style="margin-bottom: -0.5em;margin-top:-1em;margin-left: 1em;width:100em;border:2px #eaeaea solid;display:block;">
<form align="center" method="get" id="search_content"   width="100em" action=""> 

<!--<form align="center" method="post" id="search_content" width=100em action="12mongodb.php"> -->
<div>
	<h3 style="align:centre;text-align:center;font-size:30px;"><strong>安全事件的查询</strong></h3>
	<!--<table align="center" style="text-align:left;margin-left:10%;"> -->
	
	 <table>

	<tr><td>
	Reporter: </td><td value="<?php echo $r;?>"><select id="Reporter" name="Reporter" style="align:center; width: 12em; text-align: center;">
	<option value="" <?php if($r == "") echo "selected='selected'";?>>全部</option> 
	<option value="MALW-SH-00" <?php if("MALW-SH-00" ==$r) echo "selected='selected'";?>>上海交大</option>
	<option value="NBOS-26-01" <?php if("NBOS-26-01" ==$r) echo "selected='selected'";?>>NBOS-26-01</option>	
	<option value="CHAR-NJ-01" <?php if("CHAR-NJ-01" ==$r) echo "selected='selected'";?>>CHAR-NJ-01</option>
	<option value="CHAR-JL-01" <?php if("CHAR-JL-01" ==$r) echo "selected='selected'";?>>CHAR-JL-01</option>
	<option value="NBOS-01-01" <?php if("NBOS-01-01" ==$r) echo "selected='selected'";?>>NBOS-01-01</option>
	<option value="NBOS-02-01" <?php if("NBOS-02-01" ==$r) echo "selected='selected'";?>>NBOS-02-01</option>
	<option value="NBOS-03-01" <?php if("NBOS-03-01" ==$r) echo "selected='selected'";?>>NBOS-03-01</option>
	<option value="NBOS-04-01" <?php if("NBOS-04-01" ==$r) echo "selected='selected'";?>>NBOS-04-01</option>
	<option value="NBOS-05-01" <?php if("NBOS-05-01" ==$r) echo "selected='selected'";?>>NBOS-05-01</option>
	<option value="NBOS-06-01" <?php if("NBOS-06-01" ==$r) echo "selected='selected'";?>>NBOS-06-01</option>
	<option value="NBOS-07-01" <?php if("NBOS-07-01" ==$r) echo "selected='selected'";?>>NBOS-07-01</option>
    <option value="NBOS-08-01" <?php if("NBOS-08-01" ==$r) echo "selected='selected'";?>>NBOS-08-01</option>	
	<option value="NBOS-09-01" <?php if("NBOS-09-01" ==$r) echo "selected='selected'";?>>NBOS-09-01</option>
	<option value="NBOS-10-01" <?php if("NBOS-10-01" ==$r) echo "selected='selected'";?>>NBOS-10-01</option>
	<option value="NBOS-11-01" <?php if("NBOS-11-01" ==$r) echo "selected='selected'";?>>NBOS-11-01</option>
	<option value="NBOS-12-01" <?php if("NBOS-12-01" ==$r) echo "selected='selected'";?>>NBOS-12-01</option>
	<option value="NBOS-13-01" <?php if("NBOS-13-01" ==$r) echo "selected='selected'";?>>NBOS-13-01</option>
	<option value="NBOS-14-01" <?php if("NBOS-14-01" ==$r) echo "selected='selected'";?>>NBOS-14-01</option>
	<option value="NBOS-15-01" <?php if("NBOS-15-01" ==$r) echo "selected='selected'";?>>NBOS-15-01</option>
	<option value="NBOS-16-01" <?php if("NBOS-16-01" ==$r) echo "selected='selected'";?>>NBOS-16-01</option>
	<option value="NBOS-17-01" <?php if("NBOS-17-01" ==$r) echo "selected='selected'";?>>NBOS-17-01</option>
	<option value="NBOS-18-01" <?php if("NBOS-18-01" ==$r) echo "selected='selected'";?>>NBOS-18-01</option>
	<option value="NBOS-19-01" <?php if("NBOS-19-01" ==$r) echo "selected='selected'";?>>NBOS-19-01</option>
	<option value="NBOS-20-01" <?php if("NBOS-20-01" ==$r) echo "selected='selected'";?>>NBOS-20-01</option>
	<option value="NBOS-21-01" <?php if("NBOS-21-01" ==$r) echo "selected='selected'";?>>NBOS-21-01</option>
	<option value="NBOS-22-01" <?php if("NBOS-22-01" ==$r) echo "selected='selected'";?>>NBOS-22-01</option>
	<option value="NBOS-23-01" <?php if("NBOS-23-01" ==$r) echo "selected='selected'";?>>NBOS-23-01</option>
	<option value="NBOS-24-01" <?php if("NBOS-24-01" ==$r) echo "selected='selected'";?>>NBOS-24-01</option>
    <option value="NBOS-25-01" <?php if("NBOS-25-01" ==$r) echo "selected='selected'";?>>NBOS-25-01</option>
    <option value="NBOS-26-01" <?php if("NBOS-26-01" ==$r) echo "selected='selected'";?>>NBOS-26-01</option>
    <option value="NBOS-27-01" <?php if("NBOS-27-01" ==$r) echo "selected='selected'";?>>NBOS-27-01</option>
    <option value="NBOS-28-01" <?php if("NBOS-28-01" ==$r) echo "selected='selected'";?>>NBOS-28-01</option>
    <option value="NBOS-29-01" <?php if("NBOS-29-01" ==$r) echo "selected='selected'";?>>NBOS-29-01</option>
    <option value="NBOS-30-01" <?php if("NBOS-30-01" ==$r) echo "selected='selected'";?>>NBOS-30-01</option>
    <option value="NBOS-31-01" <?php if("NBOS-31-01" ==$r) echo "selected='selected'";?>>NBOS-31-01</option>
    <option value="NBOS-32-01" <?php if("NBOS-32-01" ==$r) echo "selected='selected'";?>>NBOS-32-01</option>	
    <option value="NBOS-33-01" <?php if("NBOS-33-01" ==$r) echo "selected='selected'";?>>NBOS-33-01</option>
    <option value="NBOS-34-01" <?php if("NBOS-34-01" ==$r) echo "selected='selected'";?>>NBOS-34-01</option>	
	<option value="NBOS-35-01" <?php if("NBOS-35-01" ==$r) echo "selected='selected'";?>>NBOS-35-01</option>
	<option value="NBOS-36-01" <?php if("NBOS-36-01" ==$r) echo "selected='selected'";?>>NBOS-36-01</option>
	<option value="NBOS-37-01" <?php if("NBOS-37-01" ==$r) echo "selected='selected'";?>>NBOS-37-01</option>
	<option value="NBOS-38-01" <?php if("NBOS-38-01" ==$r) echo "selected='selected'";?>>NBOS-38-01</option>
	
</select>
	</td>

	
<td>
    类型:</td><td> <select id="type" name="type" style="align:right; width: 12em; text-align: center;">
	<option value="" <?php if($t == "") echo "selected='selected'";?>>全部</option>
	<option value="13" <?php if("13" ==$t) echo "selected='selected'";?>>非授权流量</option>
	<option value="110" <?php if("110" ==$t) echo "selected='selected'";?>>网站后门</option>
	<option value="12" <?php if("12" ==$t) echo "selected='selected'";?>>僵尸网络</option>
	<option value="10" <?php if("10" ==$t) echo "selected='selected'";?>>DDOS</option>
   </select>
	</td>
	</tr>
	
	<tr> 
	<td>源IP:</td><td> <input id="srcip" type="text" name="srcip" value="<?php echo $sr;?>"></input> </td>
	<td>宿IP:</td> <td><input id="dstip" type="text" name="dstip" value="<?php echo $ds;?>"></input> </td>
	</tr>
	<tr>
	<td>时间：</td>
	<td colspan=1><input class="readonly" name="StartTime"
						size="20" onclick="SelectDate(this,'yyyy-MM-dd');" readonly="readonly" value="<?php echo $st;?>"/></td>
						<td style="text-align:center;">------</td>
	<td colspan=1><input class="readonly" name="LastTime"
						size="20" onclick="SelectDate(this,'yyyy-MM-dd');" readonly="readonly" value="<?php echo $lt;?>"/></td>
						
	<tr></tr>
	</tr>	
	
	<td></td> <td></td> <td><input id="sbt" type="submit" value="开始检索"></input></td>
	</div>
</form>
	
	</div> 
	</div>

<br/>
<br/><br/><br/>
<div id="classifyRst" style="align:center;">
<?php

    //这里采用默认连接本机的27017端口，当然你也可以连接远程主机如192.168.0.4:27017,如果端口是27017，端口可以省略
    $m = new Mongo();
    // 选择comedy数据库，如果以前没该数据库会自动创建，也可以用$m->selectDB("comedy");
    $db = $m->CHAIRS;
    //选择comedy里面的collection集合，相当于RDBMS里面的表，也-可以使用
    //$collection = $db->y10; 
	$collection = $db->y2016_Evidence;
	//$page=new Page($data['total'],10,"id=5&cid=6&user=admin");
	//$pageSize = 10;
    //$amount = 100;
	
	if(!empty($_GET['page'])){$cnt = $_GET['page']-1;}
	else $cnt = 0;
	$count = $collection->find($whr)->count();
	$cursor = $collection->find($whr)->sort(array('EndTime'=>-1))->skip($cnt*20)->limit(20);
	//if($r == ""&&$t == ""&&$sr==""&&$ds==""&&$st==""&&$lt==""){
	//$count = $count1-1900;
	//$cursor = $collection->find($whr)->sort(array('EndTime'=>-1))->skip(1900+$cnt*20)->limit(20);
	//}else{ 
	//$count = $count1-1900;
	//$cursor = $collection->find($whr)->skip(1900+$cnt*20)->limit(20);
	//}
	
	$page=new Page($count,20);
	?>
	<table class='tablecss' id="tbs" style="table-layout:fixed;width:auto;">
	<tr>
	<th width="100px">ID</th>
	<th width="100px">Reporter</th>
	<th width="80px">类型</th>
	<th width="85px">开始时间</th>
	<th width="85px">结束时间</th>
	<th width="80px">出现次数</th>
	<th width="100px">源IP</th>
	<th width="100px">宿IP</th>
	<th width="80px">宿端口</th>
	 <?php //if($r=="MALW-SH-00" || $t == "110"){?>
	<th name='hostname' style="width:80px;">HostName</th>
	<th name='hostname' style="width:80px;">WebshellUrl</th>
	<th name='hostname' style="width:80px;">注释</th>
	<th width="80px">活跃时间</th>
    <th width="80px">源端口</th>	
	
	<th width="80px">In方向字节数</th><th width="80px">In方向报文数</th><th width="80px">Out方向字节数</th><th width="80px">Out方向报文数</th>
	<th width="80px">In方向最大pps</th><th width="80px">In方向最大bps</th><th width="80px">In方向最小pps</th><th width="80px">In方向最小bps</th><th width="80px">Out方向最大pps</th>
	<th width="80px">Out方向最大bps</th><th width="80px">Out方向最小pps</th><th width="80px">Out方向最小bps</th><th width="80px">附件信息</th>
	<?php // } ?>
	</tr>		
		<?php
		$count = 0;
		foreach ($cursor as $obj)
    {
	?>
	      <tr style="table-layout:fixed;width:auto;align:center;text-align:center;">
		  <td><?php echo $obj["ID"];?></td>
		  <td><?php echo $obj["Reporter"];?></td>
		   <td><?php if($obj["Type"] ==12)echo "僵尸网络";if($obj["Type"] ==13)echo "非授权流量";
		  if($obj["Type"] ==10)echo "DDos";if($obj["Type"] ==110)echo "网站后门";?></td>
		  <td><?php echo $obj["StartTime"];?></td>
		  <td><?php echo $obj["EndTime"];?></td>
		  <td><?php if($obj["OccurNum"]==null) echo " ";echo $obj["OccurNum"];?></td>
		  <td><?php if(long2ip($obj["ThreatSrcIP"])=="0.0.0.0") echo " ";else echo long2ip($obj["ThreatSrcIP"]);?></td>
		  <td><?php if(long2ip($obj["ThreatDstIP"])=="0.0.0.0") echo " ";else echo long2ip($obj["ThreatDstIP"]);?></td>
		  <td><?php if($obj["ThreatDstPort"]==null) echo " ";else echo $obj["ThreatDstPort"];?></td>
		  <?php //if($r=="MALW-SH-00" || $t == "110") { ?>
		  <td><?php if($obj["HostName"]==null) echo " ";else echo $obj["HostName"];?></td>
		  <td><?php if($obj["reserveSString1"]==null) echo " ";echo $obj["reserveSString1"];?></td>
		  <td><?php if($obj["reserveSString2"]==null) echo " ";echo $obj["reserveSString2"];?></td>
		    <td><?php echo $obj["TimeLive"];?></td>
		    <td><?php if($obj["ThreatSrcPort"]=="-1"){echo "";}else echo $obj["ThreatSrcPort"];?></td>
			 <td><?php echo $obj["reserveInt1"];?></td>
			  <td><?php echo $obj["reserveInt2"];?></td>
			   <td><?php echo $obj["reserveInt3"];?></td>
			    <td><?php echo $obj["reserveInt4"];?></td>
				 <td><?php echo $obj["reserveInt5"];?></td>
				  <td><?php echo $obj["reserveInt6"];?></td>
				   <td><?php echo $obj["reserveInt7"];?></td>
				   <td><?php echo $obj["reserveInt8"];?></td>
				   <td><?php echo $obj["reserveInt9"];?></td>
				   <td><?php echo $obj["reserveInt10"];?></td>
				   <td><?php echo $obj["reserveInt11"];?></td>
				   <td><?php echo $obj["reserveInt12"];?></td>
				   <td><?php echo $obj["attachContent"];?></td>		
		  
		  
		  </tr>
       <?php  //} 
    }
echo '<tr><td colspan="12" align="center">'.$page->fpage(0,1,2,3,4,5,6,7).'</td></tr>';
echo "</table>";
     //ShowEvidence($cursor);

    //断开MongoDB连接
  $m->close();  

?>
</div>


</body>
</html>

