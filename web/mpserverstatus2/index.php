<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<?php require_once("./_fgms_conf.php");?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>FlightGear Multiplayer Status: mpserver15.flightgear.org</title>
  <link rel="stylesheet" href="./index.css" type="text/css" />
  <meta name="description" content="FGMS Status Page" />
  <meta http-equiv="refresh" content="15" >
</head>
<body>
	<table width="100%" border="0">
		<tr>
			<td colspan="9" class="clearbox">
				<div id="header">
					FlightGear Multiplayer Server Status 2<br />
				</div>
				<div class="title">
					<?php echo $site_name;?><br />Page generated on <?php echo date("Y-m-d G:i:s")."+08";?><br />
				</div>
				*"Tracked" means the mpserver is tracked by <a href="<?php echo $tracker_url;?>"><?php echo $tracker_name;?>"</a><br />
			</td>
		</tr>
		<tr class="header">
			<td class="clearbox">Server Name</td>
			<td class="clearbox">Server Address</td>
			<td class="clearbox">Location</td>
			<td class="clearbox">Required Version</td>
			<td class="clearbox">Reported Version</td>
			<td class="clearbox">Tracked</td>
			<td class="clearbox">Total Tracking Clients</td>
			<td class="clearbox">Last seen</td>
		</tr>
<?php
$res=json_decode(file_get_contents($json_url), true);
foreach ($res["data"] as $fgms)
{
	$f_name=$fgms['name'];
	$f_domain=$fgms['domain'];
	$f_protocol=$fgms['protocol_ver'];
	$f_ver=$fgms['ver'];
	$f_maintainer=$fgms['maintainer'];
	$f_location=$fgms['location'];
	$f_last_seen=$fgms['last_seen'];
	$f_last_seen_raw=$fgms['last_seen_raw'];
	$f_enabled=$fgms['enabled'];
	$tracking_count=$fgms['tracking_count'];
	$tracked="No";
	$bg_colour="#FF9966";
	if ($f_protocol=='V20151207')
		$f_protocol='0.12';
	else $f_protocol='Unknown';
	
	if ($f_enabled===false or $f_last_seen_raw==null)
	{	$tracking_count="N/A";
		if ($f_last_seen_raw==null)
			$f_last_seen="Never";
	}
	else if (time()-$f_last_seen_raw >1800)
	{
		$tracked="Unknown";
		$bg_colour="#FFFF00";
	}
	else 
	{
		$tracked="Yes";
		$bg_colour="#99FF66";
	}
	
	print "
		<tr style=\"background-color: $bg_colour;\">
			<td>$f_name</td>
			<td>$f_domain</td>
			<td>$f_location</td>
			<td>$f_protocol</td>
			<td>$f_ver</td>
			<td>$tracked</td>
			<td>$tracking_count</td>
			<td>$f_last_seen</td>
		</tr>";
}
?>
</table>
</body>