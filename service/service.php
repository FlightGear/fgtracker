<?PHP
/*
FGTracker service Version 1.0INCOMPLETE

Author								: Hazuki Amamiya <FlightGear forum nick Hazuki>
License								: GPL Version 3
OS requirement 						: Linux 
DB requirement						: PostgreSQL v9 or above
PHP requirement						: PHP 5.1 or above (With php-cli module installed)
Developed and tested under this env	: Debian 8.2/php 5.6.14+dfsg-0+deb8u1/PostgreSQL 9.4.5-0+deb8u1

DO NOT USE THIS PROGRAM AS THIS PROGRAM IS STILL IN DEVELOPMENT AND INCOMPLETE
See README.txt for more information
*/

/*Do not amend below unless in development*/
require (dirname(__FILE__)."/config.php");
set_time_limit(0);

require("../server/fgt_error_report.php");
$fgt_error_report=new fgt_error_report();

$var['os'] = strtoupper(PHP_OS);
$var['fgt_ver']="1.0INCOMPLETE";
$var['min_php_ver']='5.1';
$var['exitflag']=false;
$var['interval']=300;/*Interval. Default 300(seconds)*/
$var['appname']="FGTracker Service";

$message="FGTracker Service Version ".$var['fgt_ver']." in ".$var['os']." with PHP ".PHP_VERSION;
$fgt_error_report->fgt_set_error_report("CORE",$message,E_ERROR);

if (version_compare(PHP_VERSION, $var['min_php_ver'], '<')) {
	$message="PHP is not new enough to support FGTracker. FGTracker is now exiting";
	$fgt_error_report->fgt_set_error_report("CORE",$message,E_ERROR);
	return;
}

if(substr($var['os'],0,3) != "WIN")
{
	declare(ticks = 1); /*required by signal handler*/
	define('IS_WINDOWS', false);
	require("../server/signal.php");
}else
	define('IS_WINDOWS', true);

require("update.php");
require("../server/fgt_postgres.php");
require ($var['fgtracker_xoops_location'].'/include/flight_report.php');
require ($var['fgtracker_xoops_location'].'/include/get_nearest_airport.php');

$update_mgr=new UpdateMgr();
$fgt_sql=new fgt_postgres($var['appname']. "V".$var['fgt_ver']);

if($fgt_sql->check_no_of_instance($var['appname'],1)===false)
{
	$message="FGTracker service instance detected...Exiting...";
	$fgt_error_report->fgt_set_error_report("CORE",$message,E_ERROR); return;
}

if(isset($argv[1]))
	if ($argv[1]=="archive")
	{
		$var['archive_mode']=true;
		$message=chr(27)."[42mFGTracker Service is in archive mode".chr(27)."[0m";
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_ERROR);
	}	
	else $var['archive_mode']=false;
else $var['archive_mode']=false;

if ($var['archive_mode']===true)
{
	$line = readline("You must terminate any other instance of FGTracker server, FGTracker service and WEB. Press Y to continue. Any other alphabet to exit.");
	if ($line != "Y" and $line != "y")
	{
		$message="Exiting";
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_NOTICE); return;
	}
	
	$line = readline("FGTracker service will archive data before ".$var['archive_date'].". Press Y to confirm. Any other alphabet to abort.");
	if ($line != "Y" and $line != "y")
	{
		$message="Exiting";
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_NOTICE); return;
	}
}



while(1)
{
	if($var['exitflag']===true)
		break;
	
	if ($var['archive_mode']===true)
	{
		$result=$update_mgr->close_opened_flights();
		if($var['exitflag']===true or $result===false)
			break;
		
		$update_mgr->fix_no_waypoint_flights();
		if($var['exitflag']===true)
			break;
	}
	
	$update_mgr->fix_erric_data();
	if($var['exitflag']===true)
		break;
	
	$result=$update_mgr->updateeffectiveflighttimeandicao();
	if($var['exitflag']===true or $result[0]===false)
		break;
	if ($var['archive_mode']===true and $result[1]===true)
	{
		$message="Archive failed. Please clear issues on identifcal waypoints first";
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_ERROR);
		break;
	}
		
	
	$update_mgr->updateranking();
	
	if ($var['archive_mode']===true)
	{
		$result=$update_mgr->archive_flights_waypoints();
		if ($result===true)
		{
			$message="Archive completed";
			$fgt_error_report->fgt_set_error_report("CORE",$message,E_WARNING);
		}else
		{
			$message="Archive failed";
			$fgt_error_report->fgt_set_error_report("CORE",$message,E_ERROR);
		}

		break;
	}
	$message="Update completed. Going to sleep for ".$var['interval']." seconds";
	$fgt_error_report->fgt_set_error_report("CORE",$message,E_WARNING);
	sleep($var['interval']);
}
$message="Exiting";
$fgt_error_report->fgt_set_error_report("CORE",$message,E_NOTICE);

?>