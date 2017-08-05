<?php
class ResponseDocumentModel extends CommonModel
{
	
	public function getDocByTID($tid)
	{
		$res = $this->where("TID=$tid")->select();
		if($res === false)
		{
			return -1;
		}
		return $res;
	}
}

?>