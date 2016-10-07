<?php

function get_nearest_airport($conn,$lat,$lon,$alt)
{	/*return array, see return*/
	$low_lat=$lat-0.06;
	$high_lat=$lat+0.06;
	$low_lon=$lon-0.06;
	$high_lon=$lon+0.06;
	$low_alt=$alt-200;
	$high_alt=$alt+200;
	
	$res=pg_query($conn,"SELECT * FROM geo_airports WHERE lat between $low_lat and $high_lat and lon between $low_lon and $high_lon and alt between $low_alt and $high_alt");
	//print "SELECT * FROM geo_airports WHERE lat between $low_lat and $high_lat and lon between $low_lon and $high_lon and alt between $low_alt and $high_alt"; exit;
	if ($res)
    {
		$nr=pg_num_rows ( $res );
		$target_distance=999;$target_row=0;$land_airport_found=false;
		for($i=0;$i<$nr;$i++)
		{
			$distance=GML_distance($lat, $lon, pg_result($res,$i,'lat'), pg_result($res,$i,'lon'));
			//print "$distance[0], ".pg_result($res,$i,'icao').", ".pg_result($res,$i,'airport_type')."<br />";
			if($distance[0]<1.6 and $distance[0]>0.01 and pg_result($res,$i,'airport_type')=="100" and strlen(pg_result($res,$i,'icao'))==4)
			{	/*Airport within 1.6Nm and with 4 character ICAO code are considered as Nearest airport*/
				$target_distance=$distance[0];
				$target_row=$i;break;
			}
			
			if($distance[0]<$target_distance and $land_airport_found==false)
			{	/*Airport within 1.6Nm may considered as Nearest airport*/
				$target_distance=$distance[0];
				$target_row=$i;
				if($distance[0]<1.6 and $distance[0]>0.01 and pg_result($res,$i,'airport_type')=="100")
					$land_airport_found=true;
			}
			
		}//exit;
		if (pg_num_rows($res)==0)
			return Array("----","Unknown",NULL,NULL,NULL);
		$airport=pg_result($res,$target_row,'icao');
		$airport_name=pg_result($res,$target_row,'name');
		$airport_zone=pg_result($res,$target_row,'zone_name');
		if(pg_result($res,$target_row,'country')=="--")
		{
			$airport_country=NULL;
			$airport_city=NULL;
		}else
		{
			$airport_country=pg_result($res,$target_row,'country');
			$airport_city=pg_result($res,$target_row,'city');
		}

		pg_free_result($res);
		return Array($airport,$airport_name,$airport_country,$airport_city,$airport_zone);
    } else return Array("----","Unknown",NULL,NULL);
	
}

/*Section of bearing/speed calculation*/
// ------------ distance calculation function ---------------------
   
    //**************************************
    //     
    // Name: Calculate Distance and Radius u
    //     sing Latitude and Longitude in PHP
    // Description:This function calculates 
    //     the distance between two locations by us
    //     ing latitude and longitude from ZIP code
    //     , postal code or postcode. The result is
    //     available in miles, kilometers or nautic
    //     al miles based on great circle distance 
    //     calculation. 
    // By: ZipCodeWorld
    //
    //This code is copyrighted and has
	// limited warranties.Please see http://
    //     www.Planet-Source-Code.com/vb/scripts/Sh
    //     owCode.asp?txtCodeId=1848&lngWId=8    //for details.    //**************************************
    //     
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
    /*:: :*/
    /*:: This routine calculates the distance between two points (given the :*/
    /*:: latitude/longitude of those points). It is being used to calculate :*/
    /*:: the distance between two ZIP Codes or Postal Codes using our:*/
    /*:: ZIPCodeWorld(TM) and PostalCodeWorld(TM) products. :*/
    /*:: :*/
    /*:: Definitions::*/
    /*::South latitudes are negative, east longitudes are positive:*/
    /*:: :*/
    /*:: Passed to function::*/
    /*::lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees) :*/
    /*::lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees) :*/
    /*::unit = the unit you desire for results:*/
    /*::where: 'M' is statute miles:*/
    /*:: 'K' is kilometers (default):*/
    /*:: 'N' is nautical miles :*/
    /*:: United States ZIP Code/ Canadian Postal Code databases with latitude & :*/
    /*:: longitude are available at http://www.zipcodeworld.com :*/
    /*:: :*/
    /*:: For enquiries, please contact sales@zipcodeworld.com:*/
    /*:: :*/
    /*:: Official Web site: http://www.zipcodeworld.com :*/
    /*:: :*/
    /*:: Hexa Software Development Center ?All Rights Reserved 2004:*/
    /*:: :*/
    /*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
function GML_distance($lat1, $lon1, $lat2, $lon2) { 
	if($lat1==$lat2 && $lon1==$lon2)
		return(array(0,0,0,"N"));
    $theta = $lon1 - $lon2; 
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)); 
    $dist = acos($dist); 
    $dist = rad2deg($dist); 
    $miles = $dist * 60 * 1.1515;
	$bearingDeg = (rad2deg(atan2(sin(deg2rad($lon2) - deg2rad($lon1)) * 
	   cos(deg2rad($lat2)), cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - 
	   sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($lon2) - deg2rad($lon1)))) + 360) % 360;

	$bearingWR = GML_direction($bearingDeg);
	
    $km = $miles * 1.609344; 
    $kts = $miles * 0.8684;
	//$miles = round($miles);
	return(array($kts,$km,$bearingDeg,$bearingWR));
}
  
function GML_direction($degrees) {
   // figure out a text value for compass direction
   // Given the direction, return the text label
   // for that value.  16 point compass
   $winddir = $degrees;
   if ($winddir == "n/a") { return($winddir); }

  if (!isset($winddir)) {
    return "---";
  }
  if (!is_numeric($winddir)) {
	return($winddir);
  }
  $windlabel = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S",
	 "SSW","SW", "WSW", "W", "WNW", "NW", "NNW");
  $dir = $windlabel[ fmod((($winddir + 11) / 22.5),16) ];
  return($dir);

} // end function GML_direction	
/*Section of bearing/speed calculation ends*/


?>