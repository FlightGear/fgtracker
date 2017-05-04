<?php
/*
FGTracker server Version 2.3a1

Author								: Hazuki Amamiya <FlightGear forum nick Hazuki>
License								: GPL Version 3
OS requirement 						: Linux/Windows or any other OS with PHP and PostgreSQL
DB requirement						: PostgreSQL v9.5 or above
PHP requirement						: PHP 5.1 or above (With php-cli module installed)
Developed and tested under this env	: Debian 8.7/5.6.30+dfsg-0+deb8u1/PostgreSQL 9.6.2-1.pgdg80+1

See README.txt for more information
*/
/*Do not amend below unless in development*/
require (dirname(__FILE__)."/config.php");

if(!defined('MSG_DONTWAIT')) define('MSG_DONTWAIT', 0x40);
set_time_limit(0);

require("fgt_error_report.php");
$fgt_error_report=new fgt_error_report();

$var['os'] = strtoupper(PHP_OS);
$var['fgt_ver']="2.3a1";
$var['min_php_ver']='5.1';
$var['min_postgres_ver']='9.5';
$var['exitflag']=false;
$var['ping_interval']=60;/*check timeout interval. Default(=60)*/
$var['ident_interval']=5;/*check timeout interval for not yet identified connection. Default(=5)*/
$var['appname']="FGTracker V".$var['fgt_ver'];

$message="FGTracker version ".$var['fgt_ver']." in ".$var['os']." with PHP ".PHP_VERSION;
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
	require("signal.php");
}else
	define('IS_WINDOWS', true);

require("fgt_read_NOWAIT.php");
require("fgt_read_V20151207.php");
require("fgt_ident.php");
require("fgt_postgres.php");
require("fgt_msg_process.php");
require("fgt_connection_mgr.php");

$fgt_ident=new fgt_ident();
$fgt_conn=NULL; /*to be called by $fgt_sql->connectmaster*/
$fgt_sql=new fgt_postgres($var['appname']);


$clients=Array();
while (1)
{
	/*$clients[$uuid]=Array(
	[res socket],
	[bool connected],
	[bool identified],
	[str protocal_version],
	[str server_ident],
	[str read_buffer],
	[class read_class],
	[class msg_process_class],
	[int last_reception],
	[int timeout_stage],
	[str write_buffer])
	*/
	
	// accept incoming connections
	$no_data=true;
	$fgt_conn->accept_connection();
	
	foreach($clients as $uuid=>$client)
	{
		/*Check the connection*/
		if ($fgt_sql->connectmaster($var['appname'])===true)
			break;
		
		if($fgt_conn->close_connection($uuid)===true)
			continue;
		
		/*Read client input*/
		$data_len=$fgt_conn->read_connection($uuid);
		if($data_len===false)
			continue;
		else if ($data_len)$no_data=false;
		
		if(strpos($clients[$uuid]['read_buffer'], "\0")!==false) /*Process the read buffer (if needed)*/
		{
			if($client['identified']===false)
			{
				if($fgt_ident->check_ident($uuid))
				{
					$clients[$uuid]['timeout_stage']=0;
					$clients[$uuid]['last_reception']=time();
					$clients[$uuid]['read_class']->read_buffer();
				}
			}
			else 
			{
				/*update last_comm*/
				$clients[$uuid]['timeout_stage']=0;
				$clients[$uuid]['last_reception']=time();
				$sql_parm=Array($clients[$uuid]['server_ident'],$clients[$uuid]['protocal_version']);
				$sql="UPDATE fgms_servers SET last_comm=now() WHERE name =$1 and key=$2;";
				pg_query_params($sql,$sql_parm);
				$clients[$uuid]['read_class']->read_buffer();
			}	
		}
		
		/*check timeout*/
		$fgt_conn->check_timeout($uuid);
		
		/*Process the write buffer*/
		$fgt_conn->write_connection($uuid);
		
	}
	if($var['exitflag']===true)
	{
		$message="Exiting";
		$fgt_error_report->fgt_set_error_report("CORE",$message,E_NOTICE);
		break;
	}
	if ($no_data)/*only sleep when no data flown in*/
		usleep(100000);
}
// close sockets
$fgt_conn->close_all_connections();
$fgt_error_report->terminate(FALSE);
?>

