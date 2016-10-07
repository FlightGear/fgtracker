<?php
$var["min_alt"]=-9000; /*waypoints lower than this altitude are ignored*/
$var["adminname"]="Hazuki"; /*set XOOPS admin - Only admin can delete flights etc.*/
$var["alter_db_token"]="clan.ns1";
$var["reCAPTCHA_secret"]="6Lc8LAcUAAAAAE9Dy9VyJuhXvevIyQz3hCuCZRp6"; //Google reCAPTCHA secret key 
$var["callsign_blacklist"]=Array("callsig","shit","fuck");
/*Create database connection*/
//$conn=pg_connect("host=localhost port=5432 dbname=fgtracker user=fgtracker password=fgtracker");
include_once 'include/db_connect.php';/*connect to DB*/
?>