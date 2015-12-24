<?php
class fgt_error_report 
{
	var $handle_core;/*Core log file pointer*/
	var $handle_client_msg;/*client message log file pointer (Array)*/
	
	function  __construct ()
	{
		global $var;
		print $this->make_date_str()."Initializing Error reporting Manager\n";
		$this->handle_core = fopen($var['log_location']."/log.txt", "a+");
		if ($this->handle_core===false)
		{
			print $this->make_date_str()."Failed to Initialize Error reporting Manager. Exiting...\n";
			exit();
		}
		$message="Error reporting Manager initialized";
		$this->fgt_set_error_report("ERR_R",$message,E_WARNING);
		$message="Log location:".dirname(__FILE__);
		$this->fgt_set_error_report("ERR_R",$message,E_ALL);
	}
	
	function fgt_set_error_report($loc,$message,$level)
	{
		global $var;
		$to_log=FALSE;
		switch ($level)
		{
			case E_ERROR:
				$to_log=TRUE;
			break;
			case E_WARNING:
				if($var['error_reporting_level']!=E_ERROR)
					$to_log=TRUE;
			break;
			case E_NOTICE:
				if($var['error_reporting_level']==E_NOTICE or $var['error_reporting_level']==E_ALL)
					$to_log=TRUE;
			break;
			case E_ALL:
				if($var['error_reporting_level']==E_ALL)
					$to_log=TRUE;
			break;
			default:
				$to_log=TRUE;
		}
		
		if($to_log===TRUE)
		{
			$messageArr=explode ( "\n" , $message );
			$message="";
			foreach($messageArr as $messageElements)
				$message.=$this->make_date_str().$loc."\t".$messageElements."\n";
			print $message;
			fwrite($this->handle_core,$message);
		}	
		
	}
	
	function log_client_msg($ident, $message)
	{
		global $var;
		if($var['log_client_msg']===true)
		{
			if(!isset($this->handle_client_msg[$ident]))
				$this->handle_client_msg[$ident] = fopen($var['log_location']."/client_msg_$ident.txt", "a+");
			fwrite($this->handle_client_msg[$ident],$message."\n");
		}
	}
	
	function make_date_str()
	{
		return "[".date('Y-m-d H.i.s')."]\t";
	}
	
	function terminate()
	{
		$message="Terminating reporting Manager";
		$this->fgt_set_error_report("ERR_R",$message,E_WARNING);
		foreach($this->handle_client_msg as $handle)
			fclose($handle);
		fclose($this->handle_core);
		print "Error reporting Manager is terminated\n";
	}

	function send_email($title,$content)
	{
		global $var;
		
		$result=mail ( $var['error_email_address'] , $title , $content);
		if ($result)
		$message="A Email has been sent to ".$var['error_email_address'];
		else
			$message="Failed to send Email to ".$var['error_email_address'];
		$this->fgt_set_error_report("ERR_R",$message,E_ERROR);
		
	}	
}

?>