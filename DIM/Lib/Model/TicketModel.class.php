<?php
class TicketModel extends CommonModel
{
	public $_validate	=	array(
	array('Subject','require','主题必须'),
	array('StartTime','require','开始时间必须'),
	);
}

?>