<?php
header("Content-type: image/gif");

//include_once 'include/flightlog.php';
//include_once 'include/trig.php';
include_once 'include/flight_report.php';
include_once 'include/db_connect.php';

$flightid=$_GET["FLIGHTID"];
$graphtype=$_GET["graphtype"];

if ($flightid=="") $flightid=1;

$res=pg_query($conn,"SELECT latitude,longitude,altitude,EXTRACT(EPOCH FROM time) AS time_raw FROM waypoints_all WHERE flight_id=$flightid AND (longitude!=0 OR latitude!=0 OR altitude!=0)ORDER BY time");
$n=pg_num_rows($res);

for($i=0;$i<$n;$i++)
{
    $lat=pg_result($res,$i,"latitude");
	$timeraw=pg_result($res,$i,"time_raw");
    $lon=pg_result($res,$i,"longitude");
    $alt=pg_result($res,$i,"altitude");
    $array[] = Array($lat,$lon,$alt,$timeraw);
}
pg_free_result($res);
pg_close($conn);

$flight = new FLIGHT_REPORT;

if ($graphtype=="altitude")
$flight->MakeFlightReport ( $array, "AltHistory" );
else
$flight->MakeFlightReport ( $array, "GrdSpdHistory" );
?>
