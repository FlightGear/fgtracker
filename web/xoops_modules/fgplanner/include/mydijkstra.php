<?php

  $INF=2000000000;

  function dijkstra($nr,$w,$dep,$arr,$mind,$maxd)
  {
    global $INF;

    for ($i=0;$i<$nr;$i++)
    {
      $Q[$i]=$i;
      $d[$i]=$INF;
      $prev[$i]=-1;
    }

    $d[$dep]=0;

    $S=array();

    while (count($Q)>0) 
    {
      $du=$INF;
      foreach ($Q as $key => $value)
      {
        if ($d[$value]<$du)
        {
          $du=$d[$value];
          $u=$value;
          $delkey=$key;
        }
      }

      if ($du==$INF) return (array());

      unset($Q[$delkey]);
      $X=array_values($Q);
      $Q=$X;

      $S[]=$u;
      for ($v=0;$v<$nr;$v++)
      {
        if ($w[$u][$v]<$INF)
        {
	  $weight=$w[$u][$v];
      
          if ($weight<$mind || $weight>$maxd) $weight*=10;

          if ($d[$v]>$d[$u]+$weight)
          {
            $d[$v]=$d[$u]+$weight;
            $prev[$v]=$u;
          }
        }
      }
    }

    $x=$arr;

    while ($x>=0)
    {
      $P[]=$x;
      $x=$prev[$x];
    }

    return(array_reverse($P));
  }
?>
