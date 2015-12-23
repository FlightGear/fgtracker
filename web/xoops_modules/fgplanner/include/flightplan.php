<?php
///////////////////////////////////////////////////////////////////////
// DIRECT_PLAN
///////////////////////////////////////////////////////////////////////
function direct_plan($conn,$departure,$deprwy,$arrival,$arrrwy)
{
  global $xoopsTpl,$M2NM,$INF;


  $res=pg_query($conn,"SELECT a.icao||'/'||r.runway AS name, r.latitude AS lat, r.longitude AS lon FROM runways AS r INNER JOIN airports AS a ON (a.id=r.airport_id) WHERE a.icao='$departure' AND r.runway='$deprwy'");

  $departure=pg_result($res,0,'name');
  $lat=pg_result($res,0,'lat');
  $lon=pg_result($res,0,'lon');

  pg_free_result($res);

  $deplat=sprintf("%8.5f",$lat);
  $deplon=sprintf("%8.5f",$lon);

    
  $res=pg_query($conn,"SELECT a.icao||'/'||r.runway AS name, r.latitude AS lat, r.longitude AS lon FROM runways AS r INNER JOIN airports AS a ON (a.id=r.airport_id) WHERE a.icao='$arrival' AND r.runway='$arrrwy'");

  $arrival_name=pg_result($res,0,'name');
  $lat=pg_result($res,0,'lat');
  $lon=pg_result($res,0,'lon');

  pg_free_result($res);


  $arrlat=sprintf("%8.5f",$lat);
  $arrlon=sprintf("%8.5f",$lon);

    
  $xoopsTpl->assign('departure',$departure);
  $xoopsTpl->assign('arrival',$arrival_name);
  

  $xoopsTpl->assign('deplat',$deplat);
  $xoopsTpl->assign('deplon',$deplon);
  $xoopsTpl->assign('arrlat',$arrlat);
  $xoopsTpl->assign('arrlon',$arrlon);

  
  $res=pg_query($conn,"SELECT r.llz_name AS llz_name, r.llz_freq AS llz_freq, r.llz_ilt AS llz_ilt FROM runways AS r INNER JOIN airports AS a ON (a.id=r.airport_id) WHERE a.icao='$arrival' and r.llz_freq>0");

  $nr=pg_num_rows($res);

  for($i=0;$i<$nr;$i++)
  {
	$llz[name]=pg_result($res,$i,'llz_name');
	$llz[freq]=pg_result($res,$i,'llz_freq');
	$llz[ilt]=pg_result($res,$i,'llz_ilt');

	$xoopsTpl->append('llz', $llz);
  }
  pg_free_result($res);

  $direction=llll2dir($deplat,$deplon,$arrlat,$arrlon);

  $direction=round($direction);
  $xoopsTpl->assign('direction',$direction);

  $distance=llll2dist($deplat,$deplon,$arrlat,$arrlon);
  $distance=round($distance*$M2NM);
  $xoopsTpl->assign('distance',$distance);

  $navaids[0][ilt]=$departure;
  $navaids[0][lat]=$deplat;
  $navaids[0][lon]=$deplon;
  $navaids[0][type]="departure";
  $navaids[0][freq]="";

  $navaids[1][ilt]=$arrival_name;
  $navaids[1][lat]=$arrlat;
  $navaids[1][lon]=$arrlon;
  $navaids[1][type]="arrival";
  $navaids[1][freq]="";


  $clat=0;
  $clon=0;

  $x=0;
  foreach ( array(0,1) as $step)
  {
     $wpt[$x][ilt]=$navaids[$step][ilt];
     $wpt[$x][lat]=$navaids[$step][lat];
     $wpt[$x][lon]=$navaids[$step][lon];
     $wpt[$x][type]=$navaids[$step][type];
     $wpt[$x][freq]=$navaids[$step][freq];

     $clat+=$navaids[$step][lat];
     $clon+=$navaids[$step][lon];
     
     if ($x>0)
     {
       $wpt[$x][dist]=sprintf("%5.0f",llll2dist($wpt[$x-1][lat],$wpt[$x-1][lon],$wpt[$x][lat],$wpt[$x][lon])*$M2NM);
       $wpt[$x][dir]=sprintf("%5.0f",llll2dir($wpt[$x-1][lat],$wpt[$x-1][lon],$wpt[$x][lat],$wpt[$x][lon]));
     } else
     {
       $wpt[$x][dist]="N/A";
       $wpt[$x][dir]="N/A";
     }
     $xoopsTpl->append('flightplan', $wpt[$x]);
     $x++;
  }

  if ($x>0)
  {
    $clat/=$x;
    $clon/=$x;
  }

  $xoopsTpl->assign('centerlat',$clat);
  $xoopsTpl->assign('centerlon',$clon);

}

