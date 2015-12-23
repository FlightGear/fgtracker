<?php
///////////////////////////////////////////////////////////////////////
// GENERATE_KML (JSON_DB)
///////////////////////////////////////////////////////////////////////
function generate_kml($conn,$flightid)
{
    header('Content-type: application/kml');
    header('Content-Disposition: attachment; filename="fgtracker_'.$flightid.'.kml"');

    if(!JSON_DB)
	{
		$flightid_escaped=pg_escape_string($conn,$flightid);
		
	}else
	{
		$flightid_escaped=urlencode($flightid);
		$res=json_decode(file_get_contents(JSON_DB_LOCATION."?action=flight&flightid=$flightid_escaped"), true);
	}

	$flight = new FLIGHT_REPORT;

    echo '<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document>
	<name>FGFS Flight #'.$flightid.'</name>
	<open>1</open>
	<Style id="Arrival">
      <IconStyle>
        <Icon>
          <href>http://maps.google.com/mapfiles/kml/paddle/A.png</href>
        </Icon>
      </IconStyle>
    </Style>
	<Style id="Departure">
		<IconStyle>
			<Icon>
				<href>http://maps.google.com/mapfiles/kml/paddle/D.png</href>
			</Icon>
		</IconStyle>
    </Style>
	<Style id="Waypoints">
		<IconStyle>
			<Icon>
				<href>http://mpserver15.flightgear.org/modules/fgtracker/icons/reddot.png</href>
			</Icon>
	</IconStyle>
    </Style>
	<Folder>
		<name>Waypoints</name>
		<open>1</open>
';
	$wp_array=Array();
	if(!JSON_DB)
	{
		$res=pg_query($conn,"SELECT time,EXTRACT(EPOCH FROM time) AS time_raw,longitude,latitude,altitude FROM waypoints_all WHERE flight_id=$flightid_escaped AND (longitude!=0 OR latitude!=0 OR altitude!=0) ORDER BY time;");
		$nr=pg_num_rows($res);
		for($i=0;$i<$nr;$i++)
		{
			$lat=pg_result($res,$i,"latitude");
			$lon=pg_result($res,$i,"longitude");
			$alt=pg_result($res,$i,"altitude");
			$time=pg_result($res,$i,"time");
			$timeraw=pg_result($res,$i,"time_raw");
			$wp_array[] = Array($lat,$lon,$alt,$timeraw,$time);
		}
		pg_free_result($res);
	}else
	{
		foreach($res["data"]["wpt"] as $wpt)
		{
			$lat=$wpt["lat"];
			$lon=$wpt["lon"];
			$alt=$wpt["alt"];
			$time=$wpt["time"];
			$timeraw=$wpt["time_raw"];
			$wp_array[] = Array($lat,$lon,$alt,$timeraw,$time);
		}
	}

	$flight->MakeFlightReport ( $wp_array, "NoDiagram" );
	$gs=$flight->gs;
    $i=0;
	foreach($wp_array as $wp)
    {
		$lat=$wp[0];
		$lon=$wp[1];
		$alt=round(0.3048*$wp[2],2);
		$time=$wp[4];
		if ($i==0) $gs_fix="-";
		else $gs_fix=round($gs[$i][0]);
		echo "
		<Placemark>
			<name>#$i:$time</name>
			<description>
			Coordinate: $lon,$lat
			Altitude: ".round($wp[2],2)."ft
			Speed: $gs_fix knots</description>
			<styleUrl>#Waypoints</styleUrl>
			<TimeStamp>
				<when>".str_replace(' ','T',$time)."</when>
			</TimeStamp>
			<Point>
				<coordinates>$lon,$lat,$alt</coordinates>
			</Point>
		</Placemark>";
		if ($i==0)
		{
			$start_lat=$lat;
			$start_lon=$lon;
			$start_alt=$alt;	
		}$i++;
    }

	echo "
	</Folder>
	<Placemark>
		<name>Migration path</name>
		<Style>
			<LineStyle>
			<color>ff0000ff</color>
			<width>2</width>
			</LineStyle>
		</Style>
		<LineString>
			<tessellate>1</tessellate>
			<altitudeMode>absolute</altitudeMode>
			<coordinates>
			";
	foreach($wp_array as $wp)
		echo $wp[1].",".$wp[0].",". round(0.3048*$wp[2],2) ." ";

	echo "
			</coordinates>
		</LineString>
	</Placemark>
  	<Placemark> 
		<name>Departure</name> 
		<styleUrl>#Departure</styleUrl>
		<Point>
		  <coordinates>$start_lon,$start_lat,$start_alt</coordinates>
		</Point> 
	</Placemark>
	<Placemark> 
		<name>Arrival</name> 
		<styleUrl>#Arrival</styleUrl>
		<Point>
		  <coordinates>$lon,$lat,$alt</coordinates>
		</Point> 
	</Placemark>
</Document>
</kml>
";
}
?>