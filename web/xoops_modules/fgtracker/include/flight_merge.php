<?php

///////////////////////////////////////////////////////////////////////
// CHECK_MERGE_REQUEST - return TRUE if okay
///////////////////////////////////////////////////////////////////////
function check_merge_request($conn, $p_flightid, $flightid,$isadmin)
{
    /*flight details*/
	$res=pg_query($conn,"SELECT callsign,model,start_time,end_time,EXTRACT(EPOCH FROM start_time) AS starttime_raw,justify_hours(end_time-start_time) AS duration FROM flights_all WHERE id=$flightid;");
    if ($res)
    {
      $callsign=pg_result($res,0,'callsign');
      $model=pg_result($res,0,'model');
      $starttime=pg_result($res,0,'start_time');
      $endtime=pg_result($res,0,'end_time');
      $starttime_raw=pg_result($res,0,'starttime_raw');
      $duration=pg_result($res,0,'duration');
	  pg_free_result($res);
    } else return false;
	
	if($endtime=="")/*Cannot merge a open flight*/
		return false;
	
	/*previous flight details*/
	$res=pg_query($conn,"SELECT id, callsign,model,start_time,end_time,EXTRACT(EPOCH FROM end_time) AS endtime_raw FROM flights_all WHERE id=$p_flightid;");
	if ($res)
    {
      $p_model=pg_result($res,0,'model');
      $p_flightid=pg_result($res,0,'id');
      $p_endtime=pg_result($res,0,'end_time');
      $p_endtime_raw=pg_result($res,0,'endtime_raw');
      $p_starttime=pg_result($res,0,'start_time');
	  pg_free_result($res);
    } else return false;
	
	if ($p_model!=$model) return false;	/*Criteria 1: must same model*/
	if($isadmin===true) /*no more check on admin*/
		return true;
	
	if ($starttime_raw-$p_endtime_raw>90) return false;	//and $p_distancediff<33333 and $starttime_raw-$p_endtime_raw<90
	
	/*waypoints details*/
	$res=pg_query($conn,"SELECT time,longitude,latitude,altitude FROM waypoints_all WHERE flight_id=$flightid AND (longitude!=0 OR latitude!=0 OR altitude!=0) ORDER BY time limit 1");
	if ($res)
    {
		$deplat=pg_result($res,0,"latitude");
		$deplon=pg_result($res,0,"longitude");
		pg_free_result($res);
    } else return false;
	/*previous waypoints details*/
	$res=pg_query($conn,"SELECT time,longitude,latitude,altitude FROM waypoints_all WHERE flight_id=$p_flightid AND (longitude!=0 OR latitude!=0 OR altitude!=0) ORDER BY time desc limit 1");
	if ($res)
    {
		$p_arrlat=pg_result($res,0,"latitude");
		$p_arrlon=pg_result($res,0,"longitude");
		pg_free_result($res);
    }  else return false;	
	$flight = new FLIGHT_REPORT;
	$p_distancediff = $flight->computeDistance($p_arrlat,$p_arrlon,$deplat,$deplon);
	if ($p_distancediff<33333) return true; else return false;
}

///////////////////////////////////////////////////////////////////////
// MERGE_REQUEST - return: Array("ok"=>boolean,"msg"=>String())
///////////////////////////////////////////////////////////////////////
function merge_request($conn, $p_flightid, $flightid,$username,$token,$usercomments)
{
	global $var;
	
	if($token=="")
	{
		/* check with function check_merge_request*/
		if (check_merge_request($conn, $p_flightid, $flightid,false)===false)
			return Array("ok"=>false,"msg"=>"Merge check failed");
	}else
	{	/*admin skip checking*/
		if($username!=$var["adminname"])
		{
			$reply["ok"]=FALSE;
			$reply["msg"]="Unauthorized user";
			return $reply;
		}
		
		if($token!=$var["alter_db_token"])
		{
			$reply["ok"]=FALSE;
			$reply["msg"]="Wrong token";
			return $reply;
		}
		/* check with function check_merge_request*/
		if (check_merge_request($conn, $p_flightid, $flightid,true)===false)
			return Array("ok"=>false,"msg"=>"Merge check failed");
	}

		
	/*flight details*/
	$res=pg_query($conn,"SELECT callsign, end_time,\"table\",wpts FROM flights_all WHERE id=$flightid");
    if ($res)
    {
      $callsign=pg_result($res,0,'callsign');
      $endtime=pg_result($res,0,'end_time');
      $wpts=pg_result($res,0,'wpts');
      $table=pg_result($res,0,'table');
	  pg_free_result($res);
    } else return Array("ok"=>false,"msg"=>"Unable to get flight details");

	/*p_flight details*/
	$res=pg_query($conn,"SELECT \"table\",wpts FROM flights_all WHERE id=$p_flightid");
    if ($res)
    {
      $p_table=pg_result($res,0,'table');
	  $p_wpts=pg_result($res,0,'wpts');
	  pg_free_result($res);
    } else return Array("ok"=>false,"msg"=>"Unable to get previous flight details");
	
	/*check if the data is in intermediate stage i.e. data across current and achieved data*/
	if ($p_table!=$table)
		return Array("ok"=>false,"msg"=>"Flights are in intermediate stage");
	
	/*A flight cannot be merged if it stills open*/
	if ($endtime=="")
		return Array("ok"=>false,"msg"=>"Flight is still active and cannot be merged");
	
	/*update p_flight endtime*/
	$sql="START TRANSACTION;";
	if ($endtime=="")
		$sql1="update $p_table set end_time=NULL where id=$p_flightid;";
	else
		$sql1="update $p_table set end_time='$endtime' where id=$p_flightid;";
	
	/*update waypoints and no of wpts. Delete Effective_flight_time,start_icao, end_icao. Set status to open if the flight still active*/
	if ($p_table=="flights")
	{
		$waypoints_table="waypoints";
		if ($endtime=="")
			$sql2="update $p_table set effective_flight_time=NULL,start_icao=NULL, end_icao=NULL,status='OPEN' where id=$p_flightid;";
		else
			$sql2="update $p_table set effective_flight_time=NULL,start_icao=NULL, end_icao=NULL where id=$p_flightid;";
	}
	else
	{
		$waypoints_table="waypoints_archive";
		$wpts=$wpts+$p_wpts;
		$sql2="update $p_table set wpts=$wpts,effective_flight_time=NULL where id=$p_flightid;";
	}
		
	$sql3="update $waypoints_table set flight_id=$p_flightid where flight_id=$flightid;";
	
	/*delete flight*/
	$sql4="delete from $table where id=$flightid;";
	
	if($usercomments=="")
		$usercomments="NULL";
	else
		$usercomments="'".pg_escape_string($conn,$usercomments)."'";
	$sql5="INSERT into log (username,\"table\",action,\"when\",callsign,usercomments,flight_id,flight_id2) VALUES ('$username', '$p_table', 'Merge flight $flightid to $p_flightid', NOW(), '$callsign',$usercomments,$flightid,$p_flightid);";
	
	$sql6="COMMIT;";
	$res=pg_query($conn,$sql.$sql1.$sql2.$sql3.$sql4.$sql5.$sql6);
	if ($res===FALSE)
	{
		pg_query_params($conn,"rollback;",Array());
		return Array("ok"=>false,"msg"=>"Internal error:".pg_last_error ($conn));
	}
		

	return Array("ok"=>true,"msg"=>"Succeed");
}

?>