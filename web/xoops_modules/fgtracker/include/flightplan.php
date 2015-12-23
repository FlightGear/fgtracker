<?php

///////////////////////////////////////////////////////////////////////
// SELECT_CALLSIGN
///////////////////////////////////////////////////////////////////////
  function show_flight_plan($conn,$fpid)
  {
    global $xoopsTpl;
    $res=pg_query($conn,"SELECT fp.seq AS seq, f.name AS name, f.latitude AS lat, f.longitude AS lon FROM flight_plans fp INNER JOIN fixes AS f ON (f.name=fp.fix_name) WHERE fp.id=$fpid ORDER BY fp.seq");
    $nr=pg_num_rows($res);

    $lat=0;
    $lon=0;

    for($i=0;$i<$nr;$i++)
    {
      $flightplan['name']=pg_result($res,$i,"name");
      $flightplan['lon']=pg_result($res,$i,"lon");
      $flightplan['lat']=pg_result($res,$i,"lat");
      $flightplan['seq']=pg_result($res,$i,"seq");

      $lat+=$flightplan['lat'];
      $lon+=$flightplan['lon'];

      if ($i==0)
      {
        $xoopsTpl->assign('deplat',$flightplan['lat']);
        $xoopsTpl->assign('deplon',$flightplan['lon']);
      }
      else if ($i==$nr-1)
      {
        $xoopsTpl->assign('arrlat',$flightplan['lat']);
        $xoopsTpl->assign('arrlon',$flightplan['lon']);
      }

      $xoopsTpl->append('flightplan', $flightplan);
    }
    pg_free_result($res);

    if ($nr!=0)
    {
      $lat/=$nr;
      $lon/=$nr;
    }

    $xoopsTpl->assign('centerlat',$lat);
    $xoopsTpl->assign('centerlon',$lon);
    
  }

