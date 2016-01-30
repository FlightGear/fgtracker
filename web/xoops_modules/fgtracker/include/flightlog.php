<?php
include_once "general.php";
include_once 'trig.php';
include_once 'flight_report.php';
include_once 'flight_kml.php';

///////////////////////////////////////////////////////////////////////
// DELETE_FLIGHT (JSON_DB)
///////////////////////////////////////////////////////////////////////
function delete_flight($flightid,$token,$username,$callsign,$pflightid,$usercomments)
{
	$usercomments=urlencode($usercomments);
	$url=JSON_DB_LOCATION."?action=delflight&flightid=$flightid&username=$username&callsign=$callsign&token=$token&usercomments=$usercomments";
	$res=json_decode(file_get_contents($url), true);
	
	if($res["data"]["ok"]===false or !isset($res["data"]["ok"]))
		$reply="Request: $url<br />Server reply: Delete Failed - ".$res["data"]["msg"];
	else $reply="Server reply: Delete OK - ".$res["data"]["msg"];
	$reply.="<br/> Return to <a href=\"?FUNCT=FLIGHTS&CALLSIGN=$callsign\">$callsign's Flights list</a> or <a href=\"?FUNCT=FLIGHT&FLIGHTID=$pflightid\">$callsign's previous flight</a>";
	return $reply;
}

///////////////////////////////////////////////////////////////////////
// MERGE_FLIGHTS (JSON_DB)
///////////////////////////////////////////////////////////////////////
function merge_flights($flightid, $nflightid,$username,$token,$usercomments)
{
	$usercomments=urlencode($usercomments);
	$url=JSON_DB_LOCATION."?action=mergeflight&flightid=$flightid&nflightid=$nflightid&username=$username&token=$token&usercomments=$usercomments";
	$res=json_decode(file_get_contents($url), true);
	if($res["data"]["ok"]===false or !isset($res["data"]["ok"]))
		$reply="Server reply: Merge Failed - ".$res["data"]["msg"];
	else $reply="Server reply: Merge OK";
	return $reply;
}

///////////////////////////////////////////////////////////////////////
// SELECT_CALLSIGN (JSON_DB)
///////////////////////////////////////////////////////////////////////
  function select_callsign()
  {
    global $xoopsTpl;

	$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=pilotlist"), true);
	$nr=sizeof($res["data"]["pilot"]);

    for($i=0;$i<ceil($nr/2.0);$i++)
    {

		$callsigns['callsign']=$res["data"]["pilot"][$i]["callsign"];
		$callsigns['flighttime']=$res["data"]["pilot"][$i]["effective_flight_time"];
		$callsigns2['callsign']=$res["data"]["pilot"][$i+ceil($nr/2.0)]["callsign"];
		$callsigns2['flighttime']=$res["data"]["pilot"][$i+ceil($nr/2.0)]["effective_flight_time"];	

		$xoopsTpl->append('callsigns', $callsigns);
		$xoopsTpl->append('callsigns2', $callsigns2);
    }
  }

///////////////////////////////////////////////////////////////////////
// TOP10_1WEEK (JSON_DB)
///////////////////////////////////////////////////////////////////////
function top10_1Week()
{
    global $xoopsTpl;
	
	$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=pilotlist&orderby=lastweek"), true);
	$nr=10;

    for($i=0;$i<$nr;$i++)
    {

		$top10_1Week['callsign']=$res["data"]["pilot"][$i]["callsign"];
		$top10_1Week['flighttime']=$res["data"]["pilot"][$i]["lastweek"];
		$xoopsTpl->append('top10_1Week', $top10_1Week);
    }
}
 
///////////////////////////////////////////////////////////////////////
// TOP10_1MONTH (JSON_DB)
///////////////////////////////////////////////////////////////////////
function top10_1Month()
{
    global $xoopsTpl;
	
	$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=pilotlist&orderby=last30days"), true);
	$nr=10;

    for($i=0;$i<$nr;$i++)
    {
		$top10_1Month['callsign']=$res["data"]["pilot"][$i]["callsign"];
		$top10_1Month['flighttime']=$res["data"]["pilot"][$i]["last30days"];
		$xoopsTpl->append('top10_1Month', $top10_1Month);
    }
}

