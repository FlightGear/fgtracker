<html>
<title>Flights tracked by FGTracker</title>
<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyB_XARtAkZfv8AIOVProEKilytISia5sGM&&sensor=false" type="text/javascript"></script>

<div id="map-canvas" style="width: 100%; height: 100%"></div>
<?php
if(!isset($_GET["lat"]))
{
	$lat=0; $lon=0;$zoom=1;
}else
{
	$lat=$_GET["lat"]; $lon=$_GET["lon"]; $zoom=13;
}
?>
<script type="text/javascript">
function initialize() {
	var myLatlng = new google.maps.LatLng(<?php echo $lat; ?>,<?php echo $lon; ?>);

	var mapOptions = {
		zoom: <?php echo $zoom; ?>,
		center: myLatlng
	
	}

	var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
	setMarkers(map, planes);
	setTWMarkers(map, towers);
}
	var planes = [
<?php
function head_2_WR($head)
{
	if($head>348.75 or $head<=11.25)
		return "N";
	if($head>11.25 and $head<=33.75)
		return "NNE";
	if($head>33.75 and $head<=56.25)
		return "NE";
	if($head>56.25 and $head<=78.75)
		return "ENE";
	if($head>78.75 and $head<=101.25)
		return "E";
	if($head>101.25 and $head<=123.75)
		return "ESE";
	if($head>123.75 and $head<=146.25)
		return "SE";
	if($head>146.25 and $head<=168.75)
		return "SSE";
	if($head>168.75 and $head<=191.25)
		return "S";
	if($head>191.25 and $head<=213.75)
		return "SSW";
	if($head>213.75 and $head<=236.25)
		return "SW";
	if($head>236.25 and $head<=258.75)
		return "WSW";
	if($head>258.75 and $head<=281.25)
		return "W";
	if($head>281.25 and $head<=303.75)
		return "WNW";
	if($head>303.75 and $head<=326.25)
		return "NW";
	return "NNW";
}
$json=json_decode(file_get_contents('http://mpserver15.flightgear.org/modules/fgtracker/interface.php?action=livewaypoints'), true);

$callsign_array=Array();
$out=$out_TW="";
foreach ($json["data"]["wpt"] as $wpt_array)
{
	//var_dump($wpt_array);
	if (in_array($wpt_array["callsign"], $callsign_array) or $wpt_array["current_status"]!="OPEN")
		continue;
	if(substr($wpt_array["callsign"], -3)=="_TW" or $wpt_array["model"]=="OpenRadar")
	{
		$out_TW.= "
		[\"".$wpt_array["callsign"]."\",\"".$wpt_array["model"]."\",".$wpt_array["lat"].",".$wpt_array["lon"].",\"https://fgtracker.ml/modules/fgtracker/?FUNCT=FLIGHT&FLIGHTID=".$wpt_array["flight_id"]."\",\"".head_2_WR($wpt_array["hdg"])."\"],";
		$callsign_array[]=$wpt_array["callsign"];
	}
	else
	{
		$out.= "
		[\"".$wpt_array["callsign"]."\",\"".$wpt_array["model"]."\",".$wpt_array["lat"].",".$wpt_array["lon"].",\"https://fgtracker.ml/modules/fgtracker/?FUNCT=FLIGHT&FLIGHTID=".$wpt_array["flight_id"]."\",\"".head_2_WR($wpt_array["hdg"])."\"],";
		$callsign_array[]=$wpt_array["callsign"];
	}

}
print rtrim($out, ",");
?>
];
	var towers = [
	<?php print rtrim($out_TW, ",");
?>
];
	function setMarkers(map, locations) 
	{
	  // Add markers to the map
	  var planeicon_svg='M 157.98695,184.38488 L 173.37483,168.20017 C 182.38616,159.18884 197.56012,162.31477 197.56012,162.31477 L 242.58958,168.47612 L 265.39575,146.16045 C 277.41087,134.35989 288.26269,152.4142 283.54247,158.63631 L 271.83305,172.24635 L 320.32641,181.22794 L 336.78707,162.03882 C 354.38063,141.01237 367.47041,159.95529 359.53185,171.11218 L 348.89521,184.56906 L 421.75804,194.07153 C 484.40828,133.78139 509.98537,108.77262 526.46939,123.63021 C 543.05967,138.5836 513.71315,168.38877 456.64135,227.17701 L 467.00204,302.24678 L 482.26714,289.52597 C 491.27847,282.01653 507.27901,294.06392 490.75822,309.72648 L 469.76089,329.52825 L 478.61969,378.66527 L 491.73923,368.58052 C 503.32523,359.35463 517.39476,371.55518 501.7322,388.29052 L 480.88803,409.28786 C 480.02981,409.93153 487.69305,452.38631 487.69305,452.38631 C 492.41327,473.19821 480.67347,480.80195 480.67347,480.80195 L 466.35838,493.27782 L 411.97962,339.67439 C 407.47395,326.15738 396.0546,311.47862 376.97351,313.22076 C 366.8894,314.29354 341.41552,331.49026 337.98263,335.56682 L 279.00579,392.27531 C 277.5039,393.34809 288.07915,465.99635 288.07915,465.99635 C 288.07915,468.14191 269.38054,492.66454 269.38054,492.66454 L 232.01433,426.14725 L 213.56128,434.7301 L 224.35108,417.93211 L 157.06733,379.9526 L 182.29502,361.49956 C 194.31014,364.28878 257.3034,371.36975 258.59073,370.72608 C 258.59073,370.72608 309.88762,319.85344 312.81633,316.77643 C 329.76623,298.96831 335.46935,292.31456 338.04402,283.51778 C 340.6208,274.71377 336.23117,261.81195 309.62838,245.4769 C 272.93937,222.94855 157.98695,184.38488 157.98695,184.38488 z';
	  /*"N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW","SW", "WSW", "W", "WNW", "NW", "NNW"*/

		var planeicon_N = {path: planeicon_svg, rotation: -45, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_NNE = {path: planeicon_svg, rotation: -22.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_NE = {path: planeicon_svg, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_ENE = {path: planeicon_svg, rotation: 22.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_E = {path: planeicon_svg, rotation: 45, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_ESE = {path: planeicon_svg, rotation: 67.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_SE = {path: planeicon_svg, rotation: 90, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_SSE = {path: planeicon_svg, rotation: 112.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_S = {path: planeicon_svg, rotation: 135, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_SSW = {path: planeicon_svg, rotation: 157.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_SW = {path: planeicon_svg, rotation: 180, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_WSW = {path: planeicon_svg, rotation: 202.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_W = {path: planeicon_svg, rotation: 225, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_WNW = {path: planeicon_svg, rotation: 247.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_NW = {path: planeicon_svg, rotation: 270, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		var planeicon_NNW = {path: planeicon_svg, rotation: 292.5, fillColor: 'red', fillOpacity: 0.8, scale: 0.04, strokeColor: 'gold',strokeWeight: 1, anchor: new google.maps.Point(300,300)};
		
		for (var i = 0; i < planes.length; i++) 
		{
			var plane = planes[i];
			var myLatLng = new google.maps.LatLng(plane[2], plane[3]);
			var icon = planeicon_N; /*default icon*/
			/*map icon*/
			if (plane[5]=="NNE") icon= planeicon_NNE;
			if (plane[5]=="NE") icon= planeicon_NE;
			if (plane[5]=="ENE") icon= planeicon_ENE;
			if (plane[5]=="E") icon= planeicon_E;
			if (plane[5]=="ESE") icon= planeicon_ESE;
			if (plane[5]=="SE") icon= planeicon_SE;
			if (plane[5]=="SSE") icon= planeicon_SSE;
			if (plane[5]=="S") icon= planeicon_S;
			if (plane[5]=="SSW") icon= planeicon_SSW;
			if (plane[5]=="SW") icon= planeicon_SW;
			if (plane[5]=="WSW") icon= planeicon_WSW;
			if (plane[5]=="W") icon= planeicon_W;
			if (plane[5]=="WNW") icon= planeicon_WNW;
			if (plane[5]=="NW") icon= planeicon_NW;
			if (plane[5]=="NNW") icon= planeicon_NNW;
			
			var marker = new google.maps.Marker({position: myLatLng, map: map, icon: icon, url: plane[4], title: plane[0].concat("/".concat(plane[1]))});
			google.maps.event.addListener(marker, 'click', function() {window.parent.location.href = this.url;});
		}
	}

	function setTWMarkers(map, towers) 
	{
	  // Add markers to the map
		var planeicon_svg='M 16,6 C 6,6 0,15.938 0,15.938 0,15.938 6,26 16,26 26,26 32,16 32,16 32,16 26,6 16,6 z m 0,18 C 7.25,24 2.5,16 2.5,16 2.5,16 7.25,8 16,8 c 8.75,0 13.5,8 13.5,8 0,0 -4.75,8 -13.5,8 z m 6,-8 c 0,3.313708 -2.686292,6 -6,6 -3.313708,0 -6,-2.686292 -6,-6 0,-3.313708 2.686292,-6 6,-6 3.313708,0 6,2.686292 6,6 z';
		var planeicon_N = {path: planeicon_svg, fillColor: 'red', fillOpacity: 0.8, scale: 0.5, strokeColor: 'gold',strokeWeight: 1};
	
		for (var i = 0; i < towers.length; i++) 
		{
			var tower = towers[i];
			var myLatLng = new google.maps.LatLng(tower[2], tower[3]);
			var icon = planeicon_N; /*default icon*/
			
			var marker = new google.maps.Marker({position: myLatLng, map: map, icon: icon, url:  tower[4], title:  tower[0].concat("/".concat( tower[1]))});
			google.maps.event.addListener(marker, 'click', function() {window.parent.location.href = this.url;});
		}
	}

	
google.maps.event.addDomListener(window, 'load', initialize);

</script>
</html>
