<?="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";?>
<?php

require_once("./_fgms_conf.php");
$HEAD = "";
$BLANKS = "";
$ONLOAD = "";

foreach($mpserver_list as $mpserver) {

	$ONLOAD .= "\n      check_mpserver(\"".$mpserver['short']."\");";

	$BLANKS .= <<<BLANKS
		<tr id="$mpserver[short]">
			<td id="$mpserver[short]_status" class="clearbox" style="font-size: 6pt; text-align: center; background-color: #d8d8d8;">
				CHECKING...
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				$mpserver[long]
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				$mpserver[loc]
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
			<td class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
			<td id="$mpserver[short]_chk" class="clearbox" style="background-color: #d8d8d8;">
				...
			</td>
		</tr>

BLANKS;

}

$HEAD .= <<<HEAD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>FlightGear Multiplayer Status: mpserver15.flightgear.org</title>
  <link rel="stylesheet" href="./index.css" type="text/css" />
  <meta name="description" content="FlightGear Multilplayer Server #15 Status Page" />
  <script src="./fg.js" type="text/javascript"></script>
  <script type="text/javascript">
    function check_mpservers() { $ONLOAD
    }
	setInterval('check_mpservers()',15000);
  </script>
</head>

<body onload="check_mpservers();">
	<table width="100%" border="0">
		<tr>
			<td colspan="9" class="clearbox">
				<div id="header">
					FlightGear Multiplayer Server Status<br />
				</div>
				<div class="title">
					FGTracker.ml<br />Page generated on 
HEAD;
$HEAD .= date("y-m-d G:i:s").'<br />
				</div>
				*"Tracked" means the mpserver is tracked by <a href="https://fgtracker.ml">mpserver15.flightgear.org</a><br /> Minimun fgms version required for tracking: v0.'.$min_version.'.'.$min_subversion.'.
			</td>
		</tr>
		<tr class="header">
			<td class="clearbox">
				Status
			</td>
			<td class="clearbox">
				Server Address
			</td>
			<td class="clearbox">
				Location
			</td>
			<td class="clearbox">
				Server IP
			</td>
			<td class="clearbox">
				Version
			</td>
			<td class="clearbox">
				Tracked
			</td>
			<td class="clearbox">
				Tracker IP
			</td>
			<td class="clearbox">
				Total Clients
			</td>
			<td class="clearbox">
				Local Clients
			</td>
			<td class="clearbox">
				Last check
			</td>
		</tr>';

$FOOT .= <<<FOOT
	</table>
</body>
</html>

FOOT;


print $HEAD;
print $BLANKS;
print $FOOT;

?>
