<?PHP
/*
FGTracker service Version 1.0INCOMPLETE

Author								: Hazuki Amamiya <FlightGear forum nick Hazuki>
License								: GPL Version 3
OS requirement 						: Linux 
DB requirement						: PostgreSQL v8 or above
PHP requirement						: PHP 5.1 or above (With php-cli module installed)
Developed and tested under this env	: Debian 8.2/php 5.6.14+dfsg-0+deb8u1/PostgreSQL 9.4.5-0+deb8u1

DO NOT USE THIS PROGRAM AS THIS PROGRAM IS STILL IN DEVELOPMENT AND INCOMPLETE
See README.txt for more information
*/

/*variable setup*/
$var['port'] = 8000; /*Port to bind*/
$var['error_reporting_level'] = E_ALL; /*Set Error reporting level (E_ERROR, E_WARNING, E_NOTICE, E_ALL). Default E_NOTICE*/
$var['log_location']=dirname(__FILE__);

/*Postgresql information*/
$var['postgre_conn']['host'] = ""; /*(Linux only: empty sting for using unix socket*/
$var['postgre_conn']['port'] = 5432; /*(Linux only: lgnored if using unix socket*/
$var['postgre_conn']['desc'] = "AC-VSERVER";
$var['postgre_conn']['uname'] = "fgtracker";
$var['postgre_conn']['pass'] = "fgtracker";
$var['postgre_conn']['db'] = "fgtracker";

/*Do not amend below unless in development*/

set_time_limit(0);

require("../server/fgt_error_report.php");
$fgt_error_report=new fgt_error_report();

$var['os'] = strtoupper(PHP_OS);
$var['fgt_ver']="1.0INCOMPLETE";
$var['min_php_ver']='5.1';
$var['exitflag']=false;
$var['interval']=300;/*Interval. Default 300(seconds)*/

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

$update_mgr=new UpdateMgr();
$fgt_sql=new fgt_postgres();

while(1)
{
	if($var['exitflag']===true)
		break;
	
	$message="Update completed. Going to sleep for ".$var['interval']." seconds";
	$fgt_error_report->fgt_set_error_report("CORE",$message,E_WARNING);
	sleep($var['interval']);
}
$message="Exiting";
$fgt_error_report->fgt_set_error_report("CORE",$message,E_NOTICE);

?>