<?php

//////////////////////////////////////////////////////////////////////
//
//	flight report for FlightGear
//	(c) 2006 Julien Pierru
//
//	Licensed under GPL
//
//////////////////////////////////////////////////////////////////////

$ERAD=6378138.12; 
$D2R = M_PI / 180;
$R2D = 180 / M_PI;

$M2NM = 1/1852;


class FLIGHT_REPORT
{
	// Variables
	var $alt; //Altitude Array w/ time - unused
	var $wp; //Waypoints Array
	var $gs; /*Array of Array([ground speed][time in epoch])*/
	var $ias; //Air Array w/ time - unused
	var $vs;
	var $distance;
	var $effectiveFlightTime;
	var $maxAlt;
	var $minAlt;
	var $aveAlt;
	var $maxGrdSpd;
	var $minGrdSpd;
	var $aveGrdSpd;
	var $maxMach;
	var $updateFreq;
	var $width;
	var $height;	
	var $waypoints;

	// Methods
	// Constructor
	function FLIGHT_REPORT ()
	{
		/*Threshold: any measurement larger than threshold will report as Threshold unless specified*/
		$this->distance 	= 0.0;
		$this->maxAlt 		= 0.0;
		$this->minAlt		= 99999.0;
		$this->aveAlt 		= 0.0;
		$this->maxGrdSpd 	= 0.0;
		$this->maxGrdSpdThreshold 	= 2000; /*knots*/
		$this->minGrdSpdThreshold 	= 5;/*knots, For use in effectiveFlightTime*/
		$this->maxEffectiveFlightTimeSeparation 	= 60;/*sec, Waypoints over this time separation will not count as effectiveFlightTime*/
		$this->minGrdSpd	= 99999.0;
		$this->aveGrdSpd 	= 0.0;
		$this->maxMach 		= 0.0;
		$this->effectiveFlightTime=0;
		$this->updateFreq 	= 10; 	// number of seconds between updates
		$this->width	 	= 600; 	// pixels
		$this->height	 	= 200; 	// pixels
		$this->waypoints	= 0;
		$this->maxDistanceThreshold 	= 2000*$this->updateFreq; /*maxDistance between 2 way points*/
	}

	// Set the image dimension
	function SetImageDim ( $w, $h )
	{
		$this->width = w;
		$this->height= h;
	}

	// Compute the maximum/minimum altitude reached
	function MaxMinAltitude ()
	{
		foreach ( $this->wp as $value )
		{
			if ( $this->maxAlt < $value[2] )
				$this->maxAlt = $value[2];
			if ( $this->minAlt > $value[2] )
				$this->minAlt = $value[2];
		}
	}

	// Get the maximum altitude reached
	function GetmaxAltitude ()
	{
		return $this->maxAlt;
	}

	// Compute the average altitude - deprecated
	function averageAltitude ()
	{
		$this->aveAlt =-1;
	}

	// Get the average altitude
	function GetaverageAlt ()
	{
		return $this->aveAlt;
	}

	// Compute the maximum/minimum Ground Speed
	function MaxMinGrdSpd ()
	{
		foreach ( $this->gs as $value )
		{
			if ( $this->maxGrdSpd < $value[0] )
				$this->maxGrdSpd = $value[0];
			if ( $this->minGrdSpd > $value[0] )
				$this->minGrdSpd = $value[0];
		}
	}

	// Get the maximum Ground Speed
	function GetmaxGrdSpd ()
	{
		return $this->maxGrdSpd;
	}

	// Compute the average Ground Speed - deprecated
	function averageGrdSpd ()
	{
		$this->aveGrdSpd =-1;
	}

	// Get the average Ground Speed
	function GetaveGrdSpd ()
	{
		return $this->aveGrdSpd;
	}

	// Compute the maximum Mach number reached - deprecated
	function maxMach ()
	{
		/*$alt_index = 0;
		foreach ( $this->ias as $value )
		{
			if ( $this->maxMach < $value )
			{
				$this->maxMach = $value;
			}
			$alt_index++;
		}
		$a = $this->SpeedofSound ( $this->alt[$alt_index] );
		$this->maxMach = ( $this->maxMach*1852/3600 )/$a;*/
		$this->maxMach =-1;
	}

	// Get the maximum Mach number reached
	function GetmaxMach ()
	{
		return $this->maxMach;
	}

	// Get the total distance of the flight
	function Getdistance ()
	{
		return $this->distance;
	}

	function EffectiveFlightTime() /*total time that speed over 2ms^-1*/
	{
		$i=0;
		foreach ( $this->gs as $gs )
		{		
			if ($i!=0 and $this->minGrdSpdThreshold < $gs[0] and $gs[1]-$previous_ts<$this->maxEffectiveFlightTimeSeparation)
				$this->effectiveFlightTime+=$gs[1]-$previous_ts;
			$previous_ts=$gs[1];
			$i++;
		}
	}
	