///////////////////////////////////////////////////////////////////////
// NAVAID_PLAN
///////////////////////////////////////////////////////////////////////
function navaid_plan($conn,$departure,$deprwy,$arrival,$arrrwy,$vor,$ndb,$fix,$mind,$maxd)
{
  global $xoopsTpl,$M2NM,$INF;


  // New section
  $ntyplist="-121";

  if ($vor=="on")
  {
    $ntyplist.=",3";
  }
  if ($ndb=="on")
  {
    $ntyplist.=",2";
  }
  if ($fix=="on")
  {
    $ntyplist.=",999";
  }

  $resx=pg_query($conn,"SELECT id,status FROM routes WHERE dep_airport_id=(SELECT id FROM airports WHERE icao='$departure') and dep_runway='$deprwy' and arr_airport_id=(SELECT id FROM airports WHERE icao='$arrival') and arr_runway='$arrrwy' and types='$ntyplist' and mind>=$mind and maxd<=$maxd");

  $nr=pg_num_rows($resx);

  if ($nr==0)
  {
    $resy=pg_query($conn,"INSERT INTO routes (status,dep_airport_id,dep_runway,arr_airport_id,arr_runway,types,mind,maxd,plan_date) VALUES ('REQUEST',(SELECT id FROM airports WHERE icao='$departure'),'$deprwy',(SELECT id FROM airports WHERE icao='$arrival'),'$arrrwy','$ntyplist',$mind,$maxd,'now');");
  }

  // End new section


  $res=pg_query($conn,"SELECT a.icao||'/'||r.runway AS name, r.latitude AS lat, r.longitude AS lon FROM runways AS r INNER JOIN airports AS a ON (a.id=r.airport_id) WHERE a.icao='$departure' AND r.runway='$deprwy'");

  $departure=pg_result($res,0,'name');
  $lat=pg_result($res,0,'lat');
  $lon=pg_result($res,0,'lon');

  pg_free_result($res);

  $deplat=sprintf("%8.5f",$lat);
  $deplon=sprintf("%8.5f",$lon);
    
  $res=pg_query($conn,"SELECT a.icao||'/'||r.runway AS name, r.latitude AS lat, r.longitude AS lon FROM runways AS r INNER JOIN airports AS a ON (a.id=r.airport_id) WHERE a.icao='$arrival' AND r.runway='$arrrwy'");

  $arrival_name=pg_result($res,0,'name');
  $lat=pg_result($res,0,'lat');
  $lon=pg_result($res,0,'lon');

  pg_free_result($res);

  $arrlat=sprintf("%8.5f",$lat);
  $arrlon=sprintf("%8.5f",$lon);
    
  $xoopsTpl->assign('departure',$departure);
  $xoopsTpl->assign('arrival',$arrival_name);
  

  $xoopsTpl->assign('deplat',$deplat);
  $xoopsTpl->assign('deplon',$deplon);
  $xoopsTpl->assign('arrlat',$arrlat);
  $xoopsTpl->assign('arrlon',$arrlon);

  $res=pg_query($conn,"SELECT r.llz_name AS llz_name, r.llz_freq AS llz_freq, r.llz_ilt AS llz_ilt FROM runways AS r INNER JOIN airports AS a ON (a.id=r.airport_id) WHERE a.icao='$arrival' and r.llz_freq>0");
  
  $nr=pg_num_rows($res);

  for($i=0;$i<$nr;$i++)
  {
	$llz[name]=pg_result($res,$i,'llz_name');
	$llz[freq]=pg_result($res,$i,'llz_freq');
	$llz[ilt]=pg_result($res,$i,'llz_ilt');

	$xoopsTpl->append('llz', $llz);
  }
  pg_free_result($res);

  $direction=llll2dir($deplat,$deplon,$arrlat,$arrlon);

  $direction=round($direction);
  $xoopsTpl->assign('direction',$direction);

  $distance=llll2dist($deplat,$deplon,$arrlat,$arrlon);
  $distance=round($distance*$M2NM);
  $xoopsTpl->assign('distance',$distance);

  $minlat=$deplat<$arrlat?$deplat:$arrlat;
  $maxlat=$deplat>$arrlat?$deplat:$arrlat;
  $minlon=$deplon<$arrlon?$deplon:$arrlon;
  $maxlon=$deplon>$arrlon?$deplon:$arrlon;

  $minlat-=0.5;
  $maxlat+=0.5;
  $minlon-=0.5;
  $maxlon+=0.5;



  //$res=pg_query($conn,"SELECT nt.label AS type, n.freq AS freq, n.id AS id, n.latitude AS lat, n.longitude AS lon, n.ilt AS ilt, n.name AS name FROM navaids AS n INNER JOIN navaid_types AS nt ON (nt.id=n.type) WHERE n.type in ($ntyplist) AND n.latitude BETWEEN $minlat AND $maxlat AND n.longitude BETWEEN $minlon AND $maxlon");
  //$res=pg_query($conn,"SELECT nt.label AS type, n.freq AS freq, n.id AS id, n.latitude AS lat, n.longitude AS lon, n.ilt AS ilt, n.name AS name FROM navaids AS n INNER JOIN navaid_types AS nt ON (nt.id=n.type) WHERE n.type in ($ntyplist)");
  $res=pg_query($conn,"SELECT n.type AS type, n.freq AS freq, n.id AS id, n.latitude AS lat, n.longitude AS lon, n.ilt AS ilt, n.name AS name FROM navaids AS n WHERE n.type in ($ntyplist)");

  $nr=pg_num_rows($res);

  $navaids[0][ilt]=$departure;
  $navaids[0][lat]=$deplat;
  $navaids[0][lon]=$deplon;
  $navaids[0][type]="departure";
  $navaids[0][freq]="";

  $navaids[1][ilt]=$arrival_name;
  $navaids[1][lat]=$arrlat;
  $navaids[1][lon]=$arrlon;
  $navaids[1][type]="arrival";
  $navaids[1][freq]="";

  $j=2;

  for ($i=0;$i<$nr;$i++)
  {
    $id=pg_result($res,$i,'id');
    $ilt=pg_result($res,$i,'ilt');
    $lat=pg_result($res,$i,'lat');
    $lon=pg_result($res,$i,'lon');
    $type=pg_result($res,$i,'type');
    $freq=pg_result($res,$i,'freq');
    $name=pg_result($res,$i,'name');

    switch ($type)
    {
    	case 2:
		$type='NDB';
		break;
	case 3:
		$type=substr($name,strpos($name,'VOR'));
		break;
	case 999:
		$type='FIX';
		break;
    }

    if (1.05*$distance>=(llll2dist($deplat,$deplon,$lat,$lon)+llll2dist($arrlat,$arrlon,$lat,$lon))*$M2NM )
    {
      $navaids[$j][id]=$id;
      $navaids[$j][ilt]=$ilt;
      $navaids[$j][lat]=$lat;
      $navaids[$j][lon]=$lon;
      $navaids[$j][type]=$type;
      $navaids[$j][freq]=sprintf("%5.2f",$freq);
      $navaids[$j][name]=$name;

      $xoopsTpl->append('navaids', $navaids[$j]);
      $j++;
    }
  }
  pg_free_result($res);

  $nr=$j;

  $xoopsTpl->assign('numnavaids',$nr);

  $maxgap=2000;
 
  $path=array();
  

  $x=0;
  for ($i=0;$i<$nr;$i++)
    for ($j=0;$j<$nr;$j++)
    {
      $dist=llll2dist($navaids[$i][lat],$navaids[$i][lon],$navaids[$j][lat],$navaids[$j][lon])*$M2NM;
      if ($dist>$maxgap) $dist=$INF;
      $points[$x]=Array($i,$j,$dist);
      $x++;
    }

  $w = array();
 
  for ($i=0,$m=count($points); $i<$m; $i++)
  {
    $x = $points[$i][0];
    $y = $points[$i][1];
    $c = $points[$i][2];
    $w[$x][$y] = $c;
    $w[$y][$x] = $c;
  }
    
  for ($i=0; $i < $nr; $i++) {
         $w[$i][$i] = 0;
  }
     
  $path=dijkstra($nr,$w,0,1,$mind,$maxd);


  $clat=0;
  $clon=0;

  $x=0;
  foreach ($path as $step)
  {
     $wpt[$x][ilt]=$navaids[$step][ilt];
     $wpt[$x][lat]=$navaids[$step][lat];
     $wpt[$x][lon]=$navaids[$step][lon];
     $wpt[$x][type]=$navaids[$step][type];
     $wpt[$x][freq]=$navaids[$step][freq];
     $wpt[$x][name]=$navaids[$step][name];

     $clat+=$navaids[$step][lat];
     $clon+=$navaids[$step][lon];
     
     if ($x>0)
     {
       $wpt[$x][dist]=sprintf("%5.0f nm",llll2dist($wpt[$x-1][lat],$wpt[$x-1][lon],$wpt[$x][lat],$wpt[$x][lon])*$M2NM);
       $wpt[$x][dir]=sprintf("%5.0f",llll2dir($wpt[$x-1][lat],$wpt[$x-1][lon],$wpt[$x][lat],$wpt[$x][lon]));
     } else
     {
       $wpt[$x][dist]="N/A";
       $wpt[$x][dir]="N/A";
     }
     $xoopsTpl->append('flightplan', $wpt[$x]);
     $x++;
  }

  if ($x>0)
  {
    $clat/=$x;
    $clon/=$x;
  }


  $xoopsTpl->assign('centerlat',$clat);
  $xoopsTpl->assign('centerlon',$clon);
  $xoopsTpl->assign('maxgap',$maxgap);
}

function navplan()
{
}


