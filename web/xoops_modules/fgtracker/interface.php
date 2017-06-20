<?php

/*some global setting*/
$time_start = microtime(true); 
$reply=Array();

/*Allow CORS*/
header("Access-Control-Allow-Origin: *");

/*check if server is overloaded*/
$serverload=explode(" ",file_get_contents('/proc/loadavg'));
if (floatval($serverload[0])>10)
{
	
	$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error. This server is overloaded. Current loading: '.$serverload[0]);
	$reply=addheader($reply);
	print json_encode($reply);
	return;
}
require("interface.files/config.php");
include_once 'include/flight_report.php';
include_once 'include/get_nearest_airport.php';
include_once 'interface.files/client.php';
include_once 'interface.files/flight_merge.php';
include_once 'interface.files/flight_delete.php';
include_once 'interface.files/reg_callsign.php';

if ($conn ===FALSE)
{
	$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error. DB cannot be connected');
	$reply=addheader($reply);
	print json_encode($reply);
	return;
}

$res=pg_query($conn,"SET application_name = 'FGTracker interface';");
setup_client();

/*start*/
$action=$callsign=$archive=$offset=$flightid=$orderby=$wpt="";
$action=trim($_GET['action']);

switch ($action)
{
	case "airport":
		$icao=@trim($_GET['icao']);
		$reply=airport($conn,$reply,$icao);
	break;
	case "alterlog":
		$callsign=@trim($_GET['callsign']);
		$reply=alterlog($conn,$reply,$callsign);
	break;
	case "delflight":
		$flightid=@trim($_GET['flightid']);
		$token=@trim($_GET['token']);
		$username=@trim($_GET['username']);
		$callsign=@trim($_GET['callsign']);
		$usercomments=@trim($_GET['usercomments']);
		$reply=delflight($conn,$reply,$flightid,$token,$username,$callsign,$usercomments);
		$reply["header"]=Array("code"=>200,"msg"=>'OK');
	break;
	case "fgmsstatus":
		$reply=fgmsstatus($conn,$reply);
	break;
	case "flights":
		$callsign=@trim($_GET['callsign']);
		$archive=@trim($_GET['archive']);
		$offset=@trim($_GET['offset']);
		$reply=flights($conn,$reply,$callsign,$archive,$offset);
	break;
	case "flight":
		$flightid=@trim($_GET['flightid']);
		$reply=flight($conn,$reply,$flightid);
	break;
	case "livepilots":
		$callsign=@trim($_GET['callsign']);
		$wpt=@trim($_GET['wpt']);
		$reply=livepilots($conn,$reply,$callsign,$wpt);
	break;
	case "livewaypoints":
		$reply=livewaypoints($conn,$reply);
	break;
	case "mergeflight":
		$flightid=@trim($_GET['flightid']);
		$nflightid=@trim($_GET['nflightid']);
		$token=@trim($_GET['token']);
		$username=@trim($_GET['username']);
		$usercomments=@trim($_GET['usercomments']);
		$result=merge_request($conn, $flightid, $nflightid,$username,$token,$usercomments);
		if($result["ok"]===false)
		{
			$reply["data"]["ok"]=false;
			if($username=$var["adminname"])
				$reply["data"]["msg"]=$result["msg"];
			else $reply["data"]["msg"]="Please contact Hazuki @ Flightgear forum";
		}else
		{
			$reply["data"]["ok"]=true;
			$reply["data"]["msg"]="Succeed";
		}
		$reply["header"]=Array("code"=>200,"msg"=>'OK');
	break;
	case "pilotlist":
		$offset=@trim($_GET['offset']);
		$orderby=@trim($_GET['orderby']);
		$reply=pilotlist($conn,$reply,$offset,$orderby);
	break;
	case "recentstateswitch":
		$reply=recentstateswitch($conn,$reply);
	break;
	case "regcallsign":
		$callsign=@trim($_GET['callsign']);
		$email=@trim($_GET['email']);
		$ip=@trim($_GET['ip']);
		$grecaptcharesponse=@trim($_GET['grecaptcharesponse']);
		$reply=reg_callsign($conn,$reply,$callsign,$email,$ip,$grecaptcharesponse);
	break;
	case "regcallsign2":
		$callsign=@trim($_GET['callsign']);
		$token=@trim($_GET['token']);
		$reply=reg_callsign2($conn,$reply,$callsign,$token);
	break;
	default:
	$reply["header"]=Array("code"=>400,"msg"=>'Bad Request. Action not defined.');
}

/*print "<pre>";
var_dump($reply);
print "</pre>";*/
$reply=addheader($reply,$time_start,$client);
$ru = getrusage();

print json_encode(array_reverse($reply, true));

if(json_last_error()!=JSON_ERROR_NONE)
{
	print json_last_error_msg ();
	print "<pre>";
	var_dump($reply);
	print "</pre>";
}
	

