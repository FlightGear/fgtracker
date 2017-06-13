<?php
///////////////////////////////////////////////////////////////////////
// setup_client - setup client information
///////////////////////////////////////////////////////////////////////
function setup_client()
{
	global $conn, $client, $var;
	
	$client['country']=null;
	/*set timezone according to client IP http://dev.maxmind.com/geoip/legacy/geolite/ or Cloudflare*/
	if (isset($_GET['ip']))
	{
		if (filter_var($_GET['ip'], FILTER_VALIDATE_IP)) 
			$client['ip']=$_GET['ip'];
		else $client['ip']=$_SERVER['REMOTE_ADDR'];
	} else
	{
		if($var["cloudflare"]===true)
		{
			$client['ip']=$_SERVER['HTTP_CF_CONNECTING_IP'];
			if (isset($_SERVER["HTTP_CF_IPCOUNTRY"]))
				$client['country']=$_SERVER["HTTP_CF_IPCOUNTRY"];
		}	
		else	$client['ip']=$_SERVER['REMOTE_ADDR'];
	}
	
	if (isset($_GET['clientlocation']))
		if( strlen($_GET['clientlocation'])==2)
			$client['country']=pg_escape_string($conn,$_GET['clientlocation']);
		
	$clienteip=explode('.', $client['ip']);
	$clientintip=  ( 16777216 * $clienteip[0] )
				 + (    65536 * $clienteip[1] )
				 + (      256 * $clienteip[2] )
				 +              $clienteip[3];

	if (is_null($client['country']))
	$sql="select * from (SELECT a.country, country_name, (select zone_name from geo_zone c where c.country=a.country LIMIT 1), (select zone_id from geo_zone d where d.country=a.country LIMIT 1) from geo_ip a left join geo_country b on a.country= b.country where $clientintip between start_intip and end_intip) AS e left join geo_timezone as f on e.zone_id=f.zone_id where time_start < extract(epoch from now()) order by time_start desc limit 1;";
	else
	$sql="select * from (select zone_id, zone_name, z.country, country_name from geo_zone z join geo_country c on z.country=c.country where z.country ='".$client['country']."' LIMIT 1) AS e left join geo_timezone as f on e.zone_id=f.zone_id where time_start < extract(epoch from now()) order by time_start desc limit 1;";

	$res=pg_query($conn,$sql);
	if ($res!==FALSE and pg_num_rows ( $res )>0)
	{
		if (pg_result($res,0,'country')===false)
		{
			$client['country']=NULL;
			$client['country_name']="Unknown";
			$client['timezone']="UTC";
			$client['timezone_abbr']="UTC";
		}	
		else
		{
			$client['country']=pg_result($res,0,'country');
			$client['country_name']=pg_result($res,0,'country_name');
			$client['timezone']=pg_result($res,0,'zone_name');
			$client['timezone_abbr']=trim(pg_result($res,0,'abbr'));
		}
	}
	else
	{
		$client['country']=NULL;
		$client['country_name']="Unknown";
		$client['timezone']="UTC";
		$client['timezone_abbr']="UTC";
	}
	$sql="set timezone='".$client['timezone']."'";
	$res=pg_query($conn,$sql);
}
?>