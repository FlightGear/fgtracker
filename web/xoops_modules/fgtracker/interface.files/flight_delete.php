<?php
///////////////////////////////////////////////////////////////////////
// DELETE flight - return array of informations. Need set up global $var["adminname"] and $var["alter_db_token"]
///////////////////////////////////////////////////////////////////////
function delflight($conn,$reply,$flightid,$token,$username,$callsign,$usercomments)
{
	global $var;
	if($username!=$var["adminname"])
	{
		$reply["data"]["ok"]=FALSE;
		$reply["data"]["msg"]="Unauthorized user";
		$reply["header"]=Array("code"=>200,"msg"=>'OK');
		return $reply;
	}
	
	if($token!=$var["alter_db_token"])
	{
		$reply["data"]["ok"]=FALSE;
		$reply["data"]["msg"]="Wrong token";
		$reply["header"]=Array("code"=>200,"msg"=>'OK');
		return $reply;
	}
	
	/*check finished*/	
	$res=pg_query($conn,"SET TIMEZONE TO 'UTC';");
	pg_free_result($res);

	$flightid_escaped=pg_escape_string($conn,$flightid);
	$callsign_escaped=pg_escape_string($conn,$callsign);
	$username_escaped=pg_escape_string($conn,$username);
	$res=pg_query($conn,"DELETE from flights where id=$flightid_escaped and end_time IS NOT NULL;");
	$nr=pg_affected_rows ($res);
	if ($nr==1)
		$table="flights";
	else
	{
		$table="flights_archive";
		$res=pg_query($conn,"DELETE from flights_archive where id=$flightid_escaped and end_time IS NOT NULL;");
		$nr+=pg_affected_rows ($res);
	}

	if($usercomments=="")
		$usercomments="NULL";
	else
		$usercomments="'$usercomments'";
	$res=pg_query($conn,"INSERT into log VALUES ('$username_escaped', '$table', 'DELETED $flightid_escaped', NOW(), '$callsign_escaped',$usercomments,$flightid_escaped,NULL);");
	
	if($nr==1)
	{
		$reply["data"]["ok"]=TRUE;
		$reply["data"]["msg"]="Success";
		/*obtain previous record*/
		$res=pg_query($conn,"SELECT id,\"table\" FROM flights_all where id < $flightid_escaped  and callsign='$callsign_escaped' order by id desc LIMIT 1;");
		$nr=pg_num_rows ( $res );
		if ($nr==1)
		{
			$table=pg_result($res,0,'table');
			if($table=='flights')
				$is_archive=false;
			elseif ($table=='flights_archive')
				$is_archive=true;
			$reply["data"]["previous_flight"]["flight_id"]=pg_result($res,0,'id');
			$reply["data"]["previous_flight"]["is_archive"]=$is_archive;
			pg_free_result($res);
			
			$row_offset=get_flight_row_offset($conn,$reply["data"]["previous_flight"]["flight_id"],$callsign_escaped,$table);
			$reply["data"]["previous_flight"]['row']=$row_offset[0];
			$reply["data"]["previous_flight"]['offset']=$row_offset[1];
		}
		
	}else
	{
		$reply["data"]["ok"]=FALSE;
		$reply["data"]["msg"]="Affected rows not equal to 1 (May be the flight is not closed yet?)";
	}
	
	/*Write header*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}

?>
