<?php
class UpdateMgr
{
	
	public function __construct () 
	{}
	
	public function archive_flights_waypoints()
	{	/*return $is_success*/
		global $fgt_sql,$fgt_error_report,$var;
		$sub_pid="A_ARCHI";
		
		/*create flights temptable*/
		$temptable_flights='flights_'.rand();
		$sql="SELECT * INTO $temptable_flights from flights where start_time < $1;";
		$res=$this->fgt_pg_query_params($sql,Array($var['archive_date']));
		if($res===false) return false;
		if(pg_affected_rows($res)==0)
		{
			$message="No flight available for archiving";
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
			$sql="DROP TABLE $temptable_flights;";
			$res=$this->fgt_pg_query_params($sql,NULL);
			return true;
		}
		pg_free_result($res);
		
		$sql="select count(*) AS COUNTER from $temptable_flights";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="List of flights archived is stored in $temptable_flights. Total ".pg_result($res,0,"COUNTER")." records";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		pg_free_result($res);
		
		/*create waypoints temptable*/
		$temptable_waypoints='waypoints_'.rand();
		$sql="SELECT * INTO $temptable_waypoints from waypoints where flight_id IN (select id from $temptable_flights);";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		pg_free_result($res);
		
		$sql="select count(*) AS COUNTER from $temptable_waypoints";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="List of waypoints archived is stored in $temptable_waypoints. Total ".pg_result($res,0,"COUNTER")." records";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		if($res===false) return false;
		pg_free_result($res);
		
		/*create $temptable_waypoints index*/
		$sql="CREATE UNIQUE INDEX \"$temptable_waypoints-id_idx\" ON $temptable_waypoints (id);";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false; /*should be obsolate soon*/
		$sql="CREATE INDEX \"$temptable_waypoints-flightidtime_idx\" ON $temptable_waypoints (flight_id,time);";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$sql="CREATE INDEX \"$temptable_waypoints-flightid_idx\" ON $temptable_waypoints (flight_id);";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="Done Creating index for table $temptable_waypoints";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		pg_free_result($res);
		
		/*trim waypoints*/
		if($this->trim_waypoints($temptable_waypoints)===false)
			return false;
		
		/*delete flights from current*/
		$sql="delete from flights where id IN (select id from $temptable_flights);";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="Done deleting ".pg_affected_rows($res)." flights from flights table";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		pg_free_result($res);
		
		/*load flights to archive*/
		$sql="INSERT INTO flights_archive select id, callsign, status, model, start_time, end_time, effective_flight_time, NULL, start_icao, end_icao from $temptable_flights;";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="Done Inserting ".pg_affected_rows($res)." flights to flights_archive table";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		pg_free_result($res);
		
		/*update no. of waypoints*/
		$sql="update flights_archive set wpts = (select count(*) from $temptable_waypoints where $temptable_waypoints.flight_id=flights_archive.id) where wpts is null;";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="Done updating ".pg_affected_rows($res)." flights_archive waypoints count";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		pg_free_result($res);
		
		/*delete waypoints*/
		$sql="delete from waypoints where flight_id IN (select id from $temptable_flights);";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="Done deleting ".pg_affected_rows($res)." waypoints from waypoints table";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		pg_free_result($res);
		
		/*load waypoints*/
		$sql="INSERT INTO waypoints_archive select * from $temptable_waypoints;";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$message="Done Inserting ".pg_affected_rows($res)." waypoints to waypoints_archive table";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		
		/*check no waypoint flights*/
		$sql="select id from flights_archive where wpts=0 or wpts is null;";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false) return false;
		$nr=pg_num_rows($res);
		$message="$nr flight record(s) in table flight_archive is/are invalid (invalid waypoints)";
		if($nr==0)
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		else
		{
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
			$list="";
			for ($i=0;$i<$nr;$i++)
				$list.=pg_result($res,$i,"id");
			$list=trim($list,',');
			$message="Problematic flights are: $list";
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
		}
		return true;
	}
	
	public function close_opened_flights() /*Waypoints ID cleared*/
	{	/*return $is_success*/
		global $fgt_sql,$fgt_error_report,$var;
		$message="Closing flights before ".$var['archive_date'];
		$fgt_error_report->fgt_set_error_report("A_CLOSE",$message,E_WARNING);
		
		$sql="update flights set status='CLOSED' where start_time < $1 and status ='OPEN';";
		$res=$this->fgt_pg_query_params($sql,Array($var['archive_date']));
		if($res===false) return false;
		$message="Done closing ".pg_affected_rows($res)." flights";
		$fgt_error_report->fgt_set_error_report("A_CLOSE",$message,E_NOTICE);
		

		/*fix the close time of closed flights*/
		$sql="update flights set end_time = (select MAX(time) from waypoints where waypoints.flight_id=flights.id) where start_time < $1 and status ='CLOSED' and end_time is null;";
		$res=$this->fgt_pg_query_params($sql,Array($var['archive_date']));
		if($res===false) return false;
		$message="Done updating ".pg_affected_rows($res)." flights' end time";
		$fgt_error_report->fgt_set_error_report("A_CLOSE",$message,E_NOTICE);
		return true;
	}
	
	function fgt_pg_query_params($sql,$sql_parm) /*Waypoints ID cleared*/
	{	/*if $sql_parm = NULL, pg_query is used
		  if $sql_parm is an array, pg_query_params is used
		*/
		global $fgt_error_report,$var,$fgt_sql;
		$sub_pid="FGQUERY";
		
		if($sql_parm==NULL)
			$res=pg_query($fgt_sql->conn,$sql);
		else
			$res=pg_query_params($fgt_sql->conn,$sql,$sql_parm);
		if ($res===false or $res==NULL)
		{
			$phpErr=error_get_last();
			$message="Internal DB Error - ".pg_last_error ($fgt_sql->conn);
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
			$message="SQL command of last error: ".$sql;
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
			$message="PHP feedback of last error: ".$phpErr['message'];
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
			$var['exitflag']=true;
			return false;
		}return $res;
	}
	
	public function fix_erric_data() /*Waypoints ID cleared*/
	{
		global $fgt_sql,$fgt_error_report;
		$sub_pid="F_ERRIC";
		
		$message="Fixing erric data";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		
		/*Waypoints with altitude < -9000*/
		$sql="delete from waypoints where altitude<-9000";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
		$nr=pg_affected_rows($res);
		pg_free_result($res);
		$message="$nr waypoints with altitude < -9000 deleted";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_NOTICE);
		
		/*Flights with negative flight duration*/
		$sql="delete from flights where start_time > end_time";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
		$nr=0;
		$nr=pg_affected_rows($res);
		pg_free_result($res);
		$message="$nr flight with negative flight time deleted";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_NOTICE);
		
		$message="Finished fixing erric data";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
	}
	
	public function fix_no_waypoint_flights() /*Waypoints ID cleared*/
	{
		global $fgt_sql,$fgt_error_report,$var;
		$sub_pid="A_ORPHA";

		$message="Fixing no orphan waypoints and no waypoint flights";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		
		/* FIX ME: expensive performance hit
		$sql="delete from waypoints where flight_id is null or flight_id not in (select id from flights)";
		$res=$this->fgt_pg_query_params($sql,NULL);
		if($res===false)
			return;
		$message=pg_affected_rows($res)." waypoints removed";*/
		$message="Orphan waypoints fixing is disabled in this version";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		
		$sql="delete from flights where id not in (select distinct flight_id from waypoints) and status = 'CLOSED' and start_time < $1";
		$res=$this->fgt_pg_query_params($sql,Array($var['archive_date']));
		if($res===false)
			return;
		$message=pg_affected_rows($res)." flights removed";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
	}
	
	public function trim_waypoints($table)/*Waypoints ID cleared*/
	{	/*remove waypoints that have same coordination. return $is_success*/
		global $fgt_error_report,$var;
		$sub_pid="A_TRIMW";
		
		$total_count_del_wpts=0;
		$flight_Array=Array();
		$sql="SELECT DISTINCT flight_id, callsign FROM $table left join flights_all on flight_id=flights_all.id order by flight_id";
		$res=$this->fgt_pg_query_params($sql,NULL);
		$nr=pg_num_rows($res);
		if ($nr==0)
		{
			$message="No flight available for triming";
			$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
			return true;
		}

		for ($i=0;$i<$nr;$i++)
			$flight_Array[]=Array(pg_result($res,$i,"flight_id"),pg_result($res,$i,"callsign"));
		pg_free_result($res);
		
		/*waypoints matter*/
		foreach ($flight_Array AS $current_flight)
		{
			$current_flight_id=$current_flight[0];
			$callsign=$current_flight[1];
			$sql="SELECT * FROM $table WHERE flight_id=$1 order by time";
			$res=$this->fgt_pg_query_params($sql,Array($current_flight_id));
			if ($res===false) return false;
			$nr=pg_num_rows($res);
			
			/*structure of array: time, lat, lon, alt*/
			/*structure of array: 0	,	1,	2,	3*/

			$wpts=Array(Array(NULL,200,200,0),Array(NULL,200,200,0));
			$del_wpts_Array=Array();
			for ($i=0;$i<$nr;$i++)
			{	
				$time=pg_result($res,$i,"time");
				$lat=pg_result($res,$i,"latitude");
				$lon=pg_result($res,$i,"longitude");
				$alt=pg_result($res,$i,"altitude");
				
				if ($wpts[1][1]!=$lat or $wpts[1][2]!=$lon)
				{
					$wpts=Array($wpts[1],Array($time,$lat,$lon,$alt));
					continue;
				} else if (abs($wpts[1][3])-abs($alt) > 1 or abs($wpts[1][3])-abs($alt) < -1)
				{
					$wpts=Array($wpts[1],Array($time,$lat,$lon,$alt));
					continue;
				}
				//print " [Same as before]";
				
				if ($wpts[0][1]!=$lat or $wpts[0][2]!=$lon)
				{
					$wpts=Array($wpts[1],Array($time,$lat,$lon,$alt));
					continue;
				} else if (abs($wpts[1][3])-abs($alt) > 1 or abs($wpts[1][3])-abs($alt) < -1)
				{
					$wpts=Array($wpts[1],Array($time,$lat,$lon,$alt));
					continue;
				}
				
				//print " [delete".$wpts[1][0]."\n";
				$del_wpts_Array[]=$wpts[1][0];
				$wpts=Array($wpts[0],Array($time,$lat,$lon,$alt));
			}
			pg_free_result($res);
			$count_del_wpts=count($del_wpts_Array);

			$del_wpts_str="";
			foreach ($del_wpts_Array AS $del_wpts)
				$del_wpts_str.="'".$del_wpts."',";
			$del_wpts_str=trim($del_wpts_str, ",");
			
			if($del_wpts_str!="")
			{
				if ($nr-$count_del_wpts<2)
				{
					$message="Internal Error: out of limit wpts attempt to delete: $nr - $count_del_wpts on flight id:  $current_flight_id";
					$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
					return false;	
				}
				$sql="Delete from $table where flight_id =$1 AND time IN ($del_wpts_str);";
				$res=$this->fgt_pg_query_params($sql,Array($current_flight_id));
				if ($res===false)
				{
					$message= "FAILED when attempting to trim waypoints for flight $current_flight_id";
					$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
					
					$message="Total of $total_count_del_wpts wpts deleted";
					$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
					return false;
				}
				
				$total_count_del_wpts +=$count_del_wpts;
				
				/*write to log*/
				$sql="INSERT into log (username,\"table\",action,\"when\",callsign,usercomments,flight_id,flight_id2) VALUES ($1, NULL, $2, NOW(), $3,NULL,$4,NULL);";
				$res=$this->fgt_pg_query_params($sql,Array($var['appname'],"$count_del_wpts waypoint(s) removed from flight $current_flight_id due to plane idling",$callsign,$current_flight_id));
				
				if ($table=="waypoints_archive")
				{
					$sql="update flights_archive set wpts=wpts-$count_del_wpts where id=$1;";
					$res=$this->fgt_pg_query_params($sql,Array($current_flight_id));
					if ($res===false)
					{
						$message= "FAILED when attempting to fix no. of wpts for flight $current_flight_id in table $table";
						$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
						
						$message="Total of $total_count_del_wpts wpts deleted";
						$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_ERROR);
						return false;
					}
				}
				
			}
		}
		$message= "Total of $total_count_del_wpts wpts deleted";
		$fgt_error_report->fgt_set_error_report($sub_pid,$message,E_WARNING);
		return true;
	}
	
	public function updateeffectiveflighttimeandicao() /*Waypoints ID cleared*/
	{	/*return Array($is_run_till_the_end ,$is_contains_error_flight);*/
		global $fgt_sql,$fgt_error_report,$var;
		$is_contains_error_flight=false;
		$message="Updating Effective flight time and icao";
		$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_WARNING);
		
		$flight_array=Array();
		$sql="SELECT id,callsign FROM flights_all WHERE status='CLOSED' AND (effective_flight_time IS NULL)";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return Array(false,$is_contains_error_flight);
		$nr=pg_num_rows($res);
		$message="$nr flights need to be updated";
		$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_NOTICE);

		for ($i=0;$i<$nr;$i++)
			$flight_array[]=Array(pg_result($res,$i,"id"),pg_result($res,$i,"callsign"));
		pg_free_result($res);
		$query="";$j=0;
		
		foreach ($flight_array AS $flight)
		{
			$flight_id=$flight[0];
			if($var['exitflag']===true)
				return Array(false,$is_contains_error_flight);
			$sql="SELECT EXTRACT(EPOCH FROM time) AS time,longitude,latitude,altitude FROM waypoints_all WHERE flight_id=$flight_id AND (longitude!=0 OR latitude!=0 OR altitude!=0) AND altitude>=-9000 ORDER BY time";
			$res=$this->fgt_pg_query_params($sql,Array());
			if($res===false)
				return Array(false,$is_contains_error_flight);
			$nr=pg_num_rows($res);

			$array=Array();
			
			for ($i=0;$i<$nr;$i++)
				$array[]=Array(pg_result($res,$i,"latitude"),pg_result($res,$i,"longitude"),pg_result($res,$i,"altitude"),pg_result($res,$i,"time"));
			pg_free_result($res);	
		
			if($nr>1)
			{
				$flight_report = new FLIGHT_REPORT;
				$result=$flight_report->MakeFlightReport ( $array, "NoDiagram" );
				if ($result[0]===false) /*do not update if false*/
				{
					print $result[2]."\n";
					if($result[1]===false)
					{	/*$result[1]===false <- Only delete flights if wpts not identical detected*/
						/*Fix me: Need to write to log*/
						$message="Attempting to delete flight $flight_id";
						$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_NOTICE);

						$reply=delflight($fgt_sql->conn,Array(),$flight_id,$var["alter_db_token"],$var["adminname"],$flight[1],$result[2]);
						if($reply["data"]["ok"]===TRUE)
						{
							$message="Deleted flight $flight_id";
							$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_WARNING);
						}
						else
						{
							$message="Error when attempting to delete flight $flight_id";
							$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_ERROR);
							return Array(false,$is_contains_error_flight);
						}
					} else $is_contains_error_flight=true;
					continue;
				}
				$effectiveFlightTime=$flight_report->GeteffectiveFlightTime();
				$dep_airport=get_nearest_airport($fgt_sql->conn,$array[0][0],$array[0][1],$array[0][2]);
				$arr_airport=get_nearest_airport($fgt_sql->conn,$array[$nr-1][0],$array[$nr-1][1],$array[$nr-1][2]);
			}else if($nr==1)
			{
				$effectiveFlightTime=0;
				$dep_airport=$arr_airport=get_nearest_airport($fgt_sql->conn,$array[0][0],$array[0][1],$array[0][2]);
			}
				
			if ($nr<1)
			{
				$query.="UPDATE flights set effective_flight_time=0, start_icao=NULL,end_icao=NULL where id=$flight_id;";
				$query.="UPDATE flights_archive set effective_flight_time=0, start_icao=NULL,end_icao=NULL where id=$flight_id;";

			}else
			{
				$query.="UPDATE flights set effective_flight_time=$effectiveFlightTime, start_icao='$dep_airport[0]',end_icao='$arr_airport[0]' where id=$flight_id;";
				$query.="UPDATE flights_archive set effective_flight_time=$effectiveFlightTime, start_icao='$dep_airport[0]',end_icao='$arr_airport[0]' where id=$flight_id;";
			}
			
			if ($j%100==0)
			{
				$message="COMMIT $j done";
				$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_NOTICE);
				if($this->fgt_pg_query_params($query,NULL)===false)
					break;
				$query="";
			}
			$j++;
		}
		if ($query!="")
			$this->fgt_pg_query_params($query,NULL);
		$message="$j flights updated";
		$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_WARNING);
		return Array(true,$is_contains_error_flight);
	}

	public function updateranking() /*Waypoints ID cleared*/
	{
		global $fgt_sql,$fgt_error_report;
		$message="Updating Ranking";
		$fgt_error_report->fgt_set_error_report("U_RANK",$message,E_WARNING);
		
		$temp='temp_cache_top100_alltime';
		$perm='cache_top100_alltime';
		$sql="Truncate table $temp;";
		$sql.="select setval('temp_cache_top100_alltime_rank_seq',1);";
		$sql.="INSERT INTO $temp SELECT f.callsign AS callsign,justify_hours(sum(f.end_time-f.start_time)) AS flighttime,justify_hours(sum(effective_flight_time)* '1 second'::interval) AS effective_flight_time FROM flights_all as f GROUP BY f.callsign HAVING sum(effective_flight_time) is not null ORDER BY sum(effective_flight_time) DESC;";
		$sql.="update $temp set rank=rank-1;";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
		
		$sql="Truncate table $perm;";
		$sql.="INSERT INTO $perm select callsign,flighttime,rank,null,null,effective_flight_time from $temp;";
		$sql.="update $perm AS P set lastweek=(select justify_hours(sum(f.end_time-f.start_time)) AS flighttime FROM flights as f WHERE (age(now(),f.end_time)<='7 days'::interval) and f.callsign=P.callsign HAVING sum(f.end_time-f.start_time)>'00:00:05'::interval);";
		$sql.="update $perm AS P set effective_lastweek=(select justify_hours(sum(effective_flight_time)* '1 second'::interval) AS flighttime FROM flights as f WHERE (age(now(),f.end_time)<='7 days'::interval) and f.callsign=P.callsign HAVING sum(f.end_time-f.start_time)>'00:00:05'::interval);";
		$sql.="update $perm AS P set last30days=(select justify_hours(sum(f.end_time-f.start_time)) AS flighttime FROM flights as f WHERE (age(now(),f.end_time)<='30 days'::interval) and f.callsign=P.callsign HAVING sum(f.end_time-f.start_time)>'00:00:05'::interval);";
		$sql.="update $perm AS P set effective_last30days=(select justify_hours(sum(effective_flight_time)* '1 second'::interval) AS flighttime FROM flights as f WHERE (age(now(),f.end_time)<='30 days'::interval) and f.callsign=P.callsign HAVING sum(f.end_time-f.start_time)>'00:00:05'::interval);";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
	}
}
?>