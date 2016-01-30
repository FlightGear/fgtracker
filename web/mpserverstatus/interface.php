<?php
require_once("./_fgms_conf.php");


$reply=Array();
$action=$server="";
$action=trim($_GET['action']);

switch ($action)
{
	case "fgmslist":
		$reply=fgmslist($reply,$mpserver_list);
	break;
	case "getstatus":
		$server=trim($_GET['server']);
		$reply=getstatus($server,$mpserver_list);
		if ($reply==null)
			return;
	break;
	default:
	$reply["header"]=Array("code"=>400,"msg"=>'Bad Request. Action not defined.');
}

$reply=addtime($reply);
print json_encode(array_reverse($reply, true));



/*-------------------------------- functions-------------------------------*/

function addtime($reply)
{
	$reply["header"]["request_time_raw"]=$_SERVER['REQUEST_TIME'];
	$reply["header"]["request_time"]=date("Y-m-d H:i:sO",$_SERVER['REQUEST_TIME']);
	return $reply;
}

function fgmslist($reply,$mpserver_list)
{	
	foreach ($mpserver_list as $server_details)
	{
		$server=Array();
		$server["name"]=$server_details["short"];
		$server["address"]=$server_details["long"];
		$server["location"]=$server_details["loc"];
		$reply["data"]["fgms"][]=$server;
	}
	$reply["header"]=Array("code"=>200,"msg"=>'OK');
	return $reply;
}
function getstatus($server,$mpserver_list)
{	
	global $tracker_ip,$min_subversion,$min_version;
	if ($server=="")
	{
		$reply["header"]=Array("code"=>400,"msg"=>'Bad Request. Server not defined.');
		return $reply;
	}
	$short=$server;
	$report_lv="3";
	require_once("./_fgms_checker.php");
	return null;
}
?>