///////////////////////////////////////////////////////////////////////
// 10 RECENT OPENED/CLOSED FLIGHTS (JSON_DB)
///////////////////////////////////////////////////////////////////////
function ten_open_closed_flight()
{
    global $xoopsTpl;
	

	//10 RECENT OPENED/CLOSED FLIGHTS
	$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=recentstateswitch"), true);
	$nr=sizeof($res["data"]["started"]["pilot"]);
		
    for($i=0;$i<$nr;$i++)
    {

		$ten_openflight['flight_id']=$res["data"]["started"]["pilot"][$i]["flight_id"];
		$ten_openflight['model']=$res["data"]["started"]["pilot"][$i]["model"];
		$ten_openflight['callsign']=$res["data"]["started"]["pilot"][$i]["callsign"];
		$ten_openflight['start_time']=$res["data"]["started"]["pilot"][$i]["start_time"];
		$xoopsTpl->append('ten_openflight', $ten_openflight);
    }
	
	$nr=sizeof($res["data"]["ended"]["pilot"]);

    for($i=0;$i<$nr;$i++)
    {
		$ten_closedflight['flight_id']=$res["data"]["ended"]["pilot"][$i]["flight_id"];
		$ten_closedflight['model']=$res["data"]["ended"]["pilot"][$i]["model"];
		$ten_closedflight['callsign']=$res["data"]["ended"]["pilot"][$i]["callsign"];
		$ten_closedflight['end_time']=$res["data"]["ended"]["pilot"][$i]["end_time"];	
		$xoopsTpl->append('ten_closedflight', $ten_closedflight);
    }
}

