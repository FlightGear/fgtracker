<?php
class UpdateMgr
{
	
	public function __construct () 
	{}
	
	function fgt_pg_query_params($sql,$sql_parm)
	{	/*if $sql_parm = NULL, pg_query is used
		  if $sql_parm is an array, pg_query_params is used
		*/
		global $fgt_error_report,$var,$fgt_sql;
		if($sql_parm==NULL)
			$res=pg_query($fgt_sql->conn,$sql);
		else
			$res=pg_query_params($fgt_sql->conn,$sql,$sql_parm);
		if ($res===false or $res==NULL)
		{
			$phpErr=error_get_last();
			$message="Internal DB Error - ".pg_last_error ($fgt_sql->conn);
			$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_ERROR);
			$message="SQL command of last error: ".$sql;
			$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_ERROR);
			$message="PHP feedback of last error: ".$phpErr['message'];
			$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_ERROR);
			$var['exitflag']=true;
			return false;
		}return $res;
	}
	
	public function fix_erric_data()
	{
		global $fgt_sql,$fgt_error_report;
		
		$message="Fixing erric data";
		$fgt_error_report->fgt_set_error_report("F_ERRIC",$message,E_WARNING);
		
		/*Waypoints with altitude < -9000*/
		$sql="delete from waypoints where altitude<-9000";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
		$nr=pg_affected_rows($res);
		pg_free_result($res);
		$message="$nr waypoints with altitude < -9000 deleted.";
		$fgt_error_report->fgt_set_error_report("F_ERRIC",$message,E_NOTICE);
		
		/*Flights with negative flight duration*/
		$sql="delete from flights where start_time > end_time";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
		$nr=0;
		$nr=pg_affected_rows($res);
		pg_free_result($res);
		$message="$nr flight with negative flight time deleted.";
		$fgt_error_report->fgt_set_error_report("F_ERRIC",$message,E_NOTICE);
		
		$message="Finished fixing erric data";
		$fgt_error_report->fgt_set_error_report("F_ERRIC",$message,E_WARNING);
	}
	
	public function updateeffectiveflighttimeandicao()
	{
		global $fgt_sql,$fgt_error_report,$var;
		$message="Updating Effective flight time and icao";
		$fgt_error_report->fgt_set_error_report("U_EFT",$message,E_WARNING);
		
		$flight_array=Array();
		$sql="SELECT id,callsign FROM flights_all WHERE status='CLOSED' AND (effective_flight_time IS NULL)";
		$res=$this->fgt_pg_query_params($sql,Array());
		if($res===false)
			return;
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
				return;
			$sql="SELECT EXTRACT(EPOCH FROM time) AS time,longitude,latitude,altitude FROM waypoints_all WHERE flight_id=$flight_id AND (longitude!=0 OR latitude!=0 OR altitude!=0) AND altitude>=-9000 ORDER BY time";
			$res=$this->fgt_pg_query_params($sql,Array());
			if($res===false)
				return;
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
					{	/*Fix me: Need to write to log*/
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
							$var['exitflag']=true;
							return;
						}
					}
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
	}

	public function updateranking()
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