	function GeteffectiveFlightTime()
	{
		$this->EffectiveFlightTime();
		return $this->effectiveFlightTime;
	}
	
	
	// Compute the speed of sound according to the standard atmosphere model
	function SpeedofSound ( $altitude )
	{
		if ( $altitude <= 10000.0 )
		{
			$T = ((288.15-223.15)/(0.0-10000.0) * $altitude) + 288.15;
			$a = sqrt (1.4*287*$T);
		}
		else
		{
			$T = ((223.15-270.65)/(10000.0-50000.0) * $altitude) + 223.15;
			$a = sqrt (1.4*287*$T);
		}
		return $a;
	}

	// Compute the distance between two waypoints (meters)
	function computeDistance($latA,$lonA,$latB,$lonB)
	{
		global $ERAD,$D2R,$R2D;

		$latA*=$D2R;
		$lonA*=$D2R;
		$latB*=$D2R;
		$lonB*=$D2R;
		
		$distance=$ERAD*2*asin(sqrt(pow(sin(($latA-$latB)/2),2) + cos($latA)*cos($latB)*pow(sin(($lonA-$lonB)/2),2)));

		return $distance;
	}


	// Create the altitude history image
	function CreateAltHistory ()
	{
		// Image stuff
		$image = imagecreate ( $this->width, $this->height );
		$white = imagecolorallocate ( $image, 255, 255, 255 );
		$blue = imagecolorallocate ( $image, 0, 0, 255 );
		$black = imagecolorallocate ( $image, 0, 0, 0 );
		$font = 1;

		// Get the data points
		$deltaAlt = $this->maxAlt - $this->minAlt;
		
		$alt1 = $this->wp[0][2];
		if ($deltaAlt==0)
			$y1 = 10; // conversion to image coordinate system
		else
			$y1 = ( $this->height - 20 ) * ( ( $deltaAlt-( $alt1-$this->minAlt ) )/$deltaAlt ) +10; // conversion to image coordinate system
		
		for ( $i=1 ; $i<$this->waypoints ; $i++ )
		{
			$alt2 = $this->wp[$i][2];
			if ($deltaAlt==0)
				$y2 = 10;
			else
				$y2 = ( $this->height - 20 ) * ( ( $deltaAlt-( $alt2-$this->minAlt ) )/$deltaAlt ) +10;
			imageline ( $image, ( $i )*( $this->width/$this->waypoints ), $y1,
					( $i+1 )*( $this->width/$this->waypoints ), $y2, $blue );
			$alt1 = $alt2;
			$y1 = $y2;
		}

		// Make the axes
		imageline ( $image, $this->width/$this->waypoints, $this->height, $this->width/$this->waypoints, 0, $black );
		imageline ( $image, 0, $this->height - 10, $this->width, $this->height - 10, $black );
		imagestring ( $image, $font, 5, $this->height - 15, sprintf( "%d ft",$this->minAlt), $black );
		imagestring ( $image, $font, 5, 15, sprintf( "%d ft",$this->maxAlt), $black );
		imagestring ( $image, $font, 5, ( $this->height - 10 )/2 +10,
				 sprintf( "%d ft",( $this->maxAlt - $this->minAlt )/2 +$this->minAlt ), $black );
		imageline ( $image, $this->width/$this->waypoints-5, 10, $this->width/$this->waypoints+5, 10, $black );
		imageline ( $image, $this->width/$this->waypoints-5, ( $this->height - 10 )/2 +5,
				 $this->width/$this->waypoints+5, ( $this->height - 10 )/2 +5, $black );
		//for ( $i=2 ; $i<$this->waypoints ; $i++ )
		//{
		//	imageline ( $image, ( $i )*( $this->width/$this->waypoints ), $this->height - 12,
		//			( $i )*( $this->width/$this->waypoints ), $this->height - 8, $black );
		//}

		// Save the image to a file
		//imagegif ( $image, "AltHistory.gif" );
		imagegif ( $image );
	}

