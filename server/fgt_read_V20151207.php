<?php
class fgt_read_V20151207
{
	var $uuid;
	var $protocal_version;

	function __construct($uuid)
	{
		global $fgt_error_report,$var,$clients;

		$this->uuid=$uuid;
		$this->protocal_version="V20151207";
		$message="Subroutine \"".$this->protocal_version."\" for ".$clients[$this->uuid]['server_ident'] ." initialized";
		$fgt_error_report->fgt_set_error_report("R_".$this->protocal_version,$message,E_NOTICE);		
	}

	function catch_phase_error($die,$line)
	{
		global $fgt_error_report,$var,$clients;

		$message="Unrecognized Message from ".$clients[$this->uuid]['server_ident'] ." ($line)";
		$fgt_error_report->fgt_set_error_report($clients[$this->uuid]['server_ident'],$message,E_ERROR);
		if($die)
		{
			$clients[$this->uuid]['write_buffer'].="ERROR Message \"$line\" not recognized\0";
			$clients[$this->uuid]['connected']=false;		
		}
	}

	function read_buffer()
	{
		global $fgt_error_report,$var,$clients,$fgt_sql;
		$i=0;
		while(1)
		{
			/*check if stuck in the same server too long. if so return and receive another server's stream*/
			if($i==20)
			{
				$message="Buffer read stuck in ".$clients[$this->uuid]['server_ident'] ." too long, moving to another server";
				$fgt_error_report->fgt_set_error_report("R_".$this->protocal_version,$message,E_WARNING);
				break;
			}

			/*check if whole message received*/
			$packet_pos = strpos($clients[$this->uuid]['read_buffer'], "\0");
			if($packet_pos===false)
				break;

			/*obtain one packet*/
			$packets=explode("\0", $clients[$this->uuid]['read_buffer'],2);
			$packet=$packets[0];
			$clients[$this->uuid]['read_buffer']=$packets[1];
			$packet_size=strlen($packet)+1;
			if ($var['error_reporting_level'] == E_ALL)
				$message="Received packet ($packet_size bytes): $packet \\\\EOP";
			else $message="Received packet ($packet_size bytes)";
			$fgt_error_report->fgt_set_error_report("R_".$this->protocal_version,$message,E_NOTICE);
			$fgt_error_report->log_client_msg($clients[$this->uuid]['server_ident'], $packet);
			if($packet=="PONG")
			{
				$message="PONG received";
				$fgt_error_report->fgt_set_error_report($clients[$this->uuid]['server_ident'],$message,E_ALL);
				continue;
			}else if($packet=="PING")
			{
				$clients[$this->uuid]['write_buffer'].="PONG\0";
				continue;
			}else if(strpos($packet, "DEBUG")===0)
			{
				continue;
			}else if(strpos($packet, "ERROR")===0)
			{
				$message="Received message from ".$clients[$this->uuid]['server_ident'].": $packet";
				$fgt_error_report->fgt_set_error_report("R_".$this->protocal_version,$message,E_ERROR);
				continue;
			}
			/*messages other than ping/pong*/
			$lines=explode("\n",$packet);
			if($clients[$this->uuid]['msg_process_class']->msg_start()===false)
				return;
			foreach ($lines as $line )
			{
				$line=trim($line);
				if($line=="")
					continue;
				/*Sometimes the fgms sends invalid packets. Below is a workaround to prevent unnessary forced exit. 
				Example of Invalid messgae are:
				POSITION * Bad Client *  0 0 . 2012-12-03 21:03:53
				DISCONNECT * Bad Client *  * unknown * 2012-12-03 20:56:32
				POSITION franck test  . . . 2012-12-08 14:28:42
				POSITION edji-x test -15.325710 35.681043 . 313.231018 10.049998 -45.658264 2015-12-31 01:54:54" not recognized
				*/
				$data=explode(" ", $line);
				if(mb_check_encoding($line, 'UTF-8')===false)
					$this->catch_phase_error(false,$line);
				else if(stripos ( $line , "* Bad Client *"  )!==false or stripos ( $line , " . "  )!==false)
					$this->catch_phase_error(false,$line);
				else if($data[2]!="test")
					$this->catch_phase_error(false,$line); 
				else if($data[0]=="POSITION")
				{
					if(sizeof($data)!=11)
						$this->catch_phase_error(false,$line);
					else if(is_numeric($data[3])===false or is_numeric($data[4])===false or is_numeric($data[5])===false or is_numeric($data[6])===false or strpos($data[9] ,"-")!= 4)
						$this->catch_phase_error(true,$line);
					else
					{
						$msg_array=Array('nature'=>$data[0],'callsign'=>$data[1],'lat'=>$data[3],'lon'=>$data[4],'alt'=>$data[5],'heading'=>$data[6],'pitch'=>$data[7],'roll'=>$data[8],'date'=>$data[9],'time'=>$data[10]);
						$clients[$this->uuid]['msg_process_class']->msg_process($msg_array,$this->uuid);
					}
				}else if($data[0]=="CONNECT" or $data[0]=="DISCONNECT" or strpos($data[4] ,"-")!= 4)
				{
					if(sizeof($data)!=6)
						$this->catch_phase_error(false,$line);
					else
					{
						$msg_array=Array('nature'=>$data[0],'callsign'=>$data[1],'model'=>$data[3],'date'=>$data[4],'time'=>$data[5]);
						$clients[$this->uuid]['msg_process_class']->msg_process($msg_array,$this->uuid);
					}
				}else	$this->catch_phase_error(true,$line);

				if($clients[$this->uuid]['connected']===false or $fgt_sql->connected===false)
					break;

			}

			if($clients[$this->uuid]['connected']===false and $fgt_sql->connected!==false)
			{/*roll back and return*/
				$clients[$this->uuid]['msg_process_class']->rollback();
				return;
			}

			if($clients[$this->uuid]['msg_process_class']->msg_end($packet)===false)
				return;
			$clients[$this->uuid]['write_buffer'].="OK\0";
			$i++;
		}
	}
}
?>
