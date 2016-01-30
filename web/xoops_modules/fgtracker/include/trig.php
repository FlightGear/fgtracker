<?php

$ERAD=6378138.12; 
$D2R = M_PI / 180;
$R2D = 180 / M_PI;

$M2NM = 1/1852;


function ll2xyz($lat,$lon)
{
 	global $ERAD,$D2R,$R2D;

        $lonr = $lon * $D2R;
        $latr = $lat * $D2R;

        $cosphi = cos($latr);
        $x = $cosphi * cos($lonr);
        $y = $cosphi * sin($lonr);
        $z = sin($latr);
        return array( "x" => $x, "y" => $y, "z" => $z);
}

function llll2dir($latA,$lonA,$latB,$lonB)
{
 	global $ERAD,$D2R,$R2D;

	$latA*=$D2R;
	$lonA*=$D2R;
	$latB*=$D2R;
	$lonB*=$D2R;

	$xdist = sin($lonB - $lonA) * $ERAD * cos(($latA + $latB) / 2);
	$ydist = sin($latB - $latA) * $ERAD;
	$dir = atan2($xdist, $ydist) * $R2D;
	if ($dir<0) $dir += 360;
	
	return ($dir);
}

function llll2dist($latA,$lonA,$latB,$lonB)
{
        global $ERAD,$D2R,$R2D;

        $latA*=$D2R;
        $lonA*=$D2R;
        $latB*=$D2R;
        $lonB*=$D2R;

	return ($ERAD*2*asin(sqrt(pow(sin(($latA-$latB)/2),2) + 
                 cos($latA)*cos($latB)*pow(sin(($lonA-$lonB)/2),2))) );
}
?>
