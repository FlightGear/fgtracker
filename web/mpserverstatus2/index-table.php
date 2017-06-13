<?php require_once("./_fgms_conf.php");?>
<table>
<tr class="header">
	<th>Server Name</th>
	<th>Status</th>
</tr>
<?php
$res=json_decode(file_get_contents($json_url), true);
foreach ($res["data"] as $fgms)
{
	$f_name=$fgms['name'];

	$f_protocol=$fgms['protocol_ver'];
	$f_last_seen_raw=$fgms['last_seen_raw'];
	$f_enabled=$fgms['enabled'];
	$tracked="Not tracked";
	$bg_colour="#FF9966";
	$bg_colour2="#BB0000";
	if ($f_protocol=='V20151207')
		$f_protocol='0.12';
	else $f_protocol='Unknown';
	
	if ($f_enabled===false or $f_last_seen_raw==null or $f_protocol=='Unknown')
		$tracking_count="N/A";
	else if (time()-$f_last_seen_raw >1800)
	{
		$tracked="Unknown";
		$bg_colour="#FFFF00";
		$bg_colour2="#DDDD00";
	}
	else 
	{
		$tracked="Tracked";
		$bg_colour="#99FF66";
		$bg_colour2="#00BB00";
	}
	print "
	<tr>
		<td style=\"background-color: $bg_colour2;\">$f_name</td>
		<td style=\"background-color: $bg_colour;\">$tracked</td>
	</tr>";
}?>
</table>