<?php
//类
class Cart extends Think {
	//当前名
	public $sessionName;

	public function __construct($sessionName)
	{
		$this->sessionName=$sessionName;
		if(!isset($_SESSION[$this->sessionName]))
		{
			$_SESSION[$this->sessionName]="";
		}
	}

	//获取购物车的信息
	public function getCart(){
		$cur_cart_array=$_SESSION[$this->sessionName];
		return $cur_cart_array;
	}
	public function getCartIds()
	{
		$cur_cart_array=$_SESSION[$this->sessionName];
		if($cur_cart_array !='')
		{
			$ordersNum = count($cur_cart_array);
		}
		else {
			$ordersNum = 0;
		}

		$str  = "";
		for($i=0;$i<$ordersNum;$i++)
		{
			$str .= $cur_cart_array[$i][0].",";
		}

		return $str;
	}

	//更新商品数量
	public function updateCart($id,$str)
	{
		$cur_cart_array=$_SESSION[$this->sessionName];
		if($cur_cart_array !='')
		{
			$ordersNum = count($cur_cart_array);
		}
		else {
			$ordersNum = 0;
		}


		for($i=0;$i<$ordersNum;$i++)
		{
			if($cur_cart_array[$i][0] == $id)
			{
				return;
			}
		}

		$cur_cart_array[$ordersNum][0] = $id;
		$cur_cart_array[$ordersNum][1] = $str;

		$_SESSION[$this->sessionName]=$cur_cart_array;
	}

	//从购物车删除
	public function delcart($goods_array_id){
		$cur_cart_array=$_SESSION[$this->sessionName];
		if($cur_cart_array !='')
		{
			$ordersNum = count($cur_cart_array);
		}
		else {
			$ordersNum = 0;
		}


		for($i=0;$i<$ordersNum;$i++)
		{
			if($cur_cart_array[$i][0] == $goods_array_id)
			{
				//unset($cur_cart_array[$i]);
				array_splice($cur_cart_array,$i,1);
				break;
			}
		}		
		$_SESSION[$this->sessionName]=$cur_cart_array;		
	}


	//清空购物车
	public function emptycart(){
		$_SESSION[$this->sessionName]="";
	}
}
?>