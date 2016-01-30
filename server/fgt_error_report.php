<?php
class fgt_error_report 
{
	var $handle_core;/*Core log file pointer*/
	var $handle_client_msg;/*client message log file pointer (Array)*/
	var $date_str;
	
	function  __construct ()
	{
		global $var;	
		$this->date_str=date('Y-m-d');
		
		print $this->make_date_str()."Initializing Error reporting Manager\n";
		$this->handle_core = fopen($var['log_location']."/FGTrackerlog_".$this->date_str.".txt", "a+");
		if ($this->handle_core===false)
		{
			print $this->make_date_str()."Failed to create log file. (Permission denied?)\n";
		}
		$message="Error reporting Manager initialized";
		$this->fgt_set_error_report("ERR_R",$message,E_WARNING);
		$message="Log location:".$var['log_location'];
		$this->fgt_set_error_report("ERR_R",$message,E_ALL);
	}
	
	function check_log_date()
	{
		if ($this->date_str!=date('Y-m-d'))/*date changed, close all log file*/
		{
			$this->date_str=date('Y-m-d');
			$this->terminate(TRUE);
		}
	}
	
	function fgt_set_error_report($loc,$message,$level)
	{
		global $var;
		$to_log=FALSE;
		
		$this->check_log_date();
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
			if($this->handle_core!==false)
				fwrite($this->handle_core,$message);
		}	
		
	}
	
	function log_client_msg($ident, $message)
	{
		global $var;
		$this->check_log_date();
		if($var['log_client_msg']===true and $this->handle_core!==false)
		{
			if(!isset($this->handle_client_msg[$ident]))
				$this->handle_client_msg[$ident] = fopen($var['log_location']."/client_msg_$ident"."_".$this->date_str.".txt", "a+");
			fwrite($this->handle_client_msg[$ident],$message."\n");
		}
	}
	
	function make_date_str()
	{
		return "[".date('Y-m-d H:i:s')."]\t";
	}
	
	function terminate($restart)
	{
		$message="Terminating reporting Manager";
		$this->fgt_set_error_report("ERR_R",$message,E_WARNING);
		if(isset($this->handle_client_msg))
		{
			foreach($this->handle_client_msg as $handle)
				fclose($handle);
			unset($this->handle_client_msg);
		}
		if($this->handle_core!==FALSE)
		{
			fclose($this->handle_core);
			unset($this->handle_core);
		}
		print "Error reporting Manager is terminated\n";
		if ($restart)
			$this->__construct();
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