///////////////////////////////////////////////////////////////////////
// SHOW_AIRPORT (JSON_DB)
///////////////////////////////////////////////////////////////////////
function show_airport($icao)
{
	global $xoopsTpl;
	
	/*airport details*/
	$j_res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=airport&icao=$icao"), true);

    $xoopsTpl->assign('icao',$j_res["data"]["icao"]);
	$xoopsTpl->assign('name',$j_res["data"]["name"]);
	switch ($j_res["data"]["type"])
	{
		case 100:
			$xoopsTpl->assign('type',"Land airport");
		break;
		case 101:
			$xoopsTpl->assign('type',"Sea airfield");
		break;
		case 102:
			$xoopsTpl->assign('type',"Heliport");
		break;
	}
	
	$xoopsTpl->assign('city',$j_res["data"]["city"]);
	$xoopsTpl->assign('country',$j_res["data"]["country"]);
	$xoopsTpl->assign('lat',$j_res["data"]["lat"]);
	$xoopsTpl->assign('lon',$j_res["data"]["lon"]);
	$xoopsTpl->assign('alt',$j_res["data"]["alt"]);
	$lat=$j_res["data"]["lat"];
	$lon=$j_res["data"]["lon"];
	
	/*Pilots in vicinity*/
	$j_res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=livewaypoints"), true);
	foreach($j_res["data"]["wpt"] as $wpt)
	{
		if ($wpt["current_status"]!="OPEN")
			continue;
		if(abs($lat-$wpt["lat"])>1)
			continue;
		if(abs($lon-$wpt["lon"])>1)
			continue;
		
		$distance=GML_distance($lat, $lon, $wpt["lat"], $wpt["lon"]);
		$pilot['flight_id']=$wpt["flight_id"];
		$pilot['callsign']=$wpt["callsign"];
		$pilot['model']=$wpt["model"];
		$pilot['heading']=$wpt["bearing"];
		$pilot['speed']=round($wpt["speed_kts"],1);
		$pilot['distance']=round($distance[0],1);
		$pilot['bearing']=round($distance[2],1);
		$pilots[$distance[0]*100000]=$pilot;		
	}
	ksort($pilots);
	foreach($pilots as $pilot)
	{
		$xoopsTpl->append('pilots_in_vic', $pilot);
	}
}
///////////////////////////////////////////////////////////////////////
// SHOW_FLIGHTS (JSON_DB)
///////////////////////////////////////////////////////////////////////
function show_flights($callsign,$page,$summary,$archive)
{
    global $xoopsTpl;
	$flights_per_page=100;
	$offset=(intval($page)-1)*$flights_per_page;
	

    $xoopsTpl->assign('callsign',$callsign);
    $xoopsTpl->assign('summary',$summary);
	$xoopsTpl->assign('archive',$archive);

    /*Get total no of flights in that result set*/
	if($archive==1)
		$j_res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=flights&archive=true&callsign=$callsign&offset=$offset"), true);
	else
		$j_res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=flights&callsign=$callsign&offset=$offset"), true);
	$num_flights=$j_res["data"]["no_of_flights"];
	$offset=$j_res["data"]["flight_list_offset"];
	$page=$offset/$flights_per_page+1;
	$xoopsTpl->append('archivedate',$j_res["data"]["db_archive_date"]);
	$xoopsTpl->assign('lastweek',$j_res["data"]["lastweek"]);
	$xoopsTpl->assign('last30days',$j_res["data"]["last30days"]);
	$xoopsTpl->assign('total_flight_time',$j_res["data"]["total_flight_time"]);
	$xoopsTpl->assign('effective_flight_time',$j_res["data"]["effective_flight_time"]);
	$xoopsTpl->assign('effective_flight_rate', round($j_res["data"]["effective_flight_time_raw"]/$j_res["data"]["total_flight_time_raw"]*100,3)."%");
	$xoopsTpl->assign('rank',$j_res["data"]["rank"]);
	$xoopsTpl->assign('pages',intval($num_flights/$flights_per_page)+1);	
	$xoopsTpl->assign('page',$page);
	
	$i=0;
	foreach($j_res["data"]["flight_time_by_type"] as $bytype)
	{
		$table1['model']=$bytype["model"];
		$table1['duration']=$bytype["duration"];
		$table1['effective_flight_time']=$bytype["effective_flight_time"];

		if($i++%2==0)
			$xoopsTpl->append('tablea', $table1);
		else
			$xoopsTpl->append('tablea2', $table1);
	}

	foreach($j_res["data"]["start_icaos"] as $icao)
	{
		$table3['icao']=$icao["icao"];
		$table3['icao_name']=$icao["icao_name"];
		$table3['country']=$icao["country"];
		$table3['count']=$icao["count"];

		if($i++%2==0)
			$xoopsTpl->append('tablec', $table3);
		else
			$xoopsTpl->append('tablec2', $table3);
	}
	
	foreach($j_res["data"]["end_icaos"] as $icao)
	{
		$table3['icao']=$icao["icao"];
		$table3['icao_name']=$icao["icao_name"];
		$table3['country']=$icao["country"];
		$table3['count']=$icao["count"];

		if($i++%2==0)
			$xoopsTpl->append('tabled', $table3);
		else
			$xoopsTpl->append('tabled2', $table3);
	}
	
	$j=$num_flights-$offset;
	foreach($j_res["data"]["flight_list"] as $flight_list)
	{
	  $table2['row']=$j;
	  $table2['id']=$flight_list["id"];
	  $table2['callsign']=$flight_list["callsign"];
	  $table2['model']=$flight_list["model"];
	  $table2['start_time']=$flight_list["start_time"];
	  $table2['start_icao']=$flight_list["start_location"]["icao"];
	  $table2['start_country']=$flight_list["start_location"]["country"];
	  $table2['end_time']=$flight_list["end_time"];
	  $table2['end_icao']=$flight_list["end_location"]["icao"];
	  $table2['end_country']=$flight_list["end_location"]["country"];
	  $table2['duration']=$flight_list["duration"];
	  $table2['numwpts']=$flight_list["numwpts"];
	  $table2['effective_flight_time']=$flight_list["effective_flight_time"];

	  $xoopsTpl->append('tableb', $table2);

	  $j=$j-1;
	}

	/*callsign log*/
	//http://mpserver15.flightgear.org/modules/fgtracker/interface.php?action=alterrecord&callsign=callsig
	$j_res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=alterlog&callsign=$callsign"), true);
	foreach($j_res["data"]["log"] as $log)
	{
		$table4['operating_user']=$log["operating_user"];
		$table4['action']=$log["action"];
		$table4['time']=$log["time"];
		$table4['comments']=$log["comments"];
		$xoopsTpl->append('tablee', $table4);
	}

}

