<?php
class ContactUsersModel extends CommonModel
{
//field:id,或者值为唯一的其他键，$val为其值
	public function getInfoBy($field,$val)
	{
		if(!isset($field) || !isset($val))
		{
			return -1;
		}
		
		$model = M("ContactUsers");
		if(empty($model))
		{
			return -1;
		}
		
		$sql = "select * from ContactUsers where $field='$val';";
		$list = $model->query($sql);
		if($list === false)
		{
			return -1;
		}
		return $list[0];
	}
}

?>