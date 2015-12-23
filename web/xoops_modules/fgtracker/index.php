<?php
if (stripos($_SERVER['HTTP_USER_AGENT'],"baidu")!==false)
{
	header("HTTP/1.0 404 Not Found");
	exit();
}

/*if (stripos($_SERVER['HTTP_USER_AGENT'],"firefox")!==false)
{
	echo "Sorry, this page does not support your bowser";
	exit();
}*/

include_once "include/config.php";
include_once "include/flightlog.php";
include_once "include/flightplan.php";

include_once "../../mainfile.php";
include_once XOOPS_ROOT_PATH."/header.php";
date_default_timezone_set("Asia/Hong_Kong"); 

/*Uncomment the following line for tracker shutdown
	$xoopsTpl->append('topmsg',"<center><font color=\"red\">Maintenance in progress. </font></center>");
	$xoopsOption['template_main'] = "show_error.html";
	include_once XOOPS_ROOT_PATH."/footer.php";//Maintenance in progress - Data will not be updated until 23.03.2014 23:00 GMT +8
	return;*/

/*Uncomment the following line for announcements	*/
	$xoopsTpl->append('topmsg',"<center><font color=\"red\">To FGMS maintainer: New FGTracker server is launched. 
	The New server requires your FGMS be registered in FGTracker. PM hazuki@flightgear forum for details. <br> The only registered FGMS are: 1. MPSERVER01, 2. MPSERVER03, 3. MPSERVER14 and; 4. MPSERVER16 ONLY.</font></center>"); 


/*check if server is overloaded*/
$serverload=explode(" ",file_get_contents('/proc/loadavg'));
if (floatval($serverload[0])>10)
{
	$xoopsTpl->append('topmsg',"<center><font color=\"red\">Sorry, this server is overloaded. Please try again later.<br />Current loading: ".$serverload[0]."</font></center>");
	$xoopsOption['template_main'] = "show_error.html";
	include_once XOOPS_ROOT_PATH."/footer.php";
	return;
}

/*top message*/
if (date('H')=='01' and date('i')<45)
{
	$xoopsTpl->append('topmsg',"<center><font color=\"red\">Maintenance in Progress. Data might not be updated until it is finished.</font></center>");
}

$funct=get_request("FUNCT");

if ($funct=="") $funct="CALLSIGN";

switch ($funct)
{
	case "AIRPORT":
	$xoopsOption['template_main'] = "show_airport.html";
	$icao=$_GET["ICAO"];
	show_airport($icao);
	include_once XOOPS_ROOT_PATH."/footer.php";
	break;
	case "CALLSIGN": 
		$xoopsOption['template_main'] = "select_callsign.html";
		select_callsign(); /*Show top 100 pilots*/
		top10_1Week();
		top10_1Month();
		ten_open_closed_flight();
		show_tracking_pilots();
		show_mpserverstatus();
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;
	case "FLIGHTS":
		$callsign=get_request("CALLSIGN");
		$page=get_request("PAGE");
		if ($page=='') $page=0;
		
		$archive=get_request("ARCHIVE");
		if ($archive=='TRUE') $archive=1;
		
		$xoopsOption['template_main'] = "show_flights.html";
		show_flights($callsign,$page,$summary,$archive);
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;
	case "FLIGHT":
		$flightid=get_request("FLIGHTID");
		$xoopsOption['template_main'] = "show_flight.html";
		if ($_POST["action"]=="delete_flight")
		{
			$username=$_POST["username"];
			$token=$_POST["token"];
			$callsign=$_POST["callsign"];
			$pflightid=$_POST["pflightid"];
			$usercomments=$_POST["usercomments"];
			//print $usercomments; exit();
			$xoopsTpl->append('topmsg',delete_flight($flightid,$token,$username,$callsign,$pflightid,$usercomments));
		}
			
		if ($_POST["action"]=="merge_flight")
		{
			$usercomments=$_POST["usercomments"];
			$token="";
			if(isset($_POST["username"]))
			{
				$username=$_POST["username"];
				$token=$_POST["token"];
			}
			else $username=$_SERVER["REMOTE_ADDR"];
			$xoopsTpl->append('topmsg',merge_flights($flightid, $_POST["nflightid"],$username,$token,$usercomments));
		}
		show_flight($flightid);
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;
	/*case "FLIGHT2":
		$flightid=get_request("FLIGHTID");
		$xoopsOption['template_main'] = "show_flight2.html";
		show_flight($conn,$flightid);
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;*/
	case "KML":
		ob_clean();
		$flightid=get_request("FLIGHTID");
		generate_kml($conn,$flightid);
		break;
	case "PLANE":
		$model=get_request("MODEL");
		$page=get_request("PAGE");
		$xoopsOption['template_main'] = "show_plane.html";
		show_plane($model,$page);
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;
	case "RANK":
		//$fpid=get_request("FPID");
		$page=get_request("PAGE");
		if ($page=='') $page=0;
		else if (get_request("CUSTOM")=='true') $page--;
		$xoopsOption['template_main'] = "show_rank.html";
		show_rank($page);
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;
	/*case "FLIGHTPLAN": not used
		$fpid=get_request("FPID");
		$xoopsOption['template_main'] = "show_flight_plan.html";
		show_flight_plan($conn,$fpid);
		include_once XOOPS_ROOT_PATH."/footer.php";
		break;*/
}

pg_close($conn);

?>