///////////////////////////////////////////////////////////////////////
// SHOW_FLIGHT (JSON_DB)
///////////////////////////////////////////////////////////////////////
function show_flight($flightid)
{
    global $xoopsTpl;
    global $xoopsUser;

    /*flight details*/	
	$flightid_escaped=urlencode($flightid);
	$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=flight&flightid=$flightid_escaped"), true);
	$callsign=$res["data"]["callsign"];
	$xoopsTpl->assign('flightid',$res["data"]["flight_id"]);
	$xoopsTpl->assign('callsign',$callsign);
	$xoopsTpl->assign('model',$res["data"]["model"]);
	$xoopsTpl->assign('start_time',$res["data"]["start_time"]);
	$xoopsTpl->assign('starttime_raw',$res["data"]["start_time_raw"]);
	$xoopsTpl->assign('endtime',$res["data"]["end_time"]);
	$xoopsTpl->assign('duration',$res["data"]["duration"]);
	$xoopsTpl->assign('dep_airport',$res["data"]["start_location"]["icao"]);
	$xoopsTpl->assign('dep_airport_name',$res["data"]["start_location"]["icao_name"]);
	$xoopsTpl->assign('dep_airport_country',$res["data"]["start_location"]["country"]);
	$xoopsTpl->assign('arr_airport',$res["data"]["end_location"]["icao"]);
	$xoopsTpl->assign('arr_airport_name',$res["data"]["end_location"]["icao_name"]);
	$xoopsTpl->assign('arr_airport_country',$res["data"]["end_location"]["country"]);
	
    $flight = new FLIGHT_REPORT;

    /*waypoints details*/
	$xoopsTpl->assign('no_of_waypoints', $res["data"]["wpts"]);
	foreach($res["data"]["wpt"] as $wpt)
		$array[] = Array($wpt['lat'],$wpt['lon'],$wpt['alt'],$wpt['time_raw']);

    /*previous flight details*/

	$xoopsTpl->assign('p_flightid',$res["data"]["previous_flight"]["flight_id"]);
	$xoopsTpl->assign('p_model',$res["data"]["previous_flight"]["model"]);
	$xoopsTpl->assign('p_endtime',$res["data"]["previous_flight"]["end_time"]);
	$xoopsTpl->assign('p_starttime',$res["data"]["previous_flight"]["start_time"] );
	$xoopsTpl->assign('p_endtime_raw',$res["data"]["previous_flight"]["end_time_raw"]);
	$xoopsTpl->assign('p_distancediff',$res["data"]["previous_flight"]["distance_difference"]);
	
	/*check_merge_request*/
	if ($res["data"]["previous_flight"]["mergeok"]===TRUE)
	$xoopsTpl->assign('mergeok', "Y");
	else $xoopsTpl->assign('mergeok', "N");	

    /*flight details - again*/
	$flight->MakeFlightReport ( $array, "NoDiagram" );

    $distance=$flight->Getdistance ();
    $maxalt=$flight->GetmaxAltitude ();
    $maxgndspeed=$flight->GetmaxGrdSpd ();
    $maxmach=$flight->GetmaxMach ();
    $effectiveFlightTime=$flight->GeteffectiveFlightTime();
    $minGrdSpdThreshold=$flight->minGrdSpdThreshold;
	 
	$distance=$distance/1852;
    $distance=sprintf("%d nm",$distance);

    $maxalt=sprintf("%d feet",$maxalt);

    $maxgndspeed=sprintf("%d kts",$maxgndspeed);
    $maxmach=sprintf("%.2f",$maxmach);

    $xoopsTpl->assign('distance', $distance);
    $xoopsTpl->assign('maxalt', $maxalt);
    $xoopsTpl->assign('maxgndspeed', $maxgndspeed);
    $xoopsTpl->assign('maxmach', $maxmach);
    $xoopsTpl->assign('effectiveFlightTime', secondsToTime($effectiveFlightTime));
    $xoopsTpl->assign('minGrdSpdThreshold', $minGrdSpdThreshold);
	
	/*alterlog*/
	foreach($res["data"]["log"] as $log)
	{
		$table1['operating_user']=$log["operating_user"];
		$table1['time']=$log["time"];
		$table1['action']=$log["action"];
		$table1['comments']=$log["comments"];

		$xoopsTpl->append('tablea', $table1);
	}
}