	// Create the ground speed history image
	function CreateGrdSpdHistory ()
	{
		// Image stuff
		$image2 = imagecreate ( $this->width, $this->height );
		$white = imagecolorallocate ( $image2, 255, 255, 255 );
		$blue = imagecolorallocate ( $image2, 0, 0, 255 );
		$black = imagecolorallocate ( $image2, 0, 0, 0 );
		$font = 1;

		// Get the data points
		$deltaGrdSpd = $this->maxGrdSpd - $this->minGrdSpd;
		$gs1 = $this->gs[0][0];
		if ($deltaGrdSpd==0)
			$y1 = 10; // conversion to image coordinate system
		else
			$y1 = ( $this->height - 20 ) * ( ( $deltaGrdSpd-( $gs1-$this->minGrdSpd ) )/$deltaGrdSpd ) +10; // conversion to image coordinate system
		for ( $i=1 ; $i<($this->waypoints-1) ; $i++ )
		{
			$gs2 = $this->gs[$i][0];
			if ($deltaGrdSpd==0)
				$y2 = 10;
			else
				$y2 = ( $this->height - 20 ) * ( ( $deltaGrdSpd-( $gs2-$this->minGrdSpd ) )/$deltaGrdSpd ) +10;
			imageline ( $image2, ( $i )*( $this->width/$this->waypoints ), $y1,
					( $i+1 )*( $this->width/$this->waypoints ), $y2, $blue );
			$gs1 = $gs2;
			$y1 = $y2;
		}

		// Make the axes
		imageline ( $image2, $this->width/$this->waypoints, $this->height, $this->width/$this->waypoints, 0, $black );
		imageline ( $image2, 0, $this->height - 10, $this->width, $this->height - 10, $black );
		imagestring ( $image2, $font, 5, $this->height - 15, sprintf( "%d kts",$this->minGrdSpd ), $black );
		imagestring ( $image2, $font, 5, 15, sprintf( "%d kts",$this->maxGrdSpd ), $black );
		imagestring ( $image2, $font, 5, ( $this->height - 10 )/2 +10,
				 sprintf( "%d kts",( $this->maxGrdSpd - $this->minGrdSpd )/2 +$this->minGrdSpd ), $black );
		imageline ( $image2, $this->width/$this->waypoints-5, 10, $this->width/$this->waypoints+5, 10, $black );
		imageline ( $image2, $this->width/$this->waypoints-5, ( $this->height - 10 )/2 +5,
				 $this->width/$this->waypoints+5, ( $this->height - 10 )/2 +5, $black );
		//for ( $i=2 ; $i<($this->waypoints-1) ; $i++ )
		//{
		//	imageline ( $image, ( $i )*( $this->width/$this->waypoints ), $this->height - 12,
		//			( $i )*( $this->width/$this->waypoints ), $this->height - 8, $black );
		//}

		// Save the image to a file
		//imagegif ( $image, "GrdSpdHistory.gif" );
		imagegif ( $image2 );
	}

	// Create the flight report. Return Array(is_success, is_identical_if_multiple_wpts_at_the_same_time,comments) of results. 
	// NOTE: FGTrackersync need to be altered if there are multiple reasons this function return false.
	function MakeFlightReport ( $fpArray, $plot )
	{
		/*
		$fpArray: Array of waypoints with format Array($lat,$lon,$alt,$time). Alt in ft and time in Unix sec.
		$plot: Accept "AltHistory" or "GrdSpdHistory" otherwise no gif diagram will be created.		
		*/
		
		
		/*check if empty array*/
		$this->waypoints = count($fpArray);
		if($this->waypoints<=1)
		{
			$this->gs[]=Array(0,0);
			return Array(false,NULL,"Empty array feeded in");
		}
		$this->wp=$fpArray;

		// Make the ground speed array
		/*First*/
		$this->gs[] = Array(0,$fpArray[0][3]);
		/*there after*/
		for ($i=0;$i<$this->waypoints-1;$i++)
		{
			$d = $this->computeDistance ( $fpArray[$i][0], $fpArray[$i][1], $fpArray[$i+1][0], $fpArray[$i+1][1] );
			$this->distance += $d; // Sum the total distance
			
			if ($fpArray[$i+1][3]-$fpArray[$i][3]==0)
			{
				if($fpArray[$i+1][0]-$fpArray[$i][0]==0 and $fpArray[$i+1][1]-$fpArray[$i][1]==0 and $fpArray[$i+1][2]-$fpArray[$i][2]==0)
					$check_str="wpts identical.";
				else
					$check_str="wpts NOT identical.(".$fpArray[$i+1][0]."/".$fpArray[$i][0].", ".$fpArray[$i+1][1]."/".$fpArray[$i][1].", ".$fpArray[$i+1][2]."/".$fpArray[$i][2].")";
				$comments= "Divided by 0 at wpt# $i (time:".$fpArray[$i+1][3]."/".$fpArray[$i][3].") during making of flight report. $check_str";
				$this->gs[] = Array();
				
				if($check_str=="wpts identical.")
					return Array(false,true,$comments);
				else
					return Array(false,false,$comments);
			}
			$gs=$d/($fpArray[$i+1][3]-$fpArray[$i][3]) * (3600/1852); // units = knots
			
			if ($gs>$this->maxGrdSpdThreshold)
				$gs=$this->maxGrdSpdThreshold;
			$this->gs[] = Array($gs,$fpArray[$i+1][3]);
		}
		
		// set up variable
		$this->MaxMinAltitude ();
		$this->averageAltitude ();
		$this->MaxMinGrdSpd ();
		$this->averageGrdSpd ();

		// Make the plots
		if ( $plot == "AltHistory")
			$this->CreateAltHistory ();

		if ( $plot == "GrdSpdHistory")
			$this->CreateGrdSpdHistory ();
		return Array(true,NULL,"Succeed");
	} // MakeFlightReport
} // class	

?>
