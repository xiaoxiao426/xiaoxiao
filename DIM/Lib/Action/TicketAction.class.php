<?php
class TicketAction extends CommonAction
{
	
	public function insertTicket()
	{
		$fp = fopen("mail.txt","r");
		fwrite($fp,"testmail".date("Y-m-d H:i:s")."\r\n");
		$model = M("Ticket");
		if(empty($mdoel))
		{
			fwrite($fp,"so\r\n");
		}
		else {
			fwrite($fp,"not so\r\n");
		}
		fclose($fp);
		
	}
}
?>