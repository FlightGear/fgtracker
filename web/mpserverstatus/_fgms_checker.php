<?php

require_once("./_fgms_conf.php");

if (!isset($short))
	$short = trim($_GET["s"]);
if (!isset($report_lv))
	$report_lv = trim($_GET["lv"]);

$long = $mpserver_list[$short]["long"];
$port = $mpserver_list[$short]["port"];
$loc = $mpserver_list[$short]["loc"];
$ip = gethostbyname($long);
$timeout = 2;

if ($short=="")
	return;


$sock = @fsockopen($ip, $port, $errno, $errstr, $timeout);

if(!$sock) 
{
	switch ($report_lv)
	{
		case "1":
			report_lv1(FALSE,$short,$long,$loc,'-','-','-','-','-','-');
		break;
		case "2":
			report_lv2(FALSE,$short,$long,$loc,'-','-','-','-','-','-');
		break;
		case "3":
			report_lv3(FALSE,$short,$long,$loc,'-','-','-','-','-','-');
		break;
		default:
	}
	return;
} 

stream_set_blocking($sock, 0);
stream_set_timeout($sock, 5);
$raw = "";
$sleep=0;
while(!feof($sock) and $sleep<5) {
	$raw .= fread($sock,1048576);
	$sleep++;
	sleep(1);
}

fclose($sock);

if ($raw=="")
{
	switch ($report_lv)
	{
		case "1":
			report_lv1(FALSE,$short,$long,$loc,'-','-','-','-','-','-');
		break;
		case "2":
			report_lv2(FALSE,$short,$long,$loc,'-','-','-','-','-','-');
		break;
		case "3":
			report_lv3(FALSE,$short,$long,$loc,'-','-','-','-','-','-');
		break;
		default:
	}
	return;
}

foreach(preg_split("/(\n)/", $raw) as $line) 
{
	if(strstr($line, "This is ")) {
		$name = str_replace('\n', '', str_replace('# This is ', '', $line));
	} elseif(strstr($line, "FlightGear Multiplayer Server ")) {
		$version = str_replace('\n', '', str_replace('# FlightGear Multiplayer Server ', '', $line));
		$version = str_replace(' using protocol version v1.1 (LazyRelay enabled)', '', $version);
	} elseif(strstr($line, "using protocol version ")) {
		$protocol = str_replace('\n', '', str_replace('# using protocol version ', '', $line));
	} elseif(strstr($line, "This server is tracked: ")) {
		$tracker = str_replace('\n', '', str_replace('# This server is tracked: ', '', $line));
	} elseif(strstr($line, "pilot(s) online")) {
					$pilots = trim(str_replace('\n', '', str_replace(' pilot(s) online', '', str_replace('# ', '', $line))));
			} elseif(strstr($line, "pilots online")) { # legacy servers (e.g. V0.10.9):
					$pilots = trim(str_replace('\n', '', str_replace(' pilots online', '', str_replace('# ', '', $line))));
	} else {
		break;
	}
}

$uclients = substr_count($raw, "* unknown *");
$tclients = $pilots - $uclients;
$lclients = substr_count($raw, "@LOCAL: ") - $uclients;

if(isset($tracker)) {
	if (in_array($tracker,$tracker_ip))
	{
		if ($mpserver_list[$short]["force_untracked"]===true)
			$tracked = "No";
		else
		{
			$subversion=explode(".", $version);
			if (intval($subversion[1])>=$min_version)
			{
				if (intval($subversion[2])>=$min_subversion)
					$tracked = "Yes";
				else $tracked = "No";
			}	
			else
					$tracked = "No";
		}
	}
	else if (stripos($name,'MPSERVER15')!==FALSE)
	$tracked = "Yes";
	else $tracked = "No";
} else {
	$tracked = "No";
	$tracker = "-";
}

switch ($report_lv)
{
	case "1":
		report_lv1(TRUE,$short,$long,$loc,$ip,$version,$tracked,$tracker,$tclients,$lclients);
	break;
	case "2":
		report_lv2(TRUE,$short,$long,$loc,$ip,$version,$tracked,$tracker,$tclients,$lclients);
	break;
	case "3":
		report_lv3(TRUE,$short,$long,$loc,$ip,$version,$tracked,$tracker,$tclients,$lclients);
	break;
	default:
}

function report_lv1($up,$short,$long,$loc,$ip,$version,$tracked,$tracker,$tclients,$lclients)
{
	
	if ($up)
	{	
		$upstr='UP';
		if ($tracked=="Yes")
		{
			$colourcode='#99ff66';
			print "<td id=\"".$short."_status\" class=\"clearbox\" style=\"text-align: center; background-color: #00bb00;\">";
		}
		else
		{
			$colourcode='#FFFF00';
			print "<td id=\"".$short."_status\" class=\"clearbox\" style=\"text-align: center; background-color: #DDDD00;\">";
		}
	}else 
	{
		$upstr='DOWN';
		$colourcode='#ff9966';
		print "<td id=\"".$short."_status\" class=\"clearbox\" style=\"text-align: center; background-color: #bb0000;\">";
	}

	print "$upstr
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode;\">
			$long
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$loc
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$ip
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$version
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$tracked
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$tracker
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$tclients
		</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode\">
			$lclients
		</td>
		<td id=\"".$short."_chk\" class=\"clearbox\" style=\"background-color: $colourcode\">
			".date("y-m-d G:i:s")."
		</td>
		";
}

function report_lv2($up,$short,$long,$loc,$ip,$version,$tracked,$tracker,$tclients,$lclients)
{
	if ($up)
	{	
		if ($tracked=="Yes")
		{
			$st_str='Tracked';
			$colourcode='#99ff66';
			print "<td class=\"clearbox\" style=\"text-align: center; background-color: #00bb00;\">";
		}
		else
		{
			$st_str='Not Tracked';
			$colourcode='#FFFF00';
			print "<td class=\"clearbox\" style=\"text-align: center; background-color: #DDDD00;\">";
		}
	}else 
	{
		$st_str='DOWN';
		$colourcode='#ff9966';
		print "<td class=\"clearbox\" style=\"text-align: center; background-color: #bb0000;\">";
	}

	print "$long</td>
		<td class=\"clearbox\" style=\"background-color: $colourcode;\">
			$st_str
		</td>";
}

function report_lv3($up,$short,$long,$loc,$ip,$version,$tracked,$tracker,$tclients,$lclients)
{
	$reply=Array();
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	$reply["header"]["request_time_raw"]=$_SERVER['REQUEST_TIME'];
	$reply["header"]["request_time"]=date("Y-m-d H:i:sO",$_SERVER['REQUEST_TIME']);
	
	if ($tracked == "Yes")
		$tracked = true;
	else $tracked == false;
	
	$reply["data"]["name"]=$short;
	$reply["data"]["server"]=$long;
	$reply["data"]["check_time"]=date("Y-m-d H:i:sO");
	$reply["data"]["check_time_raw"]=time();
	$reply["data"]["server_running"]=$up;
	$reply["data"]["server_location"]=$loc;
	$reply["data"]["server_ip"]=$ip;
	$reply["data"]["server_version"]=$version;
	$reply["data"]["server_istracked"]=$tracked;
	$reply["data"]["server_totalclients"]=$tclients;
	$reply["data"]["server_localclients"]=$lclients;
	print json_encode($reply);
}
?>