///////////////////////////////////////////////////////////////////////
// SHOW_MPSERVERSTATUS (N/A)
///////////////////////////////////////////////////////////////////////
function show_mpserverstatus()
{
	global $xoopsTpl;
	include_once('../../mpserverstatus/_fgms_conf.php');
	$lblock['title']='Mpserver status';
	$lblock['content']='<table><tr><th>Server</th><th>Status</th></tr>';
		
	foreach ($mpserver_list as $mpserver)
	{
		$lblock['content'].='<tr id="'.$mpserver[short].'"><td>'.$mpserver[long].'</td></tr>';
		$javascipt.='check_mpserver("'.$mpserver[short].'");
		';
	}
	$lblock['content'].='</table>Note: "Not tracked" means that mpserver does not tracked by this server, but it may be tracked by other fgtracker. Detailed report can be viewed <a href="../../mpserverstatus/">here</a>';
	$xoopsTpl->append('xoops_lblocks', $lblock);
	$xoopsTpl->assign('xoops_js', '//--></script>
		
  <script src="../../mpserverstatus/fg_fgtracker.js" type="text/javascript"></script>
  <script type="text/javascript">
    function check_mpservers() { 
      '.$javascipt.'
    }
	window.onload=function(){check_mpservers();}
	
	');
}

///////////////////////////////////////////////////////////////////////
// SHOW_RANK (JSON_DB)
///////////////////////////////////////////////////////////////////////
function show_plane($model,$page)
{
	
}

///////////////////////////////////////////////////////////////////////
// SHOW_RANK (JSON_DB)
///////////////////////////////////////////////////////////////////////
function show_rank($page)
{
	global $xoopsTpl;
	$no_of_callsigns_per_page=100;

	$offset=$page*$no_of_callsigns_per_page;
	$j_res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=pilotlist&offset=$offset"), true);
	$offset=$j_res["data"]["pilot_list_offset"];
	$page=intval($offset/$no_of_callsigns_per_page);
	$num_callsigns=$j_res["data"]["no_of_pilots"];

	$pages=floor($num_callsigns/$no_of_callsigns_per_page);
	$xoopsTpl->assign('pages',$pages);
	$xoopsTpl->assign('page',$page);

	foreach($j_res["data"]["pilot"] as $pilot)
	{
		$ranks=Array($pilot["rank"],$pilot["callsign"],$pilot["lastweek"],$pilot["last30days"],$pilot["total_flight_time"],$pilot["effective_flight_time"]);
		$xoopsTpl->append('ranks', $ranks);
	}	
}
  
///////////////////////////////////////////////////////////////////////
// SHOW_TRACKING_PILOTS (JSON_DB)
///////////////////////////////////////////////////////////////////////
function show_tracking_pilots()
{
	global $xoopsTpl;
	
	$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=livepilots"), true);
	$nr=sizeof($res["data"]["pilot"]);

	$xoopsTpl->assign('no_of_tracking_pilots', $nr);
	for($i=0;$i<$nr;$i=$i+3)
	{
		$tracking_pilots['callsign0']=$res["data"]["pilot"][$i]["callsign"];
		$tracking_pilots['model0']=$res["data"]["pilot"][$i]["model"];
		$tracking_pilots['id0']=$res["data"]["pilot"][$i]["flight_id"];
		$tracking_pilots['icao0']=$res["data"]["pilot"][$i]["start_location"]["icao"];
		$tracking_pilots['country0']=$res["data"]["pilot"][$i]["start_location"]["country"];
		
		$tracking_pilots['callsign1']=$res["data"]["pilot"][$i+1]["callsign"];
		$tracking_pilots['model1']=$res["data"]["pilot"][$i+1]["model"];
		$tracking_pilots['id1']=$res["data"]["pilot"][$i+1]["flight_id"];
		$tracking_pilots['icao1']=$res["data"]["pilot"][$i+1]["start_location"]["icao"];
		$tracking_pilots['country1']=$res["data"]["pilot"][$i+1]["start_location"]["country"];
		
		$tracking_pilots['callsign2']=$res["data"]["pilot"][$i+2]["callsign"];
		$tracking_pilots['model2']=$res["data"]["pilot"][$i+2]["model"];
		$tracking_pilots['id2']=$res["data"]["pilot"][$i+2]["flight_id"];
		$tracking_pilots['icao2']=$res["data"]["pilot"][$i+2]["start_location"]["icao"];
		$tracking_pilots['country2']=$res["data"]["pilot"][$i+2]["start_location"]["country"];

		$xoopsTpl->append('tracking_pilots', $tracking_pilots);
	}
}

///////////////////////////////////////////////////////////////////////
// SECONDS TO TIME (N/A)
/////////////////////////////////////////////////////////////////////// 
function secondsToTime($ss) {
    $s = $ss%60;
    $m = floor(($ss%3600)/60);
    $h = floor(($ss%86400)/3600);
    $d = floor(($ss%2592000)/86400);
    $M = floor($ss/2592000);

    // Ensure all values are 2 digits, prepending zero if necessary.
    $s = $s < 10 ? '0' . $s : $s;
    $m = $m < 10 ? '0' . $m : $m;
    $h = $h < 10 ? '0' . $h : $h;
    $d = $d < 10 ? '0' . $d : $d;
    $M = $M < 10 ? '0' . $M : $M;

    if($d>0)
	return "$d day $h:$m:$s";
	else return "$h:$m:$s";
}

/*Section of bearing/speed calculation*/
// ------------ distance calculation function ---------------------
   
    //**************************************
    //     
    // Name: Calculate Distance and Radius u
    //     sing Latitude and Longitude in PHP
    // Description:This function calculates 
    //     the distance between two locations by us
    //     ing latitude and longitude from ZIP code
    //     , postal code or postcode. The result is
    //     available in miles, kilometers or nautic
    //     al miles based on great circle distance 
    //     calculation. 
    // By: ZipCodeWorld
    //
    //This code is copyrighted and has
	// limited warranties.Please see http://
    //     www.Planet-Source-Code.com/vb/scripts/Sh
    //     owCode.asp?txtCodeId=1848&lngWId=8    //for details.    //**************************************
    //     
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    /*:: :*/
    /*:: This routine calculates the distance between two points (given the :*/
    /*:: latitude/longitude of those points). It is being used to calculate :*/
    /*:: the distance between two ZIP Codes or Postal Codes using our:*/
    /*:: ZIPCodeWorld(TM) and PostalCodeWorld(TM) products. :*/
    /*:: :*/
    /*:: Definitions::*/
    /*::South latitudes are negative, east longitudes are positive:*/
    /*:: :*/
    /*:: Passed to function::*/
    /*::lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees) :*/
    /*::lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees) :*/
    /*::unit = the unit you desire for results:*/
    /*::where: 'M' is statute miles:*/
    /*:: 'K' is kilometers (default):*/
    /*:: 'N' is nautical miles :*/
    /*:: United States ZIP Code/ Canadian Postal Code databases with latitude & :*/
    /*:: longitude are available at http://www.zipcodeworld.com :*/
    /*:: :*/
    /*:: For enquiries, please contact sales@zipcodeworld.com:*/
    /*:: :*/
    /*:: Official Web site: http://www.zipcodeworld.com :*/
    /*:: :*/
    /*:: Hexa Software Development Center © All Rights Reserved 2004:*/
    /*:: :*/
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
function GML_distance($lat1, $lon1, $lat2, $lon2) { 
    if($lat1==$lat2 && $lon1==$lon2)
		return(array(0,0,0,"N"));
	$theta = $lon1 - $lon2; 
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
    $dist = acos($dist); 
    $dist = rad2deg($dist); 
    $miles = $dist * 60 * 1.1515;
	$bearingDeg = (rad2deg(atan2(sin(deg2rad($lon2) - deg2rad($lon1)) * 
	   cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - 
	   sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)))) + 360) % 360;

	$bearingWR = GML_direction($bearingDeg);
	
    $km = $miles * 1.609344; 
    $kts = $miles * 0.8684;
	//$miles = round($miles);
	return(array($kts,$km,$bearingDeg,$bearingWR));
}
  
function GML_direction($degrees) {
   // figure out a text value for compass direction
   // Given the direction, return the text label
   // for that value.  16 point compass
   $winddir = $degrees;
   if ($winddir == "n/a") { return($winddir); }

  if (!isset($winddir)) {
    return "---";
  }
  if (!is_numeric($winddir)) {
	return($winddir);
  }
  $windlabel = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S",
	 "SSW","SW", "WSW", "W", "WNW", "NW", "NNW");
  $dir = $windlabel[ fmod((($winddir + 11) / 22.5),16) ];
  return($dir);

} // end function GML_direction	
/*Section of bearing/speed calculation ends*/

?>