function airport($conn,$reply,$icao)
{
	$icao_escaped=pg_escape_string($conn,$icao);
    $res=pg_query($conn,"SELECT * from geo_airports WHERE icao='$icao_escaped'");
    if ($res===false)
	{
		$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error');
		return $reply;
	}
    {
		$nr=pg_num_rows ( $res );
		if($nr==0)
		{
			$reply["header"]=Array("code"=>404,"msg"=>"Airport $icao not found");
			return $reply;
		}
		
	}
	$icao=pg_result($res,0,'icao');
	$reply["data"]['icao']=$icao;
	$reply["data"]['name']=pg_result($res,0,'name');
	$reply["data"]['type']=intval(pg_result($res,0,'airport_type'));
	$reply["data"]['lat']=floatval(pg_result($res,0,'lat'));
	$reply["data"]['lon']=floatval(pg_result($res,0,'lon'));
	$reply["data"]['alt']=floatval(pg_result($res,0,'alt'));
	$reply["data"]['city']=pg_result($res,0,'city');
	$reply["data"]['country']=pg_result($res,0,'country');
	$reply["data"]['zone']=pg_result($res,0,'zone_name');
	$reply["data"]['administrative_area_level_1']=pg_result($res,0,'admin_area_lv_1');
	$reply["data"]['administrative_area_level_2']=pg_result($res,0,'admin_area_lv_2');
	$reply["data"]['administrative_area_level_3']=pg_result($res,0,'admin_area_lv_3');
	$reply["data"]['administrative_area_level_4']=pg_result($res,0,'admin_area_lv_4');

	/*Write header*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}

function addheader($reply,$time_start,$client)
{	
	$time_end = microtime(true);
	date_default_timezone_set($client['timezone']);
	$reply["header"]["request_time"]=date("Y-m-d H:i:sO",$_SERVER['REQUEST_TIME']);
	$reply["header"]["request_time_raw"]=$_SERVER['REQUEST_TIME'];
	$reply["header"]["process_time"]=$time_end-$time_start;
	$reply["header"]["request_ip"]=$client['ip'];
	$reply["header"]["request_location"]=$client['country'];
	$reply["header"]["request_location_name"]=$client['country_name'];
	$reply["header"]["request_timezone"]=$client['timezone'];
	$reply["header"]["request_timezone_abbr"]=$client['timezone_abbr'];
	return $reply;
}

function alterlog($conn,$reply,$callsign)
{
	global $var;
	if (trim($callsign)=="")
	{
		$reply["header"]=Array("code"=>400,"msg"=>'Bad Request. Callsign not defined.');
		return $reply;
	}
	
	$callsign_escaped=pg_escape_string($conn,$callsign);
	$res=pg_query($conn,"SELECT *,date_trunc('milliseconds', \"when\") as when_tunc from log WHERE callsign='$callsign_escaped' order by \"when\" desc");
	if ($res===false)
	{
		$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error');
		return $reply;
	}
	$nr=pg_num_rows ( $res );
	if($nr==0)
	{
		$reply["header"]=Array("code"=>404,"msg"=>"Callsign $callsign_escaped not found");
		return $reply;
	}
	for ($i=0;$i<$nr;$i++)
	{
		$username=pg_result($res,$i,'username');
		if (substr( $username, 0, 2 ) == "10.")
			$username="Intranet";
		else if (filter_var($username, FILTER_VALIDATE_IP))
		{
			$iparr=explode(".",$username);
			$username=$iparr[0].".".$iparr[1].".*.*";
		}
	
		$log_array=Array("flight_id"=>pg_result($res,$i,'flight_id'),"operating_user"=>$username, "action"=>pg_result($res,$i,'action'),"time"=>pg_result($res,$i,'when_tunc'),"comments"=>pg_result($res,$i,'usercomments'));
		$reply["data"]['log'][]=$log_array;
	}
	
	/*header*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}

function fgmsstatus($conn,$reply)
{
	$res=pg_query($conn,"SELECT name,ip,key,reported_ver,maintainer,location,date_trunc('second', last_comm) AS last_comm,EXTRACT(EPOCH FROM last_comm)::int AS last_comm_raw,enabled, (select count(*) from flights where server=name AND status='OPEN' and NOW()-start_time < INTERVAL '2 DAY') AS tracking_count FROM fgms_servers where enabled is true order BY name");
	if ($res===false)
	{
		$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error');
		return $reply;
	}
	$nr=pg_num_rows($res);
	for ($i=0;$i<$nr;$i++)
	{
		if (pg_result($res,$i,'enabled')=='t')
			$enabled=true;
		else $enabled=false;
		$reply["data"][]=Array("name"=>pg_result($res,$i,'name'), "domain"=>pg_result($res,$i,'ip'), "protocol_ver"=>pg_result($res,$i,'key'), "ver"=>pg_result($res,$i,'reported_ver'), "maintainer"=>pg_result($res,$i,'maintainer'), "location"=>pg_result($res,$i,'location'), "tracking_count"=>intval(pg_result($res,$i,'tracking_count')), "last_seen"=>pg_result($res,$i,'last_comm'), "last_seen_raw"=>intval(pg_result($res,$i,'last_comm_raw')), "enabled"=>$enabled);
	}
	pg_free_result($res);
	
	/*header*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}

function flight($conn,$reply,$flightid)
{
    global $var;
	
	if (intval($flightid)<=1)
	{
		$reply["header"]=Array("code"=>400,"msg"=>'Bad Request. Flightid not defined.');
		return $reply;
	}
	
	$flightid_escaped=pg_escape_string($conn,$flightid);

    /*alterlog*/
	$reply["data"]["log"]=NULL;
	$res=pg_query($conn,"SELECT username,action,date_trunc('milliseconds', \"when\") as when_tunc,usercomments FROM log WHERE flight_id=$flightid_escaped or flight_id2=$flightid_escaped order by \"when\" desc");
	$nr=pg_num_rows($res);
	for ($i=0;$i<$nr;$i++)
    {
		$username=pg_result($res,$i,'username');
		if (substr( $username, 0, 2 ) == "10.")
			$username="Intranet";
		else if (filter_var($username, FILTER_VALIDATE_IP))
		{
			$iparr=explode(".",$username);
			$username=$iparr[0].".".$iparr[1].".*.*";
		}	
		
		$log_array=Array("operating_user"=>$username, "action"=>pg_result($res,$i,'action'),"time"=>pg_result($res,$i,'when_tunc'),"comments"=>pg_result($res,$i,'usercomments'));
		$reply["data"]['log'][]=$log_array;
	}
	pg_free_result($res);
	
	/*get flight details*/
	$res=pg_query($conn,"SELECT callsign, human_string AS model, model AS model_raw, start_time, start_time AT TIME ZONE 'UTC' AS start_time_utc,EXTRACT(EPOCH FROM start_time) AS start_time_raw,end_time, end_time AT TIME ZONE 'UTC' AS end_time_utc, EXTRACT(EPOCH FROM end_time) AS end_time_raw, justify_hours(end_time-start_time) AS duration,EXTRACT(EPOCH FROM end_time-start_time) AS duration_raw, \"table\" FROM flights_all left join models AS m ON fg_string=model WHERE id=$flightid_escaped");
    if ($res===false)
	{
		$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error');
		return $reply;
	}
	$nr=pg_num_rows ( $res );
	if($nr==0)
	{
		$reply["header"]=Array("code"=>404,"msg"=>"Flight $flightid not found");
		return $reply;
	}
	$is_archive=null;
	$table=pg_result($res,0,'table');
	if($table=='flights')
		$is_archive=false;
	elseif ($table=='flights_archive')
		$is_archive=true;
	$callsign=pg_result($res,0,'callsign');
	$model=pg_result($res,0,'model');
	$model_raw=pg_result($res,0,'model_raw');
	$start_time_raw=intval(pg_result($res,0,'start_time_raw'));
	$start_time=pg_result($res,0,'start_time');
	$start_time_utc=pg_result($res,0,'start_time_utc');
	$end_time=pg_result($res,0,'end_time');
	$end_time_utc=pg_result($res,0,'end_time_utc');
	$end_time_raw=intval(pg_result($res,0,'end_time_raw'));
	$duration=pg_result($res,0,'duration');
	$duration_raw=intval(pg_result($res,0,'duration_raw'));
	pg_free_result($res);
	
	$row_offset=get_flight_row_offset($conn,$flightid,$callsign,$table);
	$reply["data"]['flight_id']=intval($flightid);
	$reply["data"]['is_archive']=$is_archive;
	$reply["data"]['row']=$row_offset[0];
	$reply["data"]['offset']=$row_offset[1];
	$reply["data"]['callsign']=$callsign;
	$reply["data"]['model']=$model;
	$reply["data"]['model_raw']=$model_raw;
	$reply["data"]['start_time']=$start_time;
	$reply["data"]['start_time_utc']=$start_time_utc;
	$reply["data"]['start_time_raw']=$start_time_raw;
	$reply["data"]['end_time']=$end_time;
	$reply["data"]['end_time_utc']=$end_time_utc;
	$reply["data"]['end_time_raw']=$end_time_raw;
	$reply["data"]['duration']=$duration;
	$reply["data"]['duration_raw']=$duration_raw;
	
    $res=pg_query($conn,"SELECT time ,EXTRACT(EPOCH FROM time) AS time_raw,longitude,latitude,altitude,heading FROM waypoints_all WHERE flight_id=$flightid_escaped AND (longitude!=0 OR latitude!=0 OR altitude!=0) AND altitude>=".$var["min_alt"]." ORDER BY time;");
    $nr=pg_num_rows($res);
	if ($res===false)
	{
		unset ($reply["data"]);
		$reply["header"]=Array("code"=>500,"msg"=>'Internal Server Error');
		return $reply;
	}
	$reply["data"]['wpts']=$nr;
	for ($i=0;$i<$nr;$i++)
    {
		$time=pg_result($res,$i,"time");
		$time_raw=intval(pg_result($res,$i,"time_raw"));
		$lat=floatval(pg_result($res,$i,"latitude"));
		$lon=floatval(pg_result($res,$i,"longitude"));
		$alt=floatval(pg_result($res,$i,"altitude"));
		if(is_null(pg_result($res,$i,"heading")))
			$hdg=null;
		else
			$hdg=floatval(pg_result($res,$i,"heading"));
	   
		$wpt=Array("time"=>$time,"time_raw"=>$time_raw,"lat"=>$lat,"lon"=>$lon,"alt"=>$alt,"hdg"=>$hdg);
		$reply["data"]['wpt'][]=$wpt;
	}
	pg_free_result($res);
	
	/*dep airport*/
	$dep_airport=get_nearest_airport($conn,$reply["data"]['wpt'][0]["lat"],$reply["data"]['wpt'][0]["lon"],$reply["data"]['wpt'][0]["alt"]);
	$reply["data"]["start_location"]["icao"]=$dep_airport[0];
	$reply["data"]["start_location"]["icao_name"]=$dep_airport[1];
	$reply["data"]["start_location"]["country"]=$dep_airport[2];
	$reply["data"]["start_location"]["zone"]=$dep_airport[4];
	
	/*Get departure local time*/
	if($dep_airport[4]!= NULL)
	{
		$res=pg_query_params($conn,"SELECT $1 AT TIME ZONE $2 AS start_time_local",Array($start_time,$dep_airport[4]));
		if (pg_num_rows($res)!=0)
		{
			$reply["data"]["start_location"]["start_time_local"]=pg_result($res,0,'start_time_local');
		}
	}

	/*arrival airport*/
	$arr_airport=get_nearest_airport($conn,$lat,$lon,$alt);
	$reply["data"]["end_location"]["icao"]=$arr_airport[0];
	$reply["data"]["end_location"]["icao_name"]=$arr_airport[1];
	$reply["data"]["end_location"]["country"]=$arr_airport[2];
	$reply["data"]["end_location"]["zone"]=$arr_airport[4];
	
	/*Get arrival local time*/
	if($arr_airport[4]!= NULL and $end_time !=NULL)
	{
		$res=pg_query_params($conn,"SELECT $1 AT TIME ZONE $2 AS end_time_local",Array($end_time,$arr_airport[4]));
		if (pg_num_rows($res)!=0)
		{
			$reply["data"]["end_location"]["end_time_local"]=pg_result($res,0,'end_time_local');
		}
	}
	
	/*previous flight details*/
	$res=pg_query($conn,"SELECT id, callsign, human_string AS model, model AS model_raw, start_time , end_time , EXTRACT(EPOCH FROM start_time) AS start_time_raw,EXTRACT(EPOCH FROM end_time) AS endtime_raw FROM flights_all left join models AS m ON fg_string=model WHERE id<$flightid_escaped and callsign='$callsign' order by end_time desc limit 1");
	if (pg_num_rows($res)!=0)
    {
		$p_flightid=pg_result($res,0,'id');
		$reply["data"]["previous_flight"]["flight_id"]=intval($p_flightid);
		$reply["data"]["previous_flight"]["model"]=pg_result($res,0,'model');
		$reply["data"]["previous_flight"]["model_raw"]=pg_result($res,0,'model_raw');
		$reply["data"]["previous_flight"]["start_time"]=pg_result($res,0,'start_time');
		$reply["data"]["previous_flight"]["start_time_raw"]=intval(pg_result($res,0,'start_time_raw'));
		$reply["data"]["previous_flight"]["end_time"]=pg_result($res,0,'end_time');
		$reply["data"]["previous_flight"]["end_time_raw"]=intval(pg_result($res,0,'endtime_raw'));
		pg_free_result($res);
	
		/*previous waypoints (arrival)*/
		$res=pg_query($conn,"SELECT time,longitude,latitude FROM waypoints_all WHERE flight_id=$p_flightid AND (longitude!=0 OR latitude!=0 OR altitude!=0) ORDER BY time desc limit 1");
		if (pg_num_rows($res)!=0)
		{
			$p_arrlat=pg_result($res,0,'latitude');
			$p_arrlon=pg_result($res,0,'longitude');
			$GML_distance=GML_distance($p_arrlat,$p_arrlon,$reply["data"]['wpt'][0]["lat"],$reply["data"]['wpt'][0]["lon"]);
			$reply["data"]["previous_flight"]["distance_difference"]=round($GML_distance[1]*1000);
			pg_free_result($res);
		}
		
		/*check merge indicator*/
		if (check_merge_request($conn, $p_flightid, $flightid,false)===TRUE)
			$reply["data"]["previous_flight"]["mergeok"]=true;
		else $reply["data"]["previous_flight"]["mergeok"]=false;
    }else $reply["data"]["previous_flight"]["mergeok"]=false;

	/*header*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}

function flights($conn,$reply,$callsign,$archive,$offset)
{
	if ($callsign=="")
	{
		$reply["header"]=Array("code"=>400,"msg"=>'Bad Request. Callsign not defined.');
		return $reply;
	}
	
	/*set Callsign*/
	$reply["data"]["callsign"]=$callsign;
	
	$callsign_escaped=pg_escape_string($conn,$callsign);
	
	/*If callsign is registered*/
	$callsign_check=callsign_registered($conn,$callsign_escaped);
	if(is_null($callsign_check["activation_level"]))
		$reply["data"]["status"]="Not Registered";
	else
		switch ($callsign_check["activation_level"]) 
		{
			case -3:
				$reply["data"]["status"]="Dispute";
			case -2:
				$reply["data"]["status"]="Protected";
			break;
			case -1:
				$reply["data"]["status"]="Deactivated";
			break;
			case 0:
				$reply["data"]["status"]="Registered";
			break;
			case 10:
				$reply["data"]["status"]="Activated";
			break;
			default:
				$reply["data"]["status"]="Unknown";
		}
	/*No of flights*/
	if ($archive=="true")
	{
		$reply["data"]["is_archive"]=true;
		$res=pg_query($conn,"SELECT count(*) FROM flights_archive WHERE callsign='$callsign_escaped';");
	}
	else
	{
		$reply["data"]["is_archive"]=false;
		$res=pg_query($conn,"SELECT count(*) FROM flights WHERE callsign='$callsign_escaped';");
	}
	if ($reply["data"]["status"]=="Dispute")
		return $reply;
	
    $num_flights=pg_result($res,0,0);
	$reply["data"]["no_of_flights"]=intval($num_flights);
    pg_free_result($res);
	
	/*offsets*/
	$offset=intval($offset);
	if ($offset<0 or $offset>$num_flights)
		$offset=0;
	$reply["data"]["flight_list_offset"]=$offset;
	
	/*pilot ranking and data*/
	$res=pg_query($conn,"SELECT rank,
	CASE WHEN effective_lastweek is null then '0' else effective_lastweek END AS lastweek ,
	CASE WHEN effective_lastweek is null then '0' else EXTRACT(EPOCH FROM effective_lastweek) END AS lastweek_raw ,
	CASE WHEN effective_last30days is null then '0' else effective_last30days END AS last30days,
	CASE WHEN effective_last30days is null then '0' else EXTRACT(EPOCH FROM effective_last30days) END AS last30days_raw,
	flighttime,EXTRACT(EPOCH FROM flighttime) AS flighttime_raw,
	effective_flight_time,EXTRACT(EPOCH FROM effective_flight_time) AS effective_flight_time_raw
	FROM cache_top100_alltime WHERE callsign='$callsign_escaped'");
	$reply["data"]["rank"]=pg_result($res,0,"rank");
	$reply["data"]["lastweek"]=pg_result($res,0,"lastweek");
	$reply["data"]["lastweek_raw"]=intval(pg_result($res,0,"lastweek_raw"));
	$reply["data"]["last30days"]=pg_result($res,0,"last30days");
	$reply["data"]["last30days_raw"]=intval(pg_result($res,0,"last30days_raw"));
	$reply["data"]["total_flight_time"]=pg_result($res,0,"flighttime");
	$reply["data"]["total_flight_time_raw"]=intval(pg_result($res,0,"flighttime_raw"));
	$reply["data"]["effective_flight_time"]=pg_result($res,0,"effective_flight_time");
	$reply["data"]["effective_flight_time_raw"]=intval(pg_result($res,0,"effective_flight_time_raw"));
	pg_free_result($res);
	
	/*GET_ARCHIVE_DATE*/
	$res=pg_query($conn,"select MAX(start_time) AS dates from flights_archive;");
	$nr=pg_num_rows($res);
	$reply["data"]["db_archive_date"]=substr(pg_result($res,0,"dates"),0,strpos(pg_result($res,0,"dates"), " "));
	pg_free_result($res);
	
	/*Flight time by type report*/
	$res=pg_query($conn,"SELECT model as model_raw,(SELECT human_string FROM models WHERE fg_string=model) AS model, justify_hours(sum(end_time-start_time)) AS duration,EXTRACT(EPOCH FROM sum(end_time-start_time)) AS duration_raw,justify_hours(sum(effective_flight_time)* '1 second'::interval) AS effective_flight_time, EXTRACT(EPOCH FROM sum(effective_flight_time)* '1 second'::interval) AS effective_flight_time_raw FROM flights_all WHERE callsign='$callsign_escaped' GROUP BY model ORDER BY justify_hours(sum(end_time-start_time)) desc");
    $nr=pg_num_rows($res);
	for($i=0;$i<$nr;$i++)
    {
        $model=pg_result($res,$i,"model");
        $model_raw=pg_result($res,$i,"model_raw");
        $duration=pg_result($res,$i,"duration");
		$duration_raw=intval(pg_result($res,$i,"duration_raw"));
        $effective_flight_time=pg_result($res,$i,"effective_flight_time");
		$effective_flight_time_raw=intval(pg_result($res,$i,"effective_flight_time_raw"));

        $table1['model']=$model;
        $table1['model_raw']=$model_raw;
        $table1['duration']=$duration;
        $table1['duration_raw']=$duration_raw;
        $table1['effective_flight_time']=$effective_flight_time;
        $table1['effective_flight_time_raw']=$effective_flight_time_raw;
		$reply["data"]["flight_time_by_type"][]=$table1;
    }
    pg_free_result($res);
	
	/*Start/end ICAO list*/
	$res=pg_query($conn,"select start_icao, name, country,count(*) AS counter from flights_all left join geo_airports ON start_icao=icao WHERE callsign='$callsign_escaped' group by start_icao, country,name order by count(*) desc, start_icao");
    $nr=pg_num_rows($res);
	for($i=0;$i<$nr;$i++)
    {
		if(pg_result($res,$i,"start_icao")=='----')
			$name='Unknown';
		else
			$name=pg_result($res,$i,"name");
		$table3['icao']=pg_result($res,$i,"start_icao");
		$table3['icao_name']=$name;
		$table3['country']=pg_result($res,$i,"country");
		$table3['count']=pg_result($res,$i,"counter");
		$reply["data"]["start_icaos"][]=$table3;
	}
	pg_free_result($res);
	
	$res=pg_query($conn,"select end_icao, name, country,count(*) AS counter from flights_all left join geo_airports ON end_icao=icao WHERE callsign='$callsign_escaped' group by end_icao, country,name order by count(*) desc, end_icao");
    $nr=pg_num_rows($res);
	for($i=0;$i<$nr;$i++)
    {
		if(pg_result($res,$i,"end_icao")=='----')
			$name='Unknown';
		else
			$name=pg_result($res,$i,"name");
		$table3['icao']=pg_result($res,$i,"end_icao");
		$table3['icao_name']=$name;
		$table3['country']=pg_result($res,$i,"country");
		$table3['count']=pg_result($res,$i,"counter");
		$reply["data"]["end_icaos"][]=$table3;
	}
	pg_free_result($res);
	
	/*flight_list*/
	if ($archive=="true")
		$res=pg_query($conn,"SELECT id,callsign, human_string AS model, model AS model_raw,start_time, EXTRACT(EPOCH FROM start_time) AS start_time_raw, end_time, EXTRACT(EPOCH FROM end_time) AS end_time_raw, end_time-start_time AS duration, EXTRACT(EPOCH FROM end_time-start_time) AS duration_raw, justify_hours(effective_flight_time* '1 second'::interval) AS effective_flight_time, EXTRACT(EPOCH FROM effective_flight_time* '1 second'::interval) AS effective_flight_time_raw, wpts as numwpts, start_icao, dep.name as start_icaoname, dep.country AS start_country, end_icao, arr.name as end_icaoname, arr.country AS end_country FROM flights_archive AS f left join models AS m ON fg_string=model left join geo_airports as dep ON start_icao=dep.icao left join geo_airports as arr ON end_icao=arr.icao WHERE callsign='$callsign_escaped' ORDER BY start_time DESC LIMIT 100 OFFSET $offset;");
	else
		$res=pg_query($conn,"SELECT id,callsign, human_string AS model, model AS model_raw,start_time, EXTRACT(EPOCH FROM start_time) AS start_time_raw, end_time, EXTRACT(EPOCH FROM end_time) AS end_time_raw, end_time-start_time AS duration, EXTRACT(EPOCH FROM end_time-start_time) AS duration_raw, justify_hours(effective_flight_time* '1 second'::interval) AS effective_flight_time, EXTRACT(EPOCH FROM effective_flight_time* '1 second'::interval) AS effective_flight_time_raw, (SELECT count(*) from waypoints where f.id=flight_id) as numwpts, start_icao, dep.name as start_icaoname, dep.country as start_country, end_icao, arr.name as end_icaoname, arr.country as end_country FROM flights AS f left join models AS m ON fg_string=model left join geo_airports as dep ON start_icao=dep.icao left join geo_airports as arr ON end_icao=arr.icao WHERE callsign='$callsign_escaped' ORDER BY start_time DESC LIMIT 100 OFFSET $offset;");

    $nr=pg_num_rows($res);

    $j=$num_flights-$offset;
	for($i=0;$i<$nr;$i++)
    {
		$id=intval(pg_result($res,$i,"id"));
		$callsign=pg_result($res,$i,"callsign");
		$model=pg_result($res,$i,"model");
		$model_raw=pg_result($res,$i,"model_raw");
		$start_time=pg_result($res,$i,"start_time");
		$start_time_raw=intval(pg_result($res,$i,"start_time_raw"));
		$start_icao=pg_result($res,$i,"start_icao");
		$start_icaoname=pg_result($res,$i,"start_icaoname");
		$start_country=pg_result($res,$i,"start_country");
		$end_time=pg_result($res,$i,"end_time");
		$end_time_raw=intval(pg_result($res,$i,"end_time_raw"));
		$end_icao=pg_result($res,$i,"end_icao");
		$end_icaoname=pg_result($res,$i,"end_icaoname");
		$end_country=pg_result($res,$i,"end_country");
		$duration=pg_result($res,$i,"duration");
		$duration_raw=pg_result($res,$i,"duration_raw");
		$numwpts=intval(pg_result($res,$i,"numwpts"));
		$effective_flight_time=pg_result($res,$i,"effective_flight_time");
		$effective_flight_time_raw=intval(pg_result($res,$i,"effective_flight_time_raw"));

		$table2['row']=$j;
		$table2['id']=$id;
		$table2['callsign']=$callsign;
		$table2['model']=$model;
		$table2['model_raw']=$model_raw;
		$table2['start_time']=$start_time;
		$table2['start_time_raw']=$start_time_raw;
		$table2['start_location']["icao"]=$start_icao;
		$table2['start_location']["icao_name"]=$start_icaoname;
		$table2['start_location']["country"]=$start_country;
		$table2['end_time']=$end_time;
		$table2['end_time_raw']=$end_time_raw;
		$table2['end_location']["icao"]=$end_icao;
		$table2['end_location']["icao_name"]=$end_icaoname;
		$table2['end_location']["country"]=$end_country;
		$table2['duration']=$duration;
		$table2['duration_raw']=$duration_raw;
		$table2['numwpts']=$numwpts;
		$table2['effective_flight_time']=$effective_flight_time;
		$table2['effective_flight_time_raw']=$effective_flight_time_raw;

		$reply["data"]["flight_list"][]=$table2;	
		$j--;
    }
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;

}

function get_flight_row_offset($conn,$flightid,$callsign,$table)
{
	$res=pg_query($conn,"SELECT id FROM flights_all WHERE callsign='$callsign' AND \"table\"='$table' order by id;");
	if ($res===false)
		return Array(false,false);
	
	$nr=pg_num_rows ( $res );
	$row=0;
	while($row<=$nr)
	{
		$row++;
		if(pg_result($res,$row-1,'id')==$flightid)
			break;
	}
	pg_free_result($res);
	return Array($row,$nr-$row);
}

function livepilots($conn,$reply,$callsign,$wpt)
{
	global $var;
	
	if ($callsign=="")
	{
		$res=pg_query($conn,"select flight_id, callsign, status, human_string AS model, model AS model_raw, start_time, EXTRACT(EPOCH FROM start_time) AS start_time_raw, time, EXTRACT(EPOCH FROM start_time) AS time_raw, latitude, longitude, altitude, heading from flights left join models AS m ON fg_string=model join waypoints on waypoints.flight_id=flights.id where status='OPEN' and NOW()-start_time < INTERVAL '2 DAY' order by flight_id desc, waypoints.time desc");
	} else
	{
		$callsign_escaped=pg_escape_string($conn,$callsign);
		$res=pg_query($conn,"select flight_id, callsign, status, human_string AS model, model AS model_raw, start_time, EXTRACT(EPOCH FROM start_time) AS start_time_raw, time, EXTRACT(EPOCH FROM start_time) AS time_raw, latitude, longitude, altitude, heading from flights left join models AS m ON fg_string=model join waypoints on waypoints.flight_id=flights.id where status='OPEN' and NOW()-start_time < INTERVAL '2 DAY' and callsign='$callsign_escaped' order by waypoints.time desc");
	}
	$nr=pg_num_rows($res);
	
	if ($nr==0)
	{
		$reply["data"]["pilot"]=NULL;
		return $reply;
	}
	
	/*init the first entry*/
	$current_callsign=pg_result($res,0,'callsign');
	$pilot_data=Array("callsign"=>$current_callsign, 
		"flight_id"=>pg_result($res,0,'flight_id'), 
		"model"=>pg_result($res,0,'model'),
		"model_raw"=>pg_result($res,0,'model_raw'),
		"start_time"=>pg_result($res,0,'start_time'),
		"start_time_raw"=>pg_result($res,0,'start_time_raw'));
	
	for($i=0;$i<$nr;$i++)
	{
		/*Check if new pilots. If so write to $reply first*/
		$callsign=pg_result($res,$i,'callsign');
		if ($current_callsign!=$callsign)
		{
			
			/*record the dep location*/
			$n_airport=get_nearest_airport($conn,$lat,$lon,$alt);
			$pilot_data['start_location']['icao']=$n_airport[0];
			$pilot_data['start_location']['icao_name']=$n_airport[1];
			$pilot_data['start_location']['country']=$n_airport[2];
			$pilot_data['start_location']['city']=$n_airport[3];
			/*write to $reply */
			$reply["data"]["pilot"][]=$pilot_data;
			
			/*New pilot*/
			$pilot_data=Array("callsign"=>$callsign, 
				"flight_id"=>pg_result($res,$i,'flight_id'), 
				"model"=>pg_result($res,$i,'model'),
				"model_raw"=>pg_result($res,$i,'model_raw'),
				"start_time"=>pg_result($res,$i,'start_time'),
				"start_time_raw"=>pg_result($res,$i,'start_time_raw'));
			$current_callsign=$callsign;
		}
		
		if(pg_result($res,$i,"altitude")<$var["min_alt"])
			continue;	
		
		$lat=floatval(pg_result($res,$i,"latitude"));
		$lon=floatval(pg_result($res,$i,"longitude"));
		$alt=floatval(pg_result($res,$i,"altitude"));
		$hdg=floatval(pg_result($res,$i,"heading"));
		
		if($wpt!="y")/*must be here. Don't think of putting this at above*/
			continue;
		$time=pg_result($res,$i,'time');
		$time_raw=floatval(pg_result($res,$i,'time_raw'));
		$pilot_data["wpt"][]=Array('time'=>$time,'time_raw'=>$time_raw,'lat'=>$lat,'lon'=>$lon,'alt'=>$alt,'hdg'=>$hdg);
	}
	
	/*write the very last data*/
	$n_airport=get_nearest_airport($conn,$lat,$lon,$alt);
	$pilot_data['start_location']['icao']=$n_airport[0];
	$pilot_data['start_location']['icao_name']=$n_airport[1];
	$pilot_data['start_location']['country']=$n_airport[2];
	$pilot_data['start_location']['city']=$n_airport[3];
	$reply["data"]["pilot"][]=$pilot_data; 
	
	/*Write header*/
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}

function livewaypoints($conn,$reply)
{
	$res=pg_query($conn,"select flight_id,callsign, human_string AS model, model AS model_raw, time,EXTRACT(EPOCH FROM time) AS time_raw,latitude,longitude, altitude,heading,status from waypoints join flights on waypoints.flight_id=flights.id left join models AS m ON fg_string=model order by time desc, flight_id desc limit 600");
    $nr=pg_num_rows($res);
	
	if($res===false)
	{
		$reply["header"]["code"]=500;
		$reply["header"]["msg"]="Server Internal Error";
		return $reply;
	}
	
	$flight_array=Array();/*to store temporary data*/
	for ($i=0;$i<$nr;$i++)
    {
		$flight_id=intval(pg_result($res,$i,'flight_id'));
		$callsign=pg_result($res,$i,'callsign');
		$model=pg_result($res,$i,'model');
		$model_raw=pg_result($res,$i,'model_raw');
		$time=pg_result($res,$i,'time');
		$time_raw=floatval(pg_result($res,$i,'time_raw'));
		$lat=floatval(pg_result($res,$i,"latitude"));
		$lon=floatval(pg_result($res,$i,"longitude"));
		$alt=floatval(pg_result($res,$i,"altitude"));
		$hdg=floatval(pg_result($res,$i,"heading"));
		$status=pg_result($res,$i,"status");
		
		if (!array_key_exists($callsign,$flight_array))
		{	/*first time encounter the callsign. Save info first for further calculation*/
			$flight_array[$callsign]=Array($lat,$lon,$alt,$status,$time,$time_raw,FALSE);
			continue;
		}
		
		if ($flight_array[$callsign][6]===TRUE)
			continue;

		$flight_array[$callsign][6]=TRUE;
		$GML_distance=GML_distance($lat, $lon, $flight_array[$callsign][0], $flight_array[$callsign][1]);
		
		$wpt=Array("flight_id"=>$flight_id,"callsign"=>$callsign,"model"=>$model,"model_raw"=>$model_raw,"time"=>$flight_array[$callsign][4],"time_raw"=>$flight_array[$callsign][5],"lat"=>$flight_array[$callsign][0],"lon"=>$flight_array[$callsign][1],"alt"=>$flight_array[$callsign][2],"hdg"=>$hdg,"speed_kts"=>$GML_distance[0]/($flight_array[$callsign][5]-$time_raw)*3600,"current_status"=>$flight_array[$callsign][3]);
		$reply["data"]['wpt'][]=$wpt;
	}

	$reply["header"]["code"]=200;
	$reply["header"]["msg"]="OK";
	return $reply;
}

function pilotlist($conn,$reply,$offset,$orderby)
{
	$res=pg_query($conn,"SELECT count(*) FROM cache_top100_alltime ;");
    $num_callsigns=pg_result($res,0,0);
	pg_free_result($res);
	
	/*offsets*/
	
	$offset=intval($offset);
	if ($offset<0 or $offset>$num_callsigns)
		$offset=0;
	$reply["data"]["pilot_list_offset"]=$offset;
	
	if ($orderby=="lastweek")
		$sql="SELECT rank,callsign,
		CASE WHEN effective_lastweek is null then '0' else effective_lastweek END AS lastweek ,
		CASE WHEN effective_lastweek is null then '0' else EXTRACT(EPOCH FROM effective_lastweek) END AS lastweek_raw ,
		CASE WHEN effective_last30days is null then '0' else effective_last30days END AS last30days,
		CASE WHEN effective_last30days is null then '0' else EXTRACT(EPOCH FROM effective_last30days) END AS last30days_raw,
		flighttime,EXTRACT(EPOCH FROM flighttime) AS flighttime_raw,
		effective_flight_time,EXTRACT(EPOCH FROM effective_flight_time) AS effective_flight_time_raw
		FROM cache_top100_alltime WHERE effective_lastweek is not null
		order by effective_lastweek DESC LIMIT 100 OFFSET $offset";
	else if ($orderby=="last30days")
		$sql="SELECT rank,callsign,
		CASE WHEN effective_lastweek is null then '0' else effective_lastweek END AS lastweek ,
		CASE WHEN effective_lastweek is null then '0' else EXTRACT(EPOCH FROM effective_lastweek) END AS lastweek_raw ,
		CASE WHEN effective_last30days is null then '0' else effective_last30days END AS last30days,
		CASE WHEN effective_last30days is null then '0' else EXTRACT(EPOCH FROM effective_last30days) END AS last30days_raw,
		flighttime,EXTRACT(EPOCH FROM flighttime) AS flighttime_raw,
		effective_flight_time,EXTRACT(EPOCH FROM effective_flight_time) AS effective_flight_time_raw
		FROM cache_top100_alltime WHERE effective_last30days is not null
		order by effective_last30days DESC LIMIT 100 OFFSET $offset";
	else
	{
		$orderby="alltime";
		$sql="SELECT rank,callsign,
		CASE WHEN effective_lastweek is null then '0' else effective_lastweek END AS lastweek ,
		CASE WHEN effective_lastweek is null then '0' else EXTRACT(EPOCH FROM effective_lastweek) END AS lastweek_raw ,
		CASE WHEN effective_last30days is null then '0' else effective_last30days END AS last30days,
		CASE WHEN effective_last30days is null then '0' else EXTRACT(EPOCH FROM effective_last30days) END AS last30days_raw,
		flighttime,EXTRACT(EPOCH FROM flighttime) AS flighttime_raw,
		effective_flight_time,EXTRACT(EPOCH FROM effective_flight_time) AS effective_flight_time_raw
		FROM cache_top100_alltime order by rank LIMIT 100 OFFSET $offset";
	}

	$reply["data"]["no_of_pilots"]=$num_callsigns;
	$reply["data"]["orderby"]=$orderby;
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
    
	
	$res=pg_query($conn,$sql);
	$nr=pg_num_rows($res);

	for($i=0;$i<$nr;$i++)
    {
		$lastweek=pg_result($res,$i,"lastweek");
		$lastweek_raw=pg_result($res,$i,"lastweek_raw");
		$last30days=pg_result($res,$i,"last30days");
		$last30days_raw=pg_result($res,$i,"last30days_raw");
		$total_flight_time=pg_result($res,$i,"flighttime");
		$total_flight_time_raw=pg_result($res,$i,"flighttime_raw");
		$effective_flight_time=pg_result($res,$i,"effective_flight_time");
		$effective_flight_time_raw=pg_result($res,$i,"effective_flight_time_raw");
		
		$ranks=Array("rank"=>pg_result($res,$i,"rank"),"callsign"=>pg_result($res,$i,"callsign"),
		"lastweek"=>$lastweek,"lastweek_raw"=>$lastweek_raw,
		"last30days"=>$last30days,"last30days_raw"=>$last30days_raw,
		"total_flight_time"=>$total_flight_time,"total_flight_time_raw"=>$total_flight_time_raw,
		"effective_flight_time"=>$effective_flight_time,"effective_flight_time_raw"=>$effective_flight_time_raw);
		$reply["data"]["pilot"][]=$ranks;
	}
	$reply["header"]["code"]=200;
	$reply["header"]["msg"]="OK";
	return $reply;
}

function recentstateswitch($conn,$reply)
{
	//10 RECENT OPENED FLIGHTS
	$res=pg_query($conn,"select id, callsign, human_string AS model, model as model_raw, start_time, EXTRACT(EPOCH FROM start_time) AS start_time_raw from flights left join models AS m ON fg_string=model where status='OPEN' order by start_time DESC LIMIT 10;");
	$nr=pg_num_rows($res);
	for($i=0;$i<$nr;$i++)
    {
		$flight=Array('flight_id'=>pg_result($res,$i,"id"),'callsign'=>pg_result($res,$i,"callsign"),'model'=> pg_result($res,$i,"model"),'model_raw'=> pg_result($res,$i,"model_raw"),'start_time'=>pg_result($res,$i,"start_time"),'start_time_raw'=>pg_result($res,$i,"start_time_raw"));
		$reply["data"]["started"]["pilot"][]=$flight;
	}
	pg_free_result($res);

	//10 RECENT ENDED FLIGHTS
	$res=pg_query($conn,"select id, callsign, human_string AS model, model as model_raw, end_time, EXTRACT(EPOCH FROM end_time) AS end_time_raw from flights left join models AS m ON fg_string=model where status='CLOSED' order by end_time DESC LIMIT 10;");
	$nr=pg_num_rows($res);
	for($i=0;$i<$nr;$i++)
    {
		$flight=Array('flight_id'=>pg_result($res,$i,"id"),'callsign'=>pg_result($res,$i,"callsign"),'model'=> pg_result($res,$i,"model"),'model_raw'=> pg_result($res,$i,"model_raw"), 'end_time'=>pg_result($res,$i,"end_time"),'end_time_raw'=>pg_result($res,$i,"end_time_raw"));
		$reply["data"]["ended"]["pilot"][]=$flight;
	}
	pg_free_result($res);

	$reply["header"]["code"]=200;
	$reply["header"]["msg"]="OK";
	return $reply;
}
?>