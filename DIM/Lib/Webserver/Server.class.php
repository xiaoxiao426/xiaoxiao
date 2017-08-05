<?php Class Server extends Think
{
	function GetInfo(){
		$model = M("EvidenceNBOSUAHT");
		if(empty($model))
		{
			return -1;
		}
		$list = $model->limit("0,100")->select();
		if($list === false)
		{
			return -1;
		}
		return $list;
		//return date('Y-m-d');
	}
	
}
$soap=new SoapServer(null,array('uri'=>"http://127.0.0.1/DIM_CHAIRS/DIM/index.php/"));
$soap=new SoapServer(null,array("location"=>"http://127.0.0.1/DIM_CHAIRS/DIM/index.php/Public/MyService","uri"=>"http://127.0.0.1/DIM_CHAIRS/DIM/index.php/Public/MyService"));
$soap->setClass("Server");
$soap->addFunction('GetInfo');
$soap->handle();
?>