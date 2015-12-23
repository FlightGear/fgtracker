<?php
/*Cron job developed by hazuki
Ver 1.0 -20110906
*/
echo "Connecting to fgtracker DB...\n";
include_once (dirname(__FILE__)."/db_connect.php");
$res=pg_query($conn,"SET TIMEZONE TO 'UTC';");
pg_free_result($res);

/*Caching top100 alltime*/
$temp='temp_cache_top100_alltime';
$perm='cache_top100_alltime';
/*Truncate temp table*/
echo "Truncate temp table '$temp'\n";
$res=pg_query($conn,"Truncate table $temp");
//pg_free_result($res);

/*Summary Data into temp table*/
echo "Summary data to temp table '$temp'\n";
$res=pg_query($conn,"INSERT INTO $temp SELECT f.callsign AS callsign,justify_hours(sum(f.end_time-f.start_time)) AS flighttime FROM flights as f GROUP BY f.callsign HAVING justify_hours(sum(f.end_time-f.start_time)) is not null ORDER BY sum(f.end_time-f.start_time) DESC LIMIT 100");
//$nr=pg_num_rows($res);

/*Load data from temp to perm*/
echo "Load temp table '$temp' to perm table '$perm'\n";
$res=pg_query($conn,"Truncate table $perm");
$res=pg_query($conn,"INSERT INTO $perm select * from $temp");

/*create timestamp record*/
echo "Create timestamp record for '$perm'\n";
$res=pg_query($conn,"Delete from cache_time where tablename ='$perm'");
$res=pg_query($conn,"INSERT INTO cache_time VALUES('$perm',NOW())");

?>