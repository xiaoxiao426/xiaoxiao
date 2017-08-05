<?php
//系统配置：运行配置，进程配置
class ConfigurationAction extends CommonAction
{
	public function index(){
		$this->display();
	}

	//运行配置
	public function runConfig()
	{
		$fileName = dirname(dirname(dirname(dirname(__FILE__)))).'/Config/config.conf';
		$fp = fopen($fileName, 'r');

		if(!$fp){
			$this->error("配置文件打开失败!");
		}

		$list = Array();
		while(!feof($fp)){
			$line = fgets($fp);
			$arr = explode('=', $line,2);
			if(trim($arr[0])=="per_check_gran")
			{
				$p = trim($arr[1]);
				$list["per_check_gran"] = ($p/60)." min";
			}
			else if(trim($arr[0])=="per_tran_gran"){
				$t = trim($arr[1]);
				$list["per_tran_gran"] = ($t/60)." min";
			}
			else if(trim($arr[0])=="space_threshold")
			{
				$s = trim($arr[1]);
				$list["space_threshold"]= $s."%";
			}
			else if(trim($arr[0])=="cpu_threshold")
			{
				$c= trim($arr[1]);
				$list["cpu_threshold"] = $c."%";
			}
			else if(trim($arr[0])=="mem_threshold")
			{
				$m =trim($arr[1]);
				$list["mem_threshold"] = $m."%";

			}
			else if(trim($arr[0])=="alert_switch")
			{
				$a = trim($arr[1]);
				if($a==0){
					$list["alert_switch"] = "不报警";
				}
				else if($a==1){
					$list["alert_switch"] = "报警";
				}
			}
			else if(trim($arr[0])=="Log_clear_cycle")
			{
				$l =trim($arr[1]);
				if($l==0){
					$list["log_clear_cycle"] = "每天";
				}
				else if($l==1){
					$list["log_clear_cycle"] = "每周";
				}
				else if($l==2){
					$list["log_clear_cycle"] = "每月";
				}
				else if($l==3){
					$list["log_clear_cycle"] = "每年";
				}
			}
		}
		fclose($fp);
		$this->assign("list",$list);
		$this->display();
	}

	//进程配置
	public function configScan()
	{
		$config = new Model("Configs");
		$str = $this->getConnetManString();
		$list=$config->db(1, $str);
		$sql = "SELECT * FROM Configs ORDER BY id DESC LIMIT 1";
		$list = $config->query($sql);
		if($list === false || count($list) != 1)
		{
			$this->error("数据错误!");
			return;
		}
		$list[0]['cpuThreshold'] .= '%';
		$list[0]['memoryThreshold'] .= '%';
		$list[0]['diskThreshold'] .= '%';
		$list[0]['performanceCheckGran'] = $list[0]['performanceCheckGran']/60 ." 分钟";
		$list[0]['logSendGran'] .= " 小时";
		$list[0]['activeGran'] = $list[0]['activeGran']/60 ." 分钟";
		
		if($list[0]['alarmSwitch']==0){
			$list[0]['alarmSwitch'] = "不报警";
		}
		else if($list[0]['alarmSwitch']==1){
			$list[0]['alarmSwitch'] = "报警";
		}
		$list[0]['logThreshold'] .= " 天";
		
		$this->assign("config",$list[0]);
		$sql = "SELECT * FROM ProcessConfig";
		$list = $config->query($sql);
		
		if($list === false)
		{
			$this->error("数据错误!");
			return;
		}
		$this->assign("processConfig",$list);
		$this->display();
	}
}